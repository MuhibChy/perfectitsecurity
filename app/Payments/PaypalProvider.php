<?php

namespace App\Payments;

/**
 * PayPal provider adapter — official merchant/gateway payments surface.
 * Operates in TEST mode until live PayPal credentials (client_id / secret) are saved.
 */
class PaypalProvider extends AbstractInternationalGatewayProvider
{
    public function key(): string
    {
        return 'paypal';
    }

    public function label(): string
    {
        return 'PayPal';
    }
}
