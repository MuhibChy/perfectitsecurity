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

        $data = [
            'openTickets' => Ticket::where('customer_id', $user->id)->open()->count(),
            'activeProjects' => Project::where('customer_id', $user->id)->active()->count(),
            'pendingInvoices' => Invoice::where('customer_id', $user->id)->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])->count(),
            'totalSpent' => Invoice::where('customer_id', $user->id)->where('status', 'paid')->sum('total'),
            'recentTickets' => Ticket::where('customer_id', $user->id)->latest()->limit(5)->get(),
            'recentInvoices' => Invoice::where('customer_id', $user->id)->latest()->limit(5)->get(),
            'activeProjectsList' => Project::where('customer_id', $user->id)->active()->latest()->limit(5)->get(),
        ];

        return view('customer.dashboard', $data);
    }
}
