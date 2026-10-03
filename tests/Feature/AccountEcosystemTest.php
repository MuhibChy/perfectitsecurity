<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\DirectMessage;
use App\Models\EmployeeAssignment;
use App\Models\Notification;
use App\Models\Salary;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AccountEarningsService;
use App\Services\DirectoryService;
use App\Services\ProfileCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Employee & Customer Account Ecosystem: additive features with strict RBAC.
 * Existing commission/salary lifecycles are reused, never reimplemented.
 */
class AccountEcosystemTest extends TestCase
{
    use RefreshDatabase;

    private function make(string $role, array $over = []): User
    {
        return User::factory()->create(array_merge(['role' => $role, 'is_active' => true], $over));
    }

    /** @test */
    public function profile_completion_scores_core_fields_and_lists_missing()
    {
        $u = $this->make('customer', ['phone' => null, 'address' => null, 'country' => null]);
        $result = app(ProfileCompletionService::class)->for($u);
        $this->assertLessThan(100, $result['percent']);
        $this->assertContains('Contact number', $result['missing']);
        $this->assertContains('Preferred communication method', $result['missing']);

        $u->profileDetail()->create(['preferred_contact_method' => 'email']);
        $u->update(['phone' => '+10000000000', 'address' => '1 Test St', 'country' => 'US', 'avatar' => 'avatars/test.png']);
        $result = app(ProfileCompletionService::class)->for($u->fresh());
        $this->assertSame(100, $result['percent']);
        $this->assertSame([], $result['missing']);
    }

    /** @test */
    public function customer_account_summary_uses_existing_records_only()
    {
        $c = $this->make('customer');
        \App\Models\Payment::create(['customer_id' => $c->id, 'amount' => 500, 'status' => 'completed', 'payment_method' => 'card', 'paid_at' => now()]);
        \App\Models\Invoice::create(['customer_id' => $c->id, 'status' => 'partially_paid', 'subtotal' => 1000, 'total' => 1000, 'amount_paid' => 500, 'amount_due' => 500]);

        $finance = app(AccountEarningsService::class)->forCustomer($c);
        $this->assertSame(500.0, $finance['total_spent']);
        $this->assertSame(500.0, $finance['outstanding']);

        $this->actingAs($c)->get(route('portal.account.summary'))->assertStatus(200);
    }

    /** @test */
    public function employee_earnings_aggregate_salary_and_commission_without_duplicates()
    {
        $e = $this->make('employee');
        Salary::create(['user_id' => $e->id, 'base_salary' => 4000, 'bonus' => 500, 'deductions' => 200, 'net_salary' => 4300, 'period' => 'monthly', 'status' => 'paid']);
        Commission::create(['worker_id' => $e->id, 'commission_type' => 'fixed', 'revenue_amount' => 1000, 'commission_rate' => 10, 'commission_amount' => 100, 'status' => 'paid', 'payment_status' => 'paid']);

        $s = app(AccountEarningsService::class)->forEmployee($e);
        $this->assertSame(4300.0, $s['salary_paid']);
        $this->assertSame(100.0, $s['commission_paid']);
        $this->assertSame(4400.0, $s['total_paid']);

        // Own page shows own figures…
        $this->actingAs($e)->get(route('admin.earnings.show'))->assertStatus(200)->assertSee('4,300');

        // …and never another member's (no id parameter exists to manipulate).
        $other = $this->make('employee');
        Salary::create(['user_id' => $other->id, 'base_salary' => 99999, 'bonus' => 0, 'deductions' => 0, 'net_salary' => 99999, 'period' => 'monthly', 'status' => 'paid']);
        $this->actingAs($e)->get(route('admin.earnings.show'))->assertStatus(200)->assertDontSee('99,999');
    }

    /** @test */
    public function compensation_is_finance_gated_and_audited()
    {
        $finance = $this->make('finance_manager');
        $employee = $this->make('employee');

        $this->actingAs($employee)->get(route('admin.ecosystem.compensation.edit', $employee))->assertStatus(403);

        $this->actingAs($finance)
            ->put(route('admin.ecosystem.compensation.update', $employee), [
                'has_salary' => '1', 'salary_amount' => 3000, 'salary_frequency' => 'monthly',
                'has_commission' => '1', 'commission_type' => 'percentage', 'commission_value' => 10,
                'status' => 'active',
            ])->assertSessionHasNoErrors();

        $this->assertSame('Salary + Commission', $employee->fresh()->compensation->modelLabel());
        $this->assertTrue(AuditLog::where('action', 'compensation.updated')->exists());
        $this->assertTrue(Notification::where('notifiable_id', $employee->id)->where('type', 'compensation_updated')->exists());
    }

