@extends('layouts.app')
@section('page-title', 'Order ' . $order->order_number)

@section('content')
<div class="space-y-6" x-data="{ showPayModal: false, payAmount: '{{ (float)$order->amount_due }}' }">
    <x-page-header :title="'Order ' . $order->order_number" :subtitle="'Source: ' . ($order->order_source_label ?: ucfirst($order->source))" sys="ORDER://ORDERS">
        <x-slot:actions>
            <a href="{{ route('portal.orders.index') }}" class="term-btn term-btn-ghost term-btn-sm">My Orders</a>
            <a href="{{ route('portal.reports.service', $order->id) }}" class="term-btn term-btn-ghost term-btn-sm">Service Report</a>
            @if((float)$order->amount_due > 0 && $order->price_locked)
            <button @click="showPayModal = true" class="term-btn term-btn-sm">
                <span>Pay Balance ({{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }})</span>
            </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Final Payment Alert if technical work completed with outstanding balance --}}
    @if($order->status === 'awaiting_final_payment')
    <div class="term-alert term-alert-warn flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="term-alert-tag">FINALIZE</span>
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white">Technical Work Completed! Final Payment Required</h3>
                <p class="text-sm text-slate-600 dark:text-term-800 mt-0.5">
                    Our team has completed all technical deliverables for this task. Please settle the remaining balance of <strong>{{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}</strong> to complete and close your order.
                </p>
            </div>
        </div>
        <button @click="showPayModal = true" class="term-btn term-btn-sm whitespace-nowrap">
            Pay Remaining {{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}
        </button>
    </div>
    @endif

    {{-- Status Banner & Financial Tracker --}}
    <div class="grid lg:grid-cols-4 gap-4">
        <div class="term-panel-2 p-4">
            <span class="term-field-label">Order Status</span>
            <div class="mt-1 flex items-center gap-2">
                <x-status-badge :status="$order->status" />
            </div>
            <span class="term-hint">Source: {{ $order->order_source_label ?: ucfirst($order->source) }}</span>
        </div>

        <div class="term-panel-2 p-4">
            <span class="term-field-label">Agreed Price</span>
            <div class="mt-1 text-lg font-bold text-slate-900 dark:text-white tabular-nums">
                {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
            </div>
            <span class="term-hint">
                {{ $order->price_locked ? 'Price Locked & Agreed' : 'Under Discussion' }}
            </span>
        </div>

        <div class="term-panel-2 p-4">
            <span class="term-field-label">Paid to Date</span>
            <div class="mt-1 text-lg font-bold tabular-nums">
                <span class="fin-tag fin-tag-income">{{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}</span>
            </div>
            <span class="term-hint">{{ $order->receipts->count() }} payment receipt(s)</span>
        </div>

        <div class="term-panel-2 p-4">
            <span class="term-field-label">Outstanding Due</span>
            <div class="mt-1 text-lg font-bold tabular-nums">
                <span class="{{ (float)$order->amount_due > 0 ? 'fin-tag fin-tag-due' : '' }}">{{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}</span>
            </div>
            <span class="term-hint">Auth: {{ ucfirst(str_replace('_', ' ', $order->payment_authorization)) }}</span>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left 2 cols: Service Requirements & Price Negotiation --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Service Overview --}}
            <div class="term-panel p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="term-tag">{{ $order->service->category?->name ?? 'IT Service' }}</span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-2">{{ $order->service->name }}</h2>
                    </div>
                    <span class="term-tag font-mono">
                        {{ $order->order_number }}
                    </span>
                </div>

                <div class="mt-4 pt-4 border-t border-white/10">
                    <h3 class="term-field-label">Requirements &amp; Scope</h3>
                    <p class="text-sm text-slate-600 dark:text-term-800 mt-1 whitespace-pre-line">{{ $order->requirements }}</p>
                </div>

                @if($order->customer_notes)
                <div class="mt-4 pt-4 border-t border-white/10">
                    <h3 class="term-field-label">Customer Notes</h3>
                    <p class="text-sm text-slate-600 dark:text-term-800 mt-1">{{ $order->customer_notes }}</p>
                </div>
                @endif
            </div>

            {{-- Price Negotiation History --}}
            <div class="term-panel p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Price Negotiation &amp; Agreement History</h3>
                        <p class="term-hint">Every proposal, offer, counter-offer, and discount is permanently recorded.</p>
                    </div>
                    @if($order->price_locked)
                        <span class="term-tag term-tag-accent">
                            Final Price Locked
                        </span>
                    @endif
                </div>

                <div class="mt-6 space-y-4">
                    @foreach($order->priceRevisions as $rev)
                    <div class="term-panel-2 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="font-mono text-[11px] uppercase tracking-[0.18em] text-slate-600 dark:text-term-800">
                                    {{ ucfirst(str_replace('_', ' ', $rev->kind)) }}
                                </span>
                                <div class="text-lg font-bold text-slate-900 dark:text-white mt-0.5 tabular-nums">
                                    {{ $order->currency }} {{ number_format((float)$rev->amount, 2) }}
                                    @if((float)$rev->discount_amount > 0)
                                    <span class="text-xs font-normal ml-1">(Discount: {{ $order->currency }} {{ number_format((float)$rev->discount_amount, 2) }})</span>
                                    @endif
                                </div>
                                <div class="term-hint mt-1">
                                    Proposed by {{ $rev->proposer?->name ?? 'System' }} on {{ $rev->created_at->format('M d, Y H:i') }}
                                </div>
                            </div>
                            <div class="text-right">
                                @if($rev->status === 'accepted')
                                    <x-status-badge status="accepted" />
                                @elseif($rev->status === 'countered')
                                    <span class="term-tag">Countered</span>
                                @elseif(in_array($rev->status, ['proposed', 'pending_approval']) && !$order->price_locked)
                                    <div class="flex gap-2 justify-end">
                                        <form action="{{ route('portal.orders.accept-price', $order->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="revision_id" value="{{ $rev->id }}">
                                            <button type="submit" class="term-btn term-btn-sm">
                                                Accept This Offer
                                            </button>
                                        </form>
                                        <form action="{{ route('portal.orders.reject-price', $order->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="revision_id" value="{{ $rev->id }}">
                                            <input type="hidden" name="reason" value="Declined from order page.">
                                            <button type="submit" class="term-btn term-btn-ghost term-btn-sm">
                                                Decline
                                            </button>
                                        </form>
                                    </div>
                                @elseif($rev->status === 'rejected')
                                    <x-status-badge status="rejected" />
                                @endif
                            </div>
                        </div>
                        @if($rev->terms)
                        <div class="mt-2 text-xs text-slate-600 dark:text-term-800 italic">"{{ $rev->terms }}"</div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Counter-Offer Form if not locked --}}
                @if(!$order->price_locked)
                <div class="mt-6 pt-6 border-t border-white/10">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Submit Counter-Offer or Proposal</h4>
                    <form action="{{ route('portal.orders.negotiate', $order->id) }}" method="POST" class="mt-4 grid md:grid-cols-3 gap-4">
                        @csrf
                        <div>
                            <label class="term-field-label">Your Offer ({{ $order->currency }})</label>
                            <input type="number" step="0.01" min="1" name="amount" required placeholder="e.g. 450.00"
                                   class="term-input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="term-field-label">Notes / Scope Adjustment</label>
                            <div class="flex gap-2">
                                <input type="text" name="terms" placeholder="e.g. Excluding weekend deployment..."
                                       class="term-input">
                                <button type="submit" class="term-btn term-btn-sm whitespace-nowrap">
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
            <div class="term-panel p-6">
                <h3 class="font-bold text-slate-900 dark:text-white">Financial Records</h3>

                @forelse($order->invoices as $inv)
                <div class="mt-4 term-panel-2 p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-accent-soft">{{ $inv->invoice_number }}</span>
                        <x-status-badge :status="$inv->status" />
                    </div>
                    <div class="flex justify-between text-xs text-slate-600 dark:text-term-800">
                        <span>Total:</span>
                        <span class="font-bold text-slate-900 dark:text-white tabular-nums">{{ $order->currency }} {{ number_format((float)$inv->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600 dark:text-term-800">
                        <span>Paid:</span>
                        <span class="fin-tag fin-tag-income">{{ $order->currency }} {{ number_format((float)$inv->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600 dark:text-term-800">
                        <span>Outstanding:</span>
                        <span class="fin-tag fin-tag-due">{{ $order->currency }} {{ number_format((float)$inv->amount_due, 2) }}</span>
                    </div>
                </div>
                @empty
                <p class="term-hint mt-2">Invoices are automatically issued once the final price agreement is locked.</p>
                @endforelse

                @if((float)$order->amount_due > 0 && $order->price_locked)
                <div class="mt-4 pt-4 border-t border-white/10">
                    <button @click="showPayModal = true" class="term-btn term-btn-sm w-full">
                        Pay Now (Full or Deposit)
                    </button>
                    <span class="term-hint block text-center mt-1">Min deposit to start: {{ $order->currency }} {{ number_format($minDeposit, 2) }}</span>
                </div>
                @endif
            </div>

            {{-- Receipts List --}}
            <div class="term-panel p-6">
                <h3 class="font-bold text-slate-900 dark:text-white">Payment Receipts</h3>
                <div class="mt-3 space-y-2">
                    @forelse($order->receipts as $rcp)
                    <div class="flex items-center justify-between term-panel-2 p-3">
                        <div>
                            <span class="font-mono text-xs font-bold text-slate-900 dark:text-white">{{ $rcp->receipt_number }}</span>
                            <div class="term-hint">{{ $rcp->issued_at->format('M d, Y') }}</div>
                        </div>
                        <div class="text-right flex items-center gap-2 flex-wrap justify-end">
                            <span class="text-xs font-bold tabular-nums"><span class="fin-tag fin-tag-income">{{ $order->currency }} {{ number_format((float)$rcp->amount, 2) }}</span></span>
                            <a href="{{ route('portal.orders.receipts.show', ['orderId' => $order->id, 'receiptId' => $rcp->id]) }}" target="_blank" class="term-link !text-[11px]">
                                View
                            </a>
                            <a href="{{ route('portal.receipts.pdf', $rcp) }}" target="_blank" class="term-link !text-[11px]">PDF ↓</a>
                            @if(optional($rcp->payment)->cashMemo)
                            <a href="{{ route('portal.cash-memos.pdf', $rcp->payment->cashMemo) }}" target="_blank" class="term-link !text-[11px]">Cash Memo ↓</a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="term-hint">No payment receipts issued yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Documents & Completion --}}
            <div class="term-panel p-6">
                <h3 class="font-bold text-slate-900 dark:text-white">Documents</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('portal.orders.pdf', $order) }}" target="_blank" class="term-btn term-btn-ghost term-btn-sm">Order PDF ↓</a>
                    @foreach($order->receipts as $rcp)
                    <a href="{{ route('portal.receipts.pdf', $rcp) }}" target="_blank" class="term-btn term-btn-ghost term-btn-sm">Receipt {{ $rcp->receipt_number }} ↓</a>
                    @endforeach
                </div>
                @if(($order->task_completed_at || !$order->tasks()->where('status', '!=', 'completed')->exists()) && !in_array($order->status, ['closed', 'cancelled'], true))
                <form method="POST" action="{{ route('portal.orders.confirm-completion', $order) }}" class="mt-3">
                    @csrf
                    <button class="term-btn term-btn-sm w-full" onclick="return confirm('Confirm that the service work is complete to your satisfaction?')">Confirm Service Completion</button>
                </form>
                @endif
            </div>

            {{-- Linked IT Ticket & Delivery Task --}}
            <div class="term-panel p-6">
                <h3 class="font-bold text-slate-900 dark:text-white">IT Ticket &amp; Task</h3>
                <div class="mt-3 space-y-3">
                    @forelse($order->tickets as $tkt)
                    <div class="term-panel-2 p-3">
                        <div class="flex items-center justify-between text-xs gap-2">
                            <span class="font-mono font-bold text-accent-soft">{{ $tkt->ticket_number }}</span>
                            <x-status-badge :status="$tkt->status" />
                        </div>
                        <div class="text-xs text-slate-600 dark:text-term-800 mt-1 truncate">{{ $tkt->subject }}</div>
                        <div class="mt-2 text-right">
                            <a href="{{ route('portal.tickets.show', $tkt->id) }}" class="term-link !text-[11px]">
                                Open Ticket Thread →
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="term-hint">An official IT Ticket will be automatically generated upon order confirmation.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Modal --}}
    <div x-show="showPayModal" x-cloak @keydown.escape.window="showPayModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="showPayModal = false" class="term-modal max-w-md w-full p-6 space-y-4">
            <div class="term-modal-head !px-0 !pt-0">
                <h3 class="term-modal-title">Make Payment</h3>
                <button @click="showPayModal = false" class="text-slate-600 dark:text-term-800 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('portal.orders.pay', $order->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="term-panel-2 p-3 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-term-800">Total Order Amount:</span>
                        <span class="font-bold tabular-nums">{{ $order->currency }} {{ number_format((float)$order->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-term-800">Already Paid:</span>
                        <span class="fin-tag fin-tag-income">{{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600 dark:text-term-800">Remaining Balance:</span>
                        <span class="fin-tag fin-tag-due">{{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-white/10">
                        <span class="text-slate-600 dark:text-term-800">Minimum Deposit to Start Work:</span>
                        <span class="font-semibold tabular-nums">{{ $order->currency }} {{ number_format($minDeposit, 2) }}</span>
                    </div>
                </div>

                <div>
                    <label class="term-field-label">Payment Amount ({{ $order->currency }})</label>
                    <input type="number" step="0.01" min="1" max="{{ (float)$order->amount_due }}" name="amount" x-model="payAmount" required
                           class="term-input font-bold">
                    <div class="flex gap-2 mt-2 flex-wrap">
                        <button type="button" @click="payAmount = '{{ $minDeposit }}'" class="term-btn term-btn-ghost term-btn-sm">
                            Min Deposit ({{ $order->currency }} {{ number_format($minDeposit, 2) }})
                        </button>
                        <button type="button" @click="payAmount = '{{ (float)$order->amount_due }}'" class="term-btn term-btn-ghost term-btn-sm">
                            Full Balance ({{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }})
                        </button>
                    </div>
                </div>

                <div>
                    <label class="term-field-label">Payment Method</label>
                    <select name="payment_method" class="term-input">
                        <option value="credit_card">Credit / Debit Card</option>
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="showPayModal = false" class="term-btn term-btn-ghost term-btn-sm">Cancel</button>
                    <button type="submit" class="term-btn term-btn-sm">
                        Confirm &amp; Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change request (approval-gated; original scope untouched until decided) --}}
    <div class="term-panel p-5 mt-4">
        <h3 class="font-bold text-slate-900 dark:text-white mb-1">Request a Scope Change</h3>
        <p class="text-sm text-slate-600 dark:text-term-800 mb-2">Describe additional requirements. The team reviews price/time impact and the original scope stays unchanged until a decision is recorded.</p>
        <form method="POST" action="{{ route('portal.orders.change-request', $order) }}" class="space-y-2">
            @csrf
            <input name="title" class="term-input text-sm" placeholder="e.g. Add API security testing" required maxlength="255">
            <textarea name="details" rows="2" class="term-input text-sm" placeholder="What should change and why? (min 10 characters)" required></textarea>
            <button class="term-btn term-btn-ghost term-btn-sm">Submit Change Request</button>
        </form>
    </div>

    @if(!in_array($order->status, ['closed', 'financially_completed', 'cancelled'], true))
    {{-- Cancel order (reason required; paid balances flag a refund; history kept) --}}
    <div class="term-panel p-5 mt-4">
        <h3 class="font-bold text-slate-900 dark:text-white mb-1">Cancel Order</h3>
        <p class="text-sm text-slate-600 dark:text-term-800 mb-2">Cancellation is recorded with your reason and timestamp. Paid amounts are flagged for refund review — nothing is deleted.</p>
        <form method="POST" action="{{ route('portal.orders.cancel', $order->id) }}" class="flex flex-col sm:flex-row gap-2">
            @csrf
            <input name="reason" class="term-input text-sm flex-1" placeholder="Reason for cancellation (required)" required maxlength="1000">
            <button class="term-btn term-btn-ghost term-btn-sm !border-red-500/50 !text-red-400" onclick="return confirm('Cancel this order? This is recorded permanently.')">Cancel Order</button>
        </form>
    </div>
    @endif
</div>
@endsection
