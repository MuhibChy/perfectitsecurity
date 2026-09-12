@extends('layouts.app')

@section('title', 'My Invoices & Billing — Customer Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Invoices & Commercial Billing"
        subtitle="Access issued invoices, download PDF receipts, track payment receipts, and review outstanding accounts."
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Invoices' => null]"
    />

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
    <div class="glass-card overflow-hidden">
        @if($invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Issued Date</th>
                        <th>Due Date</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Balance Due</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                        <td>
                            <a href="{{ route('portal.invoices.show', $invoice->id) }}" class="font-mono font-bold text-primary-600 dark:text-primary-400 hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="text-xs text-gray-500">
                            {{ $invoice->issued_date ? $invoice->issued_date->format('M d, Y') : '—' }}
                        </td>
                        <td class="text-xs {{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->amount_due > 0 ? 'text-rose-500 font-bold' : 'text-gray-500' }}">
                            {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}
                        </td>
                        <td class="font-mono font-semibold text-gray-900 dark:text-white">
                            ${{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="font-mono text-xs text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($invoice->amount_paid ?? 0, 2) }}
                        </td>
                        <td class="font-mono text-xs font-bold {{ $invoice->amount_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                            ${{ number_format($invoice->amount_due ?? 0, 2) }}
                        </td>
                        <td>
                            <x-status-badge :status="$invoice->status" />
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('portal.invoices.pdf', $invoice->id) }}" target="_blank" class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Download PDF Receipt">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                <a href="{{ route('portal.invoices.show', $invoice->id) }}" class="btn-secondary btn-sm">
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
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
