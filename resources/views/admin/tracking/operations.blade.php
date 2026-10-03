@extends('layouts.app')
@section('title', 'Live Service Operations')
@section('page-title', 'Operations')

@section('content')
<x-page-header sys="OPS://TRACKING" title="Live Service Operations" :subtitle="'Updated ' . now()->format('d M Y H:i')" badge="LIVE">
    <a href="{{ route('admin.search.index') }}" class="term-btn term-btn-ghost term-btn-sm">Search Work</a>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Active services',$stats['active']],['Queued',$stats['queued']],['Waiting for customer',$stats['waitingCustomers']],['Overdue',$stats['overdue']],['Completed today',$stats['completedToday']],['Open follow-ups',$stats['updateRequests']],['Maintenance due (14d)',$stats['maintenanceDue']]] as [$label,$val])
    <div class="term-panel"><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
    @endforeach
</div>

<h2 class="heading-sm mb-3">Active Service Work</h2>
<div class="card p-0 overflow-hidden mb-8"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Customer</th><th>Service</th><th>Order</th><th>Assigned To</th><th>Started</th><th>Running For</th><th>ETA</th><th>Status</th></tr></thead>
    <tbody>
        @forelse($active as $row)
        <tr>
            <td class="font-medium" data-label="Customer">{{ $row['task']->project->customer->name ?? '—' }}</td>
            <td data-label="Service">{{ $row['task']->project->service->name ?? $row['task']->project->name ?? '—' }}<div class="text-xs text-slate-500">{{ $row['task']->title }}</div></td>
            <td class="font-mono text-xs" data-label="Order">{{ $row['task']->serviceOrder->order_number ?? '—' }}</td>
            <td data-label="Assigned To">{{ $row['task']->assignee->name ?? 'Unassigned' }}</td>
            <td class="text-xs whitespace-nowrap" data-label="Started">{{ $row['running']['started_at']->format('d M H:i') ?? '—' }}</td>
            <td data-label="Running For">{{ $row['running']['human'] ?? '—' }}@if($row['running']['frozen'] ?? false) <span class="term-tag">paused</span>@endif</td>
            <td data-label="ETA">{{ $row['eta']['label'] }}</td>
            <td data-label="Status">@if($row['waiting'])<span class="term-tag">Waiting for customer</span>@else<span class="term-tag">In progress</span>@endif</td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-slate-500 py-6">No active work right now.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Customer Follow-Ups</h2>
        @forelse($followUps as $f)
        <div class="py-2 border-b border-slate-100 dark:border-white/5 last:border-0 text-sm">
            <strong>{{ $f->customer->name ?? 'Customer' }}</strong> · {{ $f->action === 'query_asked' ? 'asked' : 'requested an update' }}
            <p class="text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($f->comment ?? '', 140) }}</p>
            <form method="POST" action="{{ route('admin.service-events.answer', $f) }}" class="flex gap-2 mt-1">
                @csrf<input name="answer" class="term-input" placeholder="Answer using authoritative service info…" required>
                <button class="term-btn term-btn-ghost term-btn-sm whitespace-nowrap">Answer</button>
            </form>
        </div>
        @empty<p class="body-sm">No pending follow-ups.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Maintenance Due (14 days)</h2>
        @forelse($maintenanceDue as $m)
        <div class="text-sm py-2 border-b border-slate-100 dark:border-white/5 last:border-0">
            <strong>{{ $m->title }}</strong> · {{ $m->customer->name ?? '' }}
            <div class="text-xs text-slate-500">Due {{ $m->next_due_at?->format('d M Y') }} · {{ $m->assignee->name ?? 'Unassigned' }}</div>
        </div>
        @empty<p class="body-sm">Nothing due.</p>@endforelse
    </div>
</div>
@endsection
