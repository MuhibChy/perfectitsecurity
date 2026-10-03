@extends('layouts.app')
@section('title', 'Bank Transfer Instructions')
@section('page-title', 'Bank Transfer')

@section('content')
<x-page-header sys="PAY://BANK-TRANSFER" title="Pay by Bank Transfer" subtitle="Transfer {{ $txn->original_currency }} {{ number_format($txn->original_amount, 2) }}, then submit the details below." :breadcrumbs="['Checkout' => route('portal.checkout.show', $txn->invoice_id), 'Bank transfer' => null]" />

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="term-panel p-4">
        <div class="font-semibold mb-2">Company receiving accounts</div>
        @forelse($accounts as $a)
        <div class="border-b border-slate-200 dark:border-slate-700/60 py-2 text-sm">
            <div class="font-medium">{{ $a->label }} ({{ $a->currency }})</div>
            <div>Bank: {{ $a->bank_name }}@if($a->branch) — {{ $a->branch }}@endif</div>
            <div>Account name: {{ $a->account_name }}</div>
            <div>Account no: <span class="font-mono">{{ $a->maskedNumber() }}</span> <span class="text-xs text-slate-500 dark:text-slate-400">(full details on your invoice receipt after verification)</span></div>
            @if($a->routing_number)<div>Routing: <span class="font-mono">{{ $a->routing_number }}</span></div>@endif
            @if($a->swift_bic)<div>SWIFT/BIC: <span class="font-mono">{{ $a->swift_bic }}</span></div>@endif
            @if($a->iban)<div>IBAN: <span class="font-mono">{{ $a->iban }}</span></div>@endif
            @if($a->payment_instructions)<div class="text-slate-500 dark:text-slate-400 mt-1">{{ $a->payment_instructions }}</div>@endif
            <div class="mt-1 text-emerald-600 dark:text-emerald-400">Use reference: {{ $txn->invoice->invoice_number ?? $txn->reference }}</div>
        </div>
        @empty
        <p class="text-slate-500 dark:text-slate-400 text-sm">No receiving accounts are configured right now. Please contact support.</p>
        @endforelse
    </div>
    <div class="term-panel p-4">
        <div class="font-semibold mb-2">Submit your transfer</div>
        @if($submission && $submission->status !== 'rejected' || ($submission && $submission->status === 'pending_verification'))
        <p class="text-sm"><span class="term-tag">{{ strtoupper(str_replace('_',' ',$submission->status)) }}</span></p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Reference {{ $submission->reference }} is under review. It becomes paid only after finance verification.</p>
        @else
        <form method="POST" action="{{ route('portal.bank-transfer.submit', $txn->reference) }}" enctype="multipart/form-data" class="grid gap-3">
            @csrf
            <label class="block text-sm">Receiving account<select name="bank_account_id" class="term-input w-full" required>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->label }} ({{ $a->currency }})</option>@endforeach</select></label>
            <label class="block text-sm">Amount sent<input type="number" step="0.01" name="amount" value="{{ number_format($txn->original_amount, 2, '.', '') }}" max="{{ $txn->original_amount }}" class="term-input w-full" required></label>
            <label class="block text-sm">Your name<input name="sender_name" class="term-input w-full" maxlength="150"></label>
            <label class="block text-sm">Your bank<input name="sender_bank" class="term-input w-full" maxlength="150"></label>
            <label class="block text-sm">Transfer reference<input name="transfer_reference" class="term-input w-full font-mono" maxlength="120"></label>
            <label class="block text-sm">Transaction ID<input name="provider_transaction_id" class="term-input w-full font-mono" maxlength="160"></label>
            <label class="block text-sm">Transfer date<input type="date" name="transferred_at" max="{{ date('Y-m-d') }}" class="term-input w-full"></label>
            <label class="block text-sm">Receipt / proof (PDF or image, 5MB)<input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp" class="term-input w-full"></label>
            <button class="term-btn term-btn-sm">Submit transfer for verification</button>
        </form>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Uploading a receipt never marks the invoice paid by itself.</p>
        @endif
    </div>
</div>
@endsection
