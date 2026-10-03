<?php

namespace App\Payments;

use App\Models\Invoice;
use App\Models\ManualBankPayment;
use App\Models\Payment;

/**
 * Bank-transfer rail: customer submits transfer details + optional
 * receipt; finance verifies server-side. NEVER auto-marks paid.
 */
class BankTransferPaymentProvider implements PaymentProviderInterface
{
    public function key(): string
    {
        return 'bank_transfer';
    }

    public function label(): string
    {
        return 'Bank Transfer';
    }

    public function supportsCurrency(string $currencyCode): bool
    {
        return true;
    }

    public function createPayment(Invoice $invoice, float $amount, array $options = []): array
    {
        return [
            'provider' => $this->key(),
            'provider_reference' => $options['provider_reference'] ?? ('BANK-'.date('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(8))),
            'redirect_url' => $options['return_url'] ?? null,
            'amount' => round($amount, 2),
            'currency' => strtoupper($invoice->currency ?? 'USD'),
            'mode' => 'manual',
            'requires_verification' => true,
        ];
    }

    public function verifyPayment(string $providerReference, array $payload = []): array
    {
        $txn = \App\Models\PaymentTransaction::where('provider_reference', $providerReference)->first();
        if ($txn) {
            return ['status' => \App\Services\PaymentState::canonicalTransactionStatus($txn->payment_id ? 'completed' : $txn->status), 'found' => true, 'transaction_id' => $txn->id];
        }
        $mbp = ManualBankPayment::where('reference', $providerReference)->orWhere('provider_transaction_id', $providerReference)->first();
        if (! $mbp) {
            return ['status' => 'PENDING', 'found' => false];
        }
        $map = ['pending_verification' => 'PENDING', 'verified' => 'SUCCEEDED', 'rejected' => 'FAILED'];

        return ['status' => $map[$mbp->status] ?? 'PENDING', 'found' => true, 'manual_bank_payment_id' => $mbp->id];
    }

    public function handleWebhook(string $payload, ?string $signature = null): array
    {
        return ['handled' => false, 'reason' => 'bank_transfer_has_no_webhooks'];
    }

    public function refund(Payment $payment, float $amount, string $reason): array
    {
        return ['provider' => $this->key(), 'payment_id' => $payment->id, 'amount' => $amount, 'note' => 'Bank-transfer refunds settle off-rail via Admin → Payments → Refunds (audited).'];
    }

    public function status(string $providerReference): string
    {
        return $this->verifyPayment($providerReference)['status'];
    }
}
