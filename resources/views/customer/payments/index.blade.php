@extends('layouts.app')
@section('title', 'My Payments')
@section('page-title', 'My Payments')

@section('content')
<x-page-header sys="PAY://HISTORY" title="My Payments" subtitle="Every payment attempt, transaction ID, status and receipt — nothing hidden." :breadcrumbs="['Dashboard' => route('portal.dashboard'), 'My payments' => null]">
        <x-slot:actions>
            <a href="{{ route('portal.reports.mine', ['type' => 'payment', 'format' => 'pdf']) }}" class="term-btn term-btn-ghost term-btn-sm">Generate Report</a>
        </x-slot:actions>
</x-page-header>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Reference</th><th>Invoice</th><th>Method</th><th class="data-table-numeric">Amount</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
        @forelse($transactions as $t)
        <tr>
            <td class="font-mono text-xs" data-label="Reference">{{ $t->reference }}</td>
            <td data-label="Invoice">{{ $t->invoice->invoice_number ?? '—' }}</td>
            <td data-label="Method">{{ $t->provider->name ?? ucfirst(str_replace('_',' ',$t->payment_method)) }}</td>
            <td class="data-table-numeric" data-label="Amount">{{ $t->original_currency }} {{ number_format($t->gross_amount, 2) }}</td>
            <td data-label="Status"><span class="term-tag">{{ strtoupper(str_replace('_',' ',$t->status)) }}</span></td>
            <td data-label="Date">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
            <td data-label=""><a href="{{ route('portal.payments.show', $t->reference) }}" class="link-arrow text-sm">Details →</a></td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-slate-500 py-6">No payments yet.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
