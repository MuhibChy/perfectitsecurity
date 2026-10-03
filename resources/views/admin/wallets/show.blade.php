@extends('layouts.app')
@section('title', 'Wallet ' . $wallet->wallet_reference)
@section('page-title', 'Wallet')

@section('content')
<x-page-header sys="FINANCE://WALLETS" :title="'Wallet ' . $wallet->wallet_reference" :subtitle="($wallet->owner->name ?? '—') . ' · ' . $wallet->currency . ' · ' . ucfirst($wallet->status)" :breadcrumbs="['Wallets' => route('admin.wallets.index'), $wallet->wallet_reference => null]">
    <span class="term-tag {{ $wallet->status === 'active' ? '' : '' }}">{{ ucfirst($wallet->status) }}</span>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="term-panel p-6">
        <div class="stat-value">{{ $wallet->currency }} {{ number_format($wallet->balance, 2) }}</div>
        <div class="stat-label mb-2">Stored Balance</div>
        <p class="text-sm">Ledger balance: <strong>{{ number_format($reconciliation['ledger'], 2) }}</strong>
        <span class="term-tag {{ $reconciliation['match'] ? '' : '' }} ml-1">{{ $reconciliation['match'] ? 'Reconciled ✓' : 'Mismatch ' . $reconciliation['difference'] }}</span></p>
        <div class="flex gap-2 mt-4">
            @if($wallet->status === 'active')
            <form method="POST" action="{{ route('admin.wallets.freeze', $wallet) }}" class="inline">@csrf<button class="term-btn term-btn-ghost term-btn-sm">Freeze</button></form>
            @else
            <form method="POST" action="{{ route('admin.wallets.unfreeze', $wallet) }}" class="inline">@csrf<button class="term-btn term-btn-ghost term-btn-sm">Unfreeze</button></form>
            @endif
            <a href="{{ route('admin.users.wallets', $wallet->owner) }}" class="term-btn term-btn-ghost term-btn-sm">Owner wallets</a>
        </div>
    </div>
    <div class="card p-5 lg:col-span-2">
        <h2 class="heading-sm mb-2">Manual Adjustment (audited, reason mandatory)</h2>        <form method="POST" action="{{ route('admin.wallets.adjust', $wallet) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2 items-end">
            @csrf
            <div><label class="term-field-label">Direction</label><select name="direction" class="term-input"><option value="credit">Credit</option><option value="debit">Debit</option></select></div>
            <div><label class="term-field-label">Amount</label><input type="number" name="amount" min="0.01" step="0.01" class="term-input" required></div>
            <div class="sm:col-span-2"><label class="term-field-label">Reason (required)</label><input name="reason" class="term-input" placeholder="Why is this adjustment legitimate?…" required></div>
            <button class="term-btn term-btn-sm sm:col-span-4">Post Adjustment</button>
        </form>
        <details class="mt-4 rounded-xl border border-red-200 dark:border-red-900/40 p-3">
            <summary class="text-sm font-semibold text-red-700 dark:text-red-300 cursor-pointer">Exceptional ownership correction</summary>
            <p class="term-hint mb-2">Admin only. New owner must be an eligible customer. Ledger history is preserved; the change is audited with reason.</p>
            <form method="POST" action="{{ route('admin.wallets.correct-owner', $wallet) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-end">
                @csrf
                <div class="sm:col-span-2"><label class="term-field-label">New owner email</label><input type="email" name="new_owner_email" class="term-input" required></div>
                <div class="sm:col-span-3"><label class="term-field-label">Mandatory reason</label><input name="reason" class="term-input" minlength="10" placeholder="Why is this correction legitimate?…" required></div>
                <button class="term-btn term-btn-ghost term-btn-sm sm:col-span-3" onclick="return confirm('Correct wallet ownership? This is audited and cannot be undone silently.')">Correct Ownership</button>
            </form>
        </details>
    </div>
</div>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Date</th><th>Reference</th><th>Type</th><th>Description</th><th class="data-table-numeric">Amount</th><th class="data-table-numeric">After</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @foreach($transactions as $t)
        <tr>
            <td class="whitespace-nowrap text-xs" data-label="Date">{{ $t->created_at->format('d M Y H:i') }}</td>
            <td class="font-mono text-xs" data-label="Reference">{{ $t->transaction_reference }}</td>
            <td data-label="Type">{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
            <td class="text-sm" data-label="Description">{{ $t->description ?? '—' }}</td>
            <td class="data-table-numeric {{ $t->isCredit() ? 'text-emerald-600 dark:text-emerald-400' : '' }}" data-label="Amount">{{ $t->isCredit() ? '+' : '−' }}{{ number_format($t->amount, 2) }}</td>
            <td class="data-table-numeric" data-label="After">{{ number_format($t->balance_after, 2) }}</td>
            <td data-label="Status"><span class="term-tag {{ $t->status === 'completed' ? '' : '' }}">{{ ucfirst($t->status) }}</span></td>
            <td data-label="">@if($t->type === 'invoice_payment' && $t->status === 'completed')<form method="POST" action="{{ route('admin.wallets.refund', $t) }}" class="flex gap-1">@csrf<input name="reason" class="term-input w-32" placeholder="Reason…" required><button class="term-btn term-btn-ghost term-btn-sm" onclick="return confirm('Refund this wallet payment?')">Refund</button></form>@endif</td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
