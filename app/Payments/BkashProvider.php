<?php

namespace App\Payments;

/**
 * bKash adapter — official merchant/online-business integration surface
 * (tokenized checkout, payment verification, refunds where supported).
 * Runs TEST simulation until live merchant API credentials are stored.
 */
class BkashProvider extends AbstractMobileWalletProvider
{
    public function key(): string
    {
        return 'bkash';
    }

    public function label(): string
    {
        return 'bKash';
    }
}
