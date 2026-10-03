@extends('layouts.app')
@section('title', 'My Wallet')
@section('page-title', 'My Wallet')

@section('content')
<x-page-header title="My Wallet" subtitle="Top up, pay invoices and review every movement. Each balance change has a traceable ledger record." sys="PAYMENT://WALLET" num="09">
    @if($wallets->count() === 1)<a href="{{ route('portal.wallet.statement', $wallets->first()) }}" class="term-btn term-btn-ghost term-btn-sm">Statement</a>@endif
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    @foreach($wallets as $wallet)
    <div class="term-panel p-6">
        <div class="flex items-center justify-between mb-1 gap-2">
            <span class="font-mono text-[11px] text-slate-600 dark:text-term-800">{{ $wallet->wallet_reference }}</span>
            <x-status-badge :status="$wallet->status" />
        </div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white tabular-nums">{{ $wallet->currency }} {{ number_format($wallet->balance, 2) }}</div>
        <div class="term-hint mb-4">Available Balance</div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('portal.wallet.show', $wallet) }}" class="term-btn term-btn-ghost term-btn-sm">Open Wallet</a>
            <a href="{{ route('portal.wallet.statement', $wallet) }}" class="term-btn term-btn-ghost term-btn-sm">Statement</a>
        </div>
        @if($wallet->status === 'active')
        <form method="POST" action="{{ route('portal.wallet.topup', $wallet) }}" class="flex gap-2 mt-4">
            @csrf
            <input type="number" name="amount" min="1" max="100000" step="0.01" class="term-input text-sm" placeholder="Amount to add" required>
            <button class="term-btn term-btn-sm whitespace-nowrap">Add Money</button>
        </form>
        @else
        <p class="term-hint mt-3">This wallet is {{ $wallet->status }}. History remains available; contact support for help.</p>
        @endif
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="term-panel p-5">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-3">Pay an Invoice from Wallet</h2>
        @forelse($payable as $invoice)
        <div class="py-2 border-b border-white/5 last:border-0">
            <div class="flex justify-between text-sm gap-2"><strong class="text-slate-900 dark:text-white font-mono">{{ $invoice->invoice_number }}</strong><span class="tabular-nums">Due {{ $invoice->currency }} {{ number_format($invoice->amount_due, 2) }}</span></div>
            @foreach($wallets->where('status', 'active')->where('currency', $invoice->currency ?? 'USD') as $wallet)
            <form method="POST" action="{{ route('portal.wallet.pay-invoice', $wallet) }}" class="flex gap-2 mt-1">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <input type="number" name="amount" min="0.01" max="{{ $invoice->amount_due }}" step="0.01" value="{{ $invoice->amount_due }}" class="term-input text-xs">
                <button class="term-btn term-btn-sm whitespace-nowrap">Pay {{ $wallet->currency }}</button>
            </form>
            @endforeach
        </div>
        @empty
        <p class="text-sm text-slate-600 dark:text-term-800">No outstanding invoices. Balances stay untouched until you choose to pay.</p>
        @endforelse
    </div>
    <div class="term-panel p-5">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-3">Recent Transactions</h2>
        @forelse($recent as $t)
        <div class="flex justify-between text-sm py-1.5 border-b border-white/5 last:border-0 gap-2">
            <span class="text-slate-600 dark:text-term-800">{{ $t->created_at->format('d M') }} · {{ $t->description ?? ucfirst(str_replace('_', ' ', $t->type)) }}</span>
            <strong class="tabular-nums"><span class="{{ $t->isCredit() ? 'fin-tag fin-tag-income' : 'fin-tag fin-tag-expense' }}">{{ $t->isCredit() ? '+' : '−' }}{{ number_format($t->amount, 2) }}</span></strong>
        </div>
        @empty
        <p class="text-sm text-slate-600 dark:text-term-800">No movements yet.</p>
        @endforelse
    </div>
</div>
@endsection
