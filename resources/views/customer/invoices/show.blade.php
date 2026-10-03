@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number . ' — Customer Portal')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <x-page-header
        :title="'Invoice ' . $invoice->invoice_number"
        subtitle="Commercial invoice and payment settlement statement."
        sys="PAYMENT://SECURE"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Invoices' => route('portal.invoices.index'), 'Details' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.invoices.index') }}" class="term-btn term-btn-ghost term-btn-sm">
                &larr; Back to Invoices
            </a>
            <a href="{{ route('portal.invoices.pdf', $invoice->id) }}" target="_blank" class="term-btn term-btn-ghost term-btn-sm">
                Download PDF
            </a>
            @if(!in_array($invoice->status, ['paid', 'cancelled']))
            <form method="POST" action="{{ route('portal.invoices.checkout', $invoice->id) }}" class="inline">
                @csrf
                <button type="submit" class="term-btn term-btn-sm">Pay Online (Stripe)</button>
            </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Invoice Sheet Card --}}
    <div class="term-panel p-8 lg:p-12 space-y-8">
        {{-- Invoice Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-white/10">
            <div>
                <span class="term-field-label">Invoice Identifier</span>
                <h2 class="text-3xl font-mono font-bold text-slate-900 dark:text-white mt-1">
                    {{ $invoice->invoice_number }}
                </h2>
                <div class="mt-3">
                    <x-status-badge :status="$invoice->status" />
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1 font-mono text-[11px] uppercase tracking-[0.12em] text-slate-600 dark:text-term-800">
                <p>Issued: <strong class="text-slate-900 dark:text-white">{{ $invoice->issued_date ? $invoice->issued_date->format('M d, Y') : 'Draft' }}</strong></p>
                <p>Due: <strong class="{{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->amount_due > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Upon receipt' }}</strong></p>
                @if($invoice->project)
                <p>Project: <a href="{{ route('portal.projects.show', $invoice->project->id) }}" class="text-accent-soft underline font-mono">{{ $invoice->project->name }}</a></p>
                @endif
            </div>
        </div>

        {{-- Line Items --}}
        <div>
            <h3 class="term-field-label mb-4">Itemized Bill of Services</h3>
            <div class="term-table-wrap">
                <table class="data-table term-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td data-label="Item" class="font-medium text-slate-900 dark:text-white">{{ $item->description }}</td>
                            <td data-label="Qty" class="text-center font-mono">{{ $item->quantity }}</td>
                            <td data-label="Rate" class="text-right font-mono">{{ \App\Services\Money::format($item->unit_price, $invoice->currency) }}</td>
                            <td data-label="Total" class="text-right font-mono font-bold">{{ \App\Services\Money::format($item->total, $invoice->currency) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Financial Summary Grid --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-6 border-t border-white/10">
            <div class="max-w-md text-xs text-slate-600 dark:text-term-800 space-y-3">
                @if($invoice->notes)
                <div>
                    <h4 class="term-field-label mb-1">Billing Notes</h4>
                    <p class="leading-relaxed">{{ $invoice->notes }}</p>
                </div>
                @endif
                @if($invoice->terms)
                <div>
                    <h4 class="term-field-label mb-1">Payment Instructions</h4>
                    <p class="leading-relaxed">{{ $invoice->terms }}</p>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-80 term-panel-2 p-5 space-y-2.5 text-sm">
                <div class="flex justify-between text-slate-600 dark:text-term-800">
                    <span>Subtotal:</span>
                    <span class="font-mono font-medium tabular-nums">{{ \App\Services\Money::format($invoice->subtotal ?? 0, $invoice->currency) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-slate-600 dark:text-term-800">
                    <span>Tax ({{ $invoice->tax_rate ?? 0 }}%):</span>
                    <span class="font-mono font-medium tabular-nums">{{ \App\Services\Money::format($invoice->tax_amount, $invoice->currency) }}</span>
                </div>
                @endif
                <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between text-base font-bold text-slate-900 dark:text-white">
                    <span>Total Billed:</span>
                    <span class="font-mono tabular-nums"><span class="fin-tag fin-tag-profit">{{ \App\Services\Money::format($invoice->total, $invoice->currency) }}</span></span>
                </div>
                <div class="flex justify-between text-xs">
                    <span>Amount Paid:</span>
                    <span class="font-mono font-medium tabular-nums"><span class="fin-tag fin-tag-income">-{{ \App\Services\Money::format($invoice->amount_paid ?? 0, $invoice->currency) }}</span></span>
                </div>
                <div class="flex justify-between text-base font-bold">
                    <span>Outstanding Due:</span>
                    <span class="font-mono tabular-nums"><span class="{{ $invoice->amount_due > 0 ? 'fin-tag fin-tag-due' : '' }}">{{ \App\Services\Money::format($invoice->amount_due ?? 0, $invoice->currency) }}</span></span>
                </div>
            </div>
        </div>

        {{-- Recorded Payments --}}
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div class="pt-8 border-t border-white/10">
            <h3 class="term-field-label mb-3">Settlement Receipts</h3>
            <div class="term-table-wrap">
                @foreach($invoice->payments as $payment)
                <div class="p-4 flex items-center justify-between text-xs border-b border-white/5 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="term-tag term-tag-accent">PAID</span>
                        <div>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $payment->payment_number }}</span>
                            <span class="text-slate-600 dark:text-term-800 ml-2">via {{ ucfirst($payment->payment_method) }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold tabular-nums"><span class="fin-tag fin-tag-income">{{ \App\Services\Money::format($payment->amount, $invoice->currency) }}</span></span>
                        <div class="font-mono text-[10px] text-slate-600 dark:text-term-800">{{ $payment->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
