@extends('layouts.app')
@section('title', 'Audit & Traceability Dashboard')
@section('page-title', 'Audit History')

@section('content')
<x-page-header sys="OPS://TRACEABILITY" title="Audit & Traceability Dashboard" subtitle="Technical audit record: who did what, when, to which record. Business history lives on the customer/employee pages." badge="ADMIN ONLY">
    <a href="{{ route('admin.history.consistency') }}" class="term-btn term-btn-ghost term-btn-sm">Consistency Check</a>
    <a href="{{ route('admin.audit-logs.index') }}" class="term-btn term-btn-ghost term-btn-sm">Full Audit Log</a>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="term-panel"><div class="stat-value">{{ $today }}</div><div class="stat-label">Events today</div></div>
    @foreach($byModule->take(3) as $mod => $c)<div class="term-panel"><div class="stat-value">{{ $c }}</div><div class="stat-label">{{ ucfirst($mod) }} today</div></div>@endforeach
</div>

@if($failed->isNotEmpty())
<div class="card p-5 mb-6 border-l-4 border-l-amber-500">
    <h2 class="heading-sm mb-2">Failed / Breach Events (latest)</h2>
    @foreach($failed as $f)<p class="text-sm py-1">{{ $f->created_at->format('d M H:i') }} — {{ $f->description ?? $f->action }} <span class="text-xs text-slate-500">({{ $f->user->name ?? 'system' }})</span></p>@endforeach
</div>
@endif

<div class="term-panel p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="module" class="term-input w-auto"><option value="">All modules</option>@foreach($modules as $m)<option value="{{ $m }}" @selected(request('module') === $m)>{{ ucfirst($m) }}</option>@endforeach</select>
        <input name="action" value="{{ request('action') }}" class="term-input w-auto" placeholder="Action contains…">
        <input type="date" name="date" value="{{ request('date') }}" class="term-input w-auto">
        <button class="term-btn term-btn-ghost term-btn-sm">Filter</button>
    </form>
</div>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
    <thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Record</th><th>Change</th></tr></thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td class="whitespace-nowrap text-xs" data-label="When">{{ $log->created_at->format('d M Y H:i') }}</td>
            <td class="text-sm" data-label="Actor">{{ $log->user->name ?? 'System' }}<div class="text-xs text-slate-500">{{ $log->user->role ?? '' }} · {{ $log->ip_address }}</div></td>
            <td data-label="Action"><span class="term-tag">{{ $log->action }}</span><div class="text-xs text-slate-500">{{ $log->module }}</div></td>
            <td class="text-sm" data-label="Record">{{ $log->auditable_type ? class_basename($log->auditable_type) . ' #' . $log->auditable_id : '—' }}</td>
            <td class="text-xs text-slate-600 dark:text-slate-300" data-label="Change">{{ $log->description ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table></div></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
