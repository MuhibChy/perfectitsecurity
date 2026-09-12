<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isFinanceManager() || $user->isAdmin();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        // Finance managers and admins can view all
        if ($user->isFinanceManager() || $user->isAdmin()) {
            return true;
        }

        // Customers can only view their own invoices
        if ($user->isCustomer()) {
            return $invoice->customer_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isFinanceManager() || $user->isAdmin();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFinanceManager()) {
            // Can only edit draft invoices
            return $invoice->status === 'draft';
        }

        return false;
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function send(User $user, Invoice $invoice): bool
    {
        if (!$user->isFinanceManager() && !$user->isAdmin()) {
            return false;
        }

        return in_array($invoice->status, ['draft']);
    }

    public function recordPayment(User $user, Invoice $invoice): bool
    {
        if (!$user->isFinanceManager() && !$user->isAdmin()) {
            return false;
        }

        return !in_array($invoice->status, ['paid', 'cancelled', 'refunded']);
    }

    public function downloadPdf(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }
}
