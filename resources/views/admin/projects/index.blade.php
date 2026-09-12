@extends('layouts.app')

@section('title', 'Projects & Delivery Operations — Admin Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Project Management"
        subtitle="Orchestrate enterprise client deployments, cloud migrations, and security engineering deliverables."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Projects' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.projects.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Launch New Project
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Projects"
            :value="$projects->total()"
            color="blue"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'
        />
        <x-stat-card
            title="In Active Execution"
            :value="$projects->where('status', 'in_progress')->count()"
            color="amber"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
        />
        <x-stat-card
            title="Completed Projects"
            :value="$projects->where('status', 'completed')->count()"
            color="emerald"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
        <x-stat-card
            title="Total Project Budget"
            :value="'$' . number_format($projects->sum('budget'), 2)"
            color="purple"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'
        />
    </div>

    {{-- Filter Card --}}
    <div class="glass-card p-4 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto scrollbar-none">
            @php $currentStatus = request('status'); @endphp
            <a href="{{ route('admin.projects.index', array_filter(['search' => request('search')])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ !$currentStatus ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300' }}">
                All Projects
            </a>
            @foreach(['planning' => 'Planning', 'in_progress' => 'In Progress', 'review' => 'Review', 'completed' => 'Completed', 'on_hold' => 'On Hold'] as $prjSt => $prjLbl)
            <a href="{{ route('admin.projects.index', array_filter(['status' => $prjSt, 'search' => request('search')])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $currentStatus === $prjSt ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300 hover:bg-surface-200' }}">
                {{ $prjLbl }}
            </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.projects.index') }}" class="relative w-full md:w-72">
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search project or number..."
                   class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white dark:bg-navy-800 border border-surface-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </form>
    </div>

    {{-- Projects Table --}}
    <div class="glass-card overflow-hidden">
        @if($projects->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project Ref</th>
                        <th>Project Name & Client</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Project Lead</th>
                        <th>Budget</th>
                        <th>Deadline</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $prj)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                        <td>
                            <a href="{{ route('admin.projects.show', $prj->id) }}" class="font-mono font-bold text-primary-600 dark:text-primary-400 hover:underline">
                                {{ $prj->project_number }}
                            </a>
                        </td>
                        <td>
                            <div class="font-bold text-gray-900 dark:text-white">{{ $prj->name }}</div>
                            <div class="text-xs text-gray-500">Client: {{ $prj->customer->name ?? 'Internal' }}</div>
                        </td>
                        <td>
                            <x-status-badge :status="$prj->status" />
                        </td>
                        <td class="w-36">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-primary-500 to-cyan-400 h-2 rounded-full" style="width: {{ $prj->progress ?? 0 }}%"></div>
                                </div>
                                <span class="text-xs font-mono font-semibold text-gray-600 dark:text-gray-300">{{ $prj->progress ?? 0 }}%</span>
                            </div>
                        </td>
                        <td class="text-xs text-gray-600 dark:text-gray-300">
                            {{ $prj->projectManager->name ?? 'Unassigned' }}
                        </td>
                        <td class="font-mono text-xs font-semibold text-gray-900 dark:text-white">
                            ${{ number_format($prj->budget ?? 0, 2) }}
                        </td>
                        <td class="text-xs {{ $prj->deadline && $prj->deadline->isPast() ? 'text-rose-500 font-bold' : 'text-gray-500' }}">
                            {{ $prj->deadline ? $prj->deadline->format('M d, Y') : 'Ongoing' }}
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.projects.show', $prj->id) }}" class="btn-ghost btn-sm text-xs">
                                    View
                                </a>
                                <a href="{{ route('admin.projects.edit', $prj->id) }}" class="btn-secondary btn-sm text-xs">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
            {{ $projects->withQueryString()->links() }}
        </div>
        @else
        <x-empty-state
            title="No Projects Found"
            message="No active projects match the selected criteria."
            actionText="Create Project"
            :actionUrl="route('admin.projects.create')"
        />
        @endif
    </div>
</div>
@endsection
