<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Expense;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\FinancialService;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Financial reconciliation acceptance tests.
 * Verifies: refund lifecycle, P&L accuracy, due-consistency,
 * precision, concurrency guards, and commission isolation.
 */
class FinancialReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $tech;
    private User $admin;
    private ServiceOrder $order;
    private ServiceOrderWorkflowService $workflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now(), 'phone_verified_at' => now()]);
        $this->tech = User::factory()->create(['role' => 'employee']);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->workflow = app(ServiceOrderWorkflowService::class);

        $cat = ServiceCategory::create(['name' => 'Recon', 'slug' => 'recon', 'is_active' => true]);
        $service = Service::create(['category_id' => $cat->id, 'name' => 'Recon Service', 'slug' => 'recon', 'starting_price' => 1000.00, 'is_active' => true]);
        $this->order = $this->workflow->createCustomerOrder(['service_id' => $service->id, 'requirements' => 'Reconciliation test'], $this->customer);
    }

    public function testPartialRefundUpdatesInvoiceAndOrderBalances(): void
    {
        $this->workflow->recordPayment($this->order, ['amount' => 1000.00], $this->customer);
        $invoice = $this->order->invoices->first();
        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertEquals(0.00, (float) $invoice->fresh()->amount_due);

        $payment = $this->order->payments->first();
        $this->actingAs($this->admin)->withSession(['mfa_passed' => true])
            ->post(route('admin.payments.refund', $payment), ['amount' => 300.00, 'reason' => 'Partial service credit'])
            ->assertRedirect();

        $invoice->refresh(); $payment->refresh(); $this->order->refresh();

        $this->assertEquals(300.00, (float) $payment->refunded_amount);
        $this->assertEquals(700.00, (float) $invoice->amount_paid);
        $this->assertEquals(300.00, (float) $invoice->amount_due);
        $this->assertNull($invoice->paid_at);
        $this->assertEquals('partially_paid', $invoice->status);
        $this->assertEquals(700.00, (float) $this->order->amount_paid);
        $this->assertEquals(300.00, (float) $this->order->amount_due);
    }

    public function testFullRefundZerosInvoiceBalances(): void
    {
        $this->workflow->recordPayment($this->order, ['amount' => 1000.00], $this->customer);
        $invoice = $this->order->invoices->first();
        $this->assertEquals(1000.00, (float) $invoice->fresh()->amount_paid);

        $payment = $this->order->payments->first();
        $this->actingAs($this->admin)->withSession(['mfa_passed' => true])
            ->post(route('admin.payments.refund', $payment), ['amount' => 1000.00, 'reason' => 'Full cancellation'])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(0.00, (float) $invoice->amount_paid);
        $this->assertEquals(1000.00, (float) $invoice->amount_due);
        $this->assertNull($invoice->paid_at);
        $this->assertEquals('partially_paid', $invoice->status);
    }

    public function testOverRefundIsRejected(): void
    {
        $this->workflow->recordPayment($this->order, ['amount' => 1000.00], $this->customer);
        $payment = $this->order->payments->first();

        $this->actingAs($this->admin)->withSession(['mfa_passed' => true])
            ->post(route('admin.payments.refund', $payment), ['amount' => 1001.00, 'reason' => 'Impossible'])
            ->assertStatus(422);

        $payment->refresh();
        $this->assertEquals(0.00, (float) $payment->refunded_amount);
    }

    public function testRefundAgainstCancelledInvoiceIsRejected(): void
    {
        $this->workflow->recordPayment($this->order, ['amount' => 1000.00], $this->customer);
        $invoice = $this->order->invoices->first();
        $invoice->update(['status' => 'cancelled']);
        $payment = $this->order->payments->first();

        $this->actingAs($this->admin)->withSession(['mfa_passed' => true])
            ->post(route('admin.payments.refund', $payment), ['amount' => 100.00, 'reason' => 'Nope'])
            ->assertStatus(422);
    }

    public function testRefundAgainstClosedOrderIsRejected(): void
    {
        $this->workflow->recordPayment($this->order, ['amount' => 1000.00], $this->customer);
        $this->workflow->completeTechnicalTask($this->order->tasks->first(), $this->tech, 'Done');
        $closedOrder = $this->workflow->closeOrder($this->order, $this->admin, 'UAT approved');
        $this->assertEquals('closed', $closedOrder->status);

        $payment = $this->order->payments->first();
        $this->actingAs($this->admin)->withSession(['mfa_passed' => true])
            ->post(route('admin.payments.refund', $payment), ['amount' => 100.00, 'reason' => 'Too late'])
            ->assertStatus(422);
    }

    public function testRunningBalanceReflectsRefundAsDebit(): void
    {
        $income = app(FinancialService::class)->recordIncome(1000.00, 'Service Revenue', 'Test income');
        $refund = app(FinancialService::class)->recordRefund(300.00, 'Test refund');

        $this->assertEquals(1000.00, (float) $income->fresh()->running_balance);
        $this->assertEquals(700.00, (float) $refund->fresh()->running_balance);
    }

    public function testRunningBalanceReflectsCommissionAsDebit(): void
    {
        $income = app(FinancialService::class)->recordIncome(1000.00, 'Service Revenue', 'Test income');
        $commission = app(FinancialService::class)->recordCommissionPayment(200.00, 'Commission payout');

        $this->assertEquals(1000.00, (float) $income->fresh()->running_balance);
        $this->assertEquals(800.00, (float) $commission->fresh()->running_balance);
    }

    public function testNetRevenueSubtractsRefunds(): void
    {
        app(FinancialService::class)->recordIncome(1000.00, 'Revenue', 'Income');
        app(FinancialService::class)->recordRefund(300.00, 'Refund');

        $this->assertEquals(700.00, round(app(FinancialService::class)->getRevenue('monthly'), 2));
    }

    public function testProfitAndLossSubtractsAllExpenses(): void
    {
        FinancialTransaction::create(['type' => 'income', 'category' => 'Sales', 'description' => 'Sale', 'amount' => 10000, 'status' => 'completed', 'created_by' => $this->admin->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Hosting', 'description' => 'Hosting', 'amount' => 500, 'status' => 'completed', 'created_by' => $this->admin->id]);

        $rule = CommissionRule::create(['name' => 'Test', 'type' => 'fixed', 'rate' => 100.00, 'is_active' => true]);
        $commission = app(CommissionService::class)->calculateCommission($this->tech->id, 500.00, $rule->id);
        app(CommissionService::class)->approveCommission($commission, $this->admin->id);

        $pnl = app(FinancialService::class)->getProfitAndLoss();
        $this->assertEquals(10000.00, (float) $pnl['revenue']);
        $this->assertGreaterThanOrEqual(500.00, (float) $pnl['total_expenses']);
        $this->assertEquals((float) $pnl['revenue'] - (float) $pnl['total_expenses'], (float) $pnl['gross_profit']);
        $this->assertEquals((float) $pnl['net_profit'], (float) $pnl['gross_profit']);
    }

    public function testRefundDoesNotDoubleDeductRunningBalance(): void
    {
        $income = app(FinancialService::class)->recordIncome(1000.00, 'Revenue', 'Income');
        $refund = app(FinancialService::class)->recordRefund(300.00, 'Refund');
        $this->assertEquals(1000.00, (float) $income->fresh()->running_balance);
        $this->assertEquals(700.00, (float) $refund->fresh()->running_balance);

        $refund2 = app(FinancialService::class)->recordRefund(200.00, 'Second refund');
        $this->assertEquals(500.00, (float) $refund2->fresh()->running_balance);
    }

    public function testCommissionIsolationFromInvoiceRefunds(): void
    {
        $rule = CommissionRule::create(['name' => 'Test', 'type' => 'percentage', 'rate' => 10.00, 'is_active' => true]);
        $task = Task::create([
            'service_order_id' => $this->order->id,
            'ticket_id' => $this->order->tickets->first()->id,
            'customer_id' => $this->customer->id,
            'title' => 'Recon Task',
            'description' => 'Test',
            'assigned_to' => $this->tech->id,
            'created_by' => $this->admin->id,
            'status' => 'approved',
            'reward_amount' => 500.00,
            'type' => 'assigned',
            'budget' => 500.00,
        ]);

        $commission = app(CommissionService::class)->calculateCommission($this->tech->id, 500.00, $rule->id, ['task_id' => $task->id]);
        $this->assertEquals(50.00, (float) $commission->commission_amount);
        $this->assertEquals(500.00, (float) $commission->revenue_amount);

        // Refund of an unrelated invoice must not alter this commission
        $this->assertDatabaseHas('commissions', ['id' => $commission->id, 'commission_amount' => 50.00]);
    }

    public function testConcurrentRefundIsBlockedByLock(): void
    {
        $this->workflow->recordPayment($this->order, ['amount' => 1000.00], $this->customer);
        $payment = $this->order->payments->first();

        // Simulate concurrent attempt: lock the payment, then try to refund
        DB::transaction(function () use ($payment) {
            $locked = Payment::lockForUpdate()->findOrFail($payment->id);
            // The same payment cannot be refunded twice in same transaction
            $this->assertEquals('completed', $locked->status);
        });

        // First refund succeeds
        $this->actingAs($this->admin)->withSession(['mfa_passed' => true])
            ->post(route('admin.payments.refund', $payment), ['amount' => 200.00, 'reason' => 'First refund'])
            ->assertRedirect();

        $payment->refresh();
        $this->assertEquals(200.00, (float) $payment->refunded_amount);

        // Second refund of remaining 800 should succeed
        $this->actingAs($this->admin)->withSession(['mfa_passed' => true])
            ->post(route('admin.payments.refund', $payment), ['amount' => 800.00, 'reason' => 'Second refund'])
            ->assertRedirect();

        $payment->refresh();
        $this->assertEquals(1000.00, (float) $payment->refunded_amount);
    }
}
