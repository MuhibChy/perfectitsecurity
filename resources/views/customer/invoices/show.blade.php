@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number . ' — Customer Portal')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <x-page-header
        :title="'Invoice ' . $invoice->invoice_number"
        subtitle="Commercial invoice and payment settlement statement."
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Invoices' => route('portal.invoices.index'), 'Details' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.invoices.index') }}" class="btn-ghost btn-sm">
                &larr; Back to Invoices
            </a>
            <a href="{{ route('portal.invoices.pdf', $invoice->id) }}" target="_blank" class="btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            @if(!in_array($invoice->status, ['paid', 'cancelled']))
            <form method="POST" action="{{ route('portal.invoices.checkout', $invoice->id) }}" class="inline">
                @csrf
                <button type="submit" class="btn-primary btn-sm">Pay Online (Stripe)</button>
            </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Invoice Sheet Card --}}
    <div class="glass-card p-8 lg:p-12 rounded-2xl shadow-xl border border-white/10 space-y-8">
        {{-- Invoice Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-gray-100 dark:border-white/5">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Invoice Identifier</span>
                <h2 class="text-3xl font-mono font-bold text-gray-900 dark:text-white mt-1">
                    {{ $invoice->invoice_number }}
                </h2>
                <div class="mt-3">
                    <x-status-badge :status="$invoice->status" />
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <p>Issued Date: <strong class="text-gray-800 dark:text-gray-200">{{ $invoice->issued_date ? $invoice->issued_date->format('M d, Y') : 'Draft' }}</strong></p>
                <p>Payment Due: <strong class="{{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->amount_due > 0 ? 'text-rose-500' : 'text-gray-800 dark:text-gray-200' }}">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Upon receipt' }}</strong></p>
                @if($invoice->project)
                <p>Project Ref: <a href="{{ route('portal.projects.show', $invoice->project->id) }}" class="text-primary-600 underline font-mono">{{ $invoice->project->name }}</a></p>
                @endif
            </div>
        </div>

        {{-- Line Items --}}
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-4">Itemized Bill of Services</h3>
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-white/5">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Unit Rate</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white">{{ $item->description }}</td>
                            <td class="text-center font-mono">{{ $item->quantity }}</td>
                            <td class="text-right font-mono text-gray-600 dark:text-gray-300">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right font-mono font-bold text-gray-900 dark:text-white">${{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Financial Summary Grid --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-6 border-t border-gray-100 dark:border-white/5">
            <div class="max-w-md text-xs text-gray-500 space-y-3">
                @if($invoice->notes)
                <div>
                    <h4 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Billing Notes</h4>
                    <p class="leading-relaxed">{{ $invoice->notes }}</p>
                </div>
                @endif
                @if($invoice->terms)
                <div>
                    <h4 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Payment Instructions</h4>
                    <p class="leading-relaxed">{{ $invoice->terms }}</p>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-80 p-5 rounded-xl bg-surface-50 dark:bg-navy-800/60 border border-surface-200 dark:border-white/10 space-y-2.5 text-sm">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-medium">${{ number_format($invoice->subtotal ?? 0, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Tax ({{ $invoice->tax_rate ?? 0 }}%):</span>
                    <span class="font-mono font-medium">${{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between text-base font-bold text-gray-900 dark:text-white">
                    <span>Total Billed:</span>
                    <span class="font-mono text-primary-600 dark:text-primary-400">${{ number_format($invoice->total, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs text-emerald-600 dark:text-emerald-400">
                    <span>Amount Paid:</span>
                    <span class="font-mono font-medium">-${{ number_format($invoice->amount_paid ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold {{ $invoice->amount_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }}">
                    <span>Outstanding Due:</span>
                    <span class="font-mono">${{ number_format($invoice->amount_due ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Recorded Payments --}}
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div class="pt-8 border-t border-gray-100 dark:border-white/5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-3">Settlement Receipts</h3>
            <div class="divide-y divide-gray-100 dark:divide-white/5 rounded-xl border border-gray-100 dark:border-white/5 overflow-hidden">
                @foreach($invoice->payments as $payment)
                <div class="p-4 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                            ✓
                        </div>
                        <div>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $payment->payment_number }}</span>
                            <span class="text-gray-500 ml-2">via {{ ucfirst($payment->payment_method) }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($payment->amount, 2) }}</span>
                        <div class="text-[10px] text-gray-400">{{ $payment->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
