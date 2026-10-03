@extends('layouts.app')
@section('title', 'Payment Go-Live Checklist')
@section('page-title', 'Go-Live Checklist')

@section('content')
<x-page-header sys="PAYMENTS://GOLIVE" title="Payment Go-Live Checklist" subtitle="{{ $checklist['done'] }}/{{ $checklist['total'] }} complete · Overall: {{ $overall['overall'] }}" :breadcrumbs="['Sales & Finance' => route('admin.financials.index'), 'Readiness' => route('admin.payments.readiness'), 'Checklist' => null]">
    <a href="{{ route('admin.payments.health') }}" class="term-btn term-btn-ghost term-btn-sm">Health</a>
</x-page-header>

@foreach($checklist['groups'] as $group => $items)
<div class="term-panel p-4 mb-4">
    <div class="font-semibold mb-2">{{ $group }}</div>
    @foreach($items as $i)
    <div class="flex gap-2 items-start py-1 border-b border-slate-100 dark:border-slate-800 text-sm">
        <span>@if($i['done'])<span class="text-emerald-600 font-bold">🟢</span>@else<span class="text-red-600 font-bold">🔴</span>@endif</span>
        <div><strong>{{ $i['label'] }}</strong><div class="text-xs text-slate-500">{{ $i['hint'] }}</div></div>
    </div>
    @endforeach
</div>
@endforeach

<div class="term-panel p-4">
    <div class="font-semibold mb-2">Provider end-to-end verification log</div>
    <p class="text-sm text-slate-600">A provider counts as production-verified only after a controlled real transaction: invoice → payment → callback/webhook → status → balance → receipt → ledger → history → commission/wallet → notification → reconciliation. Record evidence (invoice no., transaction ref, date, verifier) in the audit log before claiming LIVE VERIFIED. Automated tests alone do not qualify.</p>
    <div class="text-sm mt-2">Per-provider status: @foreach($overall['providers'] as $k => $s)<span class="term-tag">{{ $k }}: {{ $s }}</span> @endforeach</div>
</div>
@endsection
