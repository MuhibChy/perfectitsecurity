@extends('layouts.app')
@section('page-title', 'My Service Orders')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Service Orders</h1>
            <p class="text-sm text-gray-500 mt-1">Track your service requests, price agreements, payments, invoices, and IT task delivery.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('portal.services.index') }}" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                <span>🛒</span>
                <span>Browse Services</span>
            </a>
        </div>
    </div>

    @if(!$orders->isEmpty())
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 text-xs font-semibold uppercase text-gray-500">
                        <th class="py-3.5 px-4">Order #</th>
                        <th class="py-3.5 px-4">Service</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Price</th>
                        <th class="py-3.5 px-4">Paid</th>
                        <th class="py-3.5 px-4">Balance</th>
                        <th class="py-3.5 px-4">IT Ticket</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($orders as $order)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-medium text-primary-600">
                            <a href="{{ route('portal.orders.show', $order->id) }}" class="hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $order->service->name }}</div>
                            <div class="text-xs text-gray-400">{{ $order->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $statusStyles = [
                                    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    'negotiating' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                    'confirmed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'ready_to_start' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
                                    'awaiting_final_payment' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                    'financially_completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    'closed' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusStyles[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-gray-900 dark:text-white">
                            {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-emerald-600 font-medium">
                            {{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}
                        </td>
                        <td class="py-3.5 px-4 font-medium {{ (float)$order->amount_due > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                            {{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($ticket = $order->tickets->first())
                                <a href="{{ route('portal.tickets.show', $ticket->id) }}" class="text-xs font-mono text-primary-600 hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('portal.orders.show', $order->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 dark:bg-primary-900/20 px-3 py-1.5 rounded-lg">
                                Manage →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-200 dark:border-gray-800">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="glass-card p-12 text-center space-y-4">
        <div class="w-16 h-16 bg-primary-50 dark:bg-primary-900/20 text-primary-600 rounded-full flex items-center justify-center mx-auto text-2xl">🛒</div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">No Service Orders Placed Yet</h2>
        <p class="text-sm text-gray-500 max-w-md mx-auto">Explore our catalogue of expert IT services, consult with our team, or request custom pricing for your technical needs.</p>
        <div class="pt-2">
            <a href="{{ route('portal.services.index') }}" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl inline-block">
                Browse Services Catalogue
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
