@extends('layouts.app')
@section('page-title', 'Page Health Checker')

@section('content')
<div class="space-y-6">
    <x-page-header title="Page Health Checker" sys="SYSTEM://HEALTH" />

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Page Health Checker</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Automated HTTP response and latency monitoring across all registered application routes.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.health.run-check') }}" method="POST">
                @csrf
                <button type="submit" class="term-btn flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Check All Routes</span>
                </button>
            </form>
            <a href="{{ route('admin.health.index') }}" class="term-btn term-btn-ghost font-medium">Overview</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="term-panel p-4">
        <form method="GET" action="{{ route('admin.health.pages') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search route or URI..." class="term-input">
            </div>
            <div>
                <select name="module" class="term-input">
                    <option value="">-- All Modules --</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="term-input">
                    <option value="">-- All Statuses --</option>
                    <option value="healthy" {{ request('status') === 'healthy' ? 'selected' : '' }}>Healthy</option>
                    <option value="warning" {{ request('status') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full term-btn term-btn-ghost">Filter</button>
                <a href="{{ route('admin.health.pages') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-900 dark:hover:text-white">Reset</a>
            </div>
        </form>
    </div>

    {{-- Page Health Table --}}
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
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
                        <td data-label="Route / URI">
                            <div class="font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $p->uri }}</div>
                            @if($p->route_name)
                            <div class="text-[11px] text-gray-400 font-mono">{{ $p->route_name }}</div>
                            @endif
                        </td>
                        <td data-label="Module"><span class="term-tag">{{ $p->module }}</span></td>
                        <td data-label="Tested As"><span class="text-xs text-gray-500 uppercase">{{ $p->role_tested }}</span></td>
                        <td data-label="HTTP Status">
                            @if($p->http_status >= 200 && $p->http_status < 400)
                                <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $p->http_status }} OK</span>
                            @else
                                <span class="font-mono text-xs font-bold text-red-600 dark:text-red-400">{{ $p->http_status ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td class="font-mono text-xs" data-label="Latency">{{ $p->response_time_ms ? $p->response_time_ms.'ms' : '-' }}</td>
                        <td data-label="Health Status">
                            @if($p->status === 'healthy')
                                <x-status-badge status="active" label="Healthy" />
                            @elseif($p->status === 'warning')
                                <x-status-badge status="pending" label="Warning" />
                            @else
                                <x-status-badge status="error" label="Error" />
                            @endif
                            @if($p->error_summary)
                                <p class="text-[10px] text-red-500 dark:text-red-400 mt-1 line-clamp-1" title="{{ $p->error_summary }}">{{ $p->error_summary }}</p>
                            @endif
                        </td>
                        <td class="text-xs text-gray-400" data-label="Last Checked">{{ $p->last_checked_at ? $p->last_checked_at->diffForHumans() : 'Never' }}</td>
                        <td data-label="Action">
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
