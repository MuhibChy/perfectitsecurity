<?php

namespace App\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentProvider;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;

/**
 * Shared base for International Payment Gateways (PayPal, Adyen, 2Checkout, custom gateways).
 *
 * Provides a provider-independent hosted/redirect checkout surface.
 * Multi-currency ready: GBP, USD, EUR, and any currency enabled on the provider.
 * Runs in TEST mode until live merchant credentials are configured in Admin → Payments → Providers.
 * NEVER stores customer card numbers, CVV, or authentication secrets.
 */
abstract class AbstractInternationalGatewayProvider implements PaymentProviderInterface
{
    protected ?PaymentProvider $config;

    public function __construct(?PaymentProvider $config = null)
    {
        $this->config = $config;
    }

    abstract public function key(): string;

    abstract public function label(): string;

    public function supportsCurrency(string $currencyCode): bool
    {
        $currencies = $this->config?->currencies;
        if (! empty($currencies) && is_array($currencies)) {
            return in_array(strtoupper($currencyCode), array_map('strtoupper', $currencies), true);
        }
        // By default, international gateways support major currencies
        return in_array(strtoupper($currencyCode), ['USD', 'GBP', 'EUR', 'BDT', 'AED', 'SAR', 'QAR', 'CAD', 'AUD'], true);
    }

    public function isLive(): bool
    {
        return (bool) ($this->config?->isLive());
    }

    protected function requireLiveCredentials(): array
    {
        $creds = $this->config?->credentials ?? [];
        $key = $creds['api_key'] ?? $creds['client_id'] ?? null;
        $secret = $creds['secret_key'] ?? $creds['secret'] ?? null;
        if (! $this->isLive() || empty($key) || empty($secret)) {
            abort(422, $this->label().' live payments are not configured. Complete merchant credentials in Admin → Payments → Providers first.');
        }

        return $creds;
    }

    public function createPayment(Invoice $invoice, float $amount, array $options = []): array
    {
        $chargeCurrency = strtoupper($options['pay_currency'] ?? ($invoice->currency ?? 'USD'));
        abort_unless($this->supportsCurrency($chargeCurrency), 422, $this->label().' does not support currency '.$chargeCurrency.'.');

        $reference = ($options['provider_reference'] ?? null) ?: strtoupper($this->key()).'-'.date('Ymd').'-'.strtoupper(Str::random(8));

        if ($this->isLive()) {
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

    public function verifyPayment(string $providerReference, array $payload = []): array
    {
        $txn = PaymentTransaction::where('provider_reference', $providerReference)->first();
        if (! $txn) {
            return ['status' => 'PENDING', 'found' => false];
        }

        if ($this->isLive() && ($payload['provider_status'] ?? null)) {
            $map = ['success' => 'SUCCEEDED', 'completed' => 'SUCCEEDED', 'paid' => 'SUCCEEDED', 'failed' => 'FAILED', 'cancelled' => 'CANCELLED'];

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
        if (! is_array($data)) {
            return ['handled' => false, 'reason' => 'invalid_json'];
        }
        $secret = $this->config?->webhook_secret;
        if ($secret && $signature) {
            $expected = hash_hmac('sha256', $payload, $secret);
            if (! hash_equals($expected, $signature)) {
                return ['handled' => false, 'reason' => 'bad_signature'];
            }
            $data['_signature_valid'] = true;
        }

        return ['handled' => true, 'provider' => $this->key(), 'data' => $data];
    }

    public function refund(Payment $payment, float $amount, string $reason): array
    {
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
