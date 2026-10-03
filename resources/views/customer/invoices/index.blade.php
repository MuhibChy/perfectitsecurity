@extends('layouts.app')

@section('title', 'My Invoices & Billing — Customer Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Invoices & Commercial Billing"
        subtitle="Access issued invoices, download PDF receipts, track payment receipts, and review outstanding accounts."
        sys="PAYMENT://SECURE"
        num="04"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Invoices' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.reports.mine', ['type' => 'customer-full', 'format' => 'pdf']) }}" class="term-btn term-btn-ghost term-btn-sm">Generate Report</a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stats Cards Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Billed"
            :value="'$' . number_format($invoices->sum('total'), 2)"
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>'
        />
        <x-stat-card
            title="Paid to Date"
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
            title="Invoices Issued"
            :value="$invoices->total()"
            color="purple"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>'
        />
    </div>

    {{-- Invoices Table Card --}}
    <div class="term-panel overflow-hidden">
        @if($invoices->count() > 0)
        <div class="term-table-wrap !border-0">
            <table class="data-table term-table term-table-cards">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Issued</th>
                        <th>Due</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td data-label="Invoice">
                            <a href="{{ route('portal.invoices.show', $invoice->id) }}" class="font-mono font-bold text-accent-soft hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td data-label="Issued" class="font-mono text-[11px] text-slate-600 dark:text-term-800">
                            {{ $invoice->issued_date ? $invoice->issued_date->format('M d, Y') : '—' }}
                        </td>
                        <td data-label="Due" class="font-mono text-[11px] {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->amount_due > 0 ? 'text-red-400 font-bold' : 'text-slate-600 dark:text-term-800' }}">
                            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}
                        </td>
                        <td data-label="Total" class="font-mono font-semibold tabular-nums">
                            {{ \App\Services\Money::format($invoice->total, $invoice->currency) }}
                        </td>
                        <td data-label="Paid" class="font-mono text-xs tabular-nums">
                            <span class="fin-tag fin-tag-income">{{ \App\Services\Money::format($invoice->amount_paid ?? 0, $invoice->currency) }}</span>
                        </td>
                        <td data-label="Balance" class="font-mono text-xs font-bold tabular-nums">
                            <span class="{{ $invoice->amount_due > 0 ? 'fin-tag fin-tag-due' : '' }}">{{ \App\Services\Money::format($invoice->amount_due ?? 0, $invoice->currency) }}</span>
                        </td>
                        <td data-label="Status">
                            <x-status-badge :status="$invoice->status" />
                        </td>
                        <td data-label="Action" class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('portal.invoices.pdf', $invoice->id) }}" target="_blank" class="term-btn term-btn-ghost term-btn-sm" title="Download PDF Receipt">
                                    PDF ↓
                                </a>
                                <a href="{{ route('portal.invoices.show', $invoice->id) }}" class="term-btn term-btn-ghost term-btn-sm">
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-white/10">
            {{ $invoices->links() }}
        </div>
        @else
        <x-empty-state
            title="No Invoices Issued Yet"
            message="Completed work orders and recurring retainer invoices will be listed here with instant download and payment status."
        />
        @endif
    </div>
</div>
@endsection
