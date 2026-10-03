<?php

/*
|--------------------------------------------------------------------------
| Flexible multi-provider payment configuration (additive)
|--------------------------------------------------------------------------
|
| Every secret lives ONLY in environment variables (never in the database in
| plaintext — PaymentProvider encrypts credentials at rest via model
| mutators — and never in logs). DB rows in `payment_providers` select
| environment/test/live per provider; these env values supply the actual
| keys per environment. Nothing here changes existing Stripe behaviour:
| StripePaymentService keeps reading config('services.stripe').
|
*/

return [
    'default_currency' => env('PAYMENT_DEFAULT_CURRENCY', 'BDT'),
    'supported_currencies' => array_values(array_filter(array_map('trim', explode(',', env('PAYMENT_SUPPORTED_CURRENCIES', 'BDT,GBP,USD,EUR'))))),
    'timeout_seconds' => (int) env('PAYMENT_TIMEOUT_SECONDS', 900),
    'invoice_deadline_days' => (int) env('INVOICE_PAYMENT_DEADLINE_DAYS', 14),

    // Bangladesh mobile wallets — official merchant/API credentials only.
    // bKash tokenized checkout: app key/secret + username/password per env.
    'bkash' => [
        'test' => [
            'app_key' => env('BKASH_TEST_APP_KEY'),
            'app_secret' => env('BKASH_TEST_APP_SECRET'),
            'username' => env('BKASH_TEST_USERNAME'),
            'password' => env('BKASH_TEST_PASSWORD'),
            'base_url' => env('BKASH_TEST_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'),
            'webhook_secret' => env('BKASH_TEST_WEBHOOK_SECRET'),
        ],
        'live' => [
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'base_url' => env('BKASH_BASE_URL', 'https://tokenized.pay.bka.sh/v1.2.0-beta'),
            'webhook_secret' => env('BKASH_WEBHOOK_SECRET'),
        ],
    ],

    // Nagad / Rocket (DBBL): merchant credentials supplied by the provider.
    // Adapters are replaceable — swap the class in PaymentProviderRegistry
    // if the bank changes its API, no business logic rewrite needed.
    'nagad' => [
        'test' => [
            'merchant_id' => env('NAGAD_TEST_MERCHANT_ID'),
            'merchant_key' => env('NAGAD_TEST_MERCHANT_KEY'),
            'base_url' => env('NAGAD_TEST_BASE_URL'),
            'webhook_secret' => env('NAGAD_TEST_WEBHOOK_SECRET'),
        ],
        'live' => [
            'merchant_id' => env('NAGAD_MERCHANT_ID'),
            'merchant_key' => env('NAGAD_MERCHANT_KEY'),
            'base_url' => env('NAGAD_BASE_URL'),
            'webhook_secret' => env('NAGAD_WEBHOOK_SECRET'),
        ],
    ],

    'rocket' => [
        'test' => [
            'merchant_id' => env('ROCKET_TEST_MERCHANT_ID'),
            'account_id' => env('ROCKET_TEST_ACCOUNT_ID'),
            'api_key' => env('ROCKET_TEST_API_KEY'),
            'base_url' => env('ROCKET_TEST_BASE_URL'),
            'webhook_secret' => env('ROCKET_TEST_WEBHOOK_SECRET'),
        ],
        'live' => [
            'merchant_id' => env('ROCKET_MERCHANT_ID'),
            'account_id' => env('ROCKET_ACCOUNT_ID'),
            'api_key' => env('ROCKET_API_KEY'),
            'base_url' => env('ROCKET_BASE_URL'),
            'webhook_secret' => env('ROCKET_WEBHOOK_SECRET'),
        ],
    ],

    // International: multiple gateways may operate simultaneously.
    // Stripe eligibility is country-dependent (UK yes, BD no) so this is
    // one option among card/gateway/wire — never hard-coded as the only one.
    'paypal' => [
        'test' => [
            'client_id' => env('PAYPAL_TEST_CLIENT_ID'),
            'client_secret' => env('PAYPAL_TEST_CLIENT_SECRET'),
            'webhook_id' => env('PAYPAL_TEST_WEBHOOK_ID'),
        ],
        'live' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        ],
    ],

    'international_gateway' => [
        'test' => [
            'merchant_id' => env('INTL_GATEWAY_TEST_MERCHANT_ID'),
            'api_key' => env('INTL_GATEWAY_TEST_API_KEY'),
            'api_secret' => env('INTL_GATEWAY_TEST_API_SECRET'),
            'base_url' => env('INTL_GATEWAY_TEST_BASE_URL'),
            'webhook_secret' => env('INTL_GATEWAY_TEST_WEBHOOK_SECRET'),
        ],
        'live' => [
            'merchant_id' => env('INTL_GATEWAY_MERCHANT_ID'),
            'api_key' => env('INTL_GATEWAY_API_KEY'),
            'api_secret' => env('INTL_GATEWAY_API_SECRET'),
            'base_url' => env('INTL_GATEWAY_BASE_URL'),
            'webhook_secret' => env('INTL_GATEWAY_WEBHOOK_SECRET'),
        ],
    ],
];
