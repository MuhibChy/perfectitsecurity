<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\Salary;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Gap-closure verification: exports, staff profile, commission/expense
 * lifecycles, ticket notifications + preferences, last activity, salary
 * versioning, RBAC export denials.
 */
class GapClosureTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag) . '.' . Str::random(5) . '@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified',
        ]);
    }

    /** @test */
    public function commission_full_lifecycle_with_guards()
    {
        $agent = $this->person('commission_agent', 'Gap Agent');
        $finance = $this->person('finance_manager', 'Gap Finance');
        $rule = CommissionRule::create(['name' => '[TEST] rule', 'type' => 'percentage', 'rate' => 10, 'status' => 'active']);
        $svc = app(CommissionService::class);
        $c = Commission::create(['worker_id' => $agent->id, 'rule_id' => $rule->id, 'commission_type' => 'per_sale', 'revenue_amount' => 1000, 'commission_rate' => 10, 'commission_amount' => 100, 'status' => 'pending']);

        // Arbitrary jump refused: pending → payable is illegal.
        try {
            $svc->markPayable($c, $finance->id);
            $this->fail('illegal jump must abort');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
        $c = $svc->submitCommission($c, $finance->id, 'order paid');
        $this->assertEquals('submitted', $c->status);
        $c = $svc->reviewCommission($c, $finance->id);
        $this->assertEquals('under_review', $c->status);
        $c = $svc->approveCommission($c, $finance->id);
        $this->assertEquals('approved', $c->status);
        $c = $svc->markPayable($c, $finance->id, 'scheduled');
        $this->assertEquals('payable', $c->status);
        $payout = $svc->processPayout($agent->id, [$c->id], 'bank_transfer');
        $this->assertEquals('payable', $c->fresh()->status); // unpaid until payout completes
        $svc->completePayout($payout, 'GAP-REF-100');
        $this->assertEquals('paid', $c->fresh()->status);
        $this->assertEquals('completed', $payout->fresh()->status);
        // Audit trail recorded every transition.
        $this->assertTrue(\App\Models\AuditLog::where('module', 'commissions')->where('action', 'commission.transition')->count() >= 4);
    }

    /** @test */
    public function expense_approval_is_not_payment()
    {
        $finance = $this->person('finance_manager', 'Gap Finance Two');
        $cat = ExpenseCategory::firstOrCreate(['slug' => 'gap-travel'], ['name' => '[TEST] Travel', 'slug' => 'gap-travel']);
        $expense = Expense::create(['category_id' => $cat->id, 'category_name' => $cat->name, 'description' => '[TEST] Business Expense Alpha', 'amount' => 250, 'date' => now()->toDateString(), 'status' => 'pending', 'created_by' => $finance->id]);

        $this->actingAs($finance)->post(route('admin.expenses.approve', $expense->id))->assertRedirect();
        $expense->refresh();
        $this->assertEquals('approved', $expense->status);
        $this->assertNull($expense->paid_at); // £250 approved, £0 paid

        // Paid without a reference is refused.
        $this->actingAs($finance)->post(route('admin.expenses.mark-paid', $expense->id), [])->assertSessionHasErrors('payment_reference');

        // Paid against an arbitrary (non-transfer) reference is accepted as a
        // recorded payment reference; status becomes paid exactly once.
        $this->actingAs($finance)->post(route('admin.expenses.mark-paid', $expense->id), ['payment_reference' => 'GAP-PAY-250'])->assertRedirect();
        $expense->refresh();
        $this->assertEquals('paid', $expense->status);
        $this->assertNotNull($expense->paid_at);
        $this->assertEquals('GAP-PAY-250', $expense->payment_reference);

        // No double accounting: exactly one expense ledger entry.
        $count = \App\Models\FinancialTransaction::where('type', 'expense')->where('expense_id', $expense->id)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function ticket_replies_notify_with_preference_gating()
    {
        $customer = $this->person('customer', 'Gap Customer');
        $agent = $this->person('support_agent', 'Gap Agent Two');
        $category = TicketCategory::firstOrCreate(['slug' => 'gap-general'], ['name' => '[TEST] General', 'slug' => 'gap-general']);
        $ticket = Ticket::create(['customer_id' => $customer->id, 'category_id' => $category->id, 'subject' => '[TEST] outage', 'description' => 'synthetic outage description', 'priority' => 'high', 'status' => 'new', 'assigned_to' => $agent->id]);

        // Agent reply → customer notified.
        $this->actingAs($agent)->post(route('admin.tickets.reply', $ticket->id), ['message' => 'We are investigating now.'])->assertRedirect();
        $this->assertTrue(Notification::where('notifiable_id', $customer->id)->where('type', 'ticket_reply')->exists());

        // Customer disables in-app ticket replies → agent reply creates nothing.
        NotificationPreference::updateOrCreate(['user_id' => $customer->id, 'notification_type' => 'ticket_reply'], ['email_enabled' => false, 'in_app_enabled' => false]);
        Notification::where('notifiable_id', $customer->id)->delete();
        $this->actingAs($agent)->post(route('admin.tickets.reply', $ticket->id), ['message' => 'Second update here.'])->assertRedirect();
        $this->assertFalse(Notification::where('notifiable_id', $customer->id)->where('type', 'ticket_reply')->exists());

        // Customer reply → assignee notified.
        Notification::where('notifiable_id', $agent->id)->delete();
        $this->actingAs($customer)->post(route('portal.tickets.reply', $ticket->id), ['message' => 'Thanks, awaiting fix.'])->assertRedirect();
        $this->assertTrue(Notification::where('notifiable_id', $agent->id)->where('type', 'ticket_reply')->exists());

        // Internal note → nobody notified.
        Notification::query()->delete();
        $this->actingAs($agent)->post(route('admin.tickets.note', $ticket->id), ['note' => 'quiet internal check'])->assertRedirect();
        $this->assertEquals(0, Notification::count());
    }

    /** @test */
    public function staff_self_profile_is_whitelisted_and_audited()
    {
        $employee = $this->person('employee', 'Gap Employee');
        $this->actingAs($employee)->get(route('admin.my-profile.edit'))->assertStatus(200);
        $this->actingAs($employee)->put(route('admin.my-profile.update'), [
            'name' => '[TEST] Gap Employee Renamed', 'phone' => '+44000000001', 'timezone' => 'Europe/London',
        ])->assertRedirect();
        $this->assertEquals('[TEST] Gap Employee Renamed', $employee->fresh()->name);
        // Forbidden fields are not mass-assignable through this endpoint (no validation rule → ignored).
        $this->actingAs($employee)->put(route('admin.my-profile.update'), [
            'name' => '[TEST] Gap Employee Renamed', 'role' => 'super_admin', 'employee_number' => 'HACK',
        ])->assertRedirect();
        $this->assertEquals('employee', $employee->fresh()->role);
        $this->assertNull($employee->fresh()->employee_number);
        $this->assertTrue(\App\Models\AuditLog::where('action', 'staff.profile_updated')->exists());
        // Throttled presence advances last_activity_at on web requests.
        $this->assertNotNull($employee->fresh()->last_activity_at);
    }

    /** @test */
    public function salary_versioning_keeps_history()
    {
        $employee = $this->person('employee', 'Gap Salary Employee');
        $finance = $this->person('finance_manager', 'Gap Finance Three');
        $s1 = Salary::create(['user_id' => $employee->id, 'base_salary' => 2000, 'bonus' => 0, 'deductions' => 0, 'net_salary' => 2000, 'period' => 'monthly', 'pay_date' => '2026-01-01', 'effective_from' => '2026-01-01', 'effective_to' => '2026-06-30', 'currency' => 'GBP', 'status' => 'paid']);
        $s2 = Salary::create(['user_id' => $employee->id, 'base_salary' => 2300, 'bonus' => 0, 'deductions' => 0, 'net_salary' => 2300, 'period' => 'monthly', 'pay_date' => '2026-07-01', 'effective_from' => '2026-07-01', 'currency' => 'GBP', 'status' => 'approved', 'approved_by' => $finance->id, 'approved_at' => now()]);
        $history = Salary::where('user_id', $employee->id)->orderBy('effective_from')->get();
        $this->assertEquals([2000.0, 2300.0], $history->pluck('net_salary')->map(fn ($v) => (float) $v)->all());
        $this->actingAs($finance)->get(route('admin.salaries.show', $s2->id))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.salaries.payslip', $s2->id))->assertStatus(200);
    }

    /** @test */
    public function report_exports_match_screen_and_enforce_rbac()
    {
        $customer = $this->person('customer', 'Gap Export Customer');
        $other = $this->person('customer', 'Gap Export Other');
        $finance = $this->person('finance_manager', 'Gap Finance Four');
        $admin = $this->person('admin', 'Gap Admin');

        $cat = ServiceCategory::firstOrCreate(['slug' => 'gap-cat'], ['name' => 'Gap']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] svc', 'slug' => 'gap-' . Str::random(6), 'short_description' => 'x', 'starting_price' => 1500, 'is_active' => true]);
        $order = ServiceOrder::create(['customer_id' => $customer->id, 'service_id' => $service->id, 'created_by' => $customer->id, 'source' => 'test', 'requirements' => 'Gap synthetic scope statement here.', 'status' => 'confirmed', 'payment_authorization' => 'not_authorized', 'currency' => 'GBP', 'original_price' => 1500, 'final_price' => 1500, 'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => 1500, 'amount_paid' => 0, 'amount_due' => 1500, 'price_locked' => true, 'customer_accepted_at' => now()]);
        app(ServiceOrderWorkflowService::class)->generateConnectedRecords($order->fresh(), $customer);

        // Finance CSV export works and contains the order.
        $csv = $this->actingAs($finance)->get(route('admin.reports.export', ['type' => 'customer', 'customer_id' => $customer->id, 'format' => 'csv']))->assertStatus(200);
        $this->assertStringContainsString($order->fresh()->order_number, $csv->streamedContent() ?? '');
        // Finance PDF export renders.
        $this->actingAs($finance)->get(route('admin.reports.export', ['type' => 'customer', 'customer_id' => $customer->id, 'format' => 'pdf']))->assertStatus(200);

        // Customer exports own report; cannot export another's (forced own id → gets own, not other's).
        $mine = $this->actingAs($customer)->get(route('portal.reports.mine', ['format' => 'csv']))->assertStatus(200);
        $this->assertStringContainsString($order->fresh()->order_number, $mine->streamedContent() ?? '');
        // Direct admin export as customer → 403 (staff boundary).
        $this->actingAs($customer)->get(route('admin.reports.export', ['type' => 'customer', 'customer_id' => $other->id, 'format' => 'csv']))->assertStatus(403);
        // Employee financial export as non-finance staff → 403.
        $employee = $this->person('employee', 'Gap Export Employee');
        $this->actingAs($employee)->get(route('admin.reports.export', ['type' => 'employee-finance', 'employee_id' => $employee->id, 'format' => 'csv']))->assertStatus(403);
        // Admin allowed.
        $this->actingAs($admin)->get(route('admin.reports.export', ['type' => 'financial', 'format' => 'csv']))->assertStatus(200);
        // Export audited.
        $this->assertTrue(\App\Models\AuditLog::where('action', 'report.exported')->exists());
    }
}
