@extends('layouts.app')
@section('page-title', 'Financial Reports')

@section('content')
<div class="space-y-6">
    <x-page-header title="Financial Reports" sys="REPORT://REPORTS" />
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Financial & Revenue Analytics</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Monthly revenue trends, expense allocations, and collection efficiency.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.financial.export', request()->query()) }}" class="term-btn term-btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.reports.index') }}" class="term-btn term-btn-ghost">All Reports</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="term-panel">
            <p class="text-sm text-gray-500 dark:text-gray-400">Revenue (YTD)</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($totalRevenue ?? $revenue ?? 0, 2) }}</p>
        </div>
        <div class="term-panel">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses (YTD)</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">${{ number_format($totalExpenses ?? $expenses ?? 0, 2) }}</p>
        </div>
        <div class="term-panel">
            <p class="text-sm text-gray-500 dark:text-gray-400">Paid Invoices</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $paidInvoicesCount ?? $paidCount ?? 0 }}</p>
        </div>
    </div>

    <form method="GET" class="term-panel p-4 flex flex-wrap items-end gap-2 no-print" role="search">
        <div><label class="term-field-label">From</label><input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" class="term-input"></div>
        <div><label class="term-field-label">To</label><input type="date" name="to" value="{{ request('to', now()->endOfMonth()->format('Y-m-d')) }}" class="term-input"></div>
        <button class="term-btn term-btn-sm">Apply Range</button>
        <span class="term-hint">Figures use the transaction date (created_at).</span>
    </form>

    <div class="term-panel p-6 print-panel">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Financial Summary</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Comprehensive annual and monthly ledger metrics for executive review.</p>
        <button onclick="window.print()" class="term-btn term-btn-ghost term-btn-sm mt-3 no-print">Print Report</button>
    </div>
</div>
@endsection
