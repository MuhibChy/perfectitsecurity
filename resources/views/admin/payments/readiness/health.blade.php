@extends('layouts.app')
@section('title', 'Payment Health')
@section('page-title', 'Payment Health')

@section('content')
<x-page-header sys="PAYMENTS://HEALTH" title="Payment Health" subtitle="Counts + latest events. Heavy lists stay paginated on their own pages." :breadcrumbs="['Sales & Finance' => route('admin.financials.index'), 'Payments' => route('admin.payments.overview'), 'Health' => null]">
    <a href="{{ route('admin.payments.readiness') }}" class="term-btn term-btn-ghost term-btn-sm">Readiness</a>
    <a href="{{ route('admin.payments.reconciliation') }}" class="term-btn term-btn-ghost term-btn-sm">Reconciliation</a>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    @foreach([['Paid today', $monitoring['paid_today']],['Failed today', $monitoring['failed_today']],['Pending', $monitoring['pending']],['Refunds open', $monitoring['refunds_open']],['Reconciliation review', $monitoring['recon_review']],['Webhook failures', $monitoring['webhook_failed']],['Bank transfers awaiting review', $monitoring['bank_pending']]] as [$l,$v])
    <div class="term-panel"><div class="stat-value">{{ $v }}</div><div class="stat-label">{{ $l }}</div></div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-4">
<div class="term-panel p-4">
    <div class="font-semibold mb-2">FX health — {{ $fx['status'] }}</div>
    <div class="text-sm">Source: {{ $fx['source'] }}</div>
    <div class="text-sm">Last update: {{ $fx['last_update'] ?? 'never' }} · Rows: {{ $fx['row_count'] }}</div>
    <div class="text-sm">Currencies: {{ implode(', ', $fx['supported_currencies']) }}</div>
    <div class="text-xs text-slate-500 mt-1">{{ $fx['fallback'] }}</div>
</div>
<div class="term-panel p-4">
    <div class="font-semibold mb-2">Provider availability</div>
    @foreach($matrix as $m)
    <div class="flex justify-between text-sm py-1 border-b border-slate-100 dark:border-slate-800"><span>{{ $m['name'] }}</span><strong>{{ str_replace('_',' ',$m['state']) }}</strong></div>
    @endforeach
</div>
</div>

<div class="term-panel p-4 mt-4">
    <div class="font-semibold mb-2">Latest webhook events (10)</div>
    <table class="data-table term-table"><thead><tr><th>Provider</th><th>Event</th><th>Status</th><th>Verified</th><th>At</th></tr></thead><tbody>
    @forelse($monitoring['latest_webhooks'] as $e)
    <tr><td>{{ $e->provider_key }}</td><td class="font-mono text-xs">{{ \Illuminate\Support\Str::limit($e->event_id, 28) }}</td><td>{{ $e->status }}</td><td>{{ $e->signature_valid ? '✓' : '✗' }}</td><td class="text-xs">{{ $e->created_at }}</td></tr>
    @empty<tr><td colspan="5" class="text-slate-500 text-sm">No webhook events yet.</td></tr>@endforelse
    </tbody></table>
</div>
@endsection
