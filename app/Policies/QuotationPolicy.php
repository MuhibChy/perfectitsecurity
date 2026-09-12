<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isFinanceManager() || $user->isAdmin();
    }

    public function view(User $user, Quotation $quotation): bool
    {
        if ($user->isFinanceManager() || $user->isAdmin()) {
            return true;
        }

        // Customers can only view their own quotations
        if ($user->isCustomer()) {
            return $quotation->customer_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isFinanceManager() || $user->isAdmin();
    }

    public function update(User $user, Quotation $quotation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFinanceManager()) {
            return in_array($quotation->status, ['draft', 'revised']);
        }

        return false;
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function send(User $user, Quotation $quotation): bool
    {
        if (!$user->isFinanceManager() && !$user->isAdmin()) {
            return false;
        }

        return in_array($quotation->status, ['draft']);
    }

    public function convertToInvoice(User $user, Quotation $quotation): bool
    {
        if (!$user->isFinanceManager() && !$user->isAdmin()) {
            return false;
        }

        return $quotation->status === 'accepted' && !$quotation->invoice_id;
    }

    public function accept(User $user, Quotation $quotation): bool
    {
        return $user->isCustomer() && $quotation->customer_id === $user->id
            && in_array($quotation->status, ['sent']);
    }

    public function reject(User $user, Quotation $quotation): bool
    {
        return $user->isCustomer() && $quotation->customer_id === $user->id
            && in_array($quotation->status, ['sent']);
    }
}
