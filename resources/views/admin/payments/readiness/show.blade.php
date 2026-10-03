@extends('layouts.app')
@section('title', 'Provider Readiness — ' . $detail['name'])
@section('page-title', 'Provider Readiness')

@section('content')
<x-page-header sys="PAYMENTS://READINESS/{{ strtoupper($detail['key']) }}" title="{{ $detail['name'] }} — {{ str_replace('_', ' ', $detail['state']) }}" subtitle="Env: {{ strtoupper($detail['environment']) }} · Status: {{ strtoupper($detail['status']) }}" :breadcrumbs="['Sales & Finance' => route('admin.financials.index'), 'Readiness' => route('admin.payments.readiness'), $detail['name'] => null]">
    <a href="{{ route('admin.payments.readiness') }}" class="term-btn term-btn-ghost term-btn-sm">All providers</a>
</x-page-header>

@if(session('status'))<div class="term-panel p-3 mb-4 text-emerald-700">{{ session('status') }}</div>@endif
@if($errors->any())<div class="term-panel p-3 mb-4 text-red-700">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<div class="grid lg:grid-cols-2 gap-4 mb-4">
<div class="term-panel p-4">
    <div class="font-semibold mb-2">Readiness checks (presence only — no secret values)</div>
    <table class="data-table term-table"><tbody>
    @foreach([
        'provider_enabled' => 'Provider enabled',
        'credentials_configured' => 'Credentials configured (encrypted row)',
        'required_env_present' => 'Required environment variables present',
        'webhook_configured' => 'Webhook endpoint configured',
        'webhook_secret_configured' => 'Webhook secret configured',
        'currency_supported' => 'Currency supported',
        'amount_limits_configured' => 'Amount limits configured',
        'fee_configuration' => 'Fee configuration',
        'test_mode' => 'Test mode',
        'production_mode' => 'Production mode',
        'bank_account_configured' => 'Bank account configured',
        'fx_configuration' => 'FX configuration',
        'database_configuration' => 'Database configuration',
        'security_checks' => 'Security checks',
    ] as $k => $label)
        <tr><td>{{ $label }}</td><td class="text-right">@if($detail['checks'][$k] ?? false)<span class="text-emerald-600 font-bold">✓</span>@else<span class="text-red-600 font-bold">✗</span>@endif</td></tr>
    @endforeach
    </tbody></table>
    <div class="text-xs text-slate-500 mt-2">Last health check: {{ $detail['checks']['last_health_check'] ?? '—' }}</div>
</div>

<div class="term-panel p-4">
    <div class="font-semibold mb-2">Webhook</div>
    <div class="flex gap-2 items-center mb-2">
        <code class="text-xs break-all flex-1">{{ url($detail['webhook_url']) }}</code>
        <button class="term-btn term-btn-ghost term-btn-sm" onclick="navigator.clipboard.writeText('{{ url($detail['webhook_url']) }}');this.textContent='Copied ✓';setTimeout(()=>this.textContent='Copy',1500)">Copy</button>
    </div>
    <table class="data-table term-table"><tbody>
        <tr><td>Last received</td><td class="text-right text-sm">{{ $detail['webhook_stats']['last_received'] }}</td></tr>
        <tr><td>Last verified</td><td class="text-right text-sm">{{ $detail['webhook_stats']['last_verified'] }}</td></tr>
        <tr><td>Last failed</td><td class="text-right text-sm">{{ $detail['webhook_stats']['last_failed'] }}</td></tr>
        <tr><td>Failures</td><td class="text-right text-sm">{{ $detail['webhook_stats']['failure_count'] }}</td></tr>
    </tbody></table>

    <div class="font-semibold mt-4 mb-2">Blockers / warnings</div>
    @if(empty($detail['blockers']) && empty($detail['warnings']))<p class="text-sm text-slate-500">No blockers.</p>@endif
    @foreach($detail['blockers'] as $b)<div class="text-sm text-red-700">🔴 {{ $b }}</div>@endforeach
    @foreach($detail['warnings'] as $w)<div class="text-sm text-amber-700">🟡 {{ $w }}</div>@endforeach
</div>
</div>

<div class="term-panel p-4">
    <div class="font-semibold mb-2">LIVE activation (gated)</div>
    <p class="text-sm text-slate-600 mb-2">Activation requires all blockers resolved, APP_ENV awareness, the exact confirmation phrase, and writes an immutable audit entry. It never exposes secrets.</p>
    <form method="POST" action="{{ route('admin.payments.readiness.activate', $detail['key']) }}" class="flex flex-col gap-2">
        @csrf
        <input type="hidden" name="environment" value="live">
        <label class="text-xs text-slate-500">Type exactly:</label>
        <code class="text-xs bg-slate-100 dark:bg-slate-800 p-2 rounded">{{ $confirmation }}</code>
        <input name="confirmation" class="term-input" placeholder="Paste the confirmation phrase…" required>
        <button class="term-btn term-btn-sm w-fit" @disabled(!empty($detail['blockers']))>Activate LIVE</button>
        @if(!empty($detail['blockers']))<span class="text-xs text-red-600">Blocked until all blockers above are resolved.</span>@endif
    </form>
</div>
@endsection
