<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService
{
    public function isConfigured(): bool
    {
        return !empty(config('services.stripe.secret'));
    }

    public function configure(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout session for an invoice balance.
     */
    public function createInvoiceCheckout(Invoice $invoice, string $successUrl, string $cancelUrl): ?Session
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $this->configure();
        $amountDue = (float) $invoice->amount_due;
        if ($amountDue <= 0) {
            throw new \RuntimeException('Invoice has no outstanding balance.');
        }

        $currency = strtolower($invoice->currency ?? 'usd');
        $customer = $invoice->customer;

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $invoice->id,
            'customer_email' => $customer?->email,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
            ],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => (int) round($amountDue * 100),
                    'product_data' => [
                        'name' => 'Invoice ' . $invoice->invoice_number,
                        'description' => 'Payment for invoice ' . $invoice->invoice_number,
                    ],
                ],
            ]],
        ]);

        $invoice->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return $session;
    }

    public function handleWebhook(string $payload, ?string $signature): array
    {
        $secret = config('services.stripe.webhook_secret');
        if ($secret) {
            $event = Webhook::constructEvent($payload, $signature ?? '', $secret);
        } else {
            // Local/dev fallback when webhook secret is unset — still parse JSON safely.
            $event = json_decode($payload);
            if (!$event || empty($event->type)) {
                throw new \InvalidArgumentException('Invalid Stripe payload.');
            }
        }

        $type = is_array($event) ? ($event['type'] ?? null) : ($event->type ?? null);
        $data = is_array($event) ? ($event['data']['object'] ?? []) : ($event->data->object ?? null);

        if ($type === 'checkout.session.completed') {
            return $this->markInvoicePaidFromSession($data);
        }

        return ['handled' => false, 'type' => $type];
    }

    protected function markInvoicePaidFromSession($session): array
    {
        $sessionId = is_object($session) ? ($session->id ?? null) : ($session['id'] ?? null);
        $metadata = is_object($session) ? ($session->metadata ?? null) : ($session['metadata'] ?? []);
        $invoiceId = is_object($metadata) ? ($metadata->invoice_id ?? null) : ($metadata['invoice_id'] ?? null);
        $paymentIntent = is_object($session) ? ($session->payment_intent ?? null) : ($session['payment_intent'] ?? null);
        $amountTotal = is_object($session) ? (($session->amount_total ?? 0) / 100) : (($session['amount_total'] ?? 0) / 100);
        $currency = strtoupper(is_object($session) ? ($session->currency ?? 'usd') : ($session['currency'] ?? 'usd'));

        $invoice = $invoiceId
            ? Invoice::find($invoiceId)
            : Invoice::where('stripe_checkout_session_id', $sessionId)->first();

        if (!$invoice) {
            Log::warning('Stripe webhook: invoice not found', ['session' => $sessionId]);
            return ['handled' => false, 'reason' => 'invoice_not_found'];
        }

        // Idempotency: Stripe retries the same event; never record the same
        // checkout session twice (guards partial-payment double counting).
        $existing = $sessionId
            ? Payment::where('stripe_checkout_session_id', (string) $sessionId)->first()
            : null;
        if ($existing) {
            Log::info('Stripe webhook: duplicate session ignored', ['session' => $sessionId, 'payment_id' => $existing->id]);
            return ['handled' => true, 'reason' => 'duplicate_session', 'invoice_id' => $invoice->id, 'payment_id' => $existing->id];
        }

        if (in_array($invoice->status, ['paid'], true)) {
            return ['handled' => true, 'reason' => 'already_paid', 'invoice_id' => $invoice->id];
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => $amountTotal > 0 ? $amountTotal : $invoice->amount_due,
            'currency' => $currency ?: ($invoice->currency ?? 'USD'),
            'status' => 'completed',
            'payment_method' => 'card',
            'gateway' => 'stripe',
            'transaction_id' => is_string($paymentIntent) ? $paymentIntent : (string) $paymentIntent,
            'stripe_checkout_session_id' => $sessionId,
            'stripe_payment_intent_id' => is_string($paymentIntent) ? $paymentIntent : null,
            'paid_at' => now(),
            'notes' => 'Paid via Stripe Checkout',
        ]);

        $paid = (float) $invoice->amount_paid + (float) $payment->amount;
        $due = max(0, (float) $invoice->total - $paid);
        $wasPaid = $invoice->status === 'paid';
        $invoice->update([
            'amount_paid' => $paid,
            'amount_due' => $due,
            'status' => $due <= 0 ? 'paid' : 'partially_paid',
            'paid_at' => $due <= 0 ? now() : $invoice->paid_at,
            'stripe_payment_intent_id' => is_string($paymentIntent) ? $paymentIntent : $invoice->stripe_payment_intent_id,
        ]);

        if (!$wasPaid && $due <= 0 && $invoice->customer) {
            $invoice->customer->notify(new \App\Notifications\InvoiceCreatedNotification($invoice->fresh(), 'paid'));
        }

        return ['handled' => true, 'invoice_id' => $invoice->id, 'payment_id' => $payment->id];
    }
}
