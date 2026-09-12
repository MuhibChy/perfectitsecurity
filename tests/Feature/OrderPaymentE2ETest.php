<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * ORDER → WORK → PAYMENT end-to-end verification with synthetic actors.
 * Covers: order placement, ownership, invoice sync, offline + webhook
 * payments, idempotency, failure modes, employee work, completion rules,
 * authorization matrix, isolation, and finance reflection.
 */
class OrderPaymentE2ETest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $email = 'e2e.customer@example.test'): User
    {
        return User::factory()->create([
            'role' => 'customer', 'is_active' => true, 'email' => $email,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function employee(string $role, string $email): User
    {
        return User::factory()->create([
            'role' => $role, 'is_active' => true, 'email' => $email,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function service(): Service
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 'e2e-cat'], ['name' => 'E2E Cat']);
        return Service::create([
            'category_id' => $cat->id, 'name' => 'E2E Pen Test', 'slug' => 'e2e-pen-test-' . Str::random(6),
            'short_description' => 'Synthetic E2E service', 'starting_price' => 750.00,
            'is_active' => true,
        ]);
    }

    private function placeOrder(User $customer, Service $service): ServiceOrder
    {
        return app(ServiceOrderWorkflowService::class)->createCustomerOrder([
            'service_id' => $service->id,
            'requirements' => 'E2E assessment of demo application scope.',
        ], $customer);
    }

    /** @test */
    public function customer_places_order_with_correct_links_and_totals()
    {
        $customer = $this->customer();
        $service = $this->service();

        $response = $this->actingAs($customer)->post(route('portal.orders.store'), [
            'service_id' => $service->id,
            'requirements' => 'E2E assessment of demo application scope.',
        ]);
        $response->assertRedirect();

        $order = ServiceOrder::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals($service->id, $order->service_id);
        $this->assertEquals(750.00, (float) $order->total);
        $this->assertEquals('USD', $order->currency);
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals('not_authorized', $order->payment_authorization);

        // Connected records generated exactly once.
        $this->assertEquals(1, $order->invoices()->count());
        $this->assertEquals(1, $order->tickets()->count());
        $this->assertEquals(1, $order->tasks()->count());
        $invoice = $order->invoices()->first();
        $this->assertEquals(750.00, (float) $invoice->total);
        $this->assertEquals('sent', $invoice->status);

        // Repeat submission creates a separate order (no silent dedup); each has own records.
        $this->actingAs($customer)->post(route('portal.orders.store'), [
            'service_id' => $service->id,
            'requirements' => 'Second E2E assessment scope statement here.',
        ])->assertRedirect();
        $this->assertEquals(2, ServiceOrder::where('customer_id', $customer->id)->count());
    }

    /** @test */
    public function unverified_customer_cannot_confirm_paid_order()
    {
        $customer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $service = $this->service();
        $this->actingAs($customer)->post(route('portal.orders.store'), [
            'service_id' => $service->id,
            'requirements' => 'Unverified attempt at ordering services now.',
        ])->assertStatus(422);
        $this->assertEquals(0, ServiceOrder::count());
    }

    /** @test */
    public function order_status_lifecycle_and_close_rules()
    {
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $this->service());
        $svc = app(ServiceOrderWorkflowService::class);

        // Cannot close with balance outstanding.
        try {
            $svc->closeOrder($order, $customer);
            $this->fail('closeOrder should refuse unpaid order');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // Full offline payment (finance path) → fully_paid.
        $result = $svc->recordPayment($order, [
            'amount' => 750.00, 'payment_method' => 'bank_transfer',
            'transaction_id' => 'E2E-TXN-FULL-1', 'notes' => 'E2E synthetic payment',
        ], $customer);
        $this->assertEquals('fully_paid', $result['order']->payment_authorization);
        $this->assertEquals('paid', $result['order']->invoices()->first()->status);
        $this->assertNotEmpty($result['receipt']->receipt_number);

        // Task completion with zero due → financially_completed → closeable.
        $task = $order->tasks()->firstOrFail();
        $svc->completeTechnicalTask($task, $customer, 'E2E work notes', 120);
        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertEquals('financially_completed', $order->fresh()->status);

        $closed = $svc->closeOrder($order->fresh(), $customer, 'E2E closure');
        $this->assertEquals('closed', $closed->status);
        $this->assertNotNull($closed->closed_at);
    }

    /** @test */
    public function partial_payment_keeps_order_open_and_task_gated()
    {
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $this->service());
        $svc = app(ServiceOrderWorkflowService::class);

        $result = $svc->recordPayment($order, ['amount' => 100.00, 'payment_method' => 'card'], $customer);
        $this->assertEquals(650.00, (float) $result['order']->amount_due);
        $this->assertEquals('partially_paid', $result['order']->invoices()->first()->status);
        $this->assertFalse($result['order']->fresh()->canStartWork());

        // Over-payment refused.
        try {
            $svc->recordPayment($order->fresh(), ['amount' => 99999.00], $customer);
            $this->fail('over-payment should abort');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
        $this->assertEquals(1, Payment::where('customer_id', $customer->id)->count());
    }

    /** @test */
    public function stripe_webhook_marks_paid_and_ignores_duplicates_and_failures()
    {
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $this->service());
        $invoice = $order->invoices()->firstOrFail();
        $invoice->update(['stripe_checkout_session_id' => 'cs_e2e_123']);

        $payload = json_encode(['id' => 'evt_e2e', 'type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_e2e_123', 'payment_intent' => 'pi_e2e_123',
            'amount_total' => 75000, 'currency' => 'usd',
            'metadata' => ['invoice_id' => $invoice->id],
        ]]]);

        // First delivery → paid.
        $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 'test'], $payload)
            ->assertStatus(200)->assertJsonPath('result.handled', true);
        $this->assertEquals('paid', $invoice->fresh()->status);

        // Retry of the same session → no duplicate payment.
        $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 'test'], $payload)
            ->assertStatus(200)->assertJsonPath('result.reason', 'duplicate_session');
        $this->assertEquals(1, Payment::where('invoice_id', $invoice->id)->count());

        // Failure-type event → ignored, nothing changes.
        $fail = json_encode(['id' => 'evt_e2e_fail', 'type' => 'payment_intent.payment_failed', 'data' => ['object' => []]]);
        $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 'test'], $fail)
            ->assertStatus(200)->assertJsonPath('result.handled', false);
        $this->assertEquals(1, Payment::where('invoice_id', $invoice->id)->count());
    }

    /** @test */
    public function payment_and_order_authorization_matrix()
    {
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $this->service());
        $invoice = $order->invoices()->firstOrFail();

        // Customer cannot record payments (no portal route → 404) and cannot hit finance routes.
        $this->actingAs($customer)->post(route('admin.payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 10, 'payment_method' => 'card',
        ])->assertStatus(403);
        $this->actingAs($customer)->post(route('portal.invoices.checkout', $invoice->id))
            ->assertRedirect(); // graceful degradation without Stripe keys

        // Support agent (non-finance) is forbidden from finance recording.
        $agent = $this->employee('support_agent', 'e2e.agent@example.test');
        $this->actingAs($agent)->post(route('admin.payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 10, 'payment_method' => 'card',
        ])->assertStatus(403);

        // Finance manager succeeds and balances reconcile.
        $finance = $this->employee('finance_manager', 'e2e.finance@example.test');
        $this->actingAs($finance)->post(route('admin.payments.store'), [
            'invoice_id' => $invoice->id, 'amount' => 750.00,
            'payment_method' => 'bank_transfer', 'transaction_id' => 'E2E-TXN-AUTH-1',
        ])->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0.0, round((float) $invoice->amount_due, 2));

        // Guest checkout attempt → login redirect, never paid.
        // (Log out the previously-acting finance user first.)
        $this->post(route('logout'));
        $this->post(route('portal.invoices.checkout', $invoice->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function employee_workflow_assignment_and_completion()
    {
        $customer = $this->customer();
        $agent = $this->employee('support_agent', 'e2e.worker@example.test');
        $order = $this->placeOrder($customer, $this->service());

        // Employee sees the order in admin list; assignment via task approve path.
        $this->actingAs($agent)->get(route('admin.work-orders.index'))->assertStatus(200);
        $this->actingAs($agent)->get(route('admin.work-orders.show', $order->id))->assertStatus(200);

        $task = $order->tasks()->firstOrFail();
        app(ServiceOrderWorkflowService::class)->completeTechnicalTask($task, $agent, 'E2E delivery notes', 90);
        $this->assertEquals(90, $task->fresh()->actual_minutes);
        // Balance still outstanding → awaiting_final_payment, not closed.
        $this->assertEquals('awaiting_final_payment', $order->fresh()->status);
    }

    /** @test */
    public function manager_override_unlocks_work_without_payment()
    {
        $customer = $this->customer();
        $manager = $this->employee('support_manager', 'e2e.manager@example.test');
        $order = $this->placeOrder($customer, $this->service());

        $this->actingAs($manager)->post(route('admin.work-orders.manager-override', $order->id), [
            'override_reason' => 'E2E strategic account waiver',
        ])->assertRedirect();
        $this->assertEquals('manager_override', $order->fresh()->payment_authorization);
        $this->assertTrue($order->fresh()->canStartWork());

        // Plain agent cannot grant override.
        $agent = $this->employee('support_agent', 'e2e.agent2@example.test');
        $order2 = $this->placeOrder($customer, $this->service());
        $this->actingAs($agent)->post(route('admin.work-orders.manager-override', $order2->id), [
            'override_reason' => 'unauthorized attempt',
        ])->assertStatus(403);
    }

    /** @test */
    public function e2e_customers_are_isolated()
    {
        $alpha = $this->customer('e2e.alpha@example.test');
        $beta = $this->customer('e2e.beta@example.test');
        $service = $this->service();
        $orderA = $this->placeOrder($alpha, $service);
        $invoiceA = $orderA->invoices()->firstOrFail();

        $this->actingAs($beta)->get(route('portal.orders.show', $orderA->id))->assertStatus(403);
        $this->actingAs($beta)->get(route('portal.invoices.show', $invoiceA->id))->assertStatus(404);
        $this->actingAs($alpha)->get(route('portal.orders.show', $orderA->id))->assertStatus(200);
    }

    /** @test */
    public function finance_reflects_payment_once()
    {
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $this->service());
        $finance = $this->employee('finance_manager', 'e2e.fin2@example.test');

        $incomeBefore = \App\Models\FinancialTransaction::where('type', 'income')->sum('amount');
        $this->actingAs($finance)->post(route('admin.payments.store'), [
            'invoice_id' => $order->invoices()->firstOrFail()->id, 'amount' => 750.00,
            'payment_method' => 'bank_transfer', 'transaction_id' => 'E2E-TXN-FIN-1',
        ])->assertRedirect();

        $incomeAfter = \App\Models\FinancialTransaction::where('type', 'income')->sum('amount');
        $this->assertEquals(750.00, round($incomeAfter - $incomeBefore, 2));
        $this->actingAs($finance)->get(route('admin.financials.index'))->assertStatus(200);
    }
}
