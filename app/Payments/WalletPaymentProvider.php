<?php

namespace App\Payments;

use App\Models\Invoice;
use App\Models\Payment;

class WalletPaymentProvider implements PaymentProviderInterface
{
    public function key(): string { return 'wallet'; }
    public function label(): string { return 'FWallet'; }
    public function supportsCurrency(string $currencyCode): bool { return true; }

    public function createPayment(Invoice $invoice, float $amount, array $options = []): array
    {
        return [
            'provider' => 'wallet',
            'provider_reference' => 'WALLET-' . $invoice->id . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'redirect_url' => null,
            'amount' => round($amount, 2),
            'currency' => strtoupper($invoice->currency ?? 'USD'),
        ];
    }

    public function verifyPayment(string $providerReference, array $payload = []): array
    {
        $payment = Payment::where('transaction_id', $providerReference)->where('gateway', 'wallet')->first();
        if (!$payment) return ['status' => 'PENDING', 'found' => false];
        return ['status' => \App\Services\PaymentState::canonicalTransactionStatus($payment->status), 'found' => true, 'payment_id' => $payment->id];
    }

    public function handleWebhook(string $payload, ?string $signature = null): array
    {
        return ['handled' => false, 'reason' => 'wallet_is_synchronous_ledger'];
    }

    public function refund(Payment $payment, float $amount, string $reason): array
    {
        return ['provider' => 'wallet', 'payment_id' => $payment->id, 'amount' => $amount, 'note' => 'Wallet refunds re-credit the ledger via WalletService::refundWalletPayment (audited, once-only).'];
    }

    public function status(string $providerReference): string
    {
        return $this->verifyPayment($providerReference)['status'];
    }
}
