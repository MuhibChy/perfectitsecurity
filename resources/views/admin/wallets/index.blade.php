@extends('layouts.app')
@section('title', 'Wallets Overview')
@section('page-title', 'Wallets')

@section('content')
<x-page-header sys="FINANCE://WALLETS" title="Wallets Overview" subtitle="Customer funds held in FWallet ledgers. Balances are never edited directly — only ledger transactions move money." :breadcrumbs="['Finance' => route('admin.financials.index'), 'Wallets' => null]">
    @if($stats['mismatches'] > 0)<span class="term-tag">{{ $stats['mismatches'] }} reconciliation mismatch(es)</span>@endif
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach([['Deposits',$stats['deposits']],['Wallet payments',$stats['payments']],['Refunds',$stats['refunds']],['Ledger rows',$stats['count']],['Mismatches',$stats['mismatches']]] as [$label,$val])
    <div class="term-panel"><div class="stat-value">{{ is_numeric($val) && $val > 999 ? number_format($val, 2) : $val }}</div><div class="stat-label">{{ $label }}</div></div>
    @endforeach
</div>

<div class="term-panel p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" class="term-input flex-1 min-w-[200px]" placeholder="Search wallet reference, name, email…">
        <select name="status" class="term-input w-auto"><option value="">All statuses</option>@foreach(\App\Models\Wallet::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
        <button class="term-btn term-btn-ghost term-btn-sm">Search</button>
    </form>
</div>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Wallet</th><th>Owner</th><th>Currency</th><th class="data-table-numeric">Balance</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @foreach($wallets as $w)
        <tr>
            <td class="font-mono text-xs" data-label="Wallet">{{ $w->wallet_reference }}</td>
            <td data-label="Owner">{{ $w->owner->name ?? '—' }}<div class="text-xs text-slate-500">{{ $w->owner->email ?? '' }}</div></td>
            <td data-label="Currency">{{ $w->currency }}</td>
            <td class="data-table-numeric" data-label="Balance">{{ number_format($w->balance, 2) }}</td>
            <td data-label="Status"><span class="term-tag {{ $w->status === 'active' ? '' : '' }}">{{ ucfirst($w->status) }}</span></td>
            <td data-label=""><a href="{{ route('admin.wallets.show', $w) }}" class="link-arrow text-sm">Open →</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $wallets->links() }}</div>
@endsection
