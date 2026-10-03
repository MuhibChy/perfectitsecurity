<?php

namespace App\Payments;

/** Registry: resolve a provider by key; business code depends on the interface. */
class PaymentProviderRegistry
{
    /** @return array<string, class-string> */
    public static function providers(): array
    {
        return [
            'manual' => ManualPaymentProvider::class,
            'bank_transfer' => BankTransferPaymentProvider::class,
            'bank' => BankTransferPaymentProvider::class,
            'cash' => ManualPaymentProvider::class,
            'card' => StripePaymentProvider::class,
            'stripe' => StripePaymentProvider::class,
            'wallet' => WalletPaymentProvider::class,
            'bkash' => BkashProvider::class,
            'nagad' => NagadProvider::class,
            'rocket' => RocketProvider::class,
            'paypal' => PaypalProvider::class,
            'international_gateway' => InternationalGatewayPaymentProvider::class,
        ];
    }

    public static function resolve(string $key): PaymentProviderInterface
    {
        $map = self::providers();
        $class = $map[strtolower($key)] ?? ManualPaymentProvider::class;

        return app($class);
    }

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::providers());
    }
}
