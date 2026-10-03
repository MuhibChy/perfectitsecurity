@extends('layouts.app')

@section('title', $project->name . ' — Customer Portal')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <x-page-header
        :title="$project->name"
        :subtitle="'Project ' . ($project->project_number ?? 'PRJ-' . str_pad($project->id, 5, '0', STR_PAD_LEFT))"
        sys="CLIENT://PROJECTS"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Projects' => route('portal.projects.index'), 'Details' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.projects.index') }}" class="term-btn term-btn-ghost term-btn-sm">
                &larr; Back to Projects
            </a>
            <a href="{{ route('portal.tickets.create') }}" class="term-btn term-btn-sm">
                Open Ticket for this Project
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Top Overview Card --}}
    <div class="term-panel p-6 lg:p-8">
        <div class="grid md:grid-cols-4 gap-6 items-center">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <x-status-badge :status="$project->status" />
                    <span class="term-tag">Priority: {{ ucfirst($project->priority ?? 'medium') }}</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-term-800 leading-relaxed">
                    {{ $project->description ?? 'Engineering implementation for corporate infrastructure.' }}
                </p>
            </div>

            <div>
                <span class="term-field-label">Implementation Progress</span>
                <div class="mt-2 flex items-center gap-3">
                    <div class="flex-1 bg-white/10 h-3 overflow-hidden">
                        <div class="h-3" style="width: {{ $project->progress ?? 0 }}%; background: linear-gradient(90deg,#00E67A,#4DA3FF);"></div>
                    </div>
                    <span class="font-mono font-bold text-sm tabular-nums">{{ $project->progress ?? 0 }}%</span>
                </div>
            </div>

            <div class="font-mono text-[11px] uppercase tracking-[0.12em] text-slate-600 dark:text-term-800 space-y-1 md:text-right">
                <p>Start: <strong class="text-slate-900 dark:text-white">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'Immediate' }}</strong></p>
                <p>Deadline: <strong class="{{ $project->deadline && $project->deadline->isPast() ? 'text-red-400' : 'text-slate-900 dark:text-white' }}">{{ $project->deadline ? $project->deadline->format('M d, Y') : 'Flexible' }}</strong></p>
                @if($project->budget)
                <p>Budget: <strong class="text-slate-900 dark:text-white tabular-nums">${{ number_format($project->budget, 2) }}</strong></p>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Grid: Tasks & Project Lead --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Tasks & Work Packages (2 Cols) --}}
        <div class="lg:col-span-2 term-panel p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center justify-between">
                <span>Associated Tasks &amp; Milestones</span>
                <span class="font-mono text-[11px] text-slate-600 dark:text-term-800">{{ $project->tasks->count() }} active items</span>
            </h3>

            @if($project->tasks->count() > 0)
            <div class="divide-y divide-white/5">
                @foreach($project->tasks as $task)
                <div class="py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="term-status-dot {{ $task->status === 'completed' ? '' : 'term-status-dot-blue' }}"></span>
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $task->title }}</h4>
                            <p class="text-xs text-slate-600 dark:text-term-800 line-clamp-1">{{ $task->description }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <x-status-badge :status="$task->status" />
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="py-12 text-center font-mono text-xs text-slate-600 dark:text-term-800">
                Tasks for this project are currently being scheduled by the project manager.
            </div>
            @endif
        </div>

        {{-- Project Manager & Team Card (1 Col) --}}
        <div class="space-y-6">
            <div class="term-panel p-6">
                <span class="term-field-label">Assigned Project Lead</span>
                @if($project->projectManager)
                <div class="mt-4 flex items-center gap-3">
                    <div class="w-12 h-12 bg-accent text-[#04120b] flex items-center justify-center font-bold text-lg flex-shrink-0">
                        {{ substr($project->projectManager->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $project->projectManager->name }}</h4>
                        <p class="text-xs text-slate-600 dark:text-term-800">{{ $project->projectManager->email }}</p>
                        @if($project->projectManager->phone)
                        <p class="text-xs font-mono text-accent-soft mt-0.5">{{ $project->projectManager->phone }}</p>
                        @endif
                    </div>
                </div>
                @else
                <div class="mt-4 term-hint">
                    Project manager assignment in review by lead architect.
                </div>
                @endif
            </div>

            <div class="term-panel p-6">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Technical Escalations</h4>
                <p class="text-xs text-slate-600 dark:text-term-800 mb-4 leading-relaxed">
                    Have scope adjustments or urgent timeline modifications for this project?
                </p>
                <a href="{{ route('portal.tickets.create') }}" class="term-btn term-btn-ghost term-btn-sm w-full">
                    Submit Project Ticket
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
