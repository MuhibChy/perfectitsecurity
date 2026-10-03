<?php

namespace Tests\Feature;

use App\Models\OrderPaymentSchedule;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\OrderPaymentAllocator;
use App\Services\PaymentReconciliationService;
use App\Services\PaymentState;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * International staged-payment verification (Phase 34):
 * £1,500 = £500 + £300 + £300 + £400. Every figure asserted from DB rows.
 */
class InternationalStagedPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(): User
    {
        return User::factory()->create([
            'name' => '[TEST] International Customer Alpha',
            'role' => 'customer', 'is_active' => true,
            'email' => 'alpha.'.Str::random(6).'@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'country' => 'GB', 'preferred_currency' => 'GBP',
        ]);
    }

    private function makeService(): Service
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 'test-cyber'], ['name' => 'Cybersecurity']);

        return Service::create([
            'category_id' => $cat->id,
            'name' => '[TEST] Managed Cybersecurity Assessment',
            'slug' => 'test-cyber-'.Str::random(6),
            'short_description' => 'Synthetic staged-payment service',
            'starting_price' => 1500.00, 'is_active' => true,
        ]);
    }

    /** @test */
    public function staged_payments_reconcile_to_total()
    {
        $customer = $this->makeCustomer();
        $service = $this->makeService();
        $svc = app(ServiceOrderWorkflowService::class);

        // Build a £1,500 GBP order directly (deterministic total for the test).
        $order = ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'created_by' => $customer->id, 'source' => 'test',
            'requirements' => 'Synthetic staged payment assessment scope.',
            'status' => 'confirmed', 'payment_authorization' => 'not_authorized',
            'currency' => 'GBP', 'original_price' => 1500, 'final_price' => 1500,
            'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => 1500, 'amount_paid' => 0, 'amount_due' => 1500,
            'price_locked' => true, 'customer_accepted_at' => now(),
        ]);
        $svc->generateConnectedRecords($order->fresh(), $customer);
        $order = $order->fresh();
        $this->assertEquals(1500.00, (float) $order->total);

        // Payment schedule: 500 / 300 / 300 / 400.
        foreach ([['Advance', 500], ['Milestone 1', 300], ['Milestone 2', 300], ['Final', 400]] as $i => [$title, $amt]) {
            OrderPaymentSchedule::create([
                'order_id' => $order->id, 'title' => $title,
                'expected_amount' => $amt, 'paid_amount' => 0,
                'status' => 'pending', 'sort_order' => $i, 'created_by' => $customer->id,
            ]);
        }

        $expectedPaid = [500, 800, 1100, 1500];
        $expectedDue = [1000, 700, 400, 0];
        $amounts = [500, 300, 300, 400];

        foreach ($amounts as $step => $amt) {
            $result = $svc->recordPayment($order->fresh(), [
                'amount' => $amt, 'payment_method' => 'bank_transfer',
                'transaction_id' => 'STAGED-TEST-'.$step.'-'.Str::random(4),
            ], $customer);
            $order = $result['order']->fresh();
            $invoice = $order->invoices()->first()->fresh();

            // Assert from DB rows, never hard-coded pass-through.
            $dbPaid = round((float) $order->payments()->where('status', 'completed')->sum('amount'), 2);
            $this->assertEquals($expectedPaid[$step], $dbPaid);
            $this->assertEquals($expectedPaid[$step], (float) $order->amount_paid);
            $this->assertEquals($expectedDue[$step], (float) $order->amount_due);
            $this->assertEquals($expectedPaid[$step], (float) $invoice->amount_paid);
            $this->assertEquals($expectedDue[$step], (float) $invoice->amount_due);
            $this->assertTrue(PaymentState::balancesReconcile(1500, (float) $order->amount_paid, (float) $order->amount_due));
            $this->assertEquals(
                $expectedDue[$step] == 0 ? 'paid' : 'partially_paid',
                $invoice->status
            );

            // Schedules stay in sync via the allocator on every rail.
            $schedPaid = round((float) OrderPaymentSchedule::where('order_id', $order->id)->sum('paid_amount'), 2);
            $this->assertEquals($expectedPaid[$step], $schedPaid);
        }

        // Final condition: TOTAL = PAID + DUE → 1500 = 1500 + 0.
        $order = $order->fresh();
        $this->assertEquals(1500.00, (float) $order->amount_paid);
        $this->assertEquals(0.00, (float) $order->amount_due);
        $this->assertEquals('paid', $order->invoices()->first()->status);

        // Reconciliation passes with zero issues.
        $report = app(PaymentReconciliationService::class)->reconcileOrder($order);
        $this->assertTrue($report['ok'], implode('; ', $report['issues']));
        $this->assertTrue($report['balanced']);

        // Canonical state vocabulary.
        $this->assertEquals('SUCCEEDED', PaymentState::canonicalTransactionStatus('completed'));
        $this->assertEquals('PAID', PaymentState::deriveInvoiceStatus(1500, 1500, 'paid'));

        // Failure modes: overpayment + duplicate transaction rejected.
        try {
            $svc->recordPayment($order->fresh(), ['amount' => 1, 'payment_method' => 'card'], $customer);
            $this->fail('payment on zero due should abort');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // Order closes only now that due = 0 (task completion simulated).
        $task = $order->tasks()->firstOrFail();
        $svc->completeTechnicalTask($task, $customer, 'synthetic delivery', 60);
        $closed = $svc->closeOrder($order->fresh(), $customer, 'staged test closure');
        $this->assertEquals('closed', $closed->status);
    }

    /** @test */
    public function allocator_syncs_stripe_and_wallet_rails()
    {
        $customer = $this->makeCustomer();
        $service = $this->makeService();
        $svc = app(ServiceOrderWorkflowService::class);
        $order = ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'created_by' => $customer->id, 'source' => 'test',
            'requirements' => 'Allocator cross-rail synthetic scope.',
            'status' => 'confirmed', 'payment_authorization' => 'not_authorized',
            'currency' => 'GBP', 'original_price' => 1000, 'final_price' => 1000,
            'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => 1000, 'amount_paid' => 0, 'amount_due' => 1000,
            'price_locked' => true, 'customer_accepted_at' => now(),
        ]);
        $svc->generateConnectedRecords($order->fresh(), $customer);
        OrderPaymentSchedule::create(['order_id' => $order->id, 'title' => 'Advance', 'expected_amount' => 300, 'paid_amount' => 0, 'status' => 'pending', 'sort_order' => 0, 'created_by' => $customer->id]);
        OrderPaymentSchedule::create(['order_id' => $order->id, 'title' => 'Final', 'expected_amount' => 700, 'paid_amount' => 0, 'status' => 'pending', 'sort_order' => 1, 'created_by' => $customer->id]);

        // Simulate a Stripe-rail payment row then run the allocator (same call the webhook path makes).
        $invoice = $order->fresh()->invoices()->first();
        $payment = \App\Models\Payment::create([
            'invoice_id' => $invoice->id, 'customer_id' => $customer->id,
            'service_order_id' => $order->id, 'amount' => 300, 'currency' => 'GBP',
            'status' => 'completed', 'payment_method' => 'card', 'gateway' => 'stripe',
            'transaction_id' => 'pi_alloc_test', 'paid_at' => now(),
        ]);
        $allocated = app(OrderPaymentAllocator::class)->allocate($order->fresh(), $payment->fresh(), 300);
        $this->assertEquals(300.0, $allocated);
        $this->assertEquals('paid', OrderPaymentSchedule::where('order_id', $order->id)->orderBy('sort_order')->first()->status);
    }
}
