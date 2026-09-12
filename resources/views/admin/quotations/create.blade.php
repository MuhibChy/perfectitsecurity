@extends('layouts.app')

@section('title', 'Draft New Quotation — Admin Portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="quotationBuilder()">
    <x-page-header
        title="Draft Commercial Quotation"
        subtitle="Specify client details, scope of work, itemized rates, and commercial terms."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Quotations' => route('admin.quotations.index'), 'Draft' => null]"
    />

    <form method="POST" action="{{ route('admin.quotations.store') }}" class="space-y-6">
        @csrf

        {{-- Client & Validity Card --}}
        <div class="glass-card p-6 lg:p-8 rounded-2xl shadow-xl border border-white/10 space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">1. Client & Schedule</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Target Customer</label>
                    <select name="customer_id" required class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        <option value="">Select a registered client...</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email }})
                        </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Offer Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(30)->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    @error('valid_until') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Line Items Card --}}
        <div class="glass-card p-6 lg:p-8 rounded-2xl shadow-xl border border-white/10 space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">2. Scope & Service Line Items</h3>
                <button type="button" @click="addItem()" class="btn-secondary btn-sm">
                    + Add Line Item
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-xl bg-surface-50 dark:bg-navy-800/50 border border-surface-200 dark:border-white/5 grid grid-cols-12 gap-3 items-center">
                        <div class="col-span-12 sm:col-span-5">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Description / Deliverable</label>
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required placeholder="e.g. Cloud Security Audit & Hardening"
                                   class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-navy-900 border border-surface-300 dark:border-white/10 text-gray-900 dark:text-white">
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Qty / Hours</label>
                            <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required
                                   class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-navy-900 border border-surface-300 dark:border-white/10 text-gray-900 dark:text-white">
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Unit Rate ($)</label>
                            <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" min="0" required
                                   class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-navy-900 border border-surface-300 dark:border-white/10 text-gray-900 dark:text-white">
                        </div>

                        <div class="col-span-3 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Line Total</label>
                            <div class="font-mono text-xs font-bold text-gray-900 dark:text-white pt-2" x-text="'$' + (item.quantity * item.unit_price).toFixed(2)"></div>
                        </div>

                        <div class="col-span-1 text-right">
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-500 hover:text-rose-700 p-1">
                                &times;
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Taxes, Discount & Notes Card --}}
        <div class="glass-card p-6 lg:p-8 rounded-2xl shadow-xl border border-white/10 space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">3. Commercial Terms & Taxes</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Discount Amount ($)</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discount"
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Applicable Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model.number="taxRate"
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:outline-none">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Engineering Scope Notes</label>
                    <textarea name="notes" rows="3" placeholder="Additional details or implementation notes..." class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Commercial & Payment Terms</label>
                    <textarea name="terms" rows="3" placeholder="50% upfront retainer, balance on deployment..." class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white"></textarea>
                </div>
            </div>

            {{-- Summary Totals Strip --}}
            <div class="p-4 rounded-xl bg-surface-100 dark:bg-navy-800/80 flex flex-wrap items-center justify-between text-sm">
                <div>
                    <span class="text-gray-500">Subtotal:</span>
                    <strong class="font-mono ml-1" x-text="'$' + subtotal().toFixed(2)"></strong>
                </div>
                <div>
                    <span class="text-gray-500">Tax:</span>
                    <strong class="font-mono ml-1" x-text="'$' + tax().toFixed(2)"></strong>
                </div>
                <div class="text-base font-bold text-gray-900 dark:text-white">
                    <span>Grand Total:</span>
                    <span class="font-mono text-primary-600 dark:text-primary-400 ml-1" x-text="'$' + total().toFixed(2)"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                <a href="{{ route('admin.quotations.index') }}" class="btn-ghost btn-sm">Cancel</a>
                <button type="submit" class="btn-primary btn-sm px-8">Save & Issue Quotation</button>
            </div>
        </div>
    </form>
</div>

<script>
function quotationBuilder() {
    return {
        items: [
            { description: '', quantity: 1, unit_price: 0 }
        ],
        discount: 0,
        taxRate: 0,
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },
        subtotal() {
            return this.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0);
        },
        tax() {
            const taxable = Math.max(0, this.subtotal() - (this.discount || 0));
            return taxable * ((this.taxRate || 0) / 100);
        },
        total() {
            return Math.max(0, this.subtotal() - (this.discount || 0)) + this.tax();
        }
    }
}
</script>
@endsection
