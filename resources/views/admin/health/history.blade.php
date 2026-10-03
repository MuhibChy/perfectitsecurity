@extends('layouts.app')
@section('page-title', 'System Status History & Retention')

@section('content')
<div class="space-y-6">
    <x-page-header title="System Status History & Retention" sys="SYSTEM://HEALTH" />

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">System Status History & Retention</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Audit log of all past diagnostic runs, system uptime telemetry, and log retention pruning.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.health.index') }}" class="term-btn term-btn-ghost font-medium">Overview</a>
        </div>
    </div>

    {{-- Stats & Pruning --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="term-panel p-6 flex flex-col justify-between">
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

        <div class="md:col-span-2 term-panel p-6">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-2">Automated Retention & Pruning</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Clean up old health check runs and resolved error logs to keep database storage lightweight.</p>

            <form action="{{ route('admin.health.cleanup-history') }}" method="POST" class="flex flex-wrap items-center gap-3" onsubmit="return confirm('Clean up old health monitoring logs?');">
                @csrf
                <select name="days" class="term-input">
                    <option value="7">Prune records older than 7 days</option>
                    <option value="14">Prune records older than 14 days</option>
                    <option value="30" selected>Prune records older than 30 days</option>
                    <option value="60">Prune records older than 60 days</option>
                    <option value="90">Prune records older than 90 days</option>
                </select>
                <button type="submit" class="btn btn-destructive term-btn-sm">
                    Prune Old Records
                </button>
            </form>
        </div>
    </div>

    {{-- History Table --}}
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
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
                        <td class="font-bold text-xs text-gray-900 dark:text-white" data-label="Module">{{ $h->module }}</td>
                        <td class="text-xs text-gray-600 dark:text-gray-300" data-label="Diagnostic Check">{{ $h->check_name }}</td>
                        <td data-label="Status">
                            @if($h->status === 'healthy')
                                <x-status-badge status="active" label="Healthy" />
                            @elseif($h->status === 'warning')
                                <x-status-badge status="pending" label="Warning" />
                            @else
                                <x-status-badge status="critical" label="Critical" />
                            @endif
                        </td>
                        <td class="font-mono text-xs" data-label="Latency">{{ $h->response_time_ms ? $h->response_time_ms.'ms' : '<1ms' }}</td>
                        <td class="text-xs text-gray-700 dark:text-gray-300 max-w-xs truncate" title="{{ $h->message }}" data-label="Message">{{ $h->message }}</td>
                        <td class="text-xs text-gray-400 font-mono" data-label="Checked At">{{ $h->checked_at ? $h->checked_at->format('Y-m-d H:i:s') : '-' }}</td>
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
