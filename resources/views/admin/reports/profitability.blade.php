@extends('layouts.app')
@section('page-title', 'Profitability Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Service & Project Profitability Analytics</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Traceability: Order Value → Collected Revenue → Internal Expenditures (Labour, Freelancers, Vendors) → Net Profit.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn-secondary px-4 py-2 text-sm rounded-xl">All Reports</a>
    </div>

    {{-- Service Orders Profitability Table --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-900 dark:text-white">Work Order Profitability (Collected Revenue vs Direct Costs)</h2>
            <span class="text-xs text-gray-400">{{ count($orderProfitability ?? []) }} total orders</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 text-gray-500 font-semibold uppercase">
                        <th class="py-2.5 px-3">Order #</th>
                        <th class="py-2.5 px-3">Customer</th>
                        <th class="py-2.5 px-3">Service</th>
                        <th class="py-2.5 px-3">Order Value</th>
                        <th class="py-2.5 px-3">Collected Revenue</th>
                        <th class="py-2.5 px-3">Direct Costs</th>
                        <th class="py-2.5 px-3">Net Profit</th>
                        <th class="py-2.5 px-3 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($orderProfitability ?? [] as $item)
                    <tr>
                        <td class="py-2.5 px-3 font-mono font-bold text-primary-600">
                            <a href="{{ route('admin.work-orders.show', $item['order']->id) }}" class="hover:underline">
                                {{ $item['order']->order_number }}
                            </a>
                        </td>
                        <td class="py-2.5 px-3 text-gray-800 dark:text-gray-200">{{ $item['order']->customer->name }}</td>
                        <td class="py-2.5 px-3 text-gray-700 dark:text-gray-300 truncate max-w-[140px]">{{ $item['order']->service->name }}</td>
                        <td class="py-2.5 px-3 font-medium">${{ number_format($item['order_value'], 2) }}</td>
                        <td class="py-2.5 px-3 font-semibold text-emerald-600">${{ number_format($item['revenue'], 2) }}</td>
                        <td class="py-2.5 px-3 text-gray-500">${{ number_format($item['costs'], 2) }}</td>
                        <td class="py-2.5 px-3 font-bold {{ $item['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            ${{ number_format($item['profit'], 2) }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-semibold {{ $item['margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $item['margin'] }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-gray-400">No service orders recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Profitability By Service Category --}}
    @if(!empty($byService) && count($byService) > 0)
    <div class="glass-card p-6">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Profitability By Service Catalogue Item</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($byService as $svc)
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-2">
                <div class="flex items-start justify-between">
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $svc['service_name'] }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 font-semibold">{{ $svc['order_count'] }} orders</span>
                </div>
                <div class="text-xs text-gray-400">Category: {{ $svc['category_name'] }}</div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200 dark:border-gray-800 text-xs">
                    <div>Revenue: <strong class="text-emerald-600">${{ number_format($svc['revenue'], 2) }}</strong></div>
                    <div>Costs: <strong class="text-gray-500">${{ number_format($svc['costs'], 2) }}</strong></div>
                    <div class="col-span-2 flex justify-between pt-1 border-t border-gray-100 dark:border-gray-800 font-bold">
                        <span>Net Profit:</span>
                        <span class="{{ $svc['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">${{ number_format($svc['profit'], 2) }} ({{ $svc['margin'] }}%)</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
