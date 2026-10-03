@extends('layouts.app')
@section('page-title', 'Customer Dashboard')

@section('content')
<div class="space-y-6">
    <x-page-header title="Mission Control" subtitle="Live overview of your tickets, projects, invoices and wallet." sys="CLIENT://DASHBOARD" num="01" />

    {{-- Logged-in user profile card (auth()->user() only — never another profile) --}}
    <div class="term-panel p-6 flex flex-col sm:flex-row sm:items-center gap-5">
        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} profile picture" class="h-20 w-20 rounded-full object-cover border border-slate-300 dark:border-white/10 flex-shrink-0">
        <div class="min-w-0 flex-1">
            <p class="text-lg font-bold text-slate-900 dark:text-white truncate">{{ $user->name }}</p>
            <p class="text-sm text-slate-600 dark:text-term-800 break-all">{{ $user->email }}</p>
            <p class="text-sm text-slate-600 dark:text-term-800">{{ $user->phone ?: 'Contact number: Not provided' }}</p>
            @if($completion['percent'] === 100)
            <p class="mt-1 font-mono text-[11px] tracking-[0.14em] uppercase text-emerald-700 dark:text-accent-soft">Profile Status: Complete</p>
            @else
            <p class="mt-1 font-mono text-[11px] tracking-[0.14em] uppercase text-amber-600">Profile Status: Incomplete — missing: {{ implode(', ', $completion['missing']) }}</p>
            @endif
        </div>
        <a href="{{ route('portal.profile.edit') }}" class="term-btn term-btn-sm flex-shrink-0">Edit Profile</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="term-panel-2 p-5">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-term-700">Open Tickets</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ $openTickets }}</p>
        </div>
        <div class="term-panel-2 p-5">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-term-700">Active Projects</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ $activeProjects }}</p>
        </div>
        <div class="term-panel-2 p-5">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-term-700">Pending Invoices</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ $pendingInvoices }}</p>
        </div>
        <div class="term-panel-2 p-5">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-term-700">Total Spent</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">${{ number_format($totalSpent, 2) }}</p>
        </div>
        <a href="{{ route('portal.wallet.index') }}" class="term-panel-2 p-5 transition-all hover:border-accent/40 block">
            <p class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-500 dark:text-term-700">Wallet Balance</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ $walletCurrency }} {{ number_format($walletBalance, 2) }}</p>
        </a>
    </div>

    {{-- Quick Actions --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('portal.tickets.create') }}" class="term-panel p-4 flex items-center gap-4 group">
            <div class="w-10 h-10 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="font-medium text-slate-900 dark:text-white text-sm">Create Ticket</span>
        </a>
        <a href="{{ route('portal.service-request.create') }}" class="term-panel p-4 flex items-center gap-4 group">
            <div class="w-10 h-10 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <span class="font-medium text-slate-900 dark:text-white text-sm">Request Service</span>
        </a>
        <a href="{{ route('portal.invoices.index') }}" class="term-panel p-4 flex items-center gap-4 group">
            <div class="w-10 h-10 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <span class="font-medium text-slate-900 dark:text-white text-sm">View Invoices</span>
        </a>
        <a href="{{ route('portal.tracking.index') }}" class="term-panel p-4 flex items-center gap-4 group">
            <div class="w-10 h-10 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="font-medium text-slate-900 dark:text-white text-sm">Track My Services</span>
        </a>
    </div>

    {{-- Recent Tickets --}}
    <div class="term-panel p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900 dark:text-white">Recent Tickets</h3>
            <a href="{{ route('portal.tickets.index') }}" class="term-link">View All
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="term-table-wrap">
            <table class="data-table term-table term-table-cards">
                <thead><tr><th>Ticket</th><th>Subject</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                    <tr>
                        <td data-label="Ticket"><a href="{{ route('portal.tickets.show', $ticket) }}" class="font-mono text-accent-soft hover:underline">{{ $ticket->ticket_number }}</a></td>
                        <td data-label="Subject">{{ $ticket->subject }}</td>
                        <td data-label="Status"><x-status-badge :status="$ticket->status" /></td>
                        <td data-label="Created" class="text-slate-600 dark:text-term-800">{{ $ticket->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-600 dark:text-term-800 py-4">No tickets yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Invoices --}}
    <div class="term-panel p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900 dark:text-white">Recent Invoices</h3>
            <a href="{{ route('portal.invoices.index') }}" class="term-link">View All
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="term-table-wrap">
            <table class="data-table term-table term-table-cards">
                <thead><tr><th>Invoice</th><th>Amount</th><th>Status</th><th>Due</th></tr></thead>
                <tbody>
                    @forelse($recentInvoices as $invoice)
                    <tr>
                        <td data-label="Invoice"><a href="{{ route('portal.invoices.show', $invoice) }}" class="font-mono text-accent-soft hover:underline">{{ $invoice->invoice_number }}</a></td>
                        <td data-label="Amount" class="font-semibold tabular-nums">${{ number_format($invoice->total, 2) }}</td>
                        <td data-label="Status"><x-status-badge :status="$invoice->status" /></td>
                        <td data-label="Due" class="text-slate-600 dark:text-term-800">{{ $invoice->due_date?->format('M d, Y') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-slate-600 dark:text-term-800 py-4">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
