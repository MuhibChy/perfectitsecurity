@extends('layouts.app')
@section('page-title', 'Create Manual Work Order')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    customerMode: 'existing',
    selectedCustomerId: '{{ old('customer_id') }}',
    selectedCustomerText: '',
    price: {{ old('price', 0) }},
    discount: {{ old('discount_amount', 0) }},
    taxRate: {{ old('tax_rate', 0) }},
    get subtotalAfterDiscount() {
        return Math.max(0, parseFloat(this.price || 0) - parseFloat(this.discount || 0));
    },
    get taxAmount() {
        return this.subtotalAfterDiscount * (parseFloat(this.taxRate || 0) / 100);
    },
    get total() {
        return this.subtotalAfterDiscount + this.taxAmount;
    },
    get discountPercent() {
        return parseFloat(this.price) > 0 ? (parseFloat(this.discount || 0) / parseFloat(this.price)) * 100 : 0;
    },
    get requiresManagerApproval() {
        return this.discountPercent > 20;
    }
}">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.work-orders.index') }}" class="hover:text-primary-600">Work Orders</a>
        <span>/</span>
        <span class="text-gray-900 dark:text-white font-medium">Create Manual Work Order</span>
    </div>

    <div class="glass-card p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Manual Work Order</h1>
                <p class="text-sm text-gray-500 mt-1">Manual order creation for Phone, Email, Office Walk-in, or Sales consultations. Connects immediately to Finance, Invoices, and IT Task Management.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                Staff Workflow
            </span>
        </div>

        <form action="{{ route('admin.work-orders.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf

            {{-- 1. Customer Section --}}
            <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase text-gray-700 dark:text-gray-300">1. Customer Identification</h2>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="customer_mode" value="existing" x-model="customerMode" class="text-primary-600">
                            <span>Existing Customer</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="customer_mode" value="new" x-model="customerMode" class="text-primary-600">
                            <span>New Customer</span>
                        </label>
                    </div>
                </div>

                {{-- Existing Customer Select --}}
                <div x-show="customerMode === 'existing'" x-transition>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Select Customer Profile *</label>
                    <select name="customer_id" x-model="selectedCustomerId" class="mt-1 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">
                        <option value="">-- Choose Registered Customer --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-verified="{{ $c->isFullyVerified() ? 1 : 0 }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email }} | {{ $c->phone ?: 'No phone' }}) — [{{ $c->isFullyVerified() ? '✓ Fully Verified' : '⚠️ Pending Auth' }}]
                        </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- New Customer Inputs --}}
                <div x-show="customerMode === 'new'" x-transition class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Full Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="John Doe"
                               class="mt-1 block w-full px-3 py-2 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-xs">
                        @error('customer_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Email Address *</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="john@example.com"
                               class="mt-1 block w-full px-3 py-2 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-xs">
                        @error('customer_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Phone Number (+ country code)</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+447123456789"
                               class="mt-1 block w-full px-3 py-2 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-xs">
                    </div>
                </div>
            </div>

            {{-- 2. Service & Pricing --}}
            <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                <h2 class="text-sm font-bold uppercase text-gray-700 dark:text-gray-300">2. Service & Financial Terms</h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Service *</label>
                        <select name="service_id" required class="mt-1 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500"
                                @change="
                                    let opt = $event.target.selectedOptions[0];
                                    if (opt && opt.dataset.price) {
                                        price = parseFloat(opt.dataset.price);
                                    }
                                ">
                            <option value="">-- Choose IT Service --</option>
                            @foreach($services as $svc)
                            <option value="{{ $svc->id }}" data-price="{{ $svc->base_price ?: 0 }}" {{ old('service_id') == $svc->id ? 'selected' : '' }}>
                                {{ $svc->name }} (${{ number_format($svc->base_price ?: 0, 2) }})
                            </option>
                            @endforeach
                        </select>
                        @error('service_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Order Source / Channel</label>
                        <select name="order_source_label" class="mt-1 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm">
                            <option value="Office Walk-in">Office Walk-in</option>
                            <option value="Phone Consultation">Phone Consultation</option>
                            <option value="Email Request">Email Request</option>
                            <option value="Client Referral">Client Referral</option>
                            <option value="Sales Agent">Sales Agent</option>
                        </select>
                    </div>
                </div>

                {{-- Pricing Calculation Grid --}}
                <div class="grid sm:grid-cols-4 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Agreed Price ($) *</label>
                        <input type="number" step="0.01" min="0" name="price" x-model="price" required
                               class="mt-1 block w-full px-3 py-2 text-sm font-bold border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Discount Amount ($)</label>
                        <input type="number" step="0.01" min="0" name="discount_amount" x-model="discount"
                               class="mt-1 block w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Tax Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model="taxRate"
                               class="mt-1 block w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                    </div>

                    <div class="p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex flex-col justify-center">
                        <span class="text-[10px] uppercase font-bold text-gray-400">Calculated Total</span>
                        <div class="text-base font-bold text-emerald-600">
                            $<span x-text="total.toFixed(2)">0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Manager Approval Warning if Discount > 20% --}}
                <div x-show="requiresManagerApproval" x-cloak class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                    <span>⚠️</span>
                    <span>Discount exceeds 20% (<strong x-text="discountPercent.toFixed(1) + '%'"></strong>). This work order will be created with status <strong>Pending Manager Approval</strong>.</span>
                </div>
            </div>

            {{-- 3. Scope & Assignee --}}
            <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                <h2 class="text-sm font-bold uppercase text-gray-700 dark:text-gray-300">3. Technical Execution & Assignment</h2>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Requirements & Problem Description *</label>
                    <textarea name="requirements" rows="3" required placeholder="Describe technical scope, server credentials, network configuration, or expected outcomes..."
                              class="mt-1 block w-full px-3 py-2 text-sm border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">{{ old('requirements') }}</textarea>
                    @error('requirements') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Assign IT Technician</label>
                        <select name="assigned_to" class="mt-1 block w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                            <option value="">-- Unassigned (Assign Later) --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ ucfirst(str_replace('_', ' ', $emp->role)) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Priority Level</label>
                        <select name="priority" class="mt-1 block w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Target Delivery Date</label>
                        <input type="date" name="preferred_date" class="mt-1 block w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Internal Office Notes (Restricted from Customer)</label>
                    <textarea name="internal_notes" rows="2" placeholder="Internal communication, technician notes, or contract references..."
                              class="mt-1 block w-full px-3 py-2 text-xs border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">{{ old('internal_notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('admin.work-orders.index') }}" class="px-4 py-2.5 border rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                    Create & Activate Work Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
