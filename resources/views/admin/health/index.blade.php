@extends('layouts.app')
@section('page-title', 'Website Overview & System Health')

@section('content')
<div class="space-y-6">

    {{-- Top Header & Quick Action --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Website Overview & System Health</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Centralized operational health, automated page diagnostics, and real-time error telemetry.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('admin.health.run-check') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl shadow-md shadow-primary-500/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Run System Check</span>
                </button>
            </form>
            <a href="{{ route('admin.health.pages') }}" class="btn-secondary px-4 py-2.5 rounded-xl text-sm font-medium">Page Checker</a>
            <a href="{{ route('admin.health.errors') }}" class="btn-secondary px-4 py-2.5 rounded-xl text-sm font-medium relative">
                <span>Error Center</span>
                @if($unresolvedErrorsCount > 0)
                <span class="absolute -top-1.5 -right-1.5 px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-600 text-white">{{ $unresolvedErrorsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.health.history') }}" class="btn-secondary px-4 py-2.5 rounded-xl text-sm font-medium">History</a>
        </div>
    </div>

    {{-- Overall Status Banner --}}
    <div class="p-6 rounded-2xl border {{ $overallBadgeClass }} flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl font-bold bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm border border-current shadow-sm">
                @if($overallStatus === 'healthy') 🟢 @elseif($overallStatus === 'warning') 🟡 @else 🔴 @endif
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $overallTitle }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                    @if($overallStatus === 'healthy')
                        All registered modules, routes, database queries, and storage subsystems are performing optimally.
                    @elseif($overallStatus === 'warning')
                        Some modules or routes have warnings or unresolved error reports. Review below.
                    @else
                        Critical subsystem or page failures require immediate administrative attention!
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-6 text-sm font-semibold">
            <div class="text-center">
                <span class="block text-2xl font-bold text-gray-900 dark:text-white">{{ $healthyModules }}/{{ $totalModules }}</span>
                <span class="text-xs text-gray-500 font-normal">Modules Healthy</span>
            </div>
            <div class="text-center">
                <span class="block text-2xl font-bold text-gray-900 dark:text-white">{{ $healthyPages }}/{{ $totalPages }}</span>
                <span class="text-xs text-gray-500 font-normal">Pages Healthy</span>
            </div>
            <div class="text-center">
                <span class="block text-2xl font-bold {{ $unresolvedErrorsCount > 0 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">{{ $unresolvedErrorsCount }}</span>
                <span class="text-xs text-gray-500 font-normal">Active Errors</span>
            </div>
        </div>
    </div>

    {{-- Backup Status Widget --}}
    @if (! empty($backupWidget))
    <a href="{{ route('admin.backups.index') }}" class="p-4 rounded-2xl border flex items-center justify-between gap-4 {{ $backupWidget['verified'] ? 'border-gray-200/70 dark:border-gray-800' : 'border-amber-500/40' }}">
        <div class="text-sm">
            <span class="font-bold">Backups:</span>
            @if ($backupWidget['last_id'])
                last {{ $backupWidget['last_id'] }} ({{ $backupWidget['last_at']?->diffForHumans() }}) —
                {{ $backupWidget['verified'] ? 'verified' : 'NOT verified' }}
            @else
                no backups recorded yet
            @endif
            @if ($backupWidget['failed'] > 0)
                <span class="text-red-600">· {{ $backupWidget['failed'] }} failed</span>
            @endif
        </div>
        <span class="text-xs text-primary-600 font-semibold">Backup Center &rarr;</span>
    </a>
    @endif

    {{-- Module Health Overview Grid --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Module Health Overview</h2>
            <span class="text-xs text-gray-500">Auto-monitored & evaluated</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($moduleChecks as $check)
            <div class="glass-card p-5 flex flex-col justify-between hover:shadow-lg transition-all border {{ $check->status === 'healthy' ? 'border-gray-200/70 dark:border-gray-800' : ($check->status === 'warning' ? 'border-amber-500/40' : 'border-red-500/40') }}">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $check->module }}</h3>
                        <span class="badge {{ $check->status === 'healthy' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($check->status === 'warning' ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400') }}">
                            {{ ucfirst($check->status) }}
                        </span>
                    </div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">{{ $check->check_name }}</p>
                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed">{{ $check->message }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between text-[11px] text-gray-400">
                    <span>{{ $check->response_time_ms ? $check->response_time_ms.'ms' : '<1ms' }}</span>
                    <span>{{ $check->checked_at ? $check->checked_at->diffForHumans() : 'Just now' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Quick Troubleshoot Panel & Maintenance Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Troubleshoot Panel --}}
        <div class="lg:col-span-2 glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Quick Troubleshoot & Guidance</span>
                </h2>
                <a href="{{ route('admin.health.errors') }}" class="text-xs font-semibold text-primary-600 hover:underline">View All Errors</a>
            </div>

            @if($recentErrors->count() > 0)
            <div class="space-y-3">
                @foreach($recentErrors as $err)
                <div class="p-4 rounded-xl bg-red-50/50 dark:bg-red-950/20 border border-red-200/50 dark:border-red-900/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="badge bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">{{ $err->module }}</span>
                            <span class="text-xs font-mono text-gray-500">{{ $err->route ?? 'Global' }}</span>
                            <span class="text-[10px] text-gray-400">({{ $err->occurrences }}x)</span>
                        </div>
                        <p class="text-xs font-medium text-gray-900 dark:text-gray-200 line-clamp-1">{{ $err->message }}</p>
                    </div>
                    <form action="{{ route('admin.health.resolve-error', $err->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg transition-colors whitespace-nowrap">
                            Mark Resolved
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-6 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-900/30 flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">No active errors detected</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Application exception handlers and frontend error interceptors are active and reporting healthy status.</p>
                </div>
            </div>
            @endif

            {{-- Suggested Action Guidance --}}
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400 space-y-1.5">
                <p><strong>Database Latency:</strong> Check MySQL daemon status and verify connection limits in <code>.env</code>.</p>
                <p><strong>Page 404 / 500:</strong> Visit the <a href="{{ route('admin.health.pages') }}" class="text-primary-600 font-semibold hover:underline">Page Checker</a> to pinpoint missing view variables or broken controllers.</p>
                <p><strong>Queue Backlog:</strong> Check failed jobs or execute <em>Retry Failed Jobs</em> below.</p>
            </div>
        </div>

        {{-- Safe Maintenance Actions --}}
        <div class="glass-card p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Safe Maintenance Actions</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Non-destructive operational tools to refresh caches and restart workers.</p>

                <div class="space-y-2.5">
                    <form action="{{ route('admin.health.maintenance') }}" method="POST" onsubmit="return confirm('Clear application cache?');">
                        @csrf
                        <input type="hidden" name="action" value="clear_cache">
                        <button type="submit" class="w-full btn-secondary p-2.5 text-xs font-semibold rounded-xl flex items-center justify-between">
                            <span>Clear Application Cache</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>

                    <form action="{{ route('admin.health.maintenance') }}" method="POST" onsubmit="return confirm('Clear compiled Blade views?');">
                        @csrf
                        <input type="hidden" name="action" value="clear_views">
                        <button type="submit" class="w-full btn-secondary p-2.5 text-xs font-semibold rounded-xl flex items-center justify-between">
                            <span>Clear Compiled Views</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </form>

                    <form action="{{ route('admin.health.maintenance') }}" method="POST" onsubmit="return confirm('Clear route cache?');">
                        @csrf
                        <input type="hidden" name="action" value="clear_routes">
                        <button type="submit" class="w-full btn-secondary p-2.5 text-xs font-semibold rounded-xl flex items-center justify-between">
                            <span>Clear Route Cache</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </button>
                    </form>

                    <form action="{{ route('admin.health.maintenance') }}" method="POST" onsubmit="return confirm('Retry all failed queue jobs?');">
                        @csrf
                        <input type="hidden" name="action" value="retry_failed_jobs">
                        <button type="submit" class="w-full btn-secondary p-2.5 text-xs font-semibold rounded-xl flex items-center justify-between">
                            <span>Retry Failed Queue Jobs</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-[11px] text-gray-400 text-center">
                All maintenance actions are non-destructive and safe for production.
            </div>
        </div>
    </div>
</div>
@endsection
