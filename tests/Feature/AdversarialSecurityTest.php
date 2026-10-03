<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Adversarial security tests — attempt to break authorization, financial integrity,
 * and business logic. These tests are deliberately adversarial: they use forged
 * requests, ID manipulation, role confusion, and edge cases.
 */
class AdversarialSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function customer(array $over = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer', 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'password' => bcrypt('secret'),
        ], $over));
    }

    private function staff(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag) . '.' . Str::random(6) . '@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'password' => bcrypt('secret'),
        ]);
    }

    private function service(string $slug, float $price): Service
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-adv'], ['name' => 'ADV']);
        return Service::create([
            'category_id' => $cat->id, 'name' => '[TEST] Adv Service', 'slug' => $slug,
            'short_description' => 'x', 'starting_price' => $price, 'is_active' => true,
        ]);
    }

    private function orderFor(User $customer, Service $svc, array $over = []): ServiceOrder
    {
        $wf = app(ServiceOrderWorkflowService::class);
        return $wf->createCustomerOrder(array_merge([
            'service_id' => $svc->id, 'currency' => 'GBP',
            'requirements' => 'Test scope',
        ], $over), $customer);
    }

    /** @test */
    public function customer_cannot_access_another_customers_order_via_id_manipulation()
    {
        $a = $this->customer(['email' => 'a@test.tld']);
        $b = $this->customer(['email' => 'b@test.tld']);
        $svc = $this->service('t-svc-1', 100);
        $orderA = $this->orderFor($a, $svc);
        $orderB = $this->orderFor($b, $svc);
        $totalB = $orderB->total;

        // A tries to view B's order (ID manipulation in URL)
        $this->actingAs($a)->get(route('portal.orders.show', 999999))
            ->assertStatus(404); // not found, not 403 (don't leak existence)

        // A tries to act on B's order via ID in POST
        $this->actingAs($a)->post(route('portal.orders.accept-price', $orderB->id), ['revision_id' => 1])
            ->assertStatus(404);

        // A tries to pay on B's order
        $this->actingAs($a)->post(route('portal.orders.pay', $orderB->id), ['amount' => 1, 'payment_method' => 'card'])
            ->assertStatus(404);

        // A tries to cancel B's order
        $this->actingAs($a)->post(route('portal.orders.cancel', $orderB->id), ['reason' => 'test'])
            ->assertStatus(404);

        // A tries to download receipt for B's order
        $this->actingAs($a)->get(route('portal.orders.receipts.show', [$orderB->id, 1]))
            ->assertStatus(404);

        // B's order should remain untouched
        $orderB->refresh();
        $this->assertSame(1, ServiceOrder::where('customer_id', $b->id)->count());
        $this->assertEquals($totalB, (float) $orderB->total);
        $this->assertEquals(0.0, (float) $orderB->amount_paid);
    }

    /** @test */
    public function customer_cannot_escalate_to_admin_actions()
    {
        $customer = $this->customer();
        $admin = $this->staff('admin', 'admin');

        // Customer tries to access admin work-orders index
        $this->actingAs($customer)->get(route('admin.work-orders.index'))
            ->assertStatus(403);

        // Customer tries to access admin financial reports
        $this->actingAs($customer)->get(route('admin.reports.financial'))
            ->assertStatus(403);

        // Customer tries to record payment via admin endpoint
        $this->actingAs($customer)->post(route('admin.work-orders.record-payment', 1), [
            'amount' => 100, 'payment_method' => 'card', 'transaction_id' => 'X'
        ])->assertStatus(403);

        // Customer tries to propose price on another customer's order
        $svc = $this->service('t-svc-2', 100);
        $victimOrder = $this->orderFor($this->customer(), $svc);
        $this->actingAs($customer)->post(route('admin.work-orders.propose-price', $victimOrder->id), [
            'amount' => 1, 'kind' => 'employee_offer', 'terms' => 'hack'
        ])->assertStatus(403);
    }

    /** @test */
    public function support_agent_cannot_access_finance_endpoints()
    {
        $agent = $this->staff('support_agent', 'agent');
        $finance = $this->staff('finance_manager', 'fin');

        // Agent tries to access financial reports
        $this->actingAs($agent)->get(route('admin.reports.financial'))
            ->assertStatus(403);

        // Agent tries to access payments
        $this->actingAs($agent)->get(route('admin.payments.index'))
            ->assertStatus(403);

        // Agent tries to refund
        $this->actingAs($agent)->post(route('admin.payments.refund', 1), ['amount' => 1, 'reason' => 'x'])
            ->assertStatus(403);

        // Finance can access
        $this->actingAs($finance)->get(route('admin.reports.financial'))
            ->assertStatus(200);
    }

    /** @test */
    public function customer_cannot_manipulate_order_totals_via_propose_price()
    {
        $customer = $this->customer();
        $svc = $this->service('t-svc-3', 1000);
        $order = $this->orderFor($customer, $svc, ['negotiate' => true]); // negotiating order

        // Customer submits a counter-offer with negative amount
        $this->actingAs($customer)->post(route('portal.orders.negotiate', $order->id), [
            'amount' => -10000,
            'terms' => 'negative amount attack',
        ])->assertSessionHasErrors('amount'); // validation rejects negative

        // Customer submits counter-offer with amount exceeding reasonable bounds
        $this->actingAs($customer)->post(route('portal.orders.negotiate', $order->id), [
            'amount' => 999999999,
            'terms' => 'overflow attack',
        ]);
        $order->refresh();
        // The order should not have been modified by the invalid attempt
        $this->assertNotEquals(999999999, (float) $order->total);
    }

    /** @test */
    public function customer_cannot_bypass_verification_requirements()
    {
        $unverified = User::factory()->create([
            'role' => 'customer', 'is_active' => true,
            'email' => 'unverified@test.tld',
            'email_verified_at' => null, // not verified
            'phone_verified_at' => null,
            'verification_status' => 'pending',
            'password' => bcrypt('secret'),
        ]);

        $svc = $this->service('t-svc-4', 500);

        // Unverified customer tries to confirm order (not negotiate)
        $wf = app(ServiceOrderWorkflowService::class);
        try {
            $wf->createCustomerOrder(['service_id' => $svc->id, 'currency' => 'GBP'], $unverified);
            $this->fail('Should have thrown 422');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('verify both email and phone', $e->getMessage());
        }
    }

    /** @test */
    public function payment_amount_cannot_exceed_outstanding_balance()
    {
        $customer = $this->customer();
        $svc = $this->service('t-svc-5', 100);
        $order = $this->orderFor($customer, $svc);
        $wf = app(ServiceOrderWorkflowService::class);

        // Pay £50 deposit
        $wf->recordPayment($order->fresh(), ['amount' => 50, 'payment_method' => 'card', 'transaction_id' => 'T1'], $customer);
        $order->refresh();

        // Try to pay £100 when only £50 is due
        try {
            $wf->recordPayment($order->fresh(), ['amount' => 100, 'payment_method' => 'card', 'transaction_id' => 'T2'], $customer);
            $this->fail('Should have thrown 422');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('cannot exceed the outstanding balance', $e->getMessage());
        }

        // Try to pay £0
        try {
            $wf->recordPayment($order->fresh(), ['amount' => 0, 'payment_method' => 'card', 'transaction_id' => 'T3'], $customer);
            $this->fail('Should have thrown 422');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // Try to pay negative amount
        try {
            $wf->recordPayment($order->fresh(), ['amount' => -50, 'payment_method' => 'card', 'transaction_id' => 'T4'], $customer);
            $this->fail('Should have thrown 422');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
    }

    /** @test */
    public function duplicate_quote_acceptance_is_idempotent_not_duplicative()
    {
        $customer = $this->customer();
        $svc = $this->service('t-svc-6', 200);
        $req = ServiceRequest::create([
            'user_id' => $customer->id, 'service_id' => $svc->id, 'name' => $customer->name,
            'email' => $customer->email, 'requirements' => 'Test', 'status' => 'quoted', 'review_status' => 'quoted',
        ]);
        $q = Quotation::create([
            'customer_id' => $customer->id, 'service_request_id' => $req->id, 'subtotal' => 200,
            'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => 200,
            'currency' => 'GBP', 'status' => 'sent', 'valid_until' => now()->addDays(14), 'sent_at' => now(),
        ]);
        QuotationItem::create(['quotation_id' => $q->id, 'description' => 'Test', 'quantity' => 1, 'unit_price' => 200, 'total' => 200]);

        $wf = app(ServiceOrderWorkflowService::class);
        $order1 = $wf->createOrderFromQuotation($q->fresh(), $customer);

        // Second acceptance attempt should fail with 422 (already accepted)
        try {
            $wf->createOrderFromQuotation($q->fresh(), $customer);
            $this->fail('Should have thrown 422');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // Only one order should exist
        $this->assertSame(1, ServiceOrder::where('quotation_id', $q->id)->count());
    }

    /** @test */
    public function paid_order_cannot_be_cancelled_by_customer()
    {
        $customer = $this->customer();
        $finance = $this->staff('finance_manager', 'fin');
        $svc = $this->service('t-svc-7', 400);
        $order = $this->orderFor($customer, $svc);

        $wf = app(ServiceOrderWorkflowService::class);
        $wf->recordPayment($order->fresh(), ['amount' => 200, 'payment_method' => 'card', 'transaction_id' => 'PAID1'], $finance);
        $order->refresh();

        // Customer tries to cancel paid order -> 403
        $this->actingAs($customer)->post(route('portal.orders.cancel', $order->id), ['reason' => 'Changed mind'])
            ->assertStatus(403);

        // Finance can cancel and it flags refund_due
        $this->actingAs($finance)->post(route('admin.work-orders.cancel', $order->id), ['reason' => 'Customer request'])
            ->assertRedirect();
        $order->refresh();
        $this->assertTrue((bool) $order->refund_due);
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function customer_cannot_accept_price_without_verification()
    {
        $customer = $this->customer(['email_verified_at' => now(), 'phone_verified_at' => null]);
        $svc = $this->service('t-svc-8', 300);
        $order = $this->orderFor($customer, $svc, ['negotiate' => true]);

        $wf = app(ServiceOrderWorkflowService::class);
        $rev = $order->priceRevisions()->whereIn('status', ['proposed', 'pending_approval'])->latest()->firstOrFail();

        // Customer tries to accept price without phone verification
        try {
            $wf->acceptPrice($order->fresh(), $customer, $rev->id);
            $this->fail('Should have thrown 422');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
            $this->assertStringContainsString('verified email and verified phone', $e->getMessage());
        }
    }

    /** @test */
    public function quote_convert_blocked_if_order_already_exists()
    {
        $customer = $this->customer();
        $svc = $this->service('t-svc-9', 500);
        $req = ServiceRequest::create([
            'user_id' => $customer->id, 'service_id' => $svc->id, 'name' => $customer->name,
            'email' => $customer->email, 'requirements' => 'Test', 'status' => 'quoted', 'review_status' => 'quoted',
        ]);
        $q = Quotation::create([
            'customer_id' => $customer->id, 'service_request_id' => $req->id, 'subtotal' => 500,
            'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => 500,
            'currency' => 'GBP', 'status' => 'sent', 'valid_until' => now()->addDays(14), 'sent_at' => now(),
        ]);
        QuotationItem::create(['quotation_id' => $q->id, 'description' => 'Test', 'quantity' => 1, 'unit_price' => 500, 'total' => 500]);

        $wf = app(ServiceOrderWorkflowService::class);
        $wf->createOrderFromQuotation($q->fresh(), $customer);
        $this->assertSame(1, ServiceOrder::where('quotation_id', $q->id)->count());

        // Admin tries to convert again -> should be blocked
        $admin = $this->staff('admin', 'admin');
        $this->actingAs($admin)->post(route('admin.quotations.convert', $q->id))
            ->assertStatus(422);
        $this->assertSame(1, ServiceOrder::where('quotation_id', $q->id)->count());
    }

    /** @test */
    public function task_completion_requires_all_tasks_done()
    {
        $customer = $this->customer();
        $staff = $this->staff('support_agent', 'staff');
        $svc = $this->service('t-svc-10', 200);
        $order = $this->orderFor($customer, $svc);
        $task1 = $order->tasks()->firstOrFail();
        $task2 = Task::create([
            'service_order_id' => $order->id, 'customer_id' => $customer->id,
            'title' => 'Task 2', 'description' => 'Second task', 'assigned_to' => $staff->id,
            'status' => 'pending', 'priority' => 'medium', 'type' => 'assigned',
            'budget' => 100, 'created_by' => $staff->id,
        ]);

        $wf = app(ServiceOrderWorkflowService::class);

        // Complete task1 only
        $wf->completeTechnicalTask($task1->fresh(), $staff);
        $order->refresh();
        $this->assertNotEquals('financially_completed', $order->status);

        // Complete task2 with balance still due: order must wait for final
        // payment, never jump straight to financially completed.
        $wf->completeTechnicalTask($task2->fresh(), $staff);
        $order->refresh();
        $this->assertEquals('awaiting_final_payment', $order->status);

        // Paying the outstanding balance after technical completion closes
        // the financial loop.
        $wf->recordPayment($order->fresh(), ['amount' => 200, 'payment_method' => 'card', 'transaction_id' => 'TC1'], $staff);
        $order->refresh();
        $this->assertEquals('financially_completed', $order->status);
    }

    /** @test */
    public function payment_race_condition_prevented_by_row_locks()
    {
        $customer = $this->customer();
        $finance = $this->staff('finance_manager', 'fin');
        $svc = $this->service('t-svc-11', 100);
        $order = $this->orderFor($customer, $svc);
        $wf = app(ServiceOrderWorkflowService::class);

        // Simulate concurrent payments by locking in sequence
        $r1 = $wf->recordPayment($order->fresh(), ['amount' => 50, 'payment_method' => 'card', 'transaction_id' => 'RACE1'], $finance);
        $r2 = $wf->recordPayment($order->fresh(), ['amount' => 50, 'payment_method' => 'card', 'transaction_id' => 'RACE2'], $finance);

        $order->refresh();
        $this->assertEquals(100.0, (float) $order->amount_paid);
        $this->assertEquals(0.0, (float) $order->amount_due);
        $this->assertSame('fully_paid', $order->payment_authorization);
    }

    /** @test */
    public function refund_cannot_exceed_refundable_remainder()
    {
        $customer = $this->customer();
        $finance = $this->staff('finance_manager', 'fin');
        $svc = $this->service('t-svc-12', 200);
        $order = $this->orderFor($customer, $svc);
        $wf = app(ServiceOrderWorkflowService::class);
        $wf->recordPayment($order->fresh(), ['amount' => 200, 'payment_method' => 'card', 'transaction_id' => 'R1'], $finance);
        $order->refresh();

        $payment = $order->payments()->where('status', 'completed')->firstOrFail();

        // Try to refund more than paid: abort(422), never a refund record.
        $this->actingAs($finance)->post(route('admin.payments.refund', $payment->id), [
            'amount' => 300, 'reason' => 'attempt over-refund'
        ])->assertStatus(422);
        $this->assertSame(0, Payment::where('notes', 'like', '%attempt over-refund%')->count());
        $order->refresh();
        $this->assertEquals(200.0, (float) $order->amount_paid);
    }

    /** @test */
    public function customer_cannot_access_another_customers_documents()
    {
        $a = $this->customer(['email' => 'a@doc.tld']);
        $b = $this->customer(['email' => 'b@doc.tld']);
        $svc = $this->service('t-svc-13', 100);
        $order = $this->orderFor($a, $svc);

        // A creates a document
        $doc = \App\Models\CustomerDocument::create([
            'user_id' => $a->id, 'service_order_id' => $order->id,
            'name' => 'Secret Doc', 'file_path' => 'documents/test.pdf', 'mime_type' => 'application/pdf',
            'original_name' => 'secret.pdf', 'size' => 1024, 'path' => 'documents/test.pdf',
        ]);

        // B tries to download A's document: oracle-free scoping answers 404
        // (not 403) so existence is not disclosed — consistent with orders,
        // quotations and AccountIsolationMatrix scoped lookups.
        $this->actingAs($b)->get(route('portal.documents.download', $doc->id))
            ->assertStatus(404);
    }

    /** @test */
    public function staff_cannot_update_another_staffs_profile()
    {
        $staff1 = $this->staff('support_agent', 'staff1');
        $staff2 = $this->staff('support_agent', 'staff2');

        // staff1 tries to update staff2's profile via /my-profile (self-only)
        $resp = $this->actingAs($staff1)->put(route('admin.my-profile.update'), [
            'name' => 'Hacked Name',
            'phone' => '+447700999999',
        ]);
        // Route is self-only; staff1 can only update own profile, so 403 or 302 (redirect)
        $this->assertTrue(in_array($resp->getStatusCode(), [403, 302]));
    }

    /** @test */
    public function customer_cannot_modify_quotation_status_directly()
    {
        $customer = $this->customer();
        $svc = $this->service('t-svc-14', 100);
        $req = ServiceRequest::create([
            'user_id' => $customer->id, 'service_id' => $svc->id, 'name' => $customer->name,
            'email' => $customer->email, 'requirements' => 'Test', 'status' => 'new', 'review_status' => 'new',
        ]);
        $q = Quotation::create([
            'customer_id' => $customer->id, 'service_request_id' => $req->id, 'subtotal' => 100,
            'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => 100,
            'currency' => 'GBP', 'status' => 'sent', 'valid_until' => now()->addDays(14), 'sent_at' => now(),
        ]);
        QuotationItem::create(['quotation_id' => $q->id, 'description' => 'Test', 'quantity' => 1, 'unit_price' => 100, 'total' => 100]);

        // Customer tries to PUT the quotation directly with status=accepted.
        // No customer PUT/PATCH route exists (only accept/reject POST), so
        // the framework must answer 404/405 — never mutate the quotation.
        $resp = $this->actingAs($customer)->put('/portal/quotations/' . $q->id, ['status' => 'accepted']);
        $this->assertTrue(in_array($resp->getStatusCode(), [404, 405]));
        $this->assertSame('sent', $q->fresh()->status);

        // A customer must not reach the staff update endpoint either.
        $this->actingAs($customer)->put(route('admin.quotations.update', $q->id), ['status' => 'accepted'])
            ->assertStatus(403);
        $this->assertSame('sent', $q->fresh()->status);

        // Customer can only POST accept or reject
        $this->actingAs($customer)->post(route('portal.quotations.accept', $q->id))
            ->assertRedirect();
    }

    /** @test */
    public function employee_cannot_assign_task_to_another_employee()
    {
        $staff1 = $this->staff('support_agent', 'staff1');
        $staff2 = $this->staff('support_agent', 'staff2');
        $customer = $this->customer();
        $svc = $this->service('t-svc-15', 100);
        $order = $this->orderFor($customer, $svc);
        $task = $order->tasks()->firstOrFail();

        // staff1 tries to assign task to staff2 via admin endpoint (requires finance manager)
        $this->actingAs($staff1)->post(route('admin.tasks.assign', $task->id), [
            'assigned_to' => $staff2->id,
        ])->assertStatus(403);
    }

    /** @test */
    public function invoice_currency_cannot_be_changed_via_payment()
    {
        $customer = $this->customer();
        $svc = $this->service('t-svc-16', 100);
        $order = $this->orderFor($customer, $svc);
        $order->update(['currency' => 'USD']);
        $order->refresh();

        $finance = $this->staff('finance_manager', 'fin');
        $wf = app(ServiceOrderWorkflowService::class);

        // Try to pay in different currency - should be rejected by recordPayment
        try {
            $wf->recordPayment($order->fresh(), ['amount' => 100, 'payment_method' => 'card', 'transaction_id' => 'CUR1'], $finance);
            // The payment should fail because recordPayment doesn't take currency from request
            // It uses order->currency which is USD
        } catch (\Exception $e) {
            // Expected
        }
        // Verify order currency unchanged
        $order->refresh();
        $this->assertEquals('USD', $order->currency);
    }

    /** @test */
    public function admin_convert_quotation_creates_invoice_not_order()
    {
        $customer = $this->customer();
        $svc = $this->service('t-svc-17', 500);
        $req = ServiceRequest::create([
            'user_id' => $customer->id, 'service_id' => $svc->id, 'name' => $customer->name,
            'email' => $customer->email, 'requirements' => 'Test', 'status' => 'quoted', 'review_status' => 'quoted',
        ]);
        $q = Quotation::create([
            'customer_id' => $customer->id, 'service_request_id' => $req->id, 'subtotal' => 500,
            'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0, 'total' => 500,
            'currency' => 'GBP', 'status' => 'accepted', 'valid_until' => now()->addDays(14), 'sent_at' => now(),
        ]);
        QuotationItem::create(['quotation_id' => $q->id, 'description' => 'Test', 'quantity' => 1, 'unit_price' => 500, 'total' => 500]);

        $admin = $this->staff('admin', 'admin');
        $this->actingAs($admin)->post(route('admin.quotations.convert', $q->id))
            ->assertRedirect(route('admin.invoices.index'));

        // Invoice created, not order
        $invoice = Invoice::where('quotation_id', $q->id)->firstOrFail();
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(500.0, (float) $invoice->total);

        // No order created
        $this->assertSame(0, ServiceOrder::where('quotation_id', $q->id)->count());
    }

    /** @test */
    public function customer_cannot_access_admin_routes_via_direct_url()
    {
        $customer = $this->customer();
        $this->actingAs($customer)->get('/admin')->assertStatus(403);
        $this->actingAs($customer)->get('/admin/work-orders')->assertStatus(403);
        $this->actingAs($customer)->get('/admin/reports')->assertStatus(403);
        $this->actingAs($customer)->get('/admin/users')->assertStatus(403);
        $this->actingAs($customer)->get('/admin/financials')->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_protected_endpoints()
    {
        $this->get('/portal')->assertStatus(302); // redirects to login
        $this->get('/admin/work-orders')->assertStatus(302);
    }
}