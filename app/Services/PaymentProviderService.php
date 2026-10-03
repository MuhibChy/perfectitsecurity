<?php

namespace App\Services;

use App\Models\PaymentProvider;
use App\Payments\BankTransferPaymentProvider;
use App\Payments\BkashProvider;
use App\Payments\ManualPaymentProvider;
use App\Payments\NagadProvider;
use App\Payments\PaymentProviderInterface;
use App\Payments\RocketProvider;
use App\Payments\StripePaymentProvider;
use App\Payments\WalletPaymentProvider;

/**
 * DB-backed provider registry + availability filtering + adapter factory.
 *
 * Providers are data (Admin → Payments → Providers), not code: enabling,
 * disabling, re-prioritizing or adding one never requires a deploy.
 * Code adapters stay thin and replaceable per brand.
 */
class PaymentProviderService
{
    /** Adapter classes keyed by provider key (config row injected). */
    public const ADAPTERS = [
        'bkash' => BkashProvider::class,
        'nagad' => NagadProvider::class,
        'rocket' => RocketProvider::class,
        'bank_transfer' => BankTransferPaymentProvider::class,
        'bank' => BankTransferPaymentProvider::class,
        'stripe' => StripePaymentProvider::class,
        'card' => StripePaymentProvider::class,
        'paypal' => \App\Payments\PaypalProvider::class,
        'international_gateway' => \App\Payments\InternationalGatewayPaymentProvider::class,
        'manual' => ManualPaymentProvider::class,
        'cash' => ManualPaymentProvider::class,
        'wallet' => WalletPaymentProvider::class,
    ];

    public function resolve(string $key, ?PaymentProvider $config = null): PaymentProviderInterface
    {
        $key = strtolower($key);
        $config ??= PaymentProvider::where('key', $key)->first();
        $class = self::ADAPTERS[$key] ?? ManualPaymentProvider::class;
        if (in_array($class, [
            BkashProvider::class,
            NagadProvider::class,
            RocketProvider::class,
            \App\Payments\PaypalProvider::class,
            \App\Payments\InternationalGatewayPaymentProvider::class,
        ], true)) {
            return new $class($config);
        }
        return app($class);
    }

    /**
     * Providers available for a checkout: usable + currency + amount,
     * ordered by priority. Optional country preference (BD customers see
     * mobile wallets first, others see international rails first).
     */
    public function availableFor(string $currency, float $amount, ?string $country = null): array
    {
        $currency = strtoupper($currency);
        $rows = PaymentProvider::usable()->ordered()->get()->filter(
            fn ($p) => $p->supportsCurrency($currency) && $p->supportsAmount($amount)
        )->values();
        if ($country) {
            $isBd = stripos($country, 'bangladesh') !== false || stripos($country, 'bd') === 0;
            $rows = $rows->sortBy(function ($p) use ($isBd) {
                $bdRail = in_array($p->key, ['bkash', 'nagad', 'rocket', 'bank_transfer'], true);
                if ($isBd) return [$bdRail ? 0 : 1, $p->priority];
                return [$bdRail && $p->key !== 'bank_transfer' ? 1 : 0, $p->priority];
            })->values();
        }
        return $rows->all();
    }

    public function webhookUrl(PaymentProvider $provider): string
    {
        return $provider->webhook_url ?: route('payments.webhook', $provider->key);
    }

    public function modeLabel(PaymentProvider $provider): string
    {
        if (!$provider->is_active || $provider->status === 'disabled') return 'DISABLED';
        if ($provider->status === 'maintenance') return 'MAINTENANCE';
        return $provider->environment === 'live' && $provider->status === 'live' ? 'LIVE' : 'TEST';
    }
}
