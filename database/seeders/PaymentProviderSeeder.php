<?php

namespace Database\Seeders;

use App\Models\PaymentProvider;
use Illuminate\Database\Seeder;

/**
 * Default provider registry rows (idempotent). All credentials stay
 * empty: admins enter merchant secrets in the UI (stored encrypted).
 * Nothing is LIVE after seeding — enabling is an explicit human act.
 */
class PaymentProviderSeeder extends Seeder
{
    public function run(): void
    {
        $stripeConfigured = (bool) (config('services.stripe.secret') || env('STRIPE_SECRET'));
        $rows = [
            [
                'key' => 'stripe', 'name' => 'Stripe (card / hosted checkout)', 'type' => 'gateway',
                'country' => 'International', 'currencies' => ['AED', 'SAR', 'QAR', 'KWD', 'BHD', 'OMR', 'JOD', 'EUR', 'GBP', 'USD'],
                'payment_methods' => ['checkout', 'card', 'refund'], 'environment' => 'test',
                'status' => $stripeConfigured ? 'test' : 'disabled', 'priority' => 20,
                'fee_type' => 'mixed', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => ['note' => 'Country-eligibility depends on the company legal entity. Not available for Bangladesh-domiciled accounts.'],
            ],
            [
                'key' => 'international_gateway', 'name' => 'International Gateway', 'type' => 'gateway',
                'country' => 'International', 'currencies' => ['USD', 'GBP', 'EUR', 'CAD', 'AUD', 'AED'],
                'payment_methods' => ['checkout', 'card', 'wire'], 'environment' => 'test',
                'status' => 'disabled', 'priority' => 25,
                'fee_type' => 'mixed', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => ['note' => 'Generic international gateway integration (2Checkout, Adyen, Checkout.com, or bank merchant gateway). Enter live credentials to enable.'],
            ],
            [
                'key' => 'paypal', 'name' => 'PayPal', 'type' => 'gateway',
                'country' => 'International', 'currencies' => ['USD', 'GBP', 'EUR', 'CAD', 'AUD'],
                'payment_methods' => ['checkout', 'wallet'], 'environment' => 'test',
                'status' => 'disabled', 'priority' => 22,
                'fee_type' => 'percent', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => ['note' => 'Official PayPal checkout for eligible international jurisdictions. Enter Client ID and Secret in credentials.'],
            ],
            [
                'key' => 'bkash', 'name' => 'bKash', 'type' => 'mobile_wallet',
                'country' => 'Bangladesh', 'currencies' => ['BDT'],
                'payment_methods' => ['checkout', 'tokenized', 'refund'], 'environment' => 'test',
                'status' => 'disabled', 'priority' => 10,
                'fee_type' => 'percent', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => ['integration' => 'Official merchant / tokenized-checkout API. Enter live credentials to enable LIVE.'],
            ],
            [
                'key' => 'nagad', 'name' => 'Nagad', 'type' => 'mobile_wallet',
                'country' => 'Bangladesh', 'currencies' => ['BDT'],
                'payment_methods' => ['checkout', 'refund'], 'environment' => 'test',
                'status' => 'disabled', 'priority' => 11,
                'fee_type' => 'percent', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => ['integration' => 'Official merchant / payment-gateway API. Enter live credentials to enable LIVE.'],
            ],
            [
                'key' => 'rocket', 'name' => 'Rocket (DBBL)', 'type' => 'mobile_wallet',
                'country' => 'Bangladesh', 'currencies' => ['BDT'],
                'payment_methods' => ['checkout'], 'environment' => 'test',
                'status' => 'disabled', 'priority' => 12,
                'fee_type' => 'percent', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => ['integration' => 'DBBL-supported merchant/gateway API. Adapter is replaceable if the API changes.'],
            ],
            [
                'key' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank',
                'country' => 'Bangladesh/International', 'currencies' => null,
                'payment_methods' => ['manual', 'verification'], 'environment' => 'test',
                'status' => 'enabled', 'priority' => 50,
                'fee_type' => 'none', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => ['verification' => 'Manual finance verification required. Never auto-marks paid.'],
            ],
            [
                'key' => 'manual', 'name' => 'Manual / Cash', 'type' => 'manual',
                'country' => 'International', 'currencies' => null,
                'payment_methods' => ['manual'], 'environment' => 'test',
                'status' => 'enabled', 'priority' => 90,
                'fee_type' => 'none', 'fee_value' => 0, 'platform_fee_value' => 0,
                'config' => [],
            ],
        ];
        foreach ($rows as $row) {
            PaymentProvider::firstOrCreate(['key' => $row['key']], $row);
        }
    }
}
