@extends('layouts.app')
@section('title', 'Payment LIVE Readiness')
@section('page-title', 'LIVE Readiness')

@section('content')
<x-page-header sys="PAYMENTS://READINESS" title="Payment LIVE Readiness" subtitle="App: {{ strtoupper($appEnv) }} · Presence-only checks — secrets are never displayed." :breadcrumbs="['Sales & Finance' => route('admin.financials.index'), 'Payments' => route('admin.payments.overview'), 'Readiness' => null]">
    <a href="{{ route('admin.payments.health') }}" class="term-btn term-btn-ghost term-btn-sm">Health</a>
    <a href="{{ route('admin.payments.config-check') }}" class="term-btn term-btn-ghost term-btn-sm">Config Check</a>
    <a href="{{ route('admin.payments.checklist') }}" class="term-btn term-btn-ghost term-btn-sm">Go-Live Checklist</a>
</x-page-header>

<div class="term-panel p-4 mb-4">
    <div class="font-semibold mb-2">Overall: <span class="term-tag">{{ $overall['overall'] }}</span></div>
    <div class="flex flex-wrap gap-2 text-sm">
        @foreach(['architecture' => 'Architecture','security' => 'Security','database' => 'Database','finance' => 'Finance','wallet' => 'Wallet','commission' => 'Commission','webhooks' => 'Webhooks','reconciliation' => 'Reconciliation'] as $k => $label)
        <span class="term-tag">{{ $label }}: {{ $overall[$k] ?? '—' }}</span>
        @endforeach
    </div>
</div>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Provider</th><th>Type</th><th>Env</th><th>Readiness</th><th>Blockers</th><th></th></tr></thead>
    <tbody>
    @foreach($matrix as $m)
        <tr>
            <td data-label="Provider"><strong>{{ $m['name'] }}</strong> <span class="text-slate-500 text-xs font-mono">{{ $m['key'] }}</span></td>
            <td data-label="Type">{{ $m['type'] }}</td>
            <td data-label="Env"><span class="term-tag">{{ strtoupper($m['environment']) }} / {{ strtoupper($m['status']) }}</span></td>
            <td data-label="Readiness">
                @php($color = in_array($m['state'], ['LIVE']) ? 'text-emerald-600' : (in_array($m['state'], ['LIVE_READY','TEST_PASSED']) ? 'text-sky-600' : (in_array($m['state'], ['ERROR']) ? 'text-red-600' : 'text-amber-600')))
                <strong class="{{ $color }}">{{ str_replace('_', ' ', $m['state']) }}</strong>
            </td>
            <td data-label="Blockers" class="text-sm">
                @if(empty($m['blockers']))<span class="text-slate-500">—</span>@else
                <ul class="list-disc ml-4">@foreach(array_slice($m['blockers'], 0, 2) as $b)<li>{{ $b }}</li>@endforeach</ul>
                @endif
            </td>
            <td data-label=""><a href="{{ route('admin.payments.readiness.show', $m['key']) }}" class="term-btn term-btn-ghost term-btn-sm">Detail</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
</div></div>
<p class="text-xs text-slate-500 mt-3">A provider is never LIVE until required configuration passes, explicit confirmation is recorded, and a controlled real transaction is verified. Automated checks alone do not equal production verification.</p>
@endsection
