@extends('layouts.app')
@section('page-title', 'SLA Compliance Reports')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Service Level Agreement (SLA) Compliance</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Response time commitments, breach statistics, and resolution thresholds.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn-secondary px-4 py-2 text-sm rounded-xl">All Reports</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Overall SLA Compliance Rate</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $complianceRate ?? '98.5' }}%</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Tickets Met SLA</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $metSlaCount ?? $metCount ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">SLA Breaches</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $breachedCount ?? 0 }}</p>
        </div>
    </div>

    <div class="glass-card p-6">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">SLA Policy Monitoring</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Continuous monitoring of first-response SLAs and resolution commitments across all priority tiers.</p>
    </div>
</div>
@endsection
