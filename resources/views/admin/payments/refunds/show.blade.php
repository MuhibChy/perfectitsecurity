@extends('layouts.app')
@section('title', 'Refund ' . $refund->refund_number)
@section('page-title', 'Refunds')

@section('content')
<x-page-header sys="PAYMENTS://REFUNDS" title="Refund {{ $refund->refund_number }}" subtitle="{{ $refund->currency }} {{ number_format($refund->amount, 2) }} · payment {{ $refund->payment->payment_number ?? '—' }}" :breadcrumbs="['Refunds' => route('admin.refunds.index'), $refund->refund_number => null]">
    <span class="term-tag">{{ strtoupper($refund->status) }}</span>
</x-page-header>

<div class="term-panel p-4 mb-4 max-w-3xl">
    <dl class="grid grid-cols-2 gap-2 text-sm">
        <dt class="text-slate-500">Customer</dt><dd>{{ $refund->customer->name ?? '—' }}</dd>
        <dt class="text-slate-500">Reason</dt><dd>{{ $refund->reason }}</dd>
        <dt class="text-slate-500">Requested by</dt><dd>{{ $refund->requester->name ?? '—' }} · {{ $refund->created_at?->format('Y-m-d H:i') }}</dd>
        <dt class="text-slate-500">Approved by</dt><dd>{{ $refund->approver->name ?? '—' }}</dd>
        <dt class="text-slate-500">Provider refund ID</dt><dd class="font-mono">{{ $refund->provider_refund_id ?? '—' }}</dd>
        <dt class="text-slate-500">Processed</dt><dd>{{ $refund->processed_at?->format('Y-m-d H:i') ?? '—' }}</dd>
    </dl>
    <div class="flex flex-wrap gap-2 mt-4">
        @if($refund->status === 'requested')
        <form method="POST" action="{{ route('admin.refunds.approve', $refund) }}">@csrf<button class="term-btn term-btn-sm">Approve</button></form>
        <form method="POST" action="{{ route('admin.refunds.reject', $refund) }}" class="flex gap-2">@csrf<input name="reason" class="term-input" placeholder="Rejection reason" required minlength="5"><button class="term-btn term-btn-ghost term-btn-sm">Reject</button></form>
        @endif
        @if($refund->status === 'approved')
        <form method="POST" action="{{ route('admin.refunds.execute', $refund) }}">@csrf<button class="term-btn term-btn-sm" onclick="return confirm('Execute this refund? Money movement is recorded in the ledger.')">Execute refund</button></form>
        @endif
    </div>
</div>
@endsection
