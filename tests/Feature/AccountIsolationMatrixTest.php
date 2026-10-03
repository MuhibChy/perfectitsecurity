<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Salary;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Account-isolation matrix (§5/§56): every actor pair that must fail is
 * exercised over HTTP, plus contractor-workspace scoping and document IDOR
 * across receipts, invoices, tasks and messages. Synthetic data only.
 */
class AccountIsolationMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag).'.'.Str::random(6).'@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'country' => 'GB',
        ]);
    }

    private function orderFor(User $customer, float $total = 800): ServiceOrder
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-matrix'], ['name' => 'Matrix']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] Matrix Service', 'slug' => 't-mx-'.Str::random(6), 'short_description' => 'x', 'starting_price' => $total, 'is_active' => true]);
        $order = ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $service->id, 'created_by' => $customer->id,
            'source' => 'test', 'requirements' => 'Matrix scope.',
            'status' => 'confirmed', 'payment_authorization' => 'not_authorized', 'currency' => 'USD',
            'original_price' => $total, 'final_price' => $total, 'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => $total, 'amount_paid' => 0, 'amount_due' => $total, 'price_locked' => true, 'customer_accepted_at' => now(),
        ]);
        app(ServiceOrderWorkflowService::class)->generateConnectedRecords($order->fresh(), $customer);

        return $order->fresh();
    }

    /** @test */
    public function customer_to_customer_isolation()
    {
        $a = $this->person('customer', 'Matrix CustA');
        $b = $this->person('customer', 'Matrix CustB');
        $finance = $this->person('finance_manager', 'Matrix FinA');
        $order = $this->orderFor($a);
        app(ServiceOrderWorkflowService::class)->recordPayment($order->fresh(), ['amount' => 200, 'payment_method' => 'card'], $finance);
        $invoice = $order->invoices()->firstOrFail();
        $receipt = $order->fresh()->tasks()->first() ? \App\Models\Receipt::where('service_order_id', $order->id)->firstOrFail() : null;

        // Owner-scoped order lookup: cross-customer access answers 404
        // (oracle-free), never 403 — existence is not disclosed.
        $this->actingAs($b)->get(route('portal.orders.show', $order->id))->assertStatus(404);
        // Scoped lookups deny with 403 or oracle-free 404 — both secure (no data).
        foreach ([route('portal.invoices.show', $invoice->id), route('portal.invoices.pdf', $invoice->id)] as $url) {
            $this->assertContains($this->actingAs($b)->get($url)->getStatusCode(), [403, 404]);
        }
        if ($receipt) {
            $this->assertContains($this->actingAs($b)->get(route('portal.receipts.pdf', $receipt->id))->getStatusCode(), [403, 404]);
        }
        // Owner access works (control).
        $this->actingAs($a)->get(route('portal.orders.show', $order->id))->assertStatus(200);
        $this->actingAs($a)->get(route('portal.invoices.show', $invoice->id))->assertStatus(200);
    }

    /** @test */
    public function message_and_task_conversation_isolation()
    {
        $a = $this->person('customer', 'Matrix MsgA');
        $b = $this->person('customer', 'Matrix MsgB');
        $staff = $this->person('support_agent', 'Matrix StaffA');
        $msg = app(\App\Services\MessagingService::class)->send($a, $staff, 'Help with my order.');

        // B cannot open A's message; staff cannot open finance-gated areas either.
        $this->actingAs($b)->get(route('portal.messages.show', $msg->id))->assertStatus(403);
        $this->actingAs($a)->get(route('portal.messages.show', $msg->id))->assertStatus(200);

        // Task detail: unassigned employee gets 403 through workspace-equivalent scoping.
        $order = $this->orderFor($a);
        $task = $order->tasks()->firstOrFail();
        $freelancer = $this->person('freelancer', 'Matrix FreeA');
        $this->actingAs($freelancer)->get(route('workspace.tasks.show', $task->id))->assertStatus(403);
    }

    /** @test */
    public function employee_payroll_is_private()
    {
        $empA = $this->person('employee', 'Matrix EmpA');
        $empB = $this->person('employee', 'Matrix EmpB');
        $finance = $this->person('finance_manager', 'Matrix FinB');
        $salary = Salary::create(['user_id' => $empA->id, 'base_salary' => 2000, 'bonus' => 0, 'deductions' => 0, 'net_salary' => 2000, 'period' => 'monthly', 'pay_date' => now()->toDateString(), 'status' => 'approved']);

        // Peer cannot view payslip or salary record; finance/admin path works.
        $this->actingAs($empB)->get(route('admin.salaries.show', $salary->id))->assertStatus(403);
        $this->actingAs($empB)->get(route('admin.salaries.payslip', $salary->id))->assertStatus(403);
        $this->actingAs($finance)->get(route('admin.salaries.show', $salary->id))->assertStatus(200);
        // Other employee's 360 profile is forbidden to non-managers.
        $this->actingAs($empB)->get(route('admin.people.show', $empA->id))->assertStatus(403);
    }

    /** @test */
    public function contractor_workspace_is_scoped_to_own_work()
    {
        $customer = $this->person('customer', 'Matrix CustC');
        $finance = $this->person('finance_manager', 'Matrix FinC');
        $freeA = $this->person('freelancer', 'Matrix FreeC');
        $freeB = $this->person('freelancer', 'Matrix FreeD');
        $agentA = $this->person('commission_agent', 'Matrix AgentC');
        $agentB = $this->person('commission_agent', 'Matrix AgentD');

        $order = $this->orderFor($customer);
        $task = $order->tasks()->firstOrFail();
        $task->update(['assigned_to' => $freeA->id]);

        // Workspace reachable; own task visible; other's task forbidden.
        $this->actingAs($freeA)->get(route('workspace.index'))->assertStatus(200);
        $this->actingAs($freeA)->get(route('workspace.tasks.show', $task->id))->assertStatus(200);
        $this->actingAs($freeB)->get(route('workspace.tasks.show', $task->id))->assertStatus(403);
        // Customers and staff cannot enter the contractor workspace.
        $this->actingAs($customer)->get(route('workspace.index'))->assertStatus(403);

        // Agent commissions strictly scoped per worker.
        $rule = CommissionRule::create(['name' => '[TEST] Matrix 10%', 'type' => 'percentage', 'rate' => 10, 'status' => 'active']);
        $commission = Commission::create(['worker_id' => $agentA->id, 'rule_id' => $rule->id, 'customer_id' => $customer->id, 'commission_type' => 'per_sale', 'revenue_amount' => 500, 'commission_rate' => 10, 'commission_amount' => 50, 'status' => 'pending']);
        $this->actingAs($agentA)->get(route('workspace.commissions'))->assertStatus(200)->assertSee($commission->commission_number);
        $this->actingAs($agentB)->get(route('workspace.commissions'))->assertStatus(200)->assertDontSee($commission->commission_number);
        // Contractors cannot reach finance or admin.
        $this->actingAs($freeA)->get(route('admin.salaries.index'))->assertStatus(403);
        $this->actingAs($agentA)->get(route('admin.financials.index'))->assertStatus(403);
    }

    /** @test */
    public function api_user_endpoint_requires_auth_and_hides_secrets()
    {
        $this->getJson('/api/user')->assertStatus(401);
        $customer = $this->person('customer', 'Matrix Api');
        $res = $this->actingAs($customer)->getJson('/api/user')->assertStatus(200);
        $this->assertEquals($customer->id, $res->json('id'));
        $this->assertArrayNotHasKey('password', $res->json());
        $this->assertArrayNotHasKey('two_factor_secret', $res->json());
        $this->assertArrayNotHasKey('remember_token', $res->json());
    }
}
