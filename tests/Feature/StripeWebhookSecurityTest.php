<?php

namespace Tests\Feature;

use App\Models\FinancialTransaction;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Webhook attack scenarios: forged/partial/mismatched sessions must move no
 * money; the happy path records payment + ledger + audit + receipt exactly
 * once and keeps order balances in sync.
 */
class StripeWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $email = 'whsec.customer@example.test'): User
    {
        return User::factory()->create([
            'role' => 'customer', 'is_active' => true, 'email' => $email,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function orderFor(User $customer): ServiceOrder
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 'whsec-cat'], ['name' => 'WHSEC Cat']);
        $service = Service::create([
            'category_id' => $cat->id, 'name' => 'WHSEC Svc '.Str::random(6),
            'slug' => 'whsec-'.Str::random(6), 'starting_price' => 750.00, 'is_active' => true,
        ]);

        return app(ServiceOrderWorkflowService::class)->createCustomerOrder([
            'service_id' => $service->id, 'requirements' => 'Webhook security probe order.',
        ], $customer);
    }

    private function sessionPayload($invoice, User $customer, array $over = []): string
    {
        return json_encode(['id' => 'evt_'.Str::random(8), 'type' => 'checkout.session.completed', 'data' => ['object' => array_merge([
            'id' => 'cs_'.Str::random(8), 'payment_intent' => 'pi_'.Str::random(8), 'payment_status' => 'paid',
            'amount_total' => 75000, 'currency' => 'usd', 'customer_email' => $customer->email,
            'metadata' => ['invoice_id' => $invoice->id, 'customer_id' => $customer->id],
        ], $over)]]);
    }

    private function postWebhook(string $payload)
    {
        return $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 'test'], $payload);
    }

    /** @test */
    public function happy_path_records_payment_ledger_audit_receipt_and_order_once()
    {
        $customer = $this->customer();
        $order = $this->orderFor($customer);
        $invoice = $order->invoices()->firstOrFail();

        $this->postWebhook($this->sessionPayload($invoice, $customer))
            ->assertStatus(200)->assertJsonPath('result.handled', true);

        $invoice->refresh();
        $order->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0.0, round((float) $invoice->amount_due, 2));
        $this->assertEquals(750.00, round((float) $invoice->amount_paid, 2));
        $this->assertEquals(750.00, round((float) $order->amount_paid, 2));
        $this->assertEquals(0.0, round((float) $order->amount_due, 2));
        $this->assertEquals('fully_paid', $order->payment_authorization);
        $this->assertEquals(1, Payment::where('invoice_id', $invoice->id)->count());
        $this->assertEquals(1, FinancialTransaction::where('type', 'income')->where('payment_id', Payment::first()->id)->count());
        $this->assertEquals(1, Receipt::where('invoice_id', $invoice->id)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'create']);
    }

    /** @test */
    public function forged_sessions_move_no_money()
    {
        $customer = $this->customer();
        $other = $this->customer('whsec.other@example.test');

        // A. Underpaid amount.
        $o1 = $this->orderFor($customer);
        $i1 = $o1->invoices()->firstOrFail();
        $this->postWebhook($this->sessionPayload($i1, $customer, ['amount_total' => 100]))
            ->assertStatus(200)->assertJsonPath('result.handled', false);
        $this->assertEquals(0, Payment::where('invoice_id', $i1->id)->count());

        // B. Wrong currency.
        $o2 = $this->orderFor($customer);
        $i2 = $o2->invoices()->firstOrFail();
        $this->postWebhook($this->sessionPayload($i2, $customer, ['currency' => 'eur']))
            ->assertStatus(200)->assertJsonPath('result.handled', false);
        $this->assertEquals(0, Payment::where('invoice_id', $i2->id)->count());

        // C. Another customer's identity in metadata/email.
        $o3 = $this->orderFor($customer);
        $i3 = $o3->invoices()->firstOrFail();
        $this->postWebhook($this->sessionPayload($i3, $other, ['metadata' => ['invoice_id' => $i3->id, 'customer_id' => $other->id]]))
            ->assertStatus(200)->assertJsonPath('result.handled', false);
        $this->assertEquals(0, Payment::where('invoice_id', $i3->id)->count());

        // D. Unpaid session status.
        $o4 = $this->orderFor($customer);
        $i4 = $o4->invoices()->firstOrFail();
        $this->postWebhook($this->sessionPayload($i4, $customer, ['payment_status' => 'unpaid']))
            ->assertStatus(200)->assertJsonPath('result.handled', false);
        $this->assertEquals(0, Payment::where('invoice_id', $i4->id)->count());

        // E. Missing payment_status entirely (never sent by real Stripe).
        $o5 = $this->orderFor($customer);
        $i5 = $o5->invoices()->firstOrFail();
        $data = json_decode($this->sessionPayload($i5, $customer), true);
        unset($data['data']['object']['payment_status']);
        $this->postWebhook(json_encode($data))
            ->assertStatus(200)->assertJsonPath('result.handled', false);
        $this->assertEquals(0, Payment::where('invoice_id', $i5->id)->count());

        // F. Cancelled invoice cannot be revived.
        $o6 = $this->orderFor($customer);
        $i6 = $o6->invoices()->firstOrFail();
        $i6->update(['status' => 'cancelled']);
        $this->postWebhook($this->sessionPayload($i6, $customer))
            ->assertStatus(200)->assertJsonPath('result.handled', false);
        $this->assertEquals('cancelled', $i6->fresh()->status);
        $this->assertEquals(0, Payment::where('invoice_id', $i6->id)->count());
    }

    /** @test */
    public function duplicate_sessions_and_double_submit_create_one_payment()
    {
        $customer = $this->customer();
        $order = $this->orderFor($customer);
        $invoice = $order->invoices()->firstOrFail();
        $payload = $this->sessionPayload($invoice, $customer);

        $this->postWebhook($payload)->assertStatus(200)->assertJsonPath('result.handled', true);
        $this->postWebhook($payload)->assertStatus(200)->assertJsonPath('result.reason', 'duplicate_session');
        // Different session id after full payment → already_paid, still one payment.
        $this->postWebhook($this->sessionPayload($invoice, $customer))
            ->assertStatus(200)->assertJsonPath('result.reason', 'already_paid');
        $this->assertEquals(1, Payment::where('invoice_id', $invoice->id)->count());
        $this->assertEquals(750.00, round((float) $invoice->fresh()->amount_paid, 2));
    }

    /** @test */
    public function dashboard_refund_syncs_once_and_never_double_counts()
    {
        $customer = $this->customer();
        $order = $this->orderFor($customer);
        $invoice = $order->invoices()->firstOrFail();
        $this->postWebhook($this->sessionPayload($invoice, $customer))->assertStatus(200);

        // Resolve the real intent id from the created payment.
        $intent = Payment::where('invoice_id', $invoice->id)->firstOrFail()->transaction_id;
        $payload = json_encode(['id' => 'evt_rf', 'type' => 'charge.refunded', 'data' => ['object' => [
            'id' => 'ch_x', 'payment_intent' => $intent, 'amount_refunded' => 75000,
        ]]]);

        $this->postWebhook($payload)->assertStatus(200)->assertJsonPath('result.reason', 'external_refund_synced');
        $this->assertEquals(0.0, round((float) $invoice->fresh()->amount_paid, 2));
        $this->assertEquals(750.00, round((float) $invoice->fresh()->amount_due, 2));
        // Redelivery is idempotent.
        $this->postWebhook($payload)->assertStatus(200)->assertJsonPath('result.reason', 'already_recorded');
        $this->assertEquals(1, Payment::where('invoice_id', $invoice->id)->where('status', 'refunded')->count());
    }
}
