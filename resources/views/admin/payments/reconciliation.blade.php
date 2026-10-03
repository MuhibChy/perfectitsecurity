@extends('layouts.app')
@section('title', 'Payment Reconciliation')
@section('page-title', 'Reconciliation')

@section('content')
<x-page-header sys="PAYMENTS://RECONCILIATION" title="Reconciliation" subtitle="Internal transaction vs provider status. Mismatches stay open for manual review." :breadcrumbs="['Payments' => route('admin.payments.overview'), 'Reconciliation' => null]">
    <span class="term-tag">Checked {{ $summary['checked'] }} · matched {{ $summary['matched'] }} · mismatch {{ $summary['mismatch'] }} · review {{ $summary['requires_verification'] }}</span>
    @if($needsReview > 0)<span class="term-tag">{{ $needsReview }} open item(s)</span>@endif
</x-page-header>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Internal ref</th><th>Provider ref</th><th class="data-table-numeric">Internal</th><th class="data-table-numeric">Provider</th><th>Internal status</th><th>Provider status</th><th>Result</th><th>Checked</th></tr></thead>
    <tbody>
        @forelse($records as $r)
        <tr>
            <td class="font-mono text-xs" data-label="Internal ref">{{ $r->internal_reference }}</td>
            <td class="font-mono text-xs" data-label="Provider ref">{{ $r->provider_reference ?? '—' }}</td>
            <td class="data-table-numeric" data-label="Internal">{{ $r->internal_currency }} {{ number_format($r->internal_amount, 2) }}</td>
            <td class="data-table-numeric" data-label="Provider">@if($r->provider_amount !== null){{ $r->provider_currency }} {{ number_format($r->provider_amount, 2) }}@else — @endif</td>
            <td data-label="Internal status">{{ $r->internal_status }}</td>
            <td data-label="Provider status">{{ $r->provider_status ?? '—' }}</td>
            <td data-label="Result"><span class="term-tag">{{ strtoupper(str_replace('_',' ',$r->result)) }}</span></td>
            <td data-label="Checked">{{ $r->checked_at?->format('Y-m-d H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-slate-500 py-6">No reconciliation records yet.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $records->links() }}</div>
@endsection
