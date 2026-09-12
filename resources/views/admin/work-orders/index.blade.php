@extends('layouts.app')
@section('page-title', 'Work Orders & Service Financial Traceability')

@section('content')
<div class="space-y-6">
    {{-- Header & Quick Create --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Service Work Orders</h1>
            <p class="text-sm text-gray-500 mt-1">End-to-end traceability: Customer → Service → Agreed Price → Invoice → Payment → Receipt → Ticket → Task → Expenditure → Revenue → Profit</p>
        </div>
        <div>
            <a href="{{ route('admin.work-orders.create') }}" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                <span>➕</span>
                <span>Create Manual Work Order</span>
            </a>
        </div>
    </div>

    {{-- Executive Financial & Operations Metrics --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Total Work Orders</span>
            <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $totalOrders }}</div>
            <span class="text-xs text-gray-500">All channels</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Collected Revenue</span>
            <div class="mt-1 text-2xl font-bold text-emerald-600">${{ number_format($totalRevenue, 2) }}</div>
            <span class="text-xs text-emerald-600">Verified receipts</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Outstanding Due</span>
            <div class="mt-1 text-2xl font-bold text-amber-600">${{ number_format($totalOutstanding, 2) }}</div>
            <span class="text-xs text-amber-600">Uncollected balances</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Ready To Start</span>
            <div class="mt-1 text-2xl font-bold text-teal-600">{{ $readyToStart }}</div>
            <span class="text-xs text-teal-600">Deposit / auth verified</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Awaiting Payment</span>
            <div class="mt-1 text-2xl font-bold text-blue-600">{{ $awaitingPayment }}</div>
            <span class="text-xs text-blue-600">Initial or final balance</span>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="glass-card p-4">
        <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order #, customer, service..."
                       class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
            </div>

            <div>
                <select name="source" class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                    <option value="">-- All Sources --</option>
                    <option value="employee_manual" {{ request('source') === 'employee_manual' ? 'selected' : '' }}>Employee Manual</option>
                    <option value="customer_portal" {{ request('source') === 'customer_portal' ? 'selected' : '' }}>Customer Portal</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                    <option value="">-- All Statuses --</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="negotiating" {{ request('status') === 'negotiating' ? 'selected' : '' }}>Negotiating</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="ready_to_start" {{ request('status') === 'ready_to_start' ? 'selected' : '' }}>Ready to Start</option>
                    <option value="awaiting_final_payment" {{ request('status') === 'awaiting_final_payment' ? 'selected' : '' }}>Awaiting Final Payment</option>
                    <option value="financially_completed" {{ request('status') === 'financially_completed' ? 'selected' : '' }}>Financially Completed</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div>
                <select name="payment_auth" class="w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                    <option value="">-- Payment Authorization --</option>
                    <option value="not_authorized" {{ request('payment_auth') === 'not_authorized' ? 'selected' : '' }}>Not Authorized</option>
                    <option value="deposit_required" {{ request('payment_auth') === 'deposit_required' ? 'selected' : '' }}>Deposit Required</option>
                    <option value="deposit_received" {{ request('payment_auth') === 'deposit_received' ? 'selected' : '' }}>Deposit Received</option>
                    <option value="ready_to_start" {{ request('payment_auth') === 'ready_to_start' ? 'selected' : '' }}>Ready to Start</option>
                    <option value="fully_paid" {{ request('payment_auth') === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="manager_override" {{ request('payment_auth') === 'manager_override' ? 'selected' : '' }}>Manager Override</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl">Filter</button>
                <a href="{{ route('admin.work-orders.index') }}" class="px-3 py-2 border rounded-xl text-xs text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">Reset</a>
            </div>
        </form>
    </div>

    {{-- Work Orders Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 font-semibold uppercase text-gray-500">
                        <th class="py-3 px-4">Order #</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Service</th>
                        <th class="py-3 px-4">Source</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Payment Auth</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Paid</th>
                        <th class="py-3 px-4">Due</th>
                        <th class="py-3 px-4">Actual Cost</th>
                        <th class="py-3 px-4">Profit</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-primary-600">
                            <a href="{{ route('admin.work-orders.show', $order->id) }}" class="hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $order->customer->name }}</div>
                            <div class="text-[10px] flex items-center gap-1 mt-0.5">
                                @if($order->customer->isFullyVerified())
                                    <span class="text-emerald-600 font-semibold">✓ Verified</span>
                                @else
                                    <span class="text-amber-600 font-semibold">⚠️ Pending Auth</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-900 dark:text-white truncate max-w-[150px]">{{ $order->service->name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $order->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $order->source === 'employee_manual' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                {{ $order->order_source_label ?: ucfirst(str_replace('_', ' ', $order->source)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-full font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $authStyles = [
                                    'not_authorized' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                    'deposit_required' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    'deposit_received' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
                                    'ready_to_start' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    'fully_paid' => 'bg-emerald-100 text-emerald-800 font-bold',
                                    'manager_override' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $authStyles[$order->payment_authorization] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $order->payment_authorization)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                            {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-emerald-600">
                            {{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}
                        </td>
                        <td class="py-3 px-4 font-semibold {{ (float)$order->amount_due > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                            {{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}
                        </td>
                        <td class="py-3 px-4 text-gray-500">
                            {{ $order->currency }} {{ number_format((float)$order->total_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 font-bold {{ $order->actual_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $order->currency }} {{ number_format((float)$order->actual_profit, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.work-orders.show', $order->id) }}" class="px-2.5 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-600 hover:bg-primary-100 rounded font-semibold">
                                View →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-8 text-center text-gray-400">No work orders matching criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-800">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
