@extends('layouts.app')
@section('title', 'Bank Transfer Verifications')
@section('page-title', 'Bank Transfers')

@section('content')
<x-page-header sys="PAYMENTS://BANK-TRANSFERS" title="Bank Transfer Verifications" subtitle="Customer transfer claims. Receipts are never proof — verify against the bank before confirming." :breadcrumbs="['Payments' => route('admin.payments.overview'), 'Bank transfers' => null]">
    @if($pendingCount > 0)<span class="term-tag">{{ $pendingCount }} pending verification</span>@endif
</x-page-header>

<div class="term-panel p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="status" class="term-input w-auto"><option value="">All statuses</option>@foreach(\App\Models\ManualBankPayment::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select>
        <button class="term-btn term-btn-ghost term-btn-sm">Filter</button>
    </form>
</div>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Reference</th><th>Customer</th><th>Invoice</th><th class="data-table-numeric">Amount</th><th>Sender bank</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
    <tbody>
        @forelse($payments as $t)
        <tr>
            <td class="font-mono text-xs" data-label="Reference">{{ $t->reference }}</td>
            <td data-label="Customer">{{ $t->customer->name ?? '—' }}</td>
            <td data-label="Invoice">{{ $t->invoice->invoice_number ?? '—' }}</td>
            <td class="data-table-numeric" data-label="Amount">{{ $t->currency }} {{ number_format($t->amount, 2) }}</td>
            <td data-label="Sender bank">{{ $t->sender_bank ?? '—' }}</td>
            <td data-label="Status"><span class="term-tag">{{ strtoupper(str_replace('_',' ',$t->status)) }}</span></td>
            <td data-label="Submitted">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
            <td data-label=""><a href="{{ route('admin.bank-transfers.show', $t) }}" class="link-arrow text-sm">Review →</a></td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-slate-500 py-6">No transfer submissions.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
