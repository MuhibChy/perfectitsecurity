<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isFinanceManager() || $user->isAdmin();
    }

    public function view(User $user, Wallet $wallet): bool
    {
        // Finance/admin may inspect any wallet without owning it.
        if ($user->isFinanceManager() || $user->isAdmin()) {
            return true;
        }
        // Owners see only their own wallets.
        if ($user->isCustomer()) {
            return (int) $wallet->user_id === (int) $user->id;
        }
        return false;
    }

    public function adjust(User $user, Wallet $wallet): bool
    {
        return $user->isFinanceManager() || $user->isAdmin();
    }

    public function freeze(User $user, Wallet $wallet): bool
    {
        return $user->isFinanceManager() || $user->isAdmin();
    }

    public function correctOwner(User $user, Wallet $wallet): bool
    {
        return $user->isAdmin();
    }
}