    /** @test */
    public function assignments_preserve_history_and_notify()
    {
        $manager = $this->make('project_manager');
        $employee = $this->make('employee');
        $customer = $this->make('customer');

        $svc = app(\App\Services\AssignmentService::class);
        $first = $svc->assign($manager, $employee, 'customer', $customer->id, 'Onboarding');
        $second = $svc->assign($manager, $employee, 'customer', $customer->id, 'Re-scoped');

        $this->assertSame('completed', $first->fresh()->status);
        $this->assertSame('active', $second->fresh()->status);
        $this->assertSame(2, EmployeeAssignment::where('employee_id', $employee->id)->count());
        $this->assertTrue(AuditLog::where('action', 'assignment.created')->where('auditable_id', $second->id)->exists());
        $this->assertTrue(Notification::where('notifiable_id', $employee->id)->where('type', 'assignment_created')->exists());

        // Customers can never receive staff assignments.
        try {
            $svc->assign($manager, $customer, 'customer', $customer->id);
            $this->fail('Expected rejection for customer assignment.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }

    /** @test */
    public function messages_link_to_owned_work_only()
    {
        $a = $this->make('customer');
        $b = $this->make('customer');
        $staff = $this->make('support_agent');
        $ticket = Ticket::create(['customer_id' => $b->id, 'subject' => 'Mine', 'description' => 'x', 'priority' => 'medium']);

        // B links own ticket: ok.
        $this->actingAs($b)->post(route('portal.messages.store'), [
            'recipient_id' => $staff->id, 'body' => 'About my ticket', 'related_type' => 'ticket', 'related_id' => $ticket->id,
        ])->assertSessionHasNoErrors();
        $this->assertNotNull(DirectMessage::where('sender_id', $b->id)->where('related_id', $ticket->id)->first());

        // A links B's ticket: forbidden.
        $this->actingAs($a)->post(route('portal.messages.store'), [
            'recipient_id' => $staff->id, 'body' => 'Hijack', 'related_type' => 'ticket', 'related_id' => $ticket->id,
        ])->assertStatus(403);
    }

    /** @test */
    public function call_logs_respect_visibility_and_notify_missed_calls()
    {
        $customer = $this->make('customer');
        $agent = $this->make('support_agent');
        $calls = app(\App\Services\CallLogService::class);

        $visible = $calls->log($agent, $agent, $customer, ['outcome' => 'completed', 'duration_seconds' => 120, 'is_customer_visible' => true]);
        $hidden = $calls->log($agent, $agent, $customer, ['outcome' => 'completed', 'is_customer_visible' => false]);
        $this->assertTrue($hidden->is_customer_visible); // forced visible: customer participates

        $internal = $calls->log($agent, $agent, $this->make('support_agent'), ['outcome' => 'completed', 'is_customer_visible' => false]);
        $mine = $calls->forUser($customer)->getCollection();
        $this->assertTrue($mine->contains(fn ($c) => $c->id === $visible->id));
        $this->assertFalse($mine->contains(fn ($c) => $c->id === $internal->id));

        $calls->log($agent, $agent, $customer, ['outcome' => 'missed']);
        $this->assertTrue(Notification::where('notifiable_id', $customer->id)->where('type', 'call_missed')->exists());

        // Customer-to-customer calls are forbidden.
        $this->assertFalse($calls->canCall($customer, $this->make('customer')));
    }

    /** @test */
    public function directory_hides_direct_contact_without_working_relationship()
    {
        $customer = $this->make('customer', ['phone' => '+10000000001']);
        $agent = $this->make('support_agent', ['phone' => '+10000000002']);
        $dir = app(DirectoryService::class);

        $this->assertFalse($dir->canSeeDirectContact($customer, $agent));
        $this->actingAs($customer)->get(route('portal.directory.show', $agent))->assertStatus(200);

        Ticket::create(['customer_id' => $customer->id, 'subject' => 'Help', 'description' => 'x', 'priority' => 'medium', 'assigned_to' => $agent->id]);
        $this->assertTrue($dir->canSeeDirectContact($customer, $agent));
    }

    /** @test */
    public function portal_search_is_scoped_to_own_records()
    {
        $a = $this->make('customer');
        $b = $this->make('customer');
        $ticket = Ticket::create(['customer_id' => $b->id, 'ticket_number' => 'T-SECRET-1', 'subject' => 'Secret', 'description' => 'x', 'priority' => 'medium']);

        // Non-owner finds no record row (the query itself echoes in the search box).
        $this->actingAs($a)->get(route('portal.search.index', ['q' => 'T-SECRET-1']))
            ->assertStatus(200)->assertSee('No matching records.');

        $this->actingAs($b)->get(route('portal.search.index', ['q' => 'T-SECRET-1']))
            ->assertStatus(200)->assertSee('Ticket · T-SECRET-1');
    }
}
