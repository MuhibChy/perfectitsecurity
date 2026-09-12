@extends('layouts.app')

@section('title', 'Invoices & Receivables — Admin Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Invoices & Receivables"
        subtitle="Manage client billing, track payments, monitor aging receivables, and generate fiscal records."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Invoices' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.invoices.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Invoice
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Billed"
            :value="'$' . number_format($invoices->sum('total'), 2)"
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>'
        />
        <x-stat-card
            title="Payments Settled"
            :value="'$' . number_format($invoices->sum('amount_paid'), 2)"
            color="emerald"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Outstanding Balance"
            :value="'$' . number_format($invoices->sum('amount_due'), 2)"
            color="amber"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Invoices Count"
            :value="$invoices->total()"
            color="purple"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>'
        />
    </div>

    {{-- Filter Card --}}
    <div class="glass-card p-4 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto scrollbar-none">
            @php $currentStatus = request('status'); @endphp
            <a href="{{ route('admin.invoices.index', array_filter(['search' => request('search')])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ !$currentStatus ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300' }}">
                All Invoices
            </a>
            @foreach(['draft' => 'Drafts', 'sent' => 'Sent', 'partially_paid' => 'Partial', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $invSt => $invLbl)
            <a href="{{ route('admin.invoices.index', array_filter(['status' => $invSt, 'search' => request('search')])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $currentStatus === $invSt ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300 hover:bg-surface-200' }}">
                {{ $invLbl }}
            </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.invoices.index') }}" class="relative w-full md:w-72">
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by invoice # or client..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white dark:bg-navy-800 border border-surface-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </form>
    </div>

    {{-- Invoices Table --}}
    <div class="glass-card overflow-hidden">
        @if($invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Issued / Due</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance Due</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                        <td>
                            <a href="{{ route('admin.invoices.show', $inv) }}" class="font-mono font-bold text-primary-600 dark:text-primary-400 hover:underline">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $inv->customer->name ?? 'Direct Client' }}</div>
                            @if($inv->project)
                            <div class="text-[11px] text-gray-400 font-mono">Project: {{ $inv->project->name }}</div>
                            @endif
                        </td>
                        <td>
                            <x-status-badge :status="$inv->status" />
                        </td>
                        <td class="text-xs text-gray-500">
                            <div>{{ $inv->issued_date ? $inv->issued_date->format('M d, Y') : 'Draft' }}</div>
                            <div class="{{ $inv->due_date && $inv->due_date->isPast() && $inv->amount_due > 0 ? 'text-rose-500 font-bold' : 'text-gray-400' }}">
                                Due: {{ $inv->due_date ? $inv->due_date->format('M d, Y') : '—' }}
                            </div>
                        </td>
                        <td class="font-mono font-bold text-gray-900 dark:text-white">
                            ${{ number_format($inv->total, 2) }}
                        </td>
                        <td class="font-mono text-xs text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($inv->amount_paid ?? 0, 2) }}
                        </td>
                        <td class="font-mono text-xs font-bold {{ $inv->amount_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                            ${{ number_format($inv->amount_due ?? 0, 2) }}
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.invoices.pdf', $inv) }}" target="_blank" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600" title="PDF">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                <a href="{{ route('admin.invoices.show', $inv) }}" class="btn-ghost btn-sm text-xs">
                                    View
                                </a>
                                @if($inv->status === 'draft')
                                <a href="{{ route('admin.invoices.edit', $inv) }}" class="btn-secondary btn-sm text-xs">
                                    Edit
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
            {{ $invoices->withQueryString()->links() }}
        </div>
        @else
        <x-empty-state
            title="No Invoices Found"
            message="No invoices match the chosen filters."
            actionText="Create Invoice"
            :actionUrl="route('admin.invoices.create')"
        />
        @endif
    </div>
</div>
@endsection
