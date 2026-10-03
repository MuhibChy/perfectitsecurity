@extends('layouts.app')
@section('title', 'Payments Overview')
@section('page-title', 'Payments')

@section('content')
<x-page-header sys="PAYMENTS://OVERVIEW" title="Payments Overview" subtitle="All rails, all providers, one ledger. Settlements post through the shared finance funnel." :breadcrumbs="['Sales & Finance' => route('admin.financials.index'), 'Payments' => null]">
    <a href="{{ route('admin.payment-providers.index') }}" class="term-btn term-btn-ghost term-btn-sm">Providers</a>
    <a href="{{ route('admin.bank-transfers.index') }}" class="term-btn term-btn-ghost term-btn-sm">Bank Transfers @if($stats['bank_pending'] > 0)({{ $stats['bank_pending'] }})@endif</a>
    <a href="{{ route('admin.refunds.index') }}" class="term-btn term-btn-ghost term-btn-sm">Refunds @if($stats['refund_requests'] > 0)({{ $stats['refund_requests'] }})@endif</a>
    <a href="{{ route('admin.payments.reconciliation') }}" class="term-btn term-btn-ghost term-btn-sm">Reconciliation</a>
    <a href="{{ route('admin.payments.readiness') }}" class="term-btn term-btn-ghost term-btn-sm">LIVE Readiness</a>
    <a href="{{ route('admin.payments.health') }}" class="term-btn term-btn-ghost term-btn-sm">Health</a>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Today (' . $stats['today_count'] . ')', number_format($stats['today_total'], 2)],['This month (' . $stats['month_count'] . ')', number_format($stats['month_total'], 2)],['Pending review', $stats['pending']],['Outstanding invoices', number_format($stats['outstanding'], 2)]] as [$label,$val])
    <div class="term-panel"><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
    @endforeach
</div>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Failed / cancelled', $stats['failed']],['Refunded total', number_format($stats['refunded_amount'], 2)],['Bank transfers pending', $stats['bank_pending']],['Refund requests', $stats['refund_requests']]] as [$label,$val])
    <div class="term-panel"><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
    @endforeach
</div>

<div class="term-panel p-4 mb-4">
    <div class="font-semibold mb-2">Received by currency (settled)</div>
    <div class="flex flex-wrap gap-2">
        @forelse($byCurrency as $row)
        <span class="term-tag">{{ $row->original_currency }} · {{ number_format($row->total, 2) }} ({{ $row->n }})</span>
        @empty
        <span class="text-slate-500 text-sm">No settled payments yet.</span>
        @endforelse
    </div>
</div>

<div class="term-panel p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <input name="customer" value="{{ $filters['customer'] ?? '' }}" class="term-input flex-1 min-w-[160px]" placeholder="Customer name or email…">
        <select name="provider" class="term-input w-auto"><option value="">All providers</option>@foreach($providers as $p)<option value="{{ $p->key }}" @selected(($filters['provider'] ?? '') === $p->key)>{{ $p->name }}</option>@endforeach</select>
        <select name="status" class="term-input w-auto"><option value="">All statuses</option>@foreach(\App\Models\PaymentTransaction::STATUSES as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select>
        <input name="currency" value="{{ $filters['currency'] ?? '' }}" class="term-input w-28" placeholder="Currency" maxlength="3">
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="term-input w-auto">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="term-input w-auto">
        <button class="term-btn term-btn-ghost term-btn-sm">Filter</button>
    </form>
</div>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Reference</th><th>Customer</th><th>Invoice</th><th>Provider</th><th class="data-table-numeric">Amount</th><th>Status</th><th>Created</th></tr></thead>
    <tbody>
        @forelse($transactions as $t)
        <tr>
            <td class="font-mono text-xs" data-label="Reference">{{ $t->reference }}</td>
            <td data-label="Customer">{{ $t->customer->name ?? '—' }}</td>
            <td data-label="Invoice">{{ $t->invoice->invoice_number ?? '—' }}</td>
            <td data-label="Provider">{{ $t->provider->name ?? ucfirst($t->provider_key) }}</td>
            <td class="data-table-numeric" data-label="Amount">{{ $t->original_currency }} {{ number_format($t->gross_amount, 2) }}</td>
            <td data-label="Status"><span class="term-tag">{{ strtoupper(str_replace('_',' ',$t->status)) }}</span></td>
            <td data-label="Created">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-slate-500 py-6">No payment transactions match.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
