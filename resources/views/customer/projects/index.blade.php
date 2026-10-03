@extends('layouts.app')

@section('title', 'My Projects — Customer Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Infrastructure Projects"
        subtitle="Track active cloud deployments, cybersecurity hardening milestones, and technical implementations."
        sys="CLIENT://PROJECTS"
        num="10"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Projects' => null]"
    />

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Projects"
            :value="$projects->total()"
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'
        />
        <x-stat-card
            title="Active & Planning"
            :value="$projects->whereIn('status', ['in_progress', 'planning'])->count()"
            color="amber"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
        />
        <x-stat-card
            title="Completed"
            :value="$projects->where('status', 'completed')->count()"
            color="emerald"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Allocated Budget"
            :value="'$' . number_format($projects->sum('budget'), 2)"
            color="purple"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'
        />
    </div>

    {{-- Projects Table Card --}}
    <div class="term-panel overflow-hidden">
        @if($projects->count() > 0)
        <div class="term-table-wrap !border-0">
            <table class="data-table term-table term-table-cards">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Lead</th>
                        <th>Deadline</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    <tr>
                        <td data-label="Project">
                            <a href="{{ route('portal.projects.show', $project->id) }}" class="font-mono font-semibold text-accent-soft hover:underline">
                                {{ $project->project_number ?? 'PRJ-' . str_pad($project->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td data-label="Name">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $project->name }}</div>
                            <div class="text-xs text-slate-600 dark:text-term-800 line-clamp-1">{{ $project->description }}</div>
                        </td>
                        <td data-label="Status">
                            <x-status-badge :status="$project->status" />
                        </td>
                        <td data-label="Progress" class="w-36">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-white/10 h-2 overflow-hidden">
                                    <div class="h-2" style="width: {{ $project->progress ?? 0 }}%; background: linear-gradient(90deg,#00E67A,#4DA3FF);"></div>
                                </div>
                                <span class="text-xs font-mono font-semibold tabular-nums">{{ $project->progress ?? 0 }}%</span>
                            </div>
                        </td>
                        <td data-label="Lead" class="text-xs">
                            {{ $project->projectManager->name ?? 'Engineering Staff' }}
                        </td>
                        <td data-label="Deadline" class="font-mono text-[11px] {{ $project->deadline && $project->deadline->isPast() ? 'text-red-400 font-bold' : 'text-slate-600 dark:text-term-800' }}">
                            {{ $project->deadline ? $project->deadline->format('M d, Y') : 'Ongoing' }}
                        </td>
                        <td data-label="Action" class="text-right">
                            <a href="{{ route('portal.projects.show', $project->id) }}" class="term-btn term-btn-ghost term-btn-sm">
                                View Board &rarr;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-white/10">
            {{ $projects->links() }}
        </div>
        @else
        <x-empty-state-3d type="projects" title="No Active Projects"
            message="Contracted services, cloud migrations, and cybersecurity deployments will be tracked on this dashboard."
            actionText="Request a New Project" :actionUrl="route('portal.service-request.create')" />
        @endif
    </div>
</div>
@endsection
