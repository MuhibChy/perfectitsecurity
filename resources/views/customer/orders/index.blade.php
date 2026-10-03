@extends('layouts.app')
@section('page-title', 'My Service Orders')

@section('content')
<div class="space-y-6">
    <x-page-header title="My Service Orders" subtitle="Track your service requests, price agreements, payments, invoices, and IT task delivery." sys="ORDER://ORDERS" num="03">
        <x-slot:actions>
            <a href="{{ route('portal.services.index') }}" class="term-btn term-btn-sm">
                <span>Browse Services</span>
            </a>
            <a href="{{ route('portal.reports.mine', ['type' => 'customer', 'format' => 'pdf']) }}" class="term-btn term-btn-ghost term-btn-sm">Generate Report</a>
        </x-slot:actions>
    </x-page-header>

    @if(!$orders->isEmpty())
    <div class="term-panel overflow-hidden">
        <div class="term-table-wrap !border-0">
            <table class="data-table term-table term-table-cards w-full text-left">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Ticket</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td data-label="Order" class="font-mono font-medium">
                            <a href="{{ route('portal.orders.show', $order->id) }}" class="text-accent-soft hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td data-label="Service">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $order->service->name }}</div>
                            <div class="font-mono text-[11px] text-slate-600 dark:text-term-800">{{ $order->created_at->format('M d, Y') }}</div>
                        </td>
                        <td data-label="Status">
                            <x-status-badge :status="$order->status" />
                        </td>
                        <td data-label="Price" class="font-medium tabular-nums">
                            {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                        </td>
                        <td data-label="Paid" class="tabular-nums">
                            <span class="fin-tag fin-tag-income">{{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}</span>
                        </td>
                        <td data-label="Balance" class="font-medium tabular-nums">
                            <span class="{{ (float)$order->amount_due > 0 ? 'fin-tag fin-tag-due' : '' }}">{{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}</span>
                        </td>
                        <td data-label="Ticket">
                            @if($ticket = $order->tickets->first())
                                <a href="{{ route('portal.tickets.show', $ticket->id) }}" class="text-xs font-mono text-accent-soft hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            @else
                                <span class="font-mono text-[11px] text-slate-600 dark:text-term-800">—</span>
                            @endif
                        </td>
                        <td data-label="Action" class="text-right">
                            <a href="{{ route('portal.orders.show', $order->id) }}" class="term-btn term-btn-ghost term-btn-sm">
                                Manage →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-white/10">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
    @else
    <x-empty-state-3d type="orders" title="No Service Orders Placed Yet"
        message="Explore our catalogue of expert IT services, consult with our team, or request custom pricing for your technical needs."
        actionText="Browse Services Catalogue" :actionUrl="route('portal.services.index')" />
    @endif
</div>
@endsection
