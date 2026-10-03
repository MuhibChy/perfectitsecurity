@extends('layouts.app')
@section('title', 'Service ' . $project->project_number)
@section('page-title', 'Service Detail')

@section('content')
<x-page-header sys="OPS://TRACKING" :title="$project->name" :subtitle="$project->project_number . ' · ' . ($project->customer->name ?? '')" :breadcrumbs="['Operations' => route('admin.operations.index'), $project->project_number => null]" :badge="ucfirst(str_replace('_', ' ', $project->status))">
    @if($project->status !== 'completed')
    <form method="POST" action="{{ route('admin.projects.status', $project) }}" class="inline-flex gap-2 items-center">
        @csrf
        <select name="status" class="term-input w-auto">@foreach(['pending','planning','in_progress','on_hold','review','completed','cancelled'] as $s)<option value="{{ $s }}" @selected($project->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select>
        <input name="reason" class="term-input" placeholder="Reason (recorded)…">
        <button class="term-btn term-btn-ghost term-btn-sm">Set Status</button>
    </form>
    @endif
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="term-panel"><div class="stat-value">{{ $progress['percent'] }}%</div><div class="stat-label">Progress · {{ $progress['basis'] }}</div></div>
    <div class="term-panel"><div class="stat-value text-lg">{{ $stage }}</div><div class="stat-label">Current Stage</div></div>
    <div class="term-panel"><div class="stat-value text-lg">{{ \App\Services\ServiceTrackingService::etaFor($project)['label'] }}</div><div class="stat-label">ETA</div></div>
    <div class="term-panel"><div class="stat-value text-lg">{{ $project->projectManager->name ?? 'Unassigned' }}</div><div class="stat-label">Project Manager</div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="heading-sm mb-3">Milestones & Tasks</h2>
        <div class="card p-5 mb-4">
            @foreach($project->milestones as $ms)
            <div class="flex items-center gap-2 text-sm py-1"><span>{{ $ms->is_completed ? '✓' : '○' }}</span><span class="{{ $ms->is_completed ? '' : 'font-medium' }}">{{ $ms->name }}</span></div>
            @endforeach
            @foreach($project->tasks as $task)
            @php $run = \App\Services\ServiceTrackingService::taskRunning($task); @endphp
            <div class="flex flex-wrap items-center justify-between gap-2 text-sm py-2 border-t border-slate-100 dark:border-white/5">
                <span><a href="{{ route('admin.tasks.show', $task) }}" class="link-arrow">{{ $task->title }}</a>
                <span class="text-xs text-slate-500">· {{ $task->assignee->name ?? 'Unassigned' }} · {{ ucfirst(str_replace('_',' ',$task->status)) }}{{ $run ? ' · running ' . $run['human'] : '' }}</span></span>
                <span class="flex gap-1">
                    @if($task->status === 'in_progress' && !$task->paused_at)
                    <form method="POST" action="{{ route('admin.tasks.pause', $task) }}">@csrf<input type="hidden" name="reason" value="Paused from service detail."><button class="term-btn term-btn-ghost term-btn-sm">Pause</button></form>
                    @elseif($task->paused_at)
                    <form method="POST" action="{{ route('admin.tasks.resume', $task) }}">@csrf<button class="term-btn term-btn-ghost term-btn-sm">Resume</button></form>
                    @endif
                </span>
            </div>
            @endforeach
        </div>

        <h2 class="heading-sm mb-3">Publish Customer Update</h2>
        <form method="POST" action="{{ route('admin.projects.publish-update', $project) }}" class="card p-5 mb-4 space-y-2">
            @csrf
            <textarea name="comment" rows="3" class="term-input" placeholder="Professional progress, current stage, next step, timing…" required></textarea>
            <div class="flex gap-2"><input type="date" name="next_update" class="term-input"><button class="term-btn term-btn-sm">Publish Update</button></div>
        </form>
        <h2 class="heading-sm mb-3">Internal Note (never customer-visible)</h2>
        <form method="POST" action="{{ route('admin.projects.internal-note', $project) }}" class="card p-5 mb-4 space-y-2">
            @csrf
            <textarea name="comment" rows="2" class="term-input" placeholder="Staff coordination…" required></textarea>
            <button class="term-btn term-btn-ghost term-btn-sm">Save Internal Note</button>
        </form>
    </div>

    <div>
        <h2 class="heading-sm mb-3">Service Timeline</h2>
        <div class="card p-4 mb-4 max-h-[420px] overflow-y-auto">
            @forelse($timeline as $e)
            <div class="py-2 border-b border-slate-100 dark:border-white/5 last:border-0 text-sm">
                <div class="flex justify-between gap-2"><strong>{{ ucfirst(str_replace('_', ' ', $e->action)) }}</strong><span class="text-xs text-slate-500 whitespace-nowrap">{{ $e->created_at->format('d M H:i') }}</span></div>
                <p class="text-slate-600 dark:text-slate-300">{{ $e->comment ?? (($e->old_value ? $e->old_value . ' → ' : '') . ($e->new_value ?? '')) }} {{ $e->reason ? '· ' . $e->reason : '' }}</p>
                <p class="text-xs text-slate-500">{{ $e->actor->name ?? 'System' }}{{ $e->customer_visible ? ' · customer-visible' : '' }}</p>
            </div>
            @empty
            <p class="body-sm">No events yet — status changes, updates and ETA moves will appear here.</p>
            @endforelse
        </div>

        @if($etaHistory->isNotEmpty())
        <h2 class="heading-sm mb-3">ETA History</h2>
        <div class="card p-4 mb-4">
            @foreach($etaHistory as $e)<p class="text-sm py-1">{{ $e->old_value ?? '—' }} → <strong>{{ $e->new_value ?? '—' }}</strong> <span class="text-xs text-slate-500">· {{ $e->actor->name ?? 'System' }} · {{ $e->created_at->format('d M Y') }}{{ $e->reason ? ' · ' . $e->reason : '' }}</span></p>@endforeach
        </div>
        @endif

        <h2 class="heading-sm mb-3">Maintenance</h2>
        <div class="card p-4 mb-4">
            @foreach($maintenances as $m)<p class="text-sm py-1"><strong>{{ $m->title }}</strong> · {{ ucfirst($m->status) }} · next {{ $m->next_due_at?->format('d M Y') ?? '—' }}</p>@endforeach
            <form method="POST" action="{{ route('admin.projects.maintenance.store', $project) }}" class="grid grid-cols-2 gap-2 mt-2">
                @csrf
                <input name="title" class="term-input col-span-2" placeholder="Maintenance title…" required>
                <input name="type" class="term-input" placeholder="Type (e.g. security review)" value="general" required>
                <input name="frequency" class="term-input" placeholder="Frequency (e.g. quarterly)">
                <input type="date" name="next_due_at" class="term-input">
                <button class="term-btn term-btn-ghost term-btn-sm">Schedule</button>
            </form>
        </div>

        @if($changes->isNotEmpty())
        <h2 class="heading-sm mb-3">Change Requests</h2>
        <div class="card p-4">
            @foreach($changes as $c)
            <div class="text-sm py-2 border-b border-slate-100 dark:border-white/5 last:border-0">
                <strong>{{ $c->title }}</strong> <span class="term-tag">{{ ucfirst(str_replace('_',' ',$c->status)) }}</span>
                <p class="text-slate-600 dark:text-slate-300">{{ $c->details }}</p>
                @if(in_array($c->status, ['requested','under_review'], true))
                <form method="POST" action="{{ route('admin.change-requests.review', $c) }}" class="flex flex-wrap gap-1 mt-1">
                    @csrf
                    <select name="status" class="term-input w-auto"><option value="approved">Approve</option><option value="rejected">Reject</option><option value="implemented">Implemented</option></select>
                    <input name="decision_note" class="term-input flex-1" placeholder="Decision note (required)…" required>
                    <button class="term-btn term-btn-ghost term-btn-sm">Decide</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
