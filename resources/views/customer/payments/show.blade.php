@extends('layouts.app')
@section('title', 'Payment ' . $txn->reference)
@section('page-title', 'Payment Details')

@section('content')
<x-page-header sys="PAY://DETAIL" title="Payment {{ $txn->reference }}" subtitle="{{ $txn->original_currency }} {{ number_format($txn->gross_amount, 2) }} · {{ $txn->provider->name ?? ucfirst($txn->provider_key) }}" :breadcrumbs="['My payments' => route('portal.payments.index'), $txn->reference => null]">
    <span class="term-tag">{{ strtoupper(str_replace('_',' ',$txn->status)) }}</span>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="term-panel p-4">
        <div class="font-semibold mb-2">Transaction</div>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="text-slate-500">Invoice</dt><dd>{{ $txn->invoice->invoice_number ?? '—' }}</dd>
            <dt class="text-slate-500">Method</dt><dd>{{ ucfirst(str_replace('_',' ',$txn->payment_method)) }}</dd>
            <dt class="text-slate-500">Provider ref</dt><dd class="font-mono">{{ $txn->provider_reference ?? '—' }}</dd>
            <dt class="text-slate-500">Gross</dt><dd>{{ $txn->original_currency }} {{ number_format($txn->gross_amount, 2) }}</dd>
            @if($txn->exchange_rate)<dt class="text-slate-500">Rate</dt><dd class="font-mono">{{ $txn->exchange_rate }} ({{ $txn->provider_currency }} {{ number_format($txn->provider_amount, 2) }})</dd>@endif
            @if($txn->provider_fee > 0 || $txn->platform_fee > 0)<dt class="text-slate-500">Fees</dt><dd>{{ number_format($txn->provider_fee + $txn->platform_fee, 2) }} · net {{ number_format($txn->net_amount, 2) }}</dd>@endif
            <dt class="text-slate-500">Paid at</dt><dd>{{ $txn->paid_at?->format('Y-m-d H:i') ?? '—' }}</dd>
            <dt class="text-slate-500">Receipt</dt><dd>@if($txn->payment && $txn->payment->receipt)<a href="{{ route('portal.receipts.pdf', $txn->payment->receipt->id) }}" class="link-arrow text-sm">Download PDF →</a>@else — @endif</dd>
        </dl>
    </div>
    <div class="term-panel p-4">
        <div class="font-semibold mb-2">Refunds</div>
        @forelse($refunds as $r)
        <div class="text-sm border-b border-slate-700/60 py-1">{{ $r->refund_number }} · {{ $r->currency }} {{ number_format($r->amount, 2) }} · <span class="term-tag">{{ strtoupper($r->status) }}</span></div>
        @empty
        <p class="text-sm text-slate-500">No refunds on this payment.</p>
        @endforelse
        @if($txn->payment_id && $txn->status === 'paid')
        <form method="POST" action="{{ route('portal.payments.refund-request', $txn->reference) }}" class="grid gap-2 mt-3">@csrf
            <label class="block text-sm">Refund amount<input type="number" step="0.01" name="amount" max="{{ $txn->gross_amount }}" class="term-input w-full" required></label>
            <label class="block text-sm">Reason (min 10 chars)<textarea name="reason" rows="2" class="term-input w-full" required minlength="10"></textarea></label>
            <button class="term-btn term-btn-ghost term-btn-sm">Request refund (finance approval required)</button>
        </form>
        @endif
    </div>
</div>
@endsection
