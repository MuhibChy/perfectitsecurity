@extends('layouts.app')
@section('page-title', 'System Status History & Retention')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Status History & Retention</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Audit log of all past diagnostic runs, system uptime telemetry, and log retention pruning.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.health.index') }}" class="btn-secondary px-4 py-2 rounded-xl text-sm font-medium">Overview</a>
        </div>
    </div>

    {{-- Stats & Pruning --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card p-6 flex flex-col justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Check Runs Recorded</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalChecks }}</p>
            </div>
            <div class="flex items-center gap-4 mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs">
                <span class="text-emerald-600 font-semibold">{{ $totalChecks - $criticalCount - $warningCount }} Healthy</span>
                <span class="text-amber-600 font-semibold">{{ $warningCount }} Warnings</span>
                <span class="text-red-600 font-semibold">{{ $criticalCount }} Critical</span>
            </div>
        </div>

        <div class="md:col-span-2 glass-card p-6">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-2">Automated Retention & Pruning</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Clean up old health check runs and resolved error logs to keep database storage lightweight.</p>

            <form action="{{ route('admin.health.cleanup-history') }}" method="POST" class="flex flex-wrap items-center gap-3" onsubmit="return confirm('Clean up old health monitoring logs?');">
                @csrf
                <select name="days" class="text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="7">Prune records older than 7 days</option>
                    <option value="14">Prune records older than 14 days</option>
                    <option value="30" selected>Prune records older than 30 days</option>
                    <option value="60">Prune records older than 60 days</option>
                    <option value="90">Prune records older than 90 days</option>
                </select>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition-colors">
                    Prune Old Records
                </button>
            </form>
        </div>
    </div>

    {{-- History Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Diagnostic Check</th>
                        <th>Status</th>
                        <th>Latency</th>
                        <th>Message</th>
                        <th>Checked At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                    <tr>
                        <td class="font-bold text-xs text-gray-900 dark:text-white">{{ $h->module }}</td>
                        <td class="text-xs text-gray-600 dark:text-gray-300">{{ $h->check_name }}</td>
                        <td>
                            @if($h->status === 'healthy')
                                <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">🟢 Healthy</span>
                            @elseif($h->status === 'warning')
                                <span class="badge bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">🟡 Warning</span>
                            @else
                                <span class="badge bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400">🔴 Critical</span>
                            @endif
                        </td>
                        <td class="font-mono text-xs">{{ $h->response_time_ms ? $h->response_time_ms.'ms' : '<1ms' }}</td>
                        <td class="text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $h->message }}">{{ $h->message }}</td>
                        <td class="text-xs text-gray-400 font-mono">{{ $h->checked_at ? $h->checked_at->format('Y-m-d H:i:s') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">No historical check records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">
            {{ $history->links() }}
        </div>
    </div>
</div>
@endsection
