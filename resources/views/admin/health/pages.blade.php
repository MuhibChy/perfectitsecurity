@extends('layouts.app')
@section('page-title', 'Page Health Checker')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Page Health Checker</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Automated HTTP response and latency monitoring across all registered application routes.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.health.run-check') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Check All Routes</span>
                </button>
            </form>
            <a href="{{ route('admin.health.index') }}" class="btn-secondary px-4 py-2 rounded-xl text-sm font-medium">Overview</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card p-4">
        <form method="GET" action="{{ route('admin.health.pages') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search route or URI..." class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
            </div>
            <div>
                <select name="module" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">-- All Modules --</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">-- All Statuses --</option>
                    <option value="healthy" {{ request('status') === 'healthy' ? 'selected' : '' }}>Healthy</option>
                    <option value="warning" {{ request('status') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full btn-secondary py-2 text-xs font-semibold rounded-xl">Filter</button>
                <a href="{{ route('admin.health.pages') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-900 dark:hover:text-white rounded-xl">Reset</a>
            </div>
        </form>
    </div>

    {{-- Page Health Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Route / URI</th>
                        <th>Module</th>
                        <th>Tested As</th>
                        <th>HTTP Status</th>
                        <th>Latency</th>
                        <th>Health Status</th>
                        <th>Last Checked</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $p)
                    <tr>
                        <td>
                            <div class="font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $p->uri }}</div>
                            @if($p->route_name)
                            <div class="text-[11px] text-gray-400 font-mono">{{ $p->route_name }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-primary">{{ $p->module }}</span></td>
                        <td><span class="text-xs text-gray-500 uppercase">{{ $p->role_tested }}</span></td>
                        <td>
                            @if($p->http_status >= 200 && $p->http_status < 400)
                                <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $p->http_status }} OK</span>
                            @else
                                <span class="font-mono text-xs font-bold text-red-600 dark:text-red-400">{{ $p->http_status ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td class="font-mono text-xs">{{ $p->response_time_ms ? $p->response_time_ms.'ms' : '-' }}</td>
                        <td>
                            @if($p->status === 'healthy')
                                <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">🟢 Healthy</span>
                            @elseif($p->status === 'warning')
                                <span class="badge bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">🟡 Warning</span>
                            @else
                                <span class="badge bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400">🔴 Error</span>
                            @endif
                            @if($p->error_summary)
                                <p class="text-[10px] text-red-500 dark:text-red-400 mt-1 line-clamp-1" title="{{ $p->error_summary }}">{{ $p->error_summary }}</p>
                            @endif
                        </td>
                        <td class="text-xs text-gray-400">{{ $p->last_checked_at ? $p->last_checked_at->diffForHumans() : 'Never' }}</td>
                        <td>
                            <form action="{{ route('admin.health.check-page', $p->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-primary-900/30 transition-colors">
                                    Re-test
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">No pages found matching filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">
            {{ $pages->links() }}
        </div>
    </div>
</div>
@endsection
