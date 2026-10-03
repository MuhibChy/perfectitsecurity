<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;

/**
 * WalletEligibilityService — single place answering wallet capability
 * questions. Ownership (customer-only) is separated from management
 * permission (finance/admin can act on, never own, customer wallets).
 */
class WalletEligibilityService
{
    /** Roles allowed to OWN a customer wallet. Staff never own wallets. */
    public const OWNER_ROLES = ['customer'];

    public static function isOwnerRole(?string $role): bool
    {
        return in_array($role, self::OWNER_ROLES, true);
    }

    /** Is this user eligible to hold a wallet at all? */
    public static function isEligible(User $user): bool
    {
        if (!self::isOwnerRole($user->role)) return false;
        if (!$user->is_active) return false;
        if ($user->isSuspended() || $user->isBlocked()) return false;
        if (!$user->isEmailVerified()) return false;
        return true;
    }

    public static function ineligibilityReason(User $user): ?string
    {
        if (!self::isOwnerRole($user->role)) return 'Only customer accounts hold wallets.';
        if (!$user->is_active) return 'Account is deactivated.';
        if ($user->isSuspended() || $user->isBlocked()) return 'Account is suspended or blocked.';
        if (!$user->isEmailVerified()) return 'Email address is not verified.';
        return null;
    }

    public static function canReceiveFunds(Wallet $wallet): bool
    {
        return $wallet->status === 'active' && self::ownerUsable($wallet);
    }

    public static function canSpend(Wallet $wallet): bool
    {
        return $wallet->status === 'active' && self::ownerUsable($wallet);
    }

    public static function canBeFrozen(Wallet $wallet): bool
    {
        return in_array($wallet->status, ['active', 'frozen'], true);
    }

    public static function canBeClosed(Wallet $wallet): bool
    {
        return $wallet->status !== 'closed'
            && round((float) $wallet->balance, 2) == 0.0;
    }

    protected static function ownerUsable(Wallet $wallet): bool
    {
        $owner = $wallet->owner;
        if (!$owner) return false;
        if (!$owner->is_active) return false;
        if ($owner->isSuspended() || $owner->isBlocked()) return false;
        return true;
    }
}
