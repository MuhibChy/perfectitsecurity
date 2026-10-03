@extends('layouts.app')
@section('page-title', 'Work Orders & Service Financial Traceability')

@section('content')
<div class="space-y-6">
    {{-- Header & Quick Create --}}
    <x-page-header title="Service Work Orders" subtitle="End-to-end traceability: Customer → Service → Agreed Price → Invoice → Payment → Receipt → Ticket → Task → Expenditure → Revenue → Profit" sys="OPS://WORK-ORDERS">
        <x-slot:actions>
            <a href="{{ route('admin.work-orders.create') }}" class="term-btn term-btn-sm">
                
                <span>Create Manual Work Order</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Executive Financial & Operations Metrics --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <x-stat-card title="Total Work Orders" :value="$totalOrders" subtitle="ALL CHANNELS" color="blue" />
        <x-stat-card title="Collected Revenue" :value="'$' . number_format($totalRevenue, 2)" subtitle="VERIFIED RECEIPTS" color="emerald" />
        <x-stat-card title="Outstanding Due" :value="'$' . number_format($totalOutstanding, 2)" subtitle="UNCOLLECTED BALANCES" color="amber" />
        <x-stat-card title="Ready To Start" :value="$readyToStart" subtitle="DEPOSIT / AUTH VERIFIED" color="cyan" />
        <x-stat-card title="Awaiting Payment" :value="$awaitingPayment" subtitle="INITIAL OR FINAL BALANCE" color="purple" />
    </div>

    {{-- Filters & Search --}}
    <div class="term-panel p-4">
        <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search order #, customer, service..."
                       class="term-input">
            </div>

            <div>
                <select name="source" class="term-input">
                    <option value="">-- All Sources --</option>
                    <option value="employee_manual" {{ request('source') === 'employee_manual' ? 'selected' : '' }}>Employee Manual</option>
                    <option value="customer_portal" {{ request('source') === 'customer_portal' ? 'selected' : '' }}>Customer Portal</option>
                </select>
            </div>

            <div>
                <select name="status" class="term-input">
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
                <select name="payment_auth" class="term-input">
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
                <button type="submit" class="term-btn term-btn-sm flex-1">Filter</button>
                <a href="{{ route('admin.work-orders.index') }}" class="term-btn term-btn-sm term-btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    {{-- Work Orders Table --}}
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table w-full text-left border-collapse text-xs term-table">
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
                        <td class="py-3 px-4 font-mono font-bold text-primary-600" data-label="Order #">
                            <a href="{{ route('admin.work-orders.show', $order->id) }}" class="hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4" data-label="Customer">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $order->customer->name }}</div>
                            <div class="text-[10px] flex items-center gap-1 mt-0.5">
                                @if($order->customer->isFullyVerified())
                                    <span class="text-emerald-600 font-semibold">✓ Verified</span>
                                @else
                                    <span class="text-amber-600 font-semibold">Pending Auth</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4" data-label="Service">
                            <div class="font-medium text-gray-900 dark:text-white truncate max-w-[150px]">{{ $order->service->name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $order->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="py-3 px-4" data-label="Source">
                            <span class="term-tag">
                                {{ $order->order_source_label ?: ucfirst(str_replace('_', ' ', $order->source)) }}
                            </span>
                        </td>
                        <td class="py-3 px-4" data-label="Status">
                            <x-status-badge :status="$order->status" />
                        </td>
                        <td class="py-3 px-4" data-label="Payment Auth">
                            <x-status-badge :status="match($order->payment_authorization) { 'not_authorized' => 'blocked', 'deposit_required' => 'pending', 'deposit_received' => 'partial', 'ready_to_start' => 'open', 'fully_paid' => 'paid', 'manager_override' => 'review', default => 'draft' }" :label="ucfirst(str_replace('_', ' ', $order->payment_authorization))" />
                        </td>
                        <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white" data-label="Total">
                            {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400" data-label="Paid">
                            {{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}
                        </td>
                        <td class="py-3 px-4 font-semibold {{ (float)$order->amount_due > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}" data-label="Due">
                            {{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}
                        </td>
                        <td class="py-3 px-4 text-gray-500 dark:text-gray-400" data-label="Actual Cost">
                            {{ $order->currency }} {{ number_format((float)$order->total_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 font-bold {{ $order->actual_profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}" data-label="Profit">
                            {{ $order->currency }} {{ number_format((float)$order->actual_profit, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right" data-label="Action">
                            <a href="{{ route('admin.work-orders.show', $order->id) }}" class="term-btn term-btn-sm term-btn-ghost">
                                View →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-8 text-center text-gray-500 dark:text-gray-400">No work orders matching criteria.</td>
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
