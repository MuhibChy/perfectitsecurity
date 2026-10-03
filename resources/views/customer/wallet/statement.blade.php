@extends('layouts.app')
@section('title', 'Wallet Statement')
@section('page-title', 'Statement')

@section('content')
<x-page-header :title="'Statement — ' . $wallet->wallet_reference" :subtitle="'Balance ' . $wallet->currency . ' ' . number_format($wallet->balance, 2)" sys="PAYMENT://WALLET" :breadcrumbs="['My Wallet' => route('portal.wallet.index'), 'Statement' => null]">
    <a href="{{ route('portal.wallet.statement', array_merge(['wallet' => $wallet->id], request()->except('page', 'export'), ['export' => 'pdf'])) }}" class="term-btn term-btn-ghost term-btn-sm no-print">Download PDF</a>
</x-page-header>

<div class="term-panel p-4 mb-4 no-print">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="type" class="term-input text-sm w-auto"><option value="">All types</option>@foreach(\App\Models\WalletTransaction::TYPES as $t)<option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>@endforeach</select>
        <select name="direction" class="term-input text-sm w-auto"><option value="">Credit + Debit</option><option value="credit" @selected(request('direction') === 'credit')>Credit</option><option value="debit" @selected(request('direction') === 'debit')>Debit</option></select>
        <select name="status" class="term-input text-sm w-auto"><option value="">All statuses</option>@foreach(\App\Models\WalletTransaction::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
        <input name="reference" value="{{ request('reference') }}" class="term-input text-sm w-auto" placeholder="Reference…">
        <input type="date" name="from" value="{{ request('from') }}" class="term-input text-sm w-auto">
        <input type="date" name="to" value="{{ request('to') }}" class="term-input text-sm w-auto">
        <button class="term-btn term-btn-ghost term-btn-sm">Filter</button>
    </form>
</div>

<div class="term-panel overflow-hidden"><div class="term-table-wrap !border-0">
<table class="data-table term-table term-table-cards">
    <thead><tr><th>Date</th><th>Ref</th><th>Type</th><th>Description</th><th class="text-right">Credit</th><th class="text-right">Debit</th><th class="text-right">Balance</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($transactions as $t)
        <tr>
            <td data-label="Date" class="whitespace-nowrap font-mono text-[11px]">{{ $t->created_at->format('d M Y H:i') }}</td>
            <td data-label="Ref" class="font-mono text-xs">{{ $t->transaction_reference }}</td>
            <td data-label="Type">{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
            <td data-label="Description" class="text-sm">{{ $t->description ?? '—' }}</td>
            <td data-label="Credit" class="text-right tabular-nums"><span class="fin-tag fin-tag-income">{{ $t->isCredit() ? number_format($t->amount, 2) : '' }}</span></td>
            <td data-label="Debit" class="text-right tabular-nums"><span class="fin-tag fin-tag-expense">{{ $t->isCredit() ? '' : number_format($t->amount, 2) }}</span></td>
            <td data-label="Balance" class="text-right tabular-nums">{{ number_format($t->balance_after, 2) }}</td>
            <td data-label="Status"><x-status-badge :status="$t->status" /></td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
