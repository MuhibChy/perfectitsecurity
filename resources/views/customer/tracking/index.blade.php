@extends('layouts.app')
@section('title', 'My Services')
@section('page-title', 'My Services')

@section('content')
<x-page-header title="My Services" subtitle="Live status, progress, ETA and updates for every service — from authoritative records." sys="TRACKING://LIVE" num="06">
    <x-slot:actions>
        <a href="{{ route('portal.reports.mine', ['type' => 'task', 'format' => 'pdf']) }}" class="term-btn term-btn-ghost term-btn-sm">Generate Report</a>
    </x-slot:actions>
</x-page-header>

@if($maintenance->isNotEmpty())
<div class="term-panel p-5 mb-6">
    <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2">Upcoming Maintenance</h2>
    @foreach($maintenance as $m)<p class="text-sm text-slate-600 dark:text-term-800 py-1"><strong class="text-slate-900 dark:text-white">{{ $m->title }}</strong> · next {{ $m->next_due_at?->format('d M Y') ?? 'scheduled' }} · {{ ucfirst($m->status) }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($services as $s)
    <div class="term-panel p-5">
        <div class="flex flex-wrap justify-between gap-2 mb-1">
            <strong class="text-slate-900 dark:text-white">{{ $s['order']->service->name ?? $s['order']->service_snapshot['name'] ?? 'Service' }}</strong>
            <x-status-badge :status="$s['order']->status" />
        </div>
        <p class="font-mono text-[11px] text-slate-600 dark:text-term-800 mb-2">{{ $s['order']->order_number }}</p>
        <div class="w-full h-2 bg-white/10 overflow-hidden mb-1">
            <div class="h-full" style="width: {{ $s['progress']['percent'] }}%; background: linear-gradient(90deg,#00E67A,#4DA3FF);"></div>
        </div>
        <p class="font-mono text-[11px] text-slate-600 dark:text-term-800 mb-2">{{ $s['progress']['percent'] }}% · {{ $s['progress']['basis'] }}</p>
        <dl class="text-sm space-y-1">
            <div class="flex justify-between gap-2"><dt class="text-slate-600 dark:text-term-800">Current stage</dt><dd class="font-medium text-right text-slate-900 dark:text-white">{{ $s['stage'] }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-600 dark:text-term-800">ETA</dt><dd class="font-medium">{{ $s['eta']['label'] }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-600 dark:text-term-800">Outstanding</dt><dd class="font-medium tabular-nums">{{ number_format($s['due'], 2) }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-600 dark:text-term-800">Last update</dt><dd class="text-right text-slate-600 dark:text-term-800">{{ $s['last_update'] ? $s['last_update']->created_at->format('d M Y') . ' — ' . \Illuminate\Support\Str::limit($s['last_update']->comment ?? $s['last_update']->action, 60) : '—' }}</dd></div>
        </dl>
        <div class="flex flex-wrap gap-2 mt-3">
            @if($s['project'])<a href="{{ route('portal.tracking.show', $s['project']) }}" class="term-btn term-btn-ghost term-btn-sm">Track Service →</a>@endif
            <a href="{{ route('portal.orders.show', $s['order']) }}" class="term-btn term-btn-ghost term-btn-sm">Order</a>
        </div>
    </div>
    @empty
    <div class="term-panel p-6">
        <p class="text-sm text-slate-600 dark:text-term-800">No services yet. <a href="{{ route('portal.service-request.create') }}" class="term-link text-sm">Request a service →</a></p>
    </div>
    @endforelse
</div>
@endsection
