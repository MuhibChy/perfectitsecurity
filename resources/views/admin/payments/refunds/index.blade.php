@extends('layouts.app')
@section('title', 'Payment Refunds')
@section('page-title', 'Refunds')

@section('content')
<x-page-header sys="PAYMENTS://REFUNDS" title="Refunds" subtitle="Requested → approved → executed. Execution reuses the audited RFD + ledger rails." :breadcrumbs="['Payments' => route('admin.payments.overview'), 'Refunds' => null]" />

<div class="term-panel p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="status" class="term-input w-auto"><option value="">All statuses</option>@foreach(\App\Models\PaymentRefund::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
        <button class="term-btn term-btn-ghost term-btn-sm">Filter</button>
    </form>
</div>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Refund</th><th>Customer</th><th>Payment</th><th class="data-table-numeric">Amount</th><th>Status</th><th>Requested</th><th></th></tr></thead>
    <tbody>
        @forelse($refunds as $r)
        <tr>
            <td class="font-mono text-xs" data-label="Refund">{{ $r->refund_number }}</td>
            <td data-label="Customer">{{ $r->customer->name ?? '—' }}</td>
            <td class="font-mono text-xs" data-label="Payment">{{ $r->payment->payment_number ?? '—' }}</td>
            <td class="data-table-numeric" data-label="Amount">{{ $r->currency }} {{ number_format($r->amount, 2) }}</td>
            <td data-label="Status"><span class="term-tag">{{ strtoupper($r->status) }}</span></td>
            <td data-label="Requested">{{ $r->created_at?->format('Y-m-d H:i') }}</td>
            <td data-label=""><a href="{{ route('admin.refunds.show', $r) }}" class="link-arrow text-sm">Open →</a></td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-slate-500 py-6">No refunds.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $refunds->links() }}</div>
@endsection
