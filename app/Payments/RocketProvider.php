<?php

namespace App\Payments;

/**
 * Rocket (DBBL) adapter — merchant/gateway payments surface.
 * Replaceable if the bank/provider changes its API. TEST simulation
 * until live credentials are stored.
 */
class RocketProvider extends AbstractMobileWalletProvider
{
    public function key(): string { return 'rocket'; }
    public function label(): string { return 'Rocket (DBBL)'; }
}
