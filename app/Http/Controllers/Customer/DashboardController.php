<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->loadMissing('profileDetail');

        $data = [
            // Authenticated owner's profile (never another user, no ID param).
            'user' => $user,
            'completion' => app(\App\Services\ProfileCompletionService::class)->for($user),
            'openTickets' => Ticket::where('customer_id', $user->id)->open()->count(),
            'activeProjects' => Project::where('customer_id', $user->id)->active()->count(),
            'pendingInvoices' => Invoice::where('customer_id', $user->id)->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])->count(),
            // Lifetime paid MUST come from authoritative successful payment
            // transactions (§14/§62/§63) — never invoice totals. Refund rows
            // carry status 'refunded' so they are excluded here by design.
            'totalSpent' => \App\Models\Payment::where('customer_id', $user->id)->where('status', 'completed')->sum('amount'),
            'recentTickets' => Ticket::where('customer_id', $user->id)->with(['assignee', 'category', 'serviceOrder'])->latest()->limit(5)->get(),
            'recentInvoices' => Invoice::where('customer_id', $user->id)->with(['serviceOrder'])->latest()->limit(5)->get(),
            'activeProjectsList' => Project::where('customer_id', $user->id)->with(['service', 'projectManager'])->active()->latest()->limit(5)->get(),
            'walletBalance' => \App\Models\Wallet::where('user_id', $user->id)->sum('balance'),
            'walletCurrency' => \App\Models\Wallet::where('user_id', $user->id)->first()?->currency ?? ($user->preferred_currency ?? 'USD'),
        ];

        return view('customer.dashboard', $data);
    }
}
