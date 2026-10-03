<?php

namespace App\Payments;

/**
 * International Gateway adapter — supports configurable international gateways.
 * Operates in TEST mode until live credentials are saved.
 */
class InternationalGatewayPaymentProvider extends AbstractInternationalGatewayProvider
{
    public function key(): string { return 'international_gateway'; }
    public function label(): string { return 'International Gateway'; }
}
