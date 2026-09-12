@extends('layouts.app')
@section('page-title', 'Customer Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="text-sm text-gray-500">Open Tickets</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $openTickets }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Active Projects</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $activeProjects }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Pending Invoices</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $pendingInvoices }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500">Total Spent</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($totalSpent, 2) }}</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid sm:grid-cols-3 gap-4">
        <a href="{{ route('portal.tickets.create') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="font-medium text-gray-900 dark:text-white">Create Ticket</span>
        </a>
        <a href="{{ route('portal.service-request.create') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <span class="font-medium text-gray-900 dark:text-white">Request Service</span>
        </a>
        <a href="{{ route('portal.invoices.index') }}" class="glass-card p-4 flex items-center gap-4 hover:shadow-lg transition-all group">
            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <span class="font-medium text-gray-900 dark:text-white">View Invoices</span>
        </a>
    </div>

    {{-- Recent Tickets --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white">Recent Tickets</h3>
            <a href="{{ route('portal.tickets.index') }}" class="text-sm text-primary-600">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Ticket #</th><th>Subject</th><th>Status</th><th>Created</th></tr></thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                    <tr>
                        <td><a href="{{ route('portal.tickets.show', $ticket) }}" class="font-mono text-primary-600">{{ $ticket->ticket_number }}</a></td>
                        <td>{{ $ticket->subject }}</td>
                        <td><span class="badge badge-purple">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td>
                        <td class="text-gray-500">{{ $ticket->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-500 py-4">No tickets yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Invoices --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white">Recent Invoices</h3>
            <a href="{{ route('portal.invoices.index') }}" class="text-sm text-primary-600">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Invoice #</th><th>Amount</th><th>Status</th><th>Due Date</th></tr></thead>
                <tbody>
                    @forelse($recentInvoices as $invoice)
                    <tr>
                        <td><a href="{{ route('portal.invoices.show', $invoice) }}" class="font-mono text-primary-600">{{ $invoice->invoice_number }}</a></td>
                        <td class="font-semibold">${{ number_format($invoice->total, 2) }}</td>
                        <td>
                            @php $s = ['draft'=>'badge-info','sent'=>'badge-warning','paid'=>'badge-success','overdue'=>'badge-danger']; @endphp
                            <span class="badge {{ $s[$invoice->status] ?? 'badge-info' }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span>
                        </td>
                        <td class="text-gray-500">{{ $invoice->due_date?->format('M d, Y') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-gray-500 py-4">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
