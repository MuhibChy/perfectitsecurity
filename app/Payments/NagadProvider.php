<?php

namespace App\Payments;

/**
 * Nagad adapter — official merchant/payment-gateway functionality.
 * Replaceable implementation: only this class changes if Nagad's
 * API evolves. TEST simulation until live credentials are stored.
 */
class NagadProvider extends AbstractMobileWalletProvider
{
    public function key(): string { return 'nagad'; }
    public function label(): string { return 'Nagad'; }
}
