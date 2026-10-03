<?php

namespace App\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\StripePaymentService;

class StripePaymentProvider implements PaymentProviderInterface
{
    public function key(): string
    {
        return 'stripe';
    }

    public function label(): string
    {
        return 'Stripe (card / hosted checkout)';
    }

    public function supportsCurrency(string $currencyCode): bool
    {
        return StripePaymentService::supportsCurrency($currencyCode);
    }

    public function createPayment(Invoice $invoice, float $amount, array $options = []): array
    {
        $session = app(StripePaymentService::class)->createInvoiceCheckout(
            $invoice, $options['success_url'] ?? route('portal.dashboard'), $options['cancel_url'] ?? route('portal.dashboard')
        );
        if (! $session) {
            return ['provider' => 'stripe', 'provider_reference' => null, 'redirect_url' => null, 'degraded_to_manual' => true];
        }

        return ['provider' => 'stripe', 'provider_reference' => $session->id, 'redirect_url' => $session->url ?? null];
    }

    public function verifyPayment(string $providerReference, array $payload = []): array
    {
        $payment = Payment::where('stripe_checkout_session_id', $providerReference)
            ->orWhere('stripe_payment_intent_id', $providerReference)->first();
        if (! $payment) {
            return ['status' => 'PENDING', 'found' => false];
        }

        return ['status' => \App\Services\PaymentState::canonicalTransactionStatus($payment->status), 'found' => true, 'payment_id' => $payment->id];
    }

    public function handleWebhook(string $payload, ?string $signature = null): array
    {
        return app(StripePaymentService::class)->handleWebhook($payload, $signature);
    }

    public function refund(Payment $payment, float $amount, string $reason): array
    {
        return ['provider' => 'stripe', 'payment_id' => $payment->id, 'amount' => $amount, 'note' => 'Stripe reversals execute server-side before ledger write (see PaymentController@refund).'];
    }

    public function status(string $providerReference): string
    {
        return $this->verifyPayment($providerReference)['status'];
    }
}
