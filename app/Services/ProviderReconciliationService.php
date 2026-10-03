<?php

namespace App\Services;

use App\Models\PaymentReconciliationRecord;
use App\Models\PaymentTransaction;

/**
 * Internal-vs-provider reconciliation sweep. Compares each settled
 * transaction against the adapter's authoritative status and persists a
 * flag row for finance review (matched | mismatch | requires_verification).
 */
class ProviderReconciliationService
{
    public function __construct(protected PaymentProviderService $providers) {}

    public function sweep(int $limit = 200): array
    {
        $txns = PaymentTransaction::whereIn('status', ['paid', 'refunded', 'partially_refunded', 'requires_verification'])
            ->latest('id')->limit($limit)->get();
        $summary = ['checked' => 0, 'matched' => 0, 'mismatch' => 0, 'requires_verification' => 0];
        foreach ($txns as $txn) {
            $summary['checked']++;
            try {
                $adapter = $this->providers->resolve($txn->provider_key);
                $providerStatus = $adapter->status($txn->provider_reference ?: $txn->reference);
            } catch (\Throwable $e) {
                $providerStatus = 'UNKNOWN';
            }
            $amountOk = abs(round((float) $txn->gross_amount, 2) - round((float) $txn->original_amount, 2)) < 0.015;
            $settled = in_array($txn->status, ['paid', 'refunded', 'partially_refunded'], true);
            $providerOk = in_array($providerStatus, ['SUCCEEDED', 'REFUNDED', 'PARTIALLY_REFUNDED', 'PENDING'], true);
            if ($txn->status === 'requires_verification' || $providerStatus === 'UNKNOWN') {
                $result = 'requires_verification';
            } elseif ($amountOk && ($providerOk || !$settled)) {
                $result = 'matched';
            } else {
                $result = 'mismatch';
            }
            $summary[$result]++;
            PaymentReconciliationRecord::updateOrCreate(
                ['internal_reference' => $txn->reference],
                [
                    'provider_key' => $txn->provider_key,
                    'provider_reference' => $txn->provider_reference,
                    'internal_amount' => $txn->gross_amount,
                    'internal_currency' => $txn->original_currency,
                    'provider_amount' => $txn->provider_amount,
                    'provider_currency' => $txn->provider_currency,
                    'internal_status' => $txn->status,
                    'provider_status' => $providerStatus,
                    'result' => $result,
                    'notes' => $result === 'matched' ? null : 'Auto-flagged: review provider dashboard vs internal ledger.',
                    'checked_at' => now(),
                ]
            );
        }
        if ($summary['mismatch'] > 0 || $summary['requires_verification'] > 0) {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($summary) {
                foreach (\App\Models\User::whereIn('role', ['super_admin', 'admin', 'finance_manager'])->where('is_active', true)->limit(20)->get() as $staff) {
                    $staff->notify(new \App\Notifications\PaymentStatusNotification('reconciliation_mismatch', [
                        'mismatch' => $summary['mismatch'],
                        'requires_verification' => $summary['requires_verification'],
                        'url' => route('admin.payments.reconciliation'),
                    ]));
                }
            });
        }
        return $summary;
    }
}
