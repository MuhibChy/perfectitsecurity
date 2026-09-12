<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Payment failure modes: without Stripe keys (sandbox/unconfigured) the app
 * must degrade gracefully; forged webhooks must be rejected.
 */
class StripeFailureModeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function checkout_without_stripe_keys_redirects_with_helpful_error()
    {
        config(['services.stripe.secret' => null, 'services.stripe.key' => null]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $invoice = Invoice::create(['customer_id' => $customer->id, 'status' => 'sent', 'subtotal' => 100, 'total' => 100]);

        $response = $this->actingAs($customer)->post(route('portal.invoices.checkout', $invoice->id));

        $response->assertRedirect();
        $response->assertSessionHasErrors('payment');
    }

    /** @test */
    public function customer_cannot_checkout_someone_elses_invoice()
    {
        $a = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $b = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $invoice = Invoice::create(['customer_id' => $b->id, 'status' => 'sent', 'subtotal' => 100, 'total' => 100]);

        $this->actingAs($a)->post(route('portal.invoices.checkout', $invoice->id))->assertStatus(404);
    }

    /** @test */
    public function stripe_webhook_rejects_invalid_payload_and_signature()
    {
        // Garbage payload → 400, never 500.
        $this->post(route('stripe.webhook'), [], ['HTTP_STRIPE_SIGNATURE' => 'bad'])
            ->assertStatus(400);

        // Well-formed JSON but wrong signature → 400 (or 500 only on server error path).
        $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 't=1,v1=bad'], '{"id":"evt_test"}')
            ->assertStatus(400);
    }
}
