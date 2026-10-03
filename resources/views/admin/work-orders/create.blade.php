@extends('layouts.app')
@section('page-title', 'Create Manual Work Order')

@section('content')

    <x-page-header title="Create Manual Work Order" sys="OPS://WORK-ORDERS" />
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

    <div class="term-panel p-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create Manual Work Order</h2>
                <p class="text-sm text-gray-500 mt-1">Manual order creation for Phone, Email, Office Walk-in, or Sales consultations. Connects immediately to Finance, Invoices, and IT Task Management.</p>
            </div>
            <span class="term-tag">
                Staff Workflow
            </span>
        </div>

        <form action="{{ route('admin.work-orders.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf

            {{-- 1. Customer Section --}}
            <div class="p-5 border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
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
                    <label class="term-field-label">Select Customer Profile *</label>
                    <select name="customer_id" x-model="selectedCustomerId" class="term-input mt-1">
                        <option value="">-- Choose Registered Customer --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-verified="{{ $c->isFullyVerified() ? 1 : 0 }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email }} | {{ $c->phone ?: 'No phone' }}) — [{{ $c->isFullyVerified() ? '✓ Fully Verified' : ' Pending Auth' }}]
                        </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                {{-- New Customer Inputs --}}
                <div x-show="customerMode === 'new'" x-transition class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="term-field-label">Full Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="John Doe"
                               class="term-input mt-1">
                        @error('customer_name') <p class="term-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="term-field-label">Email Address *</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="john@example.com"
                               class="term-input mt-1">
                        @error('customer_email') <p class="term-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="term-field-label">Phone Number (+ country code)</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+447123456789"
                               class="term-input mt-1">
                    </div>
                </div>
            </div>

            {{-- 2. Service & Pricing --}}
            <div class="p-5 border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                <h2 class="text-sm font-bold uppercase text-gray-700 dark:text-gray-300">2. Service & Financial Terms</h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="term-field-label">Service *</label>
                        <select name="service_id" required class="term-input mt-1"
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
                        @error('service_id') <p class="term-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="term-field-label">Order Source / Channel</label>
                        <select name="order_source_label" class="term-input mt-1">
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
                        <label class="term-field-label">Agreed Price ($) *</label>
                        <input type="number" step="0.01" min="0" name="price" x-model="price" required
                               class="term-input mt-1">
                    </div>

                    <div>
                        <label class="term-field-label">Discount Amount ($)</label>
                        <input type="number" step="0.01" min="0" name="discount_amount" x-model="discount"
                               class="term-input mt-1">
                    </div>

                    <div>
                        <label class="term-field-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model="taxRate"
                               class="term-input mt-1">
                    </div>

                    <div class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex flex-col justify-center">
                        <span class="text-[10px] uppercase font-bold text-gray-400">Calculated Total</span>
                        <div class="text-base font-bold text-emerald-600">
                            $<span x-text="total.toFixed(2)">0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Manager Approval Warning if Discount > 20% --}}
                <div x-show="requiresManagerApproval" x-cloak class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-center gap-2">
                    
                    <span>Discount exceeds 20% (<strong x-text="discountPercent.toFixed(1) + '%'"></strong>). This work order will be created with status <strong>Pending Manager Approval</strong>.</span>
                </div>
            </div>

            {{-- 3. Scope & Assignee --}}
            <div class="p-5 border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                <h2 class="text-sm font-bold uppercase text-gray-700 dark:text-gray-300">3. Technical Execution & Assignment</h2>

                <div>
                    <label class="term-field-label">Requirements & Problem Description *</label>
                    <textarea name="requirements" rows="3" required placeholder="Describe technical scope, server credentials, network configuration, or expected outcomes..."
                              class="term-input mt-1">{{ old('requirements') }}</textarea>
                    @error('requirements') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="term-field-label">Assign IT Technician</label>
                        <select name="assigned_to" class="term-input mt-1">
                            <option value="">-- Unassigned (Assign Later) --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ ucfirst(str_replace('_', ' ', $emp->role)) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="term-field-label">Priority Level</label>
                        <select name="priority" class="term-input mt-1">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div>
                        <label class="term-field-label">Target Delivery Date</label>
                        <input type="date" name="preferred_date" class="term-input mt-1">
                    </div>
                </div>

                <div>
                    <label class="term-field-label">Internal Office Notes (Restricted from Customer)</label>
                    <textarea name="internal_notes" rows="2" placeholder="Internal communication, technician notes, or contract references..."
                              class="term-input mt-1">{{ old('internal_notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('admin.work-orders.index') }}" class="term-btn term-btn-sm term-btn-ghost">
                    Cancel
                </a>
                <button type="submit" class="term-btn term-btn-sm">
                    Create & Activate Work Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
