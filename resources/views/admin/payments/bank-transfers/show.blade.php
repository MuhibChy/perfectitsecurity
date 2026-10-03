@extends('layouts.app')
@section('title', 'Review Transfer ' . $transfer->reference)
@section('page-title', 'Bank Transfers')

@section('content')
<x-page-header sys="PAYMENTS://BANK-TRANSFERS" title="Transfer {{ $transfer->reference }}" subtitle="{{ $transfer->customer->name ?? '—' }} · {{ $transfer->currency }} {{ number_format($transfer->amount, 2) }}" :breadcrumbs="['Bank transfers' => route('admin.bank-transfers.index'), $transfer->reference => null]">
    <span class="term-tag">{{ strtoupper(str_replace('_',' ',$transfer->status)) }}</span>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    <div class="term-panel p-4">
        <div class="font-semibold mb-2">Customer claim</div>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="text-slate-500">Invoice</dt><dd>{{ $transfer->invoice->invoice_number ?? '—' }} (due {{ $transfer->invoice ? number_format($transfer->invoice->amount_due, 2) : '—' }})</dd>
            <dt class="text-slate-500">To account</dt><dd>{{ $transfer->bankAccount->label ?? '—' }} — {{ $transfer->bankAccount->bank_name ?? '' }}</dd>
            <dt class="text-slate-500">Sender</dt><dd>{{ $transfer->sender_name ?? '—' }}</dd>
            <dt class="text-slate-500">Sender bank</dt><dd>{{ $transfer->sender_bank ?? '—' }}</dd>
            <dt class="text-slate-500">Transfer ref</dt><dd class="font-mono">{{ $transfer->transfer_reference ?? '—' }}</dd>
            <dt class="text-slate-500">Transaction ID</dt><dd class="font-mono">{{ $transfer->provider_transaction_id ?? '—' }}</dd>
            <dt class="text-slate-500">Transfer date</dt><dd>{{ $transfer->transferred_at?->format('Y-m-d') ?? '—' }}</dd>
            <dt class="text-slate-500">Receipt</dt><dd>@if($transfer->receipt_path)<a href="{{ route('admin.bank-transfers.receipt', $transfer) }}" class="link-arrow text-sm">Download proof →</a>@else — @endif</dd>
        </dl>
    </div>
    <div class="term-panel p-4">
        <div class="font-semibold mb-2">Decision</div>
        @if($transfer->status === 'pending_verification')
        <form method="POST" action="{{ route('admin.bank-transfers.verify', $transfer) }}" class="mb-3">@csrf
            <label class="block text-sm mb-2">Verification note (optional)<textarea name="admin_notes" rows="2" class="term-input w-full"></textarea></label>
            <button class="term-btn term-btn-sm" onclick="return confirm('Confirm the money arrived in the company account before verifying.')">Verify &amp; record payment</button>
        </form>
        <form method="POST" action="{{ route('admin.bank-transfers.reject', $transfer) }}">@csrf
            <label class="block text-sm mb-2">Rejection reason (required, sent to customer)<textarea name="admin_notes" rows="2" class="term-input w-full" required></textarea></label>
            <button class="term-btn term-btn-ghost term-btn-sm">Reject transfer</button>
        </form>
        @else
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="text-slate-500">Reviewed by</dt><dd>{{ $transfer->verifier->name ?? '—' }} · {{ $transfer->verified_at?->format('Y-m-d H:i') }}</dd>
            <dt class="text-slate-500">Note</dt><dd>{{ $transfer->admin_notes ?? '—' }}</dd>
        </dl>
        @endif
    </div>
</div>
@endsection
