@extends('layouts.app')
@section('page-title', 'Financial Transactions')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Transaction Ledger</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Complete audit log of all income payments, service expenses, and payouts.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.financials.index') }}" class="btn-secondary px-4 py-2 text-sm rounded-xl">Overview</a>
            <a href="{{ route('admin.financials.profit-loss') }}" class="btn-secondary px-4 py-2 text-sm rounded-xl">Profit & Loss</a>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Type</th>
                        <th>Category / Description</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td class="font-mono text-xs font-semibold text-gray-700 dark:text-gray-300">#{{ $tx->id }}</td>
                        <td>
                            @if(($tx->type ?? 'income') === 'income')
                                <span class="badge bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Income</span>
                            @else
                                <span class="badge bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400">Expense</span>
                            @endif
                        </td>
                        <td class="text-sm font-medium text-gray-900 dark:text-white">{{ $tx->description ?? $tx->category ?? 'General Transaction' }}</td>
                        <td class="font-bold text-sm {{ ($tx->type ?? 'income') === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ ($tx->type ?? 'income') === 'income' ? '+' : '-' }}${{ number_format(abs($tx->amount ?? 0), 2) }}
                        </td>
                        <td class="text-xs text-gray-500 font-mono">{{ $tx->reference_number ?? ($tx->invoice_id ? 'INV-'.$tx->invoice_id : '-') }}</td>
                        <td class="text-xs text-gray-500">{{ $tx->transaction_date ? $tx->transaction_date->format('M d, Y') : ($tx->created_at ? $tx->created_at->format('M d, Y') : '-') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">No transactions recorded in the ledger yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($transactions, 'links'))
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
