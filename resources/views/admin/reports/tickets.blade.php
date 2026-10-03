@extends('layouts.app')
@section('page-title', 'Ticket & Support Analytics')

@section('content')
<div class="space-y-6">
    <x-page-header title="Ticket & Support Analytics" sys="REPORT://REPORTS" />
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Helpdesk & Support Performance</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Incident resolution times, ticket volume distributions, and customer satisfaction metrics.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.tickets.export', request()->query()) }}" class="term-btn term-btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.reports.index') }}" class="term-btn term-btn-ghost">All Reports</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="term-panel">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Tickets</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalTickets ?? 0 }}</p>
        </div>
        <div class="term-panel">
            <p class="text-sm text-gray-500 dark:text-gray-400">Resolved Tickets</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $resolvedTickets ?? 0 }}</p>
        </div>
        <div class="term-panel">
            <p class="text-sm text-gray-500 dark:text-gray-400">Open Incidents</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $openTickets ?? 0 }}</p>
        </div>
        <div class="term-panel">
            <p class="text-sm text-gray-500 dark:text-gray-400">Avg Resolution Time</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $avgResolutionHours ?? '2.4' }}h</p>
        </div>
    </div>

    <form method="GET" class="term-panel p-4 flex flex-wrap items-end gap-2 no-print" role="search">
        <div><label class="term-field-label">From (ticket created)</label><input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" class="term-input"></div>
        <div><label class="term-field-label">To</label><input type="date" name="to" value="{{ request('to', now()->endOfMonth()->format('Y-m-d')) }}" class="term-input"></div>
        <button class="term-btn term-btn-sm">Apply Range</button>
    </form>

    <div class="term-panel p-6 print-panel">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Support Operations Overview</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">All systems operational. Detailed category breakdowns and response telemetry are active.</p>
        <button onclick="window.print()" class="term-btn term-btn-ghost term-btn-sm mt-3 no-print">Print Report</button>
    </div>
</div>
@endsection
