@extends('layouts.app')
@section('title', 'Wallet ' . $wallet->wallet_reference)
@section('page-title', 'Wallet')

@section('content')
<x-page-header :title="'Wallet ' . $wallet->wallet_reference" :subtitle="$wallet->currency . ' · ' . ucfirst($wallet->status)" sys="PAYMENT://WALLET" :breadcrumbs="['My Wallet' => route('portal.wallet.index'), $wallet->wallet_reference => null]">
    <x-status-badge :status="$wallet->status" />
    <a href="{{ route('portal.wallet.statement', $wallet) }}" class="term-btn term-btn-ghost term-btn-sm">Statement &amp; PDF</a>
</x-page-header>

<div class="term-panel p-6 mb-6">
    <div class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">{{ $wallet->currency }} {{ number_format($wallet->balance, 2) }}</div>
    <div class="term-hint mb-4">Available Balance</div>
    @if($wallet->status === 'active')
    <form method="POST" action="{{ route('portal.wallet.topup', $wallet) }}" class="flex flex-col sm:flex-row gap-2 max-w-md">
        @csrf
        <input type="number" name="amount" min="1" max="100000" step="0.01" class="term-input" placeholder="Amount to add" required>
        <button class="term-btn whitespace-nowrap">Add Money</button>
    </form>
    <p class="term-hint mt-2">Online top-ups are verified server-side with the payment provider before any credit. No money moves on browser word alone.</p>
    @endif
</div>

<div class="term-panel overflow-hidden"><div class="term-table-wrap !border-0">
<table class="data-table term-table term-table-cards">
    <thead><tr><th>Date</th><th>Ref</th><th>Type</th><th>Description</th><th class="text-right">Amount</th><th class="text-right">Balance</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($transactions as $t)
        <tr>
            <td data-label="Date" class="whitespace-nowrap font-mono text-[11px]">{{ $t->created_at->format('d M Y H:i') }}</td>
            <td data-label="Ref" class="font-mono text-xs">{{ $t->transaction_reference }}</td>
            <td data-label="Type">{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
            <td data-label="Description" class="text-sm">{{ $t->description ?? '—' }}</td>
            <td data-label="Amount" class="text-right tabular-nums"><span class="{{ $t->isCredit() ? 'fin-tag fin-tag-income' : 'fin-tag fin-tag-expense' }}">{{ $t->isCredit() ? '+' : '−' }}{{ number_format($t->amount, 2) }}</span></td>
            <td data-label="Balance" class="text-right tabular-nums">{{ number_format($t->balance_after, 2) }}</td>
            <td data-label="Status"><x-status-badge :status="$t->status" /></td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
