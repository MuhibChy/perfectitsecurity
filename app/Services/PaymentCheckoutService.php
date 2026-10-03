<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentProvider;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Payment initiation funnel. Server-authoritative: amount, currency and
 * payable state always come from the invoice row — never the browser.
 * Idempotent on idempotency_key (double-click / refresh / back-button safe).
 */
class PaymentCheckoutService
{
    public const PAYABLE = ['sent', 'viewed', 'overdue', 'partially_paid'];

    public function __construct(
        protected PaymentProviderService $providers
    ) {
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (422/403)
     */
    public function initiate(Invoice $invoice, int $customerId, string $providerKey, array $options = []): array
    {
        abort_unless((int) $invoice->customer_id === (int) $customerId, 403);
        abort_unless(in_array($invoice->status, self::PAYABLE, true), 422, 'This invoice is not payable.');
        $invoiceCurrency = strtoupper($invoice->currency ?? 'USD');

        $providerKey = strtolower($providerKey);
        $row = PaymentProvider::where('key', $providerKey)->first();
        if ($row) {
            abort_unless($row->isUsable(), 422, 'This payment provider is not available.');
        }
        $adapter = $this->providers->resolve($providerKey, $row);
        abort_unless($adapter->supportsCurrency($invoiceCurrency) || ! empty($options['pay_currency']), 422, 'Provider does not support '.$invoiceCurrency.'.');

        // Server-side amount: requested clamped to outstanding balance.
        $outstanding = round((float) $invoice->total - (float) $invoice->amount_paid, 2);
        abort_if($outstanding <= 0, 422, 'No outstanding balance on this invoice.');
        $requested = isset($options['amount']) ? round((float) $options['amount'], 2) : $outstanding;
        abort_if($requested <= 0, 422, 'Payment amount must be greater than zero.');
        $amount = min($requested, $outstanding);

        // Optional display/settlement currency conversion (rate recorded).
        $payCurrency = strtoupper($options['pay_currency'] ?? $invoiceCurrency);
        $rate = null;
        $converted = null;
        if ($payCurrency !== $invoiceCurrency) {
            $rate = app(CurrencyService::class)->getRate($invoiceCurrency, $payCurrency);
            $converted = round($amount * $rate, 2);
        }

        if ($row) {
            abort_unless($row->supportsAmount($converted ?? $amount), 422, 'Amount outside provider limits.');
        }
        $fees = $row ? $row->quoteFees($amount) : ['gross' => $amount, 'provider_fee' => 0.0, 'platform_fee' => 0.0, 'net' => $amount];

        $idempotencyKey = (string) ($options['idempotency_key'] ?? ('chk_'.Str::uuid()));

        return DB::transaction(function () use ($invoice, $customerId, $providerKey, $row, $adapter, $amount, $invoiceCurrency, $payCurrency, $rate, $converted, $fees, $options, $idempotencyKey) {
            $existing = PaymentTransaction::where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
            if ($existing) {
                return ['transaction' => $existing->fresh(), 'duplicate' => true, 'adapter' => []];
            }

            $txn = PaymentTransaction::create([
                'idempotency_key' => $idempotencyKey,
                'customer_id' => $customerId,
                'service_order_id' => $invoice->service_order_id,
                'invoice_id' => $invoice->id,
                'service_id' => $options['service_id'] ?? null,
                'project_id' => $invoice->project_id,
                'provider_id' => $row?->id,
                'provider_key' => $providerKey,
                'payment_method' => $options['payment_method'] ?? $providerKey,
                'original_amount' => $amount,
                'original_currency' => $invoiceCurrency,
                'exchange_rate' => $rate,
                'converted_amount' => $converted,
                'settlement_currency' => $invoiceCurrency,
                'provider_currency' => $payCurrency,
                'provider_amount' => $converted ?? $amount,
                'gross_amount' => $fees['gross'],
                'provider_fee' => $fees['provider_fee'],
                'platform_fee' => $fees['platform_fee'],
                'net_amount' => $fees['net'],
                'status' => 'initiated',
                'metadata' => array_merge($options['metadata'] ?? [], [
                    'invoice_number' => $invoice->invoice_number,
                    'return_url' => $options['return_url'] ?? null,
                ]),
                'created_by' => $customerId,
            ]);

            $adapterResult = $adapter->createPayment($invoice, $amount, array_merge($options, [
                'provider_reference' => $options['provider_reference'] ?? null,
                'return_url' => $options['return_url'] ?? route('portal.checkout.callback', ['provider' => $providerKey, 'reference' => $txn->reference]),
            ]));

            $txn->provider_reference = $adapterResult['provider_reference'] ?? $txn->provider_reference;
            $txn->transitionTo($providerKey === 'bank_transfer' ? 'pending_verification' : 'pending');

            \App\Models\AuditLog::log('payment.initiated', 'payment_transactions', $txn, "Checkout initiated: {$invoiceCurrency} {$amount} via {$providerKey} for {$invoice->invoice_number}.");

            return ['transaction' => $txn->fresh(), 'duplicate' => false, 'adapter' => $adapterResult];
        });
    }
}
