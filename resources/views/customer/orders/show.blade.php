@extends('layouts.app')
@section('page-title', 'Order ' . $order->order_number)

@section('content')
<div class="space-y-6" x-data="{ showPayModal: false, payAmount: '{{ (float)$order->amount_due }}' }">
    {{-- Breadcrumb & Actions --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('portal.orders.index') }}" class="hover:text-primary-600">My Orders</a>
            <span>/</span>
            <span class="font-mono text-gray-900 dark:text-white font-medium">{{ $order->order_number }}</span>
        </div>
        <div class="flex items-center gap-3">
            @if((float)$order->amount_due > 0 && $order->price_locked)
            <button @click="showPayModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                <span>💳</span>
                <span>Pay Balance ({{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }})</span>
            </button>
            @endif
        </div>
    </div>

    {{-- Final Payment Alert if technical work completed with outstanding balance --}}
    @if($order->status === 'awaiting_final_payment')
    <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="text-2xl">🎉</span>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white">Technical Work Completed! Final Payment Required</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">
                    Our team has completed all technical deliverables for this task. Please settle the remaining balance of <strong class="text-amber-600 dark:text-amber-400">{{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}</strong> to complete and close your order.
                </p>
            </div>
        </div>
        <button @click="showPayModal = true" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl whitespace-nowrap shadow-sm">
            Pay Remaining {{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}
        </button>
    </div>
    @endif

    {{-- Status Banner & Financial Tracker --}}
    <div class="grid lg:grid-cols-4 gap-4">
        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Order Status</span>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
            </div>
            <span class="text-xs text-gray-500">Source: {{ $order->order_source_label ?: ucfirst($order->source) }}</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Agreed Price</span>
            <div class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
            </div>
            <span class="text-xs {{ $order->price_locked ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $order->price_locked ? '🔒 Price Locked & Agreed' : '💬 Under Discussion' }}
            </span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Paid to Date</span>
            <div class="mt-1 text-lg font-bold text-emerald-600">
                {{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}
            </div>
            <span class="text-xs text-gray-500">{{ $order->receipts->count() }} payment receipt(s)</span>
        </div>

        <div class="glass-card p-4">
            <span class="text-xs font-semibold uppercase text-gray-400">Outstanding Due</span>
            <div class="mt-1 text-lg font-bold {{ (float)$order->amount_due > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                {{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}
            </div>
            <span class="text-xs text-gray-500">Auth: {{ ucfirst(str_replace('_', ' ', $order->payment_authorization)) }}</span>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left 2 cols: Service Requirements & Price Negotiation --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Service Overview --}}
            <div class="glass-card p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-semibold uppercase text-primary-600">{{ $order->service->category?->name ?? 'IT Service' }}</span>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $order->service->name }}</h2>
                    </div>
                    <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded-lg">
                        {{ $order->order_number }}
                    </span>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <h3 class="text-xs font-semibold uppercase text-gray-500">Requirements & Scope</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-line">{{ $order->requirements }}</p>
                </div>

                @if($order->customer_notes)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <h3 class="text-xs font-semibold uppercase text-gray-500">Customer Notes</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $order->customer_notes }}</p>
                </div>
                @endif
            </div>

            {{-- Price Negotiation History --}}
            <div class="glass-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Price Negotiation & Agreement History</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Every proposal, offer, counter-offer, and discount is permanently recorded.</p>
                    </div>
                    @if($order->price_locked)
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1 rounded-full">
                            🔒 Final Price Locked
                        </span>
                    @endif
                </div>

                <div class="mt-6 space-y-4">
                    @foreach($order->priceRevisions as $rev)
                    <div class="p-4 rounded-xl border {{ $rev->status === 'accepted' ? 'border-emerald-300 bg-emerald-50/50 dark:border-emerald-800 dark:bg-emerald-950/20' : 'border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/40' }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                    {{ ucfirst(str_replace('_', ' ', $rev->kind)) }}
                                </span>
                                <div class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">
                                    {{ $order->currency }} {{ number_format((float)$rev->amount, 2) }}
                                    @if((float)$rev->discount_amount > 0)
                                    <span class="text-xs text-green-600 font-normal ml-1">(Discount: {{ $order->currency }} {{ number_format((float)$rev->discount_amount, 2) }})</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Proposed by {{ $rev->proposer?->name ?? 'System' }} on {{ $rev->created_at->format('M d, Y H:i') }}
                                </div>
                            </div>
                            <div class="text-right">
                                @if($rev->status === 'accepted')
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full">✓ Accepted</span>
                                @elseif($rev->status === 'countered')
                                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Countered</span>
                                @elseif($rev->status === 'proposed' && !$order->price_locked)
                                    <form action="{{ route('portal.orders.accept-price', $order->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="revision_id" value="{{ $rev->id }}">
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                                            Accept This Offer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        @if($rev->terms)
                        <div class="mt-2 text-xs text-gray-600 dark:text-gray-300 italic">"{{ $rev->terms }}"</div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Counter-Offer Form if not locked --}}
                @if(!$order->price_locked)
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Submit Counter-Offer or Proposal</h4>
                    <form action="{{ route('portal.orders.negotiate', $order->id) }}" method="POST" class="mt-4 grid md:grid-cols-3 gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Your Offer ({{ $order->currency }})</label>
                            <input type="number" step="0.01" min="1" name="amount" required placeholder="e.g. 450.00"
                                   class="mt-1 block w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:ring-primary-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Notes / Scope Adjustment</label>
                            <div class="flex gap-2 mt-1">
                                <input type="text" name="terms" placeholder="e.g. Excluding weekend deployment..."
                                       class="block w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:ring-primary-500">
                                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-xl whitespace-nowrap">
                                    Send Counter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- Right col: Financials, Invoices, Receipts, Tickets --}}
        <div class="space-y-6">
            {{-- Invoices & Balance Box --}}
            <div class="glass-card p-6">
                <h3 class="font-bold text-gray-900 dark:text-white">Financial Records</h3>

                @forelse($order->invoices as $inv)
                <div class="mt-4 p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-primary-600">{{ $inv->invoice_number }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                        </span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Total:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $order->currency }} {{ number_format((float)$inv->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Paid:</span>
                        <span class="font-bold text-emerald-600">{{ $order->currency }} {{ number_format((float)$inv->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Outstanding:</span>
                        <span class="font-bold text-amber-600">{{ $order->currency }} {{ number_format((float)$inv->amount_due, 2) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 mt-2">Invoices are automatically issued once the final price agreement is locked.</p>
                @endforelse

                @if((float)$order->amount_due > 0 && $order->price_locked)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button @click="showPayModal = true" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm">
                        💳 Pay Now (Full or Deposit)
                    </button>
                    <span class="block text-center text-xs text-gray-400 mt-1">Min deposit to start: {{ $order->currency }} {{ number_format($minDeposit, 2) }}</span>
                </div>
                @endif
            </div>

            {{-- Receipts List --}}
            <div class="glass-card p-6">
                <h3 class="font-bold text-gray-900 dark:text-white">Payment Receipts</h3>
                <div class="mt-3 space-y-2">
                    @forelse($order->receipts as $rcp)
                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <div>
                            <span class="font-mono text-xs font-bold text-gray-800 dark:text-gray-200">{{ $rcp->receipt_number }}</span>
                            <div class="text-xs text-gray-400">{{ $rcp->issued_at->format('M d, Y') }}</div>
                        </div>
                        <div class="text-right flex items-center gap-2">
                            <span class="text-xs font-bold text-emerald-600">{{ $order->currency }} {{ number_format((float)$rcp->amount, 2) }}</span>
                            <a href="{{ route('portal.orders.receipts.show', ['orderId' => $order->id, 'receiptId' => $rcp->id]) }}" target="_blank" class="text-xs text-primary-600 hover:underline">
                                View
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No payment receipts issued yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Linked IT Ticket & Delivery Task --}}
            <div class="glass-card p-6">
                <h3 class="font-bold text-gray-900 dark:text-white">IT Ticket & Task</h3>
                <div class="mt-3 space-y-3">
                    @forelse($order->tickets as $tkt)
                    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-mono font-bold text-primary-600">{{ $tkt->ticket_number }}</span>
                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $tkt->status === 'closed' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($tkt->status) }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate">{{ $tkt->subject }}</div>
                        <div class="mt-2 text-right">
                            <a href="{{ route('portal.tickets.show', $tkt->id) }}" class="text-xs text-primary-600 hover:underline font-semibold">
                                Open Ticket Thread →
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">An official IT Ticket will be automatically generated upon order confirmation.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="showPayModal = false" class="bg-white dark:bg-gray-900 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Make Payment</h3>
                <button @click="showPayModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('portal.orders.pay', $order->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Order Amount:</span>
                        <span class="font-bold">{{ $order->currency }} {{ number_format((float)$order->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Already Paid:</span>
                        <span class="text-emerald-600 font-bold">{{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Remaining Balance:</span>
                        <span class="text-amber-600 font-bold">{{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-gray-500">Minimum Deposit to Start Work:</span>
                        <span class="font-semibold">{{ $order->currency }} {{ number_format($minDeposit, 2) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Payment Amount ({{ $order->currency }})</label>
                    <input type="number" step="0.01" min="1" max="{{ (float)$order->amount_due }}" name="amount" x-model="payAmount" required
                           class="mt-1 block w-full px-3 py-2 text-base font-bold text-gray-900 dark:text-white border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:ring-primary-500">
                    <div class="flex gap-2 mt-2">
                        <button type="button" @click="payAmount = '{{ $minDeposit }}'" class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200">
                            Min Deposit ({{ $order->currency }} {{ number_format($minDeposit, 2) }})
                        </button>
                        <button type="button" @click="payAmount = '{{ (float)$order->amount_due }}'" class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200">
                            Full Balance ({{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }})
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Payment Method</label>
                    <select name="payment_method" class="mt-1 block w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                        <option value="credit_card">Credit / Debit Card</option>
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="showPayModal = false" class="px-4 py-2 border rounded-xl text-xs font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm">
                        Confirm & Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
