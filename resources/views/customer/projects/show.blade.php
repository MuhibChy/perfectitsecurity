@extends('layouts.app')

@section('title', $project->name . ' — Customer Portal')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    <x-page-header
        :title="$project->name"
        :subtitle="'Project ' . ($project->project_number ?? 'PRJ-' . str_pad($project->id, 5, '0', STR_PAD_LEFT))"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Projects' => route('portal.projects.index'), 'Details' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.projects.index') }}" class="btn-ghost btn-sm">
                &larr; Back to Projects
            </a>
            <a href="{{ route('portal.tickets.create') }}" class="btn-primary btn-sm">
                Open Ticket for this Project
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Top Overview Card --}}
    <div class="glass-card p-6 lg:p-8 rounded-2xl shadow-xl border border-white/10">
        <div class="grid md:grid-cols-4 gap-6 items-center">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-2">
                    <x-status-badge :status="$project->status" />
                    <span class="text-xs text-gray-500 font-mono">Priority: {{ ucfirst($project->priority ?? 'medium') }}</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ $project->description ?? 'Engineering implementation for corporate infrastructure.' }}
                </p>
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Implementation Progress</span>
                <div class="mt-2 flex items-center gap-3">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary-500 to-cyan-400 h-3 rounded-full transition-all duration-500" style="width: {{ $project->progress ?? 0 }}%"></div>
                    </div>
                    <span class="font-mono font-bold text-sm text-gray-900 dark:text-white">{{ $project->progress ?? 0 }}%</span>
                </div>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1 md:text-right">
                <p>Start Date: <strong class="text-gray-800 dark:text-gray-200">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'Immediate' }}</strong></p>
                <p>Target Deadline: <strong class="{{ $project->deadline && $project->deadline->isPast() ? 'text-rose-500' : 'text-gray-800 dark:text-gray-200' }}">{{ $project->deadline ? $project->deadline->format('M d, Y') : 'Flexible' }}</strong></p>
                @if($project->budget)
                <p>Approved Budget: <strong class="text-gray-800 dark:text-gray-200">${{ number_format($project->budget, 2) }}</strong></p>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Grid: Tasks & Project Lead --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Tasks & Work Packages (2 Cols) --}}
        <div class="lg:col-span-2 glass-card p-6 rounded-2xl">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                <span>Associated Tasks & Milestones</span>
                <span class="text-xs font-normal text-gray-500">{{ $project->tasks->count() }} active items</span>
            </h3>

            @if($project->tasks->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($project->tasks as $task)
                <div class="py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full {{ $task->status === 'completed' ? 'bg-emerald-500' : 'bg-primary-500' }}"></div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $task->title }}</h4>
                            <p class="text-xs text-gray-500 line-clamp-1">{{ $task->description }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <x-status-badge :status="$task->status" />
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="py-12 text-center text-xs text-gray-500">
                Tasks for this project are currently being scheduled by the project manager.
            </div>
            @endif
        </div>

        {{-- Project Manager & Team Card (1 Col) --}}
        <div class="space-y-6">
            <div class="glass-card p-6 rounded-2xl">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Assigned Project Lead</span>
                @if($project->projectManager)
                <div class="mt-4 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-primary-600/20">
                        {{ substr($project->projectManager->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $project->projectManager->name }}</h4>
                        <p class="text-xs text-gray-500">{{ $project->projectManager->email }}</p>
                        @if($project->projectManager->phone)
                        <p class="text-xs font-mono text-primary-600 dark:text-primary-400 mt-0.5">{{ $project->projectManager->phone }}</p>
                        @endif
                    </div>
                </div>
                @else
                <div class="mt-4 text-xs text-gray-500">
                    Project manager assignment in review by lead architect.
                </div>
                @endif
            </div>

            <div class="glass-card p-6 rounded-2xl bg-gradient-to-br from-primary-500/10 to-transparent border border-primary-500/20">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Technical Escalations</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
                    Have scope adjustments or urgent timeline modifications for this project?
                </p>
                <a href="{{ route('portal.tickets.create') }}" class="btn-secondary btn-sm w-full">
                    Submit Project Ticket
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
