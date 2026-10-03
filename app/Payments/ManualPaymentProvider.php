<?php

namespace App\Payments;

use App\Models\Invoice;
use App\Models\Payment;

/** Manual / bank-transfer / cash rail: server-recorded, no external API. */
class ManualPaymentProvider implements PaymentProviderInterface
{
    public function key(): string { return 'manual'; }
    public function label(): string { return 'Manual / Bank transfer'; }
    public function supportsCurrency(string $currencyCode): bool { return true; }

    public function createPayment(Invoice $invoice, float $amount, array $options = []): array
    {
        return [
            'provider' => $this->key(),
            'provider_reference' => $options['transaction_id'] ?? ('MANUAL-' . strtoupper(\Illuminate\Support\Str::random(10))),
            'redirect_url' => null,
            'amount' => round($amount, 2),
            'currency' => strtoupper($invoice->currency ?? 'USD'),
        ];
    }

    public function verifyPayment(string $providerReference, array $payload = []): array
    {
        $payment = Payment::where('transaction_id', $providerReference)->first();
        if (!$payment) return ['status' => 'PENDING', 'found' => false];
        return ['status' => \App\Services\PaymentState::canonicalTransactionStatus($payment->status), 'found' => true, 'payment_id' => $payment->id];
    }

    public function handleWebhook(string $payload, ?string $signature = null): array
    {
        return ['handled' => false, 'reason' => 'manual_has_no_webhooks'];
    }

    public function refund(Payment $payment, float $amount, string $reason): array
    {
        return ['provider' => $this->key(), 'payment_id' => $payment->id, 'amount' => $amount, 'note' => 'Record via Admin → Payments → Refund (audited reversal, history preserved).'];
    }

    public function status(string $providerReference): string
    {
        return $this->verifyPayment($providerReference)['status'];
    }
}
