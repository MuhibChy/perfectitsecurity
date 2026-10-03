@extends('layouts.app')
@section('title', 'Pay ' . $invoice->invoice_number)
@section('page-title', 'Checkout')

@section('content')
<x-page-header sys="PAY://CHECKOUT" title="Payment Summary" subtitle="Invoice {{ $invoice->invoice_number }} · choose a payment method below." :breadcrumbs="['Invoices' => route('portal.invoices.index'), $invoice->invoice_number => route('portal.invoices.show', $invoice->id), 'Pay' => null]" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="term-panel p-4">
        <div class="font-semibold mb-2">Amount due</div>
        <div class="stat-value">{{ $invoice->currency }} {{ number_format($outstanding, 2) }}</div>
        <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total {{ $invoice->currency }} {{ number_format($invoice->total, 2) }} · paid {{ number_format($invoice->amount_paid, 2) }}</div>
        @if($invoice->service_order_id)<div class="text-sm mt-1">Order: {{ $invoice->serviceOrder->order_number ?? $invoice->service_order_id }}</div>@endif
    </div>
    <div class="term-panel p-4 lg:col-span-2">
        <div class="font-semibold mb-2">Choose payment method</div>
        @forelse($methods as $m)
        <form method="POST" action="{{ route('portal.checkout.initiate', $invoice->id) }}" class="flex items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-700/60 py-2">
            @csrf
            <input type="hidden" name="provider" value="{{ $m->key }}">
            <div>
                <div class="font-medium">{{ $m->name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $m->country ?? '' }} · {{ $m->currencies ? implode(', ', $m->currencies) : 'all currencies' }} · {{ $m->environment === 'live' && $m->status === 'live' ? '🔴 LIVE' : '🟡 TEST' }}</div>
            </div>
            <button class="term-btn term-btn-sm">Pay with {{ $m->name }}</button>
        </form>
        @empty
        <p class="text-slate-500 dark:text-slate-400 text-sm">No payment providers are currently enabled. Please contact support.</p>
        @endforelse
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">The amount is taken from your invoice — never from this page. Card and wallet rails use hosted checkout; we never see or store card numbers, PINs or OTPs.</p>
    </div>
</div>

@if($history->isNotEmpty())
<div class="card p-0 overflow-hidden mt-4"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Reference</th><th>Method</th><th class="data-table-numeric">Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @foreach($history as $t)
        <tr>
            <td class="font-mono text-xs">{{ $t->reference }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$t->payment_method)) }}</td>
            <td class="data-table-numeric">{{ $t->original_currency }} {{ number_format($t->gross_amount, 2) }}</td>
            <td><span class="term-tag">{{ strtoupper(str_replace('_',' ',$t->status)) }}</span></td>
            <td><a href="{{ route('portal.payments.show', $t->reference) }}" class="link-arrow text-sm">View →</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
@endif
@endsection
