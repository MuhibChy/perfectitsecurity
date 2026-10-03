<?php

namespace App\Services;

use App\Models\PaymentProvider;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhookEvent;
use App\Notifications\PaymentStatusNotification;
use Illuminate\Support\Facades\DB;

/**
 * Secure per-provider webhook pipeline:
 * store event → verify signature → idempotency → settle → ledger.
 * Browser redirects are NEVER trusted; only this server-side path
 * (or explicit finance verification) settles money.
 */
class PaymentWebhookService
{
    public function __construct(
        protected PaymentProviderService $providers,
        protected PaymentSettlementService $settlement
    ) {
    }

    /**
     * Ingest a raw webhook body. Idempotent on provider+event_id:
     * replays return ['duplicate'=>true] without touching money.
     */
    public function ingest(string $providerKey, string $eventId, array $payload, ?string $signature, string $rawBody): array
    {
        $providerKey = strtolower($providerKey);
        $event = DB::transaction(function () use ($providerKey, $eventId, $payload, $signature, $rawBody) {
            $existing = PaymentWebhookEvent::where('provider_key', $providerKey)->where('event_id', $eventId)->lockForUpdate()->first();
            if ($existing) {
                if ($existing->status === 'processed') {
                    $existing->increment('attempts');
                    $existing->update(['status' => 'duplicate']);
                }

                return $existing;
            }
            $row = PaymentProvider::where('key', $providerKey)->first();
            $signatureValid = false;
            if ($row && $row->webhook_secret && $signature) {
                $signatureValid = hash_equals(hash_hmac('sha256', $rawBody, $row->webhook_secret), $signature);
            } elseif (! $row || ! $row->webhook_secret) {
                // No secret configured (test mode): payload accepted but
                // flagged unverified — settlement still requires a
                // matching, payable internal transaction.
                $signatureValid = false;
            }

            return PaymentWebhookEvent::create([
                'provider_key' => $providerKey,
                'event_id' => $eventId,
                'transaction_reference' => $payload['transaction_reference'] ?? $payload['reference'] ?? null,
                'payload' => $payload,
                'signature_valid' => $signatureValid,
                'status' => 'received',
            ]);
        });

        if ($event->status === 'duplicate') {
            return ['duplicate' => true, 'event' => $event->fresh()];
        }
        if (in_array($event->status, ['processed'], true)) {
            return ['duplicate' => true, 'event' => $event->fresh()];
        }

        return $this->process($event->fresh());
    }

    /** Process one stored event exactly once (row-locked). */
    public function process(PaymentWebhookEvent $event): array
    {
        return DB::transaction(function () use ($event) {
            $event = PaymentWebhookEvent::lockForUpdate()->findOrFail($event->id);
            if (in_array($event->status, ['processed', 'duplicate'], true)) {
                return ['duplicate' => true, 'event' => $event];
            }
            $event->update(['status' => 'processing', 'attempts' => $event->attempts + 1]);

            try {
                $payload = $event->payload ?? [];
                $txn = null;
                if (! empty($event->transaction_reference)) {
                    $txn = PaymentTransaction::where('reference', $event->transaction_reference)->first();
                }
                if (! $txn && ! empty($payload['provider_reference'])) {
                    $txn = PaymentTransaction::where('provider_reference', $payload['provider_reference'])->first();
                }
                abort_unless($txn, 422, 'Unknown transaction reference.');

                $verdict = strtolower($payload['provider_status'] ?? $payload['status'] ?? 'paid');
                if (in_array($verdict, ['paid', 'success', 'completed', 'succeeded', 'verified'], true)) {
                    // Hardened rails (e.g. Stripe live) require a valid
                    // signature; test-mode/manual rails settle on a known
                    // internal reference + payable invoice instead.
                    $row = PaymentProvider::where('key', $event->provider_key)->first();
                    $strict = $row && $row->isLive() && in_array($event->provider_key, ['stripe', 'card'], true);
                    if ($strict && ! $event->signature_valid) {
                        throw new \RuntimeException('Invalid webhook signature for live provider.');
                    }
                    $result = $this->settlement->settle($txn, null, $event->provider_key.':'.$event->event_id);
                    $event->update(['status' => 'processed', 'processed_at' => now()]);

                    return ['settled' => true, 'duplicate' => $result['duplicate'], 'event' => $event->fresh()];
                }

                if (in_array($verdict, ['failed', 'cancelled', 'expired'], true)) {
                    app(PaymentSettlementService::class)->markFailed($txn, 'Provider reported: '.$verdict);
                    $event->update(['status' => 'processed', 'processed_at' => now()]);
                    $this->notifyPaymentFailed($txn, 'Provider reported: '.$verdict);

                    return ['settled' => false, 'event' => $event->fresh()];
                }

                // Uncertain state: never auto-charge elsewhere; flag review.
                $txn->transitionTo('requires_verification');
                $event->update(['status' => 'processed', 'processed_at' => now(), 'error' => 'Uncertain provider state; flagged for review.']);
                $this->notifyFinanceReview($txn, 'Provider returned an uncertain state; payment requires verification.');

                return ['settled' => false, 'needs_review' => true, 'event' => $event->fresh()];
            } catch (\Throwable $e) {
                $event->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 1000)]);

                return ['settled' => false, 'error' => $e->getMessage(), 'event' => $event->fresh()];
            }
        });
    }

    /** Customer + finance ping when a provider reports failure. */
    protected function notifyPaymentFailed(PaymentTransaction $txn, string $reason): void
    {
        $customer = $txn->customer;
        $reference = $txn->reference;
        $amount = $txn->original_amount;
        $currency = $txn->original_currency;
        $providerKey = $txn->provider_key;
        DB::afterCommit(function () use ($customer, $reference, $amount, $currency, $providerKey) {
            if ($customer) {
                $customer->notify(new PaymentStatusNotification('payment_failed', [
                    'reference' => $reference,
                    'amount' => $amount,
                    'currency' => $currency,
                    'provider' => $providerKey,
                    'url' => route('portal.payments.show', $reference),
                ]));
            }
            foreach (\App\Models\User::whereIn('role', ['super_admin', 'admin', 'finance_manager'])->where('is_active', true)->limit(20)->get() as $staff) {
                $staff->notify(new PaymentStatusNotification('payment_failed', [
                    'reference' => $reference,
                    'amount' => $amount,
                    'currency' => $currency,
                    'provider' => $providerKey,
                    'url' => route('admin.payments.overview'),
                ]));
            }
        });
    }

    /** Finance ping for uncertain provider states needing human review. */
    protected function notifyFinanceReview(PaymentTransaction $txn, string $reason): void
    {
        $reference = $txn->reference;
        $amount = $txn->original_amount;
        $currency = $txn->original_currency;
        $providerKey = $txn->provider_key;
        DB::afterCommit(function () use ($reference, $amount, $currency, $providerKey, $reason) {
            foreach (\App\Models\User::whereIn('role', ['super_admin', 'admin', 'finance_manager'])->where('is_active', true)->limit(20)->get() as $staff) {
                $staff->notify(new PaymentStatusNotification('payment_pending', [
                    'reference' => $reference,
                    'amount' => $amount,
                    'currency' => $currency,
                    'provider' => $providerKey,
                    'reason' => $reason,
                    'url' => route('admin.payments.overview'),
                ]));
            }
        });
    }
}
