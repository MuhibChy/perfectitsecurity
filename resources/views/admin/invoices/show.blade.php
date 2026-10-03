@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number . ' — Admin Portal')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ paymentModal: false }">
    <x-page-header sys="FINANCE://INVOICES"
        :title="'Invoice ' . $invoice->invoice_number"
        subtitle="Manage invoice lifecycle, payment settlements, and customer notifications."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Invoices' => route('admin.invoices.index'), 'Details' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.invoices.index') }}" class="term-btn term-btn-ghost term-btn-sm">
                &larr; Back
            </a>
            <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank" class="term-btn term-btn-ghost term-btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF Receipt
            </a>

            @if(in_array($invoice->status, ['draft', 'sent', 'viewed', 'overdue', 'partially_paid']))
            <button type="button" @click="paymentModal = true" class="term-btn term-btn-sm">
                + Record Payment
            </button>
            @endif

            @if($invoice->status === 'draft')
            <form action="{{ route('admin.invoices.send', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Dispatch invoice notification to client email?')">
                @csrf
                <button type="submit" class="term-btn term-btn-sm">
                    Send to Client
                </button>
            </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))<div class="term-alert term-alert-ok"><span class="term-alert-tag">SYS.OK</span><span>
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Invoice Sheet --}}
    <div class="term-panel p-8 lg:p-12 shadow-xl border border-white/10 space-y-8">
        {{-- Header Info --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-gray-100 dark:border-white/5">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Bill To Customer</span>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $invoice->customer->name ?? 'Direct Client' }}</h3>
                <p class="text-xs text-gray-500 font-mono">{{ $invoice->customer->email ?? '' }}</p>
                <div class="mt-3">
                    <x-status-badge :status="$invoice->status" />
                </div>
            </div>

            <div class="text-left sm:text-right space-y-1 text-xs text-gray-500 dark:text-gray-400">
                <p>Invoice #: <strong class="font-mono text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</strong></p>
                <p>Issued: <strong class="text-gray-800 dark:text-gray-200">{{ $invoice->issued_date ? $invoice->issued_date->format('M d, Y') : 'Draft' }}</strong></p>
                <p>Due: <strong class="{{ $invoice->due_date && $invoice->due_date->isPast() && $invoice->amount_due > 0 ? 'text-rose-500 font-bold' : 'text-gray-800 dark:text-gray-200' }}">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</strong></p>
            </div>
        </div>

        {{-- Line Items --}}
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Itemized Deliverables</h4>
            <div class="overflow-x-auto border border-gray-100 dark:border-white/5 term-table-wrap">
                <table class="data-table term-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white" data-label="Description">{{ $item->description }}</td>
                            <td class="text-center font-mono" data-label="Quantity">{{ $item->quantity }}</td>
                            <td class="text-right font-mono text-gray-600 dark:text-gray-300" data-label="Rate">{{ \App\Services\Money::format($item->unit_price, $invoice->currency) }}</td>
                            <td class="text-right font-mono font-bold text-gray-900 dark:text-white" data-label="Line Total">{{ \App\Services\Money::format($item->total, $invoice->currency) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totals --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-6 border-t border-gray-100 dark:border-white/5">
            <div class="max-w-md text-xs text-gray-500 space-y-2">
                @if($invoice->notes)
                <div>
                    <h5 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Notes</h5>
                    <p class="mt-1 leading-relaxed">{{ $invoice->notes }}</p>
                </div>
                @endif
                @if($invoice->terms)
                <div class="mt-2">
                    <h5 class="font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Commercial Terms</h5>
                    <p class="mt-1 leading-relaxed">{{ $invoice->terms }}</p>
                </div>
                @endif
            </div>

            <div class="w-full sm:w-80 p-5 bg-surface-50 dark:bg-navy-800/60 border border-surface-200 dark:border-white/10 space-y-2.5 text-sm">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-medium">{{ \App\Services\Money::format($invoice->subtotal ?? 0, $invoice->currency) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Tax ({{ $invoice->tax_rate ?? 0 }}%):</span>
                    <span class="font-mono font-medium">{{ \App\Services\Money::format($invoice->tax_amount, $invoice->currency) }}</span>
                </div>
                @endif
                <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between text-base font-bold text-gray-900 dark:text-white">
                    <span>Total Billed:</span>
                    <span class="font-mono text-primary-600 dark:text-primary-400">{{ \App\Services\Money::format($invoice->total, $invoice->currency) }}</span>
                </div>
                <div class="flex justify-between text-xs text-emerald-600 dark:text-emerald-400">
                    <span>Amount Paid:</span>
                    <span class="font-mono font-medium">-{{ \App\Services\Money::format($invoice->amount_paid ?? 0, $invoice->currency) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold {{ $invoice->amount_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-300' }}">
                    <span>Outstanding Due:</span>
                    <span class="font-mono">{{ \App\Services\Money::format($invoice->amount_due ?? 0, $invoice->currency) }}</span>
                </div>
            </div>
        </div>

        {{-- Recorded Payments Section --}}
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div class="pt-8 border-t border-gray-100 dark:border-white/5">
            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Recorded Payment Receipts</h4>
            <div class="divide-y divide-gray-100 dark:divide-white/5 border border-gray-100 dark:border-white/5 overflow-hidden">
                @foreach($invoice->payments as $p)
                <div class="p-4 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $p->payment_number }}</span>
                        <span class="text-gray-500 ml-2">via {{ ucfirst($p->payment_method) }}</span>
                        @if($p->transaction_id)
                        <span class="text-gray-400 font-mono ml-2">Ref: {{ $p->transaction_id }}</span>
                        @endif
                        @if($p->status === 'refunded')
                        <span class="ml-2 px-1.5 py-0.5 rounded bg-gray-500/10 text-gray-500">refunded</span>
                        @endif
                        @if($p->refunded_amount > 0 && $p->status === 'completed')
                        <span class="ml-2 text-gray-500">({{ number_format($p->refunded_amount, 2) }} refunded)</span>
                        @endif
                    </div>
                    <div class="text-right flex items-center gap-3">
                        <div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ \App\Services\Money::format($p->amount, $invoice->currency) }}</span>
                            <div class="text-[10px] text-slate-500 dark:text-gray-400">{{ $p->created_at->format('M d, Y') }}</div>
                        </div>
                        @if($p->status === 'completed' && ($p->amount - $p->refunded_amount) > 0)
                        <form action="{{ route('admin.payments.refund', $p) }}" method="POST" class="flex items-center gap-1" onsubmit="return confirm('Record a refund for this payment?')">
                            @csrf
                            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $p->amount - $p->refunded_amount }}" value="{{ $p->amount - $p->refunded_amount }}" required
                                   class="w-20 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800" title="Refund amount">
                            <input type="text" name="reason" required maxlength="500" placeholder="Reason"
                                   class="w-28 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800">
                            <button class="text-xs text-amber-600 hover:underline">Refund</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Record Payment Modal --}}
    <div x-show="paymentModal" x-cloak @keydown.escape.window="paymentModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="paymentModal = false" class="term-modal max-w-md w-full">
            <div class="term-modal-head">
                <span class="term-modal-title">Record Client Payment</span>
                <button type="button" @click="paymentModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.payments.store') }}" class="space-y-4 p-6">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                <div>
                    <label class="term-field-label">Amount to Record ($)</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $invoice->amount_due }}" name="amount" value="{{ $invoice->amount_due }}" required
                           class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Payment Method</label>
                    <select name="payment_method" required class="term-input">
                        <option value="wire_transfer">Wire / Bank Transfer</option>
                        <option value="credit_card">Credit Card (Stripe)</option>
                        <option value="check">Company Check</option>
                        <option value="cash">Cash / Petty</option>
                        <option value="crypto">Cryptocurrency</option>
                    </select>
                </div>

                <div>
                    <label class="term-field-label">Transaction Ref #</label>
                    <input type="text" name="transaction_id" placeholder="e.g. WIRE-882190" class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Internal Note</label>
                    <textarea name="notes" rows="2" placeholder="Optional settlement note..." class="term-input"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="paymentModal = false" class="term-btn term-btn-ghost term-btn-sm">Cancel</button>
                    <button type="submit" class="term-btn term-btn-sm">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
