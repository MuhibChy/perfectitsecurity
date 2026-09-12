<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Staff with support access can view all tickets.
     * Customers can only view their own tickets.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSupportAgent() || $user->isAdmin();
    }

    /**
     * Support agents/managers/admins can view any ticket.
     * Customers can only view their own tickets.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isSupportManager()) {
            return true;
        }

        // Support agents can only view assigned or unassigned tickets
        if ($user->isSupportAgent()) {
            return $ticket->assigned_to === $user->id || is_null($ticket->assigned_to);
        }

        // Customers can only view their own tickets
        if ($user->isCustomer()) {
            return $ticket->customer_id === $user->id;
        }

        return false;
    }

    /**
     * Customers can create tickets. Support staff can also create on behalf.
     */
    public function create(User $user): bool
    {
        return $user->isCustomer() || $user->isSupportAgent() || $user->isAdmin();
    }

    /**
     * Only support agents/managers assigned to the ticket, or admins, can update.
     * Customers can add replies to their own tickets.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isSupportManager()) {
            return true;
        }

        if ($user->isSupportAgent()) {
            return $ticket->assigned_to === $user->id;
        }

        // Customers can update their own tickets (for replies)
        if ($user->isCustomer()) {
            return $ticket->customer_id === $user->id
                && in_array($ticket->status, ['new', 'in_progress', 'waiting_customer', 'reopened']);
        }

        return false;
    }

    /**
     * Only support managers and admins can assign tickets.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->isSupportManager();
    }

    /**
     * Only support agents/managers can reply as staff.
     * Customers can reply to their own tickets.
     */
    public function reply(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isSupportManager()) {
            return true;
        }

        if ($user->isSupportAgent()) {
            return $ticket->assigned_to === $user->id;
        }

        if ($user->isCustomer()) {
            return $ticket->customer_id === $user->id;
        }

        return false;
    }

    /**
     * Only support staff can change ticket status.
     */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isSupportManager()) {
            return true;
        }

        if ($user->isSupportAgent()) {
            return $ticket->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Only support staff can add internal notes.
     */
    public function addNote(User $user, Ticket $ticket): bool
    {
        return $user->isSupportAgent() || $user->isAdmin();
    }

    /**
     * Only admins can delete tickets.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }
}
