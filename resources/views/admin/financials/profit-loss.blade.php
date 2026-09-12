@extends('layouts.app')
@section('page-title', 'Profit & Loss Statement')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Profit & Loss Analysis</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Financial summary of operational revenues, operating expenditures, and net margin.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.financials.index') }}" class="btn-secondary px-4 py-2 text-sm rounded-xl">Financials Overview</a>
            <a href="{{ route('admin.financials.transactions') }}" class="btn-secondary px-4 py-2 text-sm rounded-xl">Transactions</a>
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Income</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($totalIncome ?? $income ?? 0, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">${{ number_format($totalExpenses ?? $expenses ?? 0, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Net Profit / Margin</p>
            @php $net = ($totalIncome ?? $income ?? 0) - ($totalExpenses ?? $expenses ?? 0); @endphp
            <p class="text-2xl font-bold {{ $net >= 0 ? 'text-primary-600 dark:text-primary-400' : 'text-red-600' }} mt-1">
                ${{ number_format($net, 2) }}
            </p>
        </div>
    </div>

    {{-- Profit Loss Breakdown --}}
    <div class="glass-card p-6">
        <h3 class="font-bold text-gray-900 dark:text-white mb-4">Financial Statement Breakdown</h3>
        <div class="space-y-4">
            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-800">
                <span class="font-medium text-gray-700 dark:text-gray-300">Gross Invoiced Revenue</span>
                <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($totalIncome ?? $income ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-800">
                <span class="font-medium text-gray-700 dark:text-gray-300">Operating Expenses & Overheads</span>
                <span class="font-semibold text-red-600 dark:text-red-400">-${{ number_format($totalExpenses ?? $expenses ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between py-3 font-bold text-lg border-t-2 border-gray-200 dark:border-gray-700 pt-4">
                <span class="text-gray-900 dark:text-white">Net Operating Profit</span>
                <span class="{{ $net >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">${{ number_format($net, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
