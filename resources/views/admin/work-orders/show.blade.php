@extends('layouts.app')
@section('page-title', 'Work Order ' . $order->order_number)

@section('content')

    <x-page-header :title="'Work Order ' . $order->order_number" sys="OPS://WORK-ORDERS" />
<div class="space-y-6" x-data="{
    activeTab: 'overview',
    showExpenseModal: false,
    showPaymentModal: false,
    showOverrideModal: false,
    showCloseModal: false,
    expenseCostType: 'labour',
    expenseHours: 0,
    expenseRate: 25,
    get calculatedLabour() { return (parseFloat(this.expenseHours || 0) * parseFloat(this.expenseRate || 0)).toFixed(2); }
}">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.work-orders.index') }}" class="hover:text-primary-600">Work Orders</a>
            <span>/</span>
            <span class="font-mono text-gray-900 dark:text-white font-bold">{{ $order->order_number }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if((float)$order->amount_due > 0)
            <button @click="showPaymentModal = true" class="term-btn term-btn-sm">
                <span>Record Payment</span>
            </button>
            @endif

            <button @click="showExpenseModal = true" class="term-btn term-btn-sm term-btn-ghost">
                <span>Log Expenditure</span>
            </button>

            @if($order->payment_authorization !== 'ready_to_start' && $order->payment_authorization !== 'fully_paid' && $order->payment_authorization !== 'manager_override')
            <button @click="showOverrideModal = true" class="term-btn term-btn-sm term-btn-ghost">
                <span>Manager Override</span>
            </button>
            @endif

            @if($order->status !== 'closed')
            <button @click="showCloseModal = true" class="term-btn term-btn-sm term-btn-ghost">
                Close Order
            </button>
            @endif
        </div>
    </div>

    {{-- PAYMENT AUTHORIZATION BANNER --}}
    @php
        $authMeta = [
            'not_authorized' => ['label' => 'NOT AUTHORIZED FOR IT WORK', 'desc' => 'No payment received. Technical tasks must remain on hold until minimum deposit or manager override is in place.', 'bg' => 'bg-red-500/10 border-red-500/30 text-red-700 dark:text-red-400'],
            'deposit_required' => ['label' => 'DEPOSIT REQUIRED', 'desc' => "Minimum deposit of {$order->currency} " . number_format($minDeposit, 2) . ' is required before technical delivery begins.', 'bg' => 'bg-amber-500/10 border-amber-500/30 text-amber-700 dark:text-amber-400'],
            'deposit_received' => ['label' => 'DEPOSIT RECEIVED', 'desc' => "Minimum deposit satisfied ({$order->currency} " . number_format((float)$order->amount_paid, 2) . '). Work may proceed.', 'bg' => 'bg-teal-500/10 border-teal-500/30 text-teal-700 dark:text-teal-400'],
            'ready_to_start' => ['label' => 'READY TO START', 'desc' => 'Financial conditions satisfied. Technical team is authorized to start execution.', 'bg' => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 dark:text-emerald-400'],
            'fully_paid' => ['label' => 'FULLY PAID', 'desc' => 'Entire order amount has been collected and verified. Zero balance remaining.', 'bg' => 'bg-emerald-600/15 border-emerald-600/40 text-emerald-800 dark:text-emerald-300 font-bold'],
            'manager_override' => ['label' => 'MANAGER OVERRIDE ACTIVE', 'desc' => 'Authorized by ' . ($order->managerOverride?->name ?? 'Manager') . ': ' . $order->manager_override_reason, 'bg' => 'bg-purple-500/10 border-purple-500/30 text-purple-700 dark:text-purple-400'],
        ];
        $currentAuth = $authMeta[$order->payment_authorization] ?? $authMeta['not_authorized'];
    @endphp
    <div class="p-4 border {{ $currentAuth['bg'] }} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="term-tag w-3.5 h-3.5 animate-pulse"></span>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider block">{{ $currentAuth['label'] }}</span>
                <span class="text-xs opacity-90">{{ $currentAuth['desc'] }}</span>
            </div>
        </div>
        <div class="text-xs font-mono font-bold whitespace-nowrap">
            Paid: {{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }} / {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
        </div>
    </div>

    {{-- EXECUTIVE FINANCIAL SUMMARY CARD (Core Section 35 Requirement) --}}
    <div class="term-panel p-6 border-l-4 border-l-primary-500">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-2">
                
                <span>ORDER FINANCIAL SUMMARY & PROFITABILITY TRAIL</span>
            </h2>
            <span class="text-xs text-gray-400">All amounts verified server-side</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="term-panel-2 p-3">
                <span class="text-[10px] uppercase font-semibold text-gray-500 dark:text-gray-400">Order Value</span>
                <div class="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                    {{ $order->currency }} {{ number_format((float)$order->total, 2) }}
                </div>
            </div>

            <div class="term-panel-2 p-3">
                <span class="text-[10px] uppercase font-semibold text-gray-400 fin-tag fin-tag-income">Collected Revenue</span>
                <div class="text-base font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                    {{ $order->currency }} {{ number_format((float)$order->amount_paid, 2) }}
                </div>
            </div>

            <div class="term-panel-2 p-3">
                <span class="text-[10px] uppercase font-semibold text-gray-400 fin-tag fin-tag-due">Outstanding Balance</span>
                <div class="text-base font-bold {{ (float)$order->amount_due > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }} mt-0.5">
                    {{ $order->currency }} {{ number_format((float)$order->amount_due, 2) }}
                </div>
            </div>

            <div class="term-panel-2 p-3">
                <span class="text-[10px] uppercase font-semibold text-gray-400 fin-tag fin-tag-expense">Total Expenditure</span>
                <div class="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                    {{ $order->currency }} {{ number_format((float)$order->total_cost, 2) }}
                </div>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $order->expenses->count() }} expense items</span>
            </div>

            <div class="term-panel-2 p-3">
                <span class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-400 fin-tag fin-tag-profit">Actual Profit</span>
                <div class="text-base font-extrabold {{ $order->actual_profit >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-400' }} mt-0.5">
                    {{ $order->currency }} {{ number_format((float)$order->actual_profit, 2) }}
                </div>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">Collected - Costs</span>
            </div>

            <div class="term-panel-2 p-3">
                <span class="text-[10px] uppercase font-bold text-blue-700 dark:text-blue-400 fin-tag fin-tag-profit">Expected Profit</span>
                <div class="text-base font-extrabold text-blue-700 dark:text-blue-300 mt-0.5">
                    {{ $order->currency }} {{ number_format((float)$order->expected_profit, 2) }}
                </div>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">Total - Expected Cost</span>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-800 text-xs font-semibold">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-primary-600 text-primary-600 border-b-2 py-3 px-4' : 'text-gray-500 hover:text-gray-700">
            Overview & Customer
        </button>
        <button @click="activeTab = 'finance'" :class="activeTab === 'finance' ? 'border-primary-600 text-primary-600 border-b-2 py-3 px-4' : 'text-gray-500 hover:text-gray-700">
            Invoices, Payments & Receipts ({{ $order->receipts->count() }})
        </button>
        <button @click="activeTab = 'tasks'" :class="activeTab === 'tasks' ? 'border-primary-600 text-primary-600 border-b-2 py-3 px-4' : 'text-gray-500 hover:text-gray-700">
            IT Ticket & Technical Tasks ({{ $order->tasks->count() }})
        </button>
        <button @click="activeTab = 'expenses'" :class="activeTab === 'expenses' ? 'border-primary-600 text-primary-600 border-b-2 py-3 px-4' : 'text-gray-500 hover:text-gray-700">
            Expenditures & Labour ({{ $order->expenses->count() }})
        </button>
        <button @click="activeTab = 'negotiation'" :class="activeTab === 'negotiation' ? 'border-primary-600 text-primary-600 border-b-2 py-3 px-4' : 'text-gray-500 hover:text-gray-700">
            Price Revisions ({{ $order->priceRevisions->count() }})
        </button>
    </div>

    {{-- TAB 1: OVERVIEW & CUSTOMER --}}
    <div x-show="activeTab === 'overview'" class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="term-panel p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-bold uppercase text-primary-600">{{ $order->service->category?->name ?? 'Service' }}</span>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $order->service->name }}</h3>
                    </div>
                    <span class="term-tag">
                        Priority: {{ ucfirst($order->priority) }}
                    </span>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <h4 class="text-xs font-bold uppercase text-gray-500">Service Scope & Requirements</h4>
                    <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $order->requirements }}</p>
                </div>

                @if($order->internal_notes)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-1 bg-amber-50/50 dark:bg-amber-950/20 p-3">
                    <h4 class="text-xs font-bold uppercase text-amber-800 dark:text-amber-300">Internal Staff Notes</h4>
                    <p class="text-xs text-amber-900 dark:text-amber-200">{{ $order->internal_notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Customer Identity Card --}}
        <div class="term-panel p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">Customer Profile</h3>
            <div class="p-4 border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/40 space-y-3">
                <div class="font-bold text-gray-900 dark:text-white text-base">{{ $order->customer->name }}</div>
                <div class="text-xs text-gray-500">{{ $order->customer->email }}</div>
                <div class="text-xs text-gray-500">{{ $order->customer->phone ?: 'No phone recorded' }}</div>

                <div class="pt-3 border-t border-gray-200 dark:border-gray-800 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Email Verification:</span>
                        @if($order->customer->isEmailVerified())
                            <span class="text-emerald-600 font-bold">✓ Verified</span>
                        @else
                            <span class="text-amber-600 font-semibold">Pending</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Phone Verification:</span>
                        @if($order->customer->isPhoneVerified())
                            <span class="text-emerald-600 font-bold">✓ Verified</span>
                        @else
                            <span class="text-amber-600 font-semibold">Pending</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1 border-t border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500 font-semibold">Standing:</span>
                        @if($order->customer->isFullyVerified())
                        <x-status-badge status="verified" label="Fully Verified" />
                        @else
                        <x-status-badge status="pending" label="Pending Verification" />
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-xs text-gray-400 space-y-1">
                <div>Created By: {{ $order->creator?->name ?? 'Customer' }}</div>
                <div>Created At: {{ $order->created_at->format('M d, Y H:i') }}</div>
                <div>Source: {{ $order->order_source_label ?: ucfirst($order->source) }}</div>
            </div>
        </div>
    </div>

    {{-- TAB 2: FINANCE, INVOICES, PAYMENTS & RECEIPTS --}}
    <div x-show="activeTab === 'finance'" class="space-y-6">
        <div class="grid lg:grid-cols-2 gap-6">
            {{-- Invoices Card --}}
            <div class="term-panel p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Invoices Linked to Order</h3>
                <div class="space-y-3">
                    @forelse($order->invoices as $inv)
                    <div class="p-4 border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-primary-600 text-sm">{{ $inv->invoice_number }}</span>
                            <x-status-badge :status="$inv->status" />
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-gray-200 dark:border-gray-800">
                            <div>Total: <strong class="block text-gray-900 dark:text-white">{{ $order->currency }} {{ number_format((float)$inv->total, 2) }}</strong></div>
                            <div>Paid: <strong class="block text-emerald-600">{{ $order->currency }} {{ number_format((float)$inv->amount_paid, 2) }}</strong></div>
                            <div>Balance: <strong class="block text-amber-600">{{ $order->currency }} {{ number_format((float)$inv->amount_due, 2) }}</strong></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No invoices issued.</p>
                    @endforelse
                </div>
            </div>

            {{-- Receipts Card --}}
            <div class="term-panel p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900 dark:text-white">Confirmed Payment Receipts</h3>
                    <span class="text-xs text-gray-400">Auto-generated upon confirmed receipt</span>
                </div>
                <div class="space-y-3">
                    @forelse($order->receipts as $rcp)
                    <div class="p-3 border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/40 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $rcp->receipt_number }}</span>
                            <div class="text-[10px] text-gray-400">{{ $rcp->issued_at->format('M d, Y H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-emerald-600 block">{{ $order->currency }} {{ number_format((float)$rcp->amount, 2) }}</span>
                            <a href="{{ route('admin.work-orders.receipts.show', ['id' => $order->id, 'receiptId' => $rcp->id]) }}" target="_blank" class="text-[11px] text-primary-600 hover:underline">
                                View Official Receipt →
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No payment receipts recorded yet.</p>
                    @endforelse
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('admin.work-orders.pdf', $order->id) }}" target="_blank" class="term-btn term-btn-sm term-btn-ghost">Order PDF</a>
                    @foreach($order->receipts as $rcp)
                    <a href="{{ route('admin.receipts.pdf', $rcp) }}" target="_blank" class="term-btn term-btn-sm term-btn-ghost">Receipt {{ $rcp->receipt_number }} PDF</a>
                    @endforeach
                </div>
            </div>

            {{-- Payment Schedule Card --}}
            <div class="term-panel p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Stage Payment Schedule</h3>
                <p class="text-[11px] text-gray-400 mb-3">Planned stage amounts. Actuals always come from payment records. Total planned may not exceed the order total.</p>
                <div class="space-y-2 mb-3">
                    @forelse($schedules as $sch)
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-800">
                        <span class="font-semibold">{{ $sch->title }} <span class="text-gray-400">· {{ ucfirst($sch->status) }}</span></span>
                        <span>{{ $order->currency }} {{ number_format((float) $sch->paid_amount, 2) }} / {{ number_format((float) $sch->expected_amount, 2) }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">No schedule defined. Define advance + stage amounts below.</p>
                    @endforelse
                </div>
                <form action="{{ route('admin.work-orders.schedule.store', $order->id) }}" method="POST" class="flex flex-wrap gap-2 items-end">
                    @csrf
                    <input name="title" class="term-input flex-1 min-w-[140px]" placeholder="Stage title, e.g. Advance" required maxlength="255">
                    <input type="number" step="0.01" min="0.01" name="expected_amount" class="term-input w-28" placeholder="Amount" required>
                    <input type="date" name="due_at" class="term-input">
                    <button class="term-btn term-btn-sm">Add Stage</button>
                </form>
            </div>
        </div>
    </div>

    {{-- TAB 3: IT TICKET & TASKS --}}
    <div x-show="activeTab === 'tasks'" class="space-y-6">
        <div class="term-panel p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Technical Delivery Tasks</h3>
            <div class="space-y-4">
                @forelse($order->tasks as $task)
                <div class="p-4 border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/40 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-mono text-xs font-bold text-primary-600">{{ $task->task_number }}</span>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ $task->title }}</h4>
                        </div>
                        <x-status-badge :status="$task->status" />
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300">{{ $task->description }}</p>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-200 dark:border-gray-800 text-xs">
                        <div class="flex items-center gap-4">
                            <span>Assignee: <strong>{{ $task->assignee?->name ?? 'Unassigned' }}</strong></span>
                            <span>SLA Priority: <strong>{{ ucfirst($task->sla_priority ?: 'Medium') }}</strong></span>
                            @if($task->completed_at)
                            <span class="text-emerald-600 font-bold">✓ Completed {{ $task->completed_at->format('M d, Y H:i') }}</span>
                            @endif
                        </div>

                        @if($task->status !== 'completed')
                        <form action="{{ route('admin.tasks.update', $task->id) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="title" value="{{ $task->title }}">
                            <input type="hidden" name="priority" value="{{ $task->priority ?: 'medium' }}">
                            <input type="hidden" name="status" value="completed">
                            <input type="text" name="technical_notes" placeholder="Completion technical notes..." class="term-input">
                            <button type="submit" class="term-btn term-btn-sm">
                                Complete Technical Work
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400">Tasks are automatically created upon order confirmation.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- TAB 4: EXPENDITURES & LABOUR --}}
    <div x-show="activeTab === 'expenses'" class="space-y-6">
        <div class="term-panel p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Internal Expenditures & Vendor Costs</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Every cost (employee labour hours, freelancer, commission, software license, cloud) recorded against this order.</p>
                </div>
                <button @click="showExpenseModal = true" class="term-btn term-btn-sm term-btn-ghost">Add Expense</button>
            </div>

            <div class="overflow-x-auto -mx-6 px-6 term-table-wrap">
            <table class="data-table w-full text-left border-collapse text-xs min-w-[640px] term-table">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 text-gray-500 font-semibold uppercase">
                        <th class="py-2.5 px-3">Expense #</th>
                        <th class="py-2.5 px-3">Type</th>
                        <th class="py-2.5 px-3">Worker / Vendor</th>
                        <th class="py-2.5 px-3">Description</th>
                        <th class="py-2.5 px-3">Hours / Rate</th>
                        <th class="py-2.5 px-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($order->expenses as $exp)
                    <tr>
                        <td class="py-2.5 px-3 font-mono font-bold" data-label="Expense #">{{ $exp->expense_number }}</td>
                        <td class="py-2.5 px-3" data-label="Type">
                            <span class="term-tag uppercase">
                                {{ $exp->cost_type }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 font-medium text-gray-800 dark:text-gray-200" data-label="Worker / Vendor">
                            {{ $exp->worker?->name ?? $exp->vendor ?? 'Internal' }}
                        </td>
                        <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400" data-label="Description">{{ $exp->description }}</td>
                        <td class="py-2.5 px-3 text-gray-500" data-label="Hours / Rate">
                            @if($exp->hours)
                                {{ $exp->hours }}h @ ${{ number_format((float)$exp->hourly_rate, 2) }}/h
                            @elseif($exp->commission_percentage)
                                {{ $exp->commission_percentage }}%
                            @else
                                —
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold text-gray-900 dark:text-white" data-label="Amount">
                            ${{ number_format((float)$exp->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400">No expenditures recorded for this order yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- TAB 5: PRICE NEGOTIATION --}}
    <div x-show="activeTab === 'negotiation'" class="space-y-6">
        <div class="term-panel p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Complete Price Revision History</h3>
            <div class="space-y-3">
                @foreach($order->priceRevisions as $rev)
                <div class="p-4 border {{ $rev->status === 'accepted' ? 'border-emerald-400 bg-emerald-50/40 dark:border-emerald-800 dark:bg-emerald-950/20' : 'border-gray-200 dark:border-gray-800' }} text-xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold uppercase tracking-wider text-gray-500">{{ ucfirst(str_replace('_', ' ', $rev->kind)) }}</span>
                            <div class="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                {{ $order->currency }} {{ number_format((float)$rev->amount, 2) }}
                                @if((float)$rev->discount_amount > 0)
                                    <span class="text-xs text-green-600 font-normal ml-1">(-${{ number_format((float)$rev->discount_amount, 2) }})</span>
                                @endif
                            </div>
                        </div>
                        <x-status-badge :status="$rev->status" />
                    </div>
                    @if($rev->terms)
                    <div class="mt-2 text-gray-600 dark:text-gray-400 italic">"{{ $rev->terms }}"</div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Propose price revision form --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                <h4 class="text-xs font-bold uppercase text-gray-700 dark:text-gray-300">Submit Staff Price Proposal / Discount Offer</h4>
                <form action="{{ route('admin.work-orders.propose-price', $order->id) }}" method="POST" class="mt-3 grid sm:grid-cols-4 gap-3">
                    @csrf
                    <div>
                        <label class="term-field-label">Offer Amount ($)</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="500.00" class="term-input mt-1">
                    </div>
                    <div>
                        <label class="term-field-label">Discount Amount ($)</label>
                        <input type="number" step="0.01" min="0" name="discount_amount" placeholder="0.00" class="term-input mt-1">
                    </div>
                    <div>
                        <label class="term-field-label">Offer Type</label>
                        <select name="kind" class="term-input mt-1">
                            <option value="employee_offer">Employee Offer</option>
                            <option value="discount">Special Discount</option>
                            <option value="final_offer">Final Binding Offer</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="term-btn term-btn-sm w-full">
                            Submit Revision
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 1: RECORD PAYMENT (OFFLINE/FINANCE) --}}
    <div x-show="showPaymentModal" x-cloak @keydown.escape.window="showPaymentModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="showPaymentModal = false" class="term-modal max-w-md w-full">
            <div class="term-modal-head">
                <span class="term-modal-title">Record Verified Payment</span><button type="button" @click="showPaymentModal = false" class="term-modal-x" aria-label="Close dialog">&times;</button>
            </div>
            <form action="{{ route('admin.work-orders.record-payment', $order->id) }}" method="POST" class="space-y-3 p-6">
                @csrf
                <div>
                    <label class="term-field-label">Amount Received ($) *</label>
                    <input type="number" step="0.01" min="1" max="{{ (float)$order->amount_due }}" name="amount" value="{{ (float)$order->amount_due }}" required
                           class="term-input mt-1">
                    <span class="term-hint">Outstanding: ${{ number_format((float)$order->amount_due, 2) }}</span>
                </div>

                <div>
                    <label class="term-field-label">Payment Method *</label>
                    <select name="payment_method" class="term-input mt-1">
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="credit_card">Card Terminal / POS</option>
                        <option value="cash">Office Cash</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="term-field-label">External Transaction / Receipt ID</label>
                    <input type="text" name="transaction_id" placeholder="e.g. WIRE-892319" class="term-input mt-1">
                </div>

                @if(isset($schedules) && $schedules->count())
                <div>
                    <label class="term-field-label">Apply to Stage (optional)</label>
                    <select name="schedule_id" class="term-input mt-1">
                        <option value="">— No stage —</option>
                        @foreach($schedules->whereNotIn('status', ['paid', 'waived']) as $sch)
                        <option value="{{ $sch->id }}">{{ $sch->title }} ({{ number_format((float) $sch->paid_amount, 2) }}/{{ number_format((float) $sch->expected_amount, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showPaymentModal = false" class="term-btn term-btn-sm term-btn-ghost">Cancel</button>
                    <button type="submit" class="term-btn term-btn-sm">
                        Confirm & Issue Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: LOG EXPENDITURE --}}
    <div x-show="showExpenseModal" x-cloak @keydown.escape.window="showExpenseModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="showExpenseModal = false" class="term-modal max-w-lg w-full">
            <div class="term-modal-head">
                <span class="term-modal-title">Log Work Order Expenditure</span><button type="button" @click="showExpenseModal = false" class="term-modal-x" aria-label="Close dialog">×</button>
            </div>
            <form action="{{ route('admin.work-orders.add-expense', $order->id) }}" method="POST" class="space-y-3 p-6">
                @csrf
                <div>
                    <label class="term-field-label">Cost Category / Type *</label>
                    <select name="cost_type" x-model="expenseCostType" class="term-input mt-1">
                        <option value="labour">Employee Labour Cost (Hours × Rate)</option>
                        <option value="freelancer">Freelancer Fee</option>
                        <option value="commission">Sales / Agent Commission</option>
                        <option value="software">Software / License Cost</option>
                        <option value="cloud">Cloud / Infrastructure / Hosting</option>
                        <option value="vendor">Third-Party Vendor</option>
                        <option value="travel">Travel & Onsite Operational Cost</option>
                    </select>
                </div>

                <div x-show="expenseCostType === 'labour'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 term-panel-2">
                    <div>
                        <label class="term-field-label">Hours Worked *</label>
                        <input type="number" step="0.25" min="0" name="hours" x-model="expenseHours" placeholder="5" class="term-input mt-1">
                    </div>
                    <div>
                        <label class="term-field-label">Internal Rate ($/hr) *</label>
                        <input type="number" step="0.5" min="0" name="hourly_rate" x-model="expenseRate" placeholder="25" class="term-input mt-1">
                    </div>
                    <div class="col-span-2 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                        Total Labour Expense: $<span x-text="calculatedLabour">0.00</span>
                    </div>
                </div>

                <div x-show="expenseCostType !== 'labour'">
                    <label class="term-field-label">Total Expense Amount ($) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" placeholder="100.00" class="term-input mt-1">
                </div>

                <div>
                    <label class="term-field-label">Assigned Worker / Freelancer</label>
                    <select name="worker_id" class="term-input mt-1">
                        <option value="">-- None / Internal --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="term-field-label">Description *</label>
                    <input type="text" name="description" required placeholder="e.g. 5 hours backend migration labour" class="term-input mt-1">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showExpenseModal = false" class="term-btn term-btn-sm term-btn-ghost">Cancel</button>
                    <button type="submit" class="term-btn term-btn-sm">
                        Record Expense
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: MANAGER OVERRIDE --}}
    <div x-show="showOverrideModal" x-cloak @keydown.escape.window="showOverrideModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="showOverrideModal = false" class="term-modal max-w-md w-full">
            <div class="term-modal-head">
                <span class="term-modal-title">Grant Manager Payment Override</span><button type="button" @click="showOverrideModal = false" class="term-modal-x" aria-label="Close dialog">&times;</button>
            </div>
            <div class="space-y-3 p-6">
            <p class="text-xs text-gray-500 dark:text-gray-400">Authorizes IT work to begin immediately prior to customer deposit. This action will be permanently logged in the audit trail.</p>
            <form action="{{ route('admin.work-orders.manager-override', $order->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="term-field-label">Reason for Override *</label>
                    <textarea name="override_reason" rows="3" required placeholder="State business reason, corporate client terms, emergency agreement..."
                              class="term-input mt-1"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showOverrideModal = false" class="term-btn term-btn-sm term-btn-ghost">Cancel</button>
                    <button type="submit" class="term-btn term-btn-sm">
                        Authorize Work Immediately
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>

    {{-- MODAL 4: CLOSE ORDER --}}
    <div x-show="showCloseModal" x-cloak @keydown.escape.window="showCloseModal = false" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
        <div @click.away="showCloseModal = false" class="term-modal max-w-md w-full">
            <div class="term-modal-head">
                <span class="term-modal-title">Close Work Order</span><button type="button" @click="showCloseModal = false" class="term-modal-x" aria-label="Close dialog">&times;</button>
            </div>
            <div class="space-y-3 p-6">
            <p class="text-xs text-gray-500 dark:text-gray-400">An order can only be closed once technical tasks are completed and outstanding financial balance is $0.</p>
            <div class="p-3 text-xs space-y-1.5 {{ ($canClose ?? true) ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300' }}">
                <div class="flex justify-between"><span>Open technical tasks:</span><strong>{{ $openTasksCount ?? 0 }}</strong></div>
                <div class="flex justify-between"><span>Outstanding due:</span><strong>{{ $order->currency }} {{ number_format((float) $order->amount_due, 2) }}</strong></div>
                @unless($canClose ?? true)
                <p class="pt-1">Complete the open tasks and settle the balance first — the server will reject premature closure.</p>
                @endunless
            </div>
            <form action="{{ route('admin.work-orders.close', $order->id) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="term-field-label">Closure Notes</label>
                    <textarea name="closure_notes" rows="2" placeholder="Client signed off, final documentation delivered..."
                              class="term-input mt-1"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCloseModal = false" class="term-btn term-btn-sm term-btn-ghost">Cancel</button>
                    <button type="submit" class="term-btn term-btn-sm">
                        Confirm Closure
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
@endsection
