<?php

namespace Tests\Feature;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Quote accept auto-creates the confirmed order (no manual duplication),
 * price-reject and order-cancel workflows, quote expiry.
 */
class QuoteAcceptOrderTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag, bool $verified = true): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag) . '.' . Str::random(6) . '@example.test',
            'email_verified_at' => $verified ? now() : null,
            'phone' => '+447700900123',
            'phone_verified_at' => $verified ? now() : null,
            'verification_status' => $verified ? 'verified' : 'pending',
        ]);
    }

    private function sentQuote(User $customer, float $total = 600.0): Quotation
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-qao'], ['name' => 'QAO']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] QAO Service', 'slug' => 't-qao-' . Str::random(6), 'short_description' => 'x', 'starting_price' => $total, 'is_active' => true]);
        $req = ServiceRequest::create([
            'user_id' => $customer->id, 'service_id' => $service->id, 'name' => $customer->name,
            'email' => $customer->email, 'requirements' => 'QAO scope.', 'status' => 'quoted', 'review_status' => 'quoted',
        ]);
        $q = Quotation::create([
            'customer_id' => $customer->id, 'service_request_id' => $req->id,
            'subtotal' => $total, 'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => $total, 'currency' => 'GBP', 'status' => 'sent',
            'valid_until' => now()->addDays(14), 'sent_at' => now(),
        ]);
        QuotationItem::create(['quotation_id' => $q->id, 'description' => 'QAO work', 'quantity' => 1, 'unit_price' => $total, 'total' => $total]);
        return $q->fresh();
    }

    /** @test */
    public function accept_creates_confirmed_order_once_with_connected_records()
    {
        $customer = $this->person('customer', 'QAO Cust');
        $q = $this->sentQuote($customer);

        $this->actingAs($customer)->post(route('portal.quotations.accept', $q->id))
            ->assertRedirect();
        $order = ServiceOrder::where('quotation_id', $q->id)->firstOrFail();
        $this->assertSame('confirmed', $order->status);
        $this->assertEquals(600.0, (float) $order->total);
        $this->assertEquals(600.0, (float) $order->amount_due);
        $this->assertTrue($order->price_locked);
        $this->assertSame('accepted', $q->fresh()->status);
        $this->assertSame(1, $order->invoices()->count());
        $this->assertSame(1, $order->tickets()->count());
        $this->assertSame(1, $order->tasks()->count());

        // Double accept: 422, still exactly one order (idempotent state machine).
        $this->actingAs($customer)->post(route('portal.quotations.accept', $q->id))->assertStatus(422);
        $this->assertSame(1, ServiceOrder::where('quotation_id', $q->id)->count());

        // Admin convert after accept: blocked, no duplicate invoice.
        $admin = $this->person('admin', 'QAO Admin');
        $this->actingAs($admin)->post(route('admin.quotations.convert', $q->id))->assertStatus(422);
        $this->assertSame(1, $order->invoices()->count());
    }

    /** @test */
    public function unverified_customer_cannot_accept()
    {
        $customer = $this->person('customer', 'QAO Unver', false);
        $q = $this->sentQuote($customer);
        // Email-unverified users are stopped by the verified middleware (302),
        // and even verified-email-but-unverified-phone users get 422 in service.
        $this->actingAs($customer)->post(route('portal.quotations.accept', $q->id))->assertStatus(302);
        $this->assertSame(0, ServiceOrder::where('quotation_id', $q->id)->count());

        // Email verified but phone not: reaches controller, service aborts 422.
        $customer->update(['email_verified_at' => now()]);
        $this->actingAs($customer)->post(route('portal.quotations.accept', $q->id))->assertStatus(422);
        $this->assertSame(0, ServiceOrder::where('quotation_id', $q->id)->count());
    }

    /** @test */
    public function price_reject_and_order_cancel_flows()
    {
        $customer = $this->person('customer', 'QAO RC');
        $other = $this->person('customer', 'QAO Other');
        $finance = $this->person('finance_manager', 'QAO Fin');
        $svc = app(ServiceOrderWorkflowService::class);

        // Negotiating order with a proposed revision.
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-qao2'], ['name' => 'QAO2']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] QAO2', 'slug' => 't-qao2-' . Str::random(6), 'short_description' => 'x', 'starting_price' => 100, 'is_active' => true]);
        $order = $svc->createCustomerOrder(['service_id' => $service->id, 'negotiate' => true, 'currency' => 'GBP'], $customer);

        $rev = $order->priceRevisions()->whereIn('status', ['proposed', 'pending_approval'])->firstOrFail();
        // Intruder cannot reject (oracle-free 404 — order invisible).
        $this->actingAs($other)->post(route('portal.orders.reject-price', $order->id), ['reason' => 'Nope'])->assertStatus(404);
        // Owner rejects with reason.
        $this->actingAs($customer)->post(route('portal.orders.reject-price', $order->id), ['reason' => 'Too expensive.', 'revision_id' => $rev->id])->assertRedirect();
        $this->assertSame('rejected', $rev->fresh()->status);
        // Double reject: 422.
        $this->actingAs($customer)->post(route('portal.orders.reject-price', $order->id), ['reason' => 'Again.', 'revision_id' => $rev->id])->assertStatus(422);

        // Owner cancels unpaid order: reason/by/timestamp recorded, history kept.
        $this->actingAs($customer)->post(route('portal.orders.cancel', $order->id), ['reason' => 'No longer needed.'])->assertRedirect();
        $order = $order->fresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame('No longer needed.', $order->cancel_reason);
        $this->assertSame($customer->id, (int) $order->cancelled_by);
        $this->assertFalse((bool) $order->refund_due);
        $this->assertTrue(\App\Models\AuditLog::where('action', 'service_order.cancelled')->where('auditable_id', $order->id)->exists());

        // Paid order: owner blocked, finance cancels with refund_due flag.
        $q = $this->sentQuote($customer, 400.0);
        $this->actingAs($customer)->post(route('portal.quotations.accept', $q->id))->assertRedirect();
        $paid = ServiceOrder::where('quotation_id', $q->id)->firstOrFail();
        $svc->recordPayment($paid->fresh(), ['amount' => 400, 'payment_method' => 'card', 'transaction_id' => 'QAO-P1'], $finance);
        $this->actingAs($customer)->post(route('portal.orders.cancel', $paid->id), ['reason' => 'Changed mind.'])->assertStatus(403);
        $this->actingAs($finance)->post(route('admin.work-orders.cancel', $paid->id), ['reason' => 'Customer request, paid.'])->assertRedirect();
        $this->assertSame('cancelled', $paid->fresh()->status);
        $this->assertTrue((bool) $paid->fresh()->refund_due);
    }

    /** @test */
    public function expired_quotes_are_marked_by_command()
    {
        $customer = $this->person('customer', 'QAO Exp');
        $q = $this->sentQuote($customer);
        $q->update(['valid_until' => now()->subDay()]);
        $this->artisan('quotes:expire')->assertSuccessful();
        $this->assertSame('expired', $q->fresh()->status);
        $this->actingAs($customer)->post(route('portal.quotations.accept', $q->id))->assertStatus(422);
    }
}
