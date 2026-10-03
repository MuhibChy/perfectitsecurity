@extends('layouts.app')
@section('title', $project->name)
@section('page-title', $project->name)

@section('content')
<x-page-header :title="$project->name" :subtitle="'Stage: ' . $stage . ' · ETA: ' . \App\Services\ServiceTrackingService::etaFor($project)['label']" sys="TRACKING://LIVE" :breadcrumbs="['My Services' => route('portal.tracking.index'), $project->name => null]" :badge="ucfirst(str_replace('_', ' ', $project->status))">
    <a href="{{ route('portal.tracking.report-pdf', $project) }}" target="_blank" class="term-btn term-btn-ghost term-btn-sm no-print">Service Report PDF ↓</a>
</x-page-header>

<div class="w-full h-2 bg-white/10 overflow-hidden mb-1">
    <div class="h-full" style="width: {{ $progress['percent'] }}%; background: linear-gradient(90deg,#00E67A,#4DA3FF);"></div>
</div>
<p class="font-mono text-[11px] text-slate-600 dark:text-term-800 mb-6">{{ $progress['percent'] }}% · {{ $progress['basis'] }}</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-3">Milestones &amp; Work</h2>
        <div class="term-panel p-5 mb-4">
            @foreach($project->milestones as $ms)<p class="text-sm text-slate-600 dark:text-term-800 py-1">{{ $ms->is_completed ? '✓' : '○' }} {{ $ms->name }}</p>@endforeach
            @foreach($project->tasks as $task)
            @php $run = \App\Services\ServiceTrackingService::taskRunning($task); @endphp
            <p class="text-sm text-slate-600 dark:text-term-800 py-1 border-t border-white/5">{{ $task->title }} <span class="font-mono text-[11px]">· {{ ucfirst(str_replace('_',' ',$task->status)) }}{{ $run ? ' · running ' . $run['human'] : '' }}</span></p>
            @endforeach
            <p class="term-hint mt-2">Team: {{ $project->projectManager->name ?? 'Assigned team' }}</p>
        </div>

        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-3">Ask / Request Update</h2>
        <div class="term-panel p-5 space-y-3">
            <form method="POST" action="{{ route('portal.tracking.request-update', $project) }}">
                @csrf<button class="term-btn term-btn-ghost term-btn-sm">Request Service Update</button>
            </form>
            <form method="POST" action="{{ route('portal.tracking.ask', $project) }}" class="space-y-2">
                @csrf
                <textarea name="question" rows="2" class="term-input text-sm" placeholder="What is the status? Can I upload another file?…" required></textarea>
                <button class="term-btn term-btn-ghost term-btn-sm">Send Question</button>
            </form>
        </div>

        @if($maintenances->isNotEmpty())
        <h2 class="text-base font-bold text-slate-900 dark:text-white mt-4 mb-3">Maintenance</h2>
        <div class="term-panel p-5">
            @foreach($maintenances as $m)<p class="text-sm text-slate-600 dark:text-term-800 py-1"><strong class="text-slate-900 dark:text-white">{{ $m->title }}</strong> · {{ ucfirst($m->status) }} · next {{ $m->next_due_at?->format('d M Y') ?? '—' }}</p>@endforeach
        </div>
        @endif
    </div>

    <div>
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-3">Latest Updates</h2>
        <div class="space-y-3">
            @forelse($updates as $u)
            <div class="term-panel p-4">
                <p class="text-sm text-slate-600 dark:text-term-800">{{ $u->comment ?? ucfirst(str_replace('_', ' ', $u->action)) }}</p>
                <p class="term-hint mt-1">{{ $u->created_at->format('d M Y H:i') }}</p>
            </div>
            @empty
            <p class="text-sm text-slate-600 dark:text-term-800">No updates published yet.</p>
            @endforelse
        </div>
        <h2 class="text-base font-bold text-slate-900 dark:text-white mt-4 mb-3">Team Notes (shared)</h2>
        <div class="space-y-2">
            @foreach($comments as $c)
            <div class="term-panel-2 p-3"><p class="text-sm text-slate-600 dark:text-term-800">{{ $c->comment }}</p><p class="term-hint">{{ $c->user->name ?? '' }} · {{ $c->created_at->format('d M H:i') }}</p></div>
            @endforeach
        </div>
    </div>
</div>
@endsection
