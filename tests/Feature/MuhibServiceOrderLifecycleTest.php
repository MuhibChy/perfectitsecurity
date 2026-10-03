<?php

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Full service-order lifecycle with the dedicated synthetic customer
 * (display name Muhib, unique test email — never a production account).
 *
 * Journey: register/login → catalogue order → 30% advance → processing →
 * staff assignment → progress → completion evidence → 70% balance →
 * reconciliation → closure → histories. All on the isolated :memory: test
 * database; payments flow through the application's real recordPayment
 * pipeline (server-side totals, locks, receipts, ledger). Simulated
 * gateway handshakes are labelled as such — no real money moves.
 */
class MuhibServiceOrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function muhib(): User
    {
        return User::factory()->create([
            'name' => 'Muhib', 'role' => 'customer', 'is_active' => true,
            'email' => 'muhib.lifecycle@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function staff(string $role, string $email): User
    {
        return User::factory()->create([
            'role' => $role, 'is_active' => true, 'email' => $email,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function service(): Service
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 'muhib-cat'], ['name' => 'Muhib Cat']);

        return Service::create([
            'category_id' => $cat->id, 'name' => 'Business PC Setup', 'slug' => 'biz-pc-setup-'.Str::random(6),
            'short_description' => 'Five office computer setups', 'starting_price' => 1000.00,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function full_lifecycle_advance_assignment_completion_balance_closure()
    {
        $customer = $this->muhib();
        $service = $this->service();
        $svc = app(ServiceOrderWorkflowService::class);

        // B: catalogue order through the real customer route.
        $this->actingAs($customer)->post(route('portal.orders.store'), [
            'service_id' => $service->id,
            'requirements' => 'Please set up five office computers for our team.',
        ])->assertRedirect();
        $order = ServiceOrder::where('customer_id', $customer->id)->firstOrFail();
        $this->assertNotEmpty($order->order_number); // genuine reference
        $this->assertEquals(1000.00, (float) $order->total);
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals(1, $order->invoices()->count());
        $this->assertEquals(1, $order->tasks()->count());
        $this->actingAs($customer)->get(route('portal.orders.show', $order->id))->assertStatus(200);

        // C: 30% advance through the real payment pipeline.
        $advance = round(1000.00 * 30 / 100, 2);
        $this->assertEquals(300.00, $advance);
        $r1 = $svc->recordPayment($order, [
            'amount' => $advance, 'payment_method' => 'bank_transfer',
            'transaction_id' => 'MUHIB-ADV-1', 'notes' => 'Synthetic 30% advance',
        ], $customer);
        $order = $r1['order']->fresh();
        $this->assertEquals(300.00, (float) $order->amount_paid);
        $this->assertEquals(700.00, (float) $order->amount_due);
        $this->assertEquals('ready_to_start', $order->payment_authorization);

        // D: processing state, not fully paid.
        $this->assertEquals('ready_to_start', $order->status);
        $this->assertFalse($order->isFullyPaid());
        $this->assertEquals('partially_paid', $order->invoices()->first()->status);
        $this->assertEquals(700.00, (float) $r1['receipt']->remaining_balance);

        // E: dispatcher assigns authorized staff; staff sees the order.
        $dispatcher = $this->staff('support_manager', 'muhib.dispatcher@example.test');
        $agent = $this->staff('support_agent', 'muhib.agent@example.test');
        $assignment = app(AssignmentService::class)->assign($dispatcher, $agent, 'order', $order->id, 'Lifecycle E2E');
        $this->assertEquals('active', $assignment->status);
        $order->update(['assigned_to' => $agent->id]);
        $this->actingAs($agent)->get(route('admin.work-orders.show', $order->id))->assertStatus(200);

        // F/G: staff progress + completion evidence; balance outstanding → awaiting payment.
        $task = $order->tasks()->firstOrFail();
        $svc->completeTechnicalTask($task, $agent, 'Five office PCs imaged, joined, verified.', 240);
        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertEquals('awaiting_final_payment', $order->fresh()->status);

        // Premature closure refused.
        try {
            $svc->closeOrder($order->fresh(), $dispatcher, 'too early');
            $this->fail('closeOrder must refuse outstanding balance');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // H: remaining 70% through the real pipeline.
        $outstanding = (float) $order->fresh()->outstanding_balance;
        $this->assertEquals(700.00, $outstanding);
        $r2 = $svc->recordPayment($order->fresh(), [
            'amount' => $outstanding, 'payment_method' => 'bank_transfer',
            'transaction_id' => 'MUHIB-FIN-1', 'notes' => 'Synthetic final balance',
        ], $customer);
        $order = $r2['order']->fresh();
        $this->assertEquals('financially_completed', $order->status);
        $this->assertEquals('paid', $order->invoices()->first()->status);
        $this->assertEquals(0.0, (float) $order->amount_due);

        // I: reconciliation T = A + F.
        $this->assertEquals(2, Payment::where('service_order_id', $order->id)->where('status', 'completed')->count());
        $this->assertEquals(2, Receipt::where('service_order_id', $order->id)->count());
        $paidSum = round((float) Payment::where('service_order_id', $order->id)->where('status', 'completed')->sum('amount'), 2);
        $this->assertEquals(1000.00, $paidSum);
        $this->assertEquals(1000.00, (float) $order->amount_paid);
        $ledger = round((float) FinancialTransaction::where('type', 'income')
            ->where('description', 'like', '%'.$order->order_number.'%')->sum('amount'), 2);
        $this->assertEquals(1000.00, $ledger);

        // J: closure + histories.
        $closed = $svc->closeOrder($order, $dispatcher, 'Lifecycle E2E closure');
        $this->assertEquals('closed', $closed->status);
        $this->assertNotNull($closed->closed_at);
        $this->actingAs($customer)->get(route('portal.orders.show', $order->id))->assertStatus(200);
        $this->assertEquals(1, ServiceOrder::where('customer_id', $customer->id)->where('status', 'closed')->count());
        $this->assertEquals(1, \App\Models\EmployeeAssignment::where('employee_id', $agent->id)
            ->where('assignable_type', ServiceOrder::class)->where('assignable_id', $order->id)->count());
    }

    /** @test */
    public function duplicate_transaction_id_does_not_double_record_money()
    {
        $customer = $this->muhib();
        $order = app(ServiceOrderWorkflowService::class)->createCustomerOrder([
            'service_id' => $this->service()->id,
            'requirements' => 'Duplicate-callback probe with sufficient detail.',
        ], $customer);
        $svc = app(ServiceOrderWorkflowService::class);

        $first = $svc->recordPayment($order, ['amount' => 300.00, 'transaction_id' => 'MUHIB-DUP-1'], $customer);
        $replay = $svc->recordPayment($order->fresh(), ['amount' => 300.00, 'transaction_id' => 'MUHIB-DUP-1'], $customer);
        $this->assertTrue($replay['duplicate'] ?? false);
        $this->assertEquals($first['payment']->id, $replay['payment']->id);
        $this->assertEquals(1, Payment::where('service_order_id', $order->id)->count());
        $this->assertEquals(300.00, (float) $order->fresh()->amount_paid);
    }

    /** @test */
    public function lifecycle_negative_battery()
    {
        $customer = $this->muhib();
        $other = User::factory()->create(['role' => 'customer', 'is_active' => true,
            'email' => 'muhib.other@example.test', 'email_verified_at' => now(), 'phone_verified_at' => now()]);
        $order = app(ServiceOrderWorkflowService::class)->createCustomerOrder([
            'service_id' => $this->service()->id,
            'requirements' => 'Negative battery probe with sufficient detail.',
        ], $customer);
        $svc = app(ServiceOrderWorkflowService::class);

        // Forged total ignored: server-side total wins.
        $this->actingAs($customer)->post(route('portal.orders.store'), [
            'service_id' => $this->service()->id,
            'requirements' => 'Second probe order with sufficient detail.',
            'proposed_price' => 1,
        ]);
        // Forged total neutralized: off-catalogue proposed_price forces the
        // negotiating path — no confirmed order, no invoice at 1.00.
        $second = ServiceOrder::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertEquals('negotiating', $second->status);
        $this->assertEquals(0, $second->invoices()->count());

        // Overpayment refused.
        try {
            $svc->recordPayment($order, ['amount' => 5000.00, 'transaction_id' => 'MUHIB-OVER-1'], $customer);
            $this->fail('overpayment must abort');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // Cross-customer access: 404, never leak.
        $this->actingAs($other)->get(route('portal.orders.show', $order->id))->assertStatus(404);

        // Unauthorized assignment: customer cannot hit dispatcher endpoint.
        $agent = $this->staff('support_agent', 'muhib.neg.agent@example.test');
        $this->actingAs($customer)->post(route('admin.ecosystem.assignments.store'), [
            'employee_id' => $agent->id, 'assignable_type' => 'order', 'assignable_id' => $order->id,
        ])->assertStatus(403);

        // Closed order refuses payment.
        $svc->recordPayment($order, ['amount' => 1000.00, 'transaction_id' => 'MUHIB-NEG-FULL'], $customer);
        $task = $order->tasks()->firstOrFail();
        $svc->completeTechnicalTask($task, $agent, 'notes', 30);
        $svc->closeOrder($order->fresh(), $this->staff('support_manager', 'muhib.neg.mgr@example.test'), 'neg close');
        try {
            $svc->recordPayment($order->fresh(), ['amount' => 10.00, 'transaction_id' => 'MUHIB-NEG-LATE'], $customer);
            $this->fail('payment on closed order must abort');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
    }
}
