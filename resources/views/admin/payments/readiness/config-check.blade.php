@extends('layouts.app')
@section('title', 'Production Config Check')
@section('page-title', 'Config Check')

@section('content')
<x-page-header sys="PAYMENTS://CONFIG" title="Production Configuration Check" subtitle="Presence only — values are never printed." :breadcrumbs="['Sales & Finance' => route('admin.financials.index'), 'Readiness' => route('admin.payments.readiness'), 'Config' => null]">
    <a href="{{ route('admin.payments.readiness') }}" class="term-btn term-btn-ghost term-btn-sm">Readiness</a>
</x-page-header>

@if(!$check['pass'])
<div class="term-panel p-3 mb-4 text-red-700">⚠ Insecure configuration detected ({{ count($check['insecure']) }} item(s)). Resolve before LIVE activation. @if($check['debug'] && $check['app_env'] === 'production') APP_DEBUG=true in production is a go-live blocker. @endif</div>
@else
<div class="term-panel p-3 mb-4 text-emerald-700">✓ Configuration check passed (APP_ENV={{ $check['app_env'] }}).</div>
@endif

<div class="term-panel p-4 mb-4">
<table class="data-table term-table"><thead><tr><th>Key</th><th>State</th><th>Detail</th></tr></thead><tbody>
@foreach($check['rows'] as $r)
<tr><td class="font-mono text-xs">{{ $r['key'] }}</td><td>@if($r['present'])<span class="text-emerald-600 font-bold">✓ set</span>@else<span class="text-red-600 font-bold">✗ missing</span>@endif @unless($r['secure'])<span class="text-red-600 font-bold">· INSECURE</span>@endunless</td><td class="text-sm">{{ $r['value_shown'] }}</td></tr>
@endforeach
</tbody></table>
</div>

<div class="term-panel p-4">
    <div class="font-semibold mb-1">FX — {{ $fx['status'] }}</div>
    <div class="text-sm">Last update: {{ $fx['last_update'] ?? 'never' }} · {{ $fx['row_count'] }} rows · {{ implode(', ', $fx['supported_currencies']) }}</div>
    <div class="text-xs text-slate-500">{{ $fx['fallback'] }}</div>
</div>
@endsection
