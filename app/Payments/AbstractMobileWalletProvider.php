<?php

namespace App\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentProvider;
use App\Models\PaymentTransaction;

/**
 * Shared base for bKash / Nagad / Rocket adapters.
 *
 * Each adapter talks to that brand's OFFICIAL merchant/API integration
 * (tokenized checkout, verification, refunds where supported) once the
 * company supplies live credentials. Until then every adapter runs in
 * TEST mode: a faithful state-machine simulation (initiate → pending →
 * verify → paid) so checkout, webhooks, idempotency and reconciliation
 * can be proven end-to-end without touching real money.
 *
 * NEVER stores customer PIN / OTP / passwords — only provider-issued
 * tokens and references.
 */
abstract class AbstractMobileWalletProvider implements PaymentProviderInterface
{
    protected ?PaymentProvider $config;

    public function __construct(?PaymentProvider $config = null)
    {
        $this->config = $config;
    }

    abstract public function key(): string;
    abstract public function label(): string;

    /** Mobile wallets in this rollout settle BDT. */
    public function supportsCurrency(string $currencyCode): bool
    {
        return strtoupper($currencyCode) === 'BDT';
    }

    public function isLive(): bool
    {
        return (bool) ($this->config?->isLive());
    }

    /** Live calls require real merchant credentials — fail loudly otherwise. */
    protected function requireLiveCredentials(): array
    {
        $creds = $this->config?->credentials ?? [];
        if (!$this->isLive() || empty($creds['api_key']) || empty($creds['secret_key'])) {
            abort(422, $this->label() . ' live payments are not configured. Complete merchant credentials in Admin → Payments → Providers first.');
        }
        return $creds;
    }

    public function createPayment(Invoice $invoice, float $amount, array $options = []): array
    {
        // Cross-currency checkout: the charged currency is the requested
        // pay_currency (converted + recorded upstream); otherwise invoice.
        $chargeCurrency = strtoupper($options['pay_currency'] ?? ($invoice->currency ?? ''));
        abort_unless($this->supportsCurrency($chargeCurrency), 422, $this->label() . ' settles BDT invoices only.');
        $reference = ($options['provider_reference'] ?? null) ?: strtoupper($this->key()) . '-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8));
        if ($this->isLive()) {
            // Live path: credentials present (requireLiveCredentials throws
            // otherwise). Provider-specific API call plugs in here; the
            // returned shape stays identical so business code never changes.
            $this->requireLiveCredentials();
            return [
                'provider' => $this->key(),
                'provider_reference' => $reference,
                'redirect_url' => $options['return_url'] ?? null,
                'amount' => round($amount, 2),
                'currency' => $chargeCurrency,
                'mode' => 'live',
            ];
        }
        return [
            'provider' => $this->key(),
            'provider_reference' => $reference,
            'redirect_url' => $options['return_url'] ?? null,
            'amount' => round($amount, 2),
            'currency' => $chargeCurrency,
            'mode' => 'test',
        ];
    }

    /**
     * Server-side verification (authoritative). TEST mode resolves against
     * the internal transaction + stored provider token; LIVE mode would
     * query the provider's official verification endpoint here.
     */
    public function verifyPayment(string $providerReference, array $payload = []): array
    {
        $txn = PaymentTransaction::where('provider_reference', $providerReference)->first();
        if (!$txn) return ['status' => 'PENDING', 'found' => false];
        if ($this->isLive() && ($payload['provider_status'] ?? null)) {
            // Live: map the provider's authoritative status response.
            $map = ['success' => 'SUCCEEDED', 'completed' => 'SUCCEEDED', 'failed' => 'FAILED', 'cancelled' => 'CANCELLED'];
            return ['status' => $map[strtolower($payload['provider_status'])] ?? 'PENDING', 'found' => true, 'transaction_id' => $txn->id];
        }
        $settled = $txn->payment_id ? 'completed' : $txn->status;
        return [
            'status' => \App\Services\PaymentState::canonicalTransactionStatus($settled),
            'found' => true,
            'transaction_id' => $txn->id,
        ];
    }

    public function handleWebhook(string $payload, ?string $signature = null): array
    {
        $data = json_decode($payload, true);
        if (!is_array($data)) return ['handled' => false, 'reason' => 'invalid_json'];
        $secret = $this->config?->webhook_secret;
        if ($secret && $signature) {
            $expected = hash_hmac('sha256', $payload, $secret);
            if (!hash_equals($expected, $signature)) {
                return ['handled' => false, 'reason' => 'bad_signature'];
            }
            $data['_signature_valid'] = true;
        }
        return ['handled' => true, 'provider' => $this->key(), 'data' => $data];
    }

    public function refund(Payment $payment, float $amount, string $reason): array
    {
        // Provider refund executes through PaymentRefundService (approval
        // workflow); the adapter only describes provider capability.
        return [
            'provider' => $this->key(),
            'payment_id' => $payment->id,
            'amount' => $amount,
            'mode' => $this->isLive() ? 'live' : 'test',
            'note' => 'Processed via Admin → Payments → Refunds (audited, idempotent).',
        ];
    }

    public function status(string $providerReference): string
    {
        return $this->verifyPayment($providerReference)['status'];
    }
}
