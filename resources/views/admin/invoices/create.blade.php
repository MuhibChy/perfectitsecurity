@extends('layouts.app')

@section('title', 'Create Invoice — Admin Portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="invoiceBuilder()">
    <x-page-header sys="FINANCE://INVOICES"
        title="Create Client Invoice"
        subtitle="Generate itemized billing for delivered IT support, cybersecurity audits, or retainer services."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Invoices' => route('admin.invoices.index'), 'New' => null]"
    />

    <form method="POST" action="{{ route('admin.invoices.store') }}" class="space-y-6">
        @csrf

        {{-- Client & Due Date --}}
        <div class="term-panel p-6 lg:p-8 shadow-xl border border-white/10 space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">1. Client & Schedule</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Billed Customer</label>
                    <select name="customer_id" required class="term-input">
                        <option value="">Select a client...</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email }})
                        </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Payment Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required
                           class="term-input">
                    @error('due_date') <p class="term-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Itemized Line Items --}}
        <div class="term-panel p-6 lg:p-8 shadow-xl border border-white/10 space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">2. Itemized Deliverables</h3>
                <button type="button" @click="addItem()" class="term-btn term-btn-ghost term-btn-sm">
                    + Add Line Item
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 bg-surface-50 dark:bg-navy-800/50 border border-surface-200 dark:border-white/5 grid grid-cols-12 gap-3 items-center">
                        <div class="col-span-12 sm:col-span-5">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Service Description</label>
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required placeholder="e.g. SOC Monitoring — Monthly"
                                   class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-navy-900 border border-surface-300 dark:border-white/10 text-gray-900 dark:text-white">
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Quantity</label>
                            <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required
                                   class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-navy-900 border border-surface-300 dark:border-white/10 text-gray-900 dark:text-white">
                        </div>

                        <div class="col-span-4 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Rate ($)</label>
                            <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" min="0" required
                                   class="w-full px-3 py-2 rounded-lg text-xs bg-white dark:bg-navy-900 border border-surface-300 dark:border-white/10 text-gray-900 dark:text-white">
                        </div>

                        <div class="col-span-3 sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-gray-500 mb-1">Total</label>
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

        {{-- Tax & Discount --}}
        <div class="term-panel p-6 lg:p-8 shadow-xl border border-white/10 space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">3. Tax & Payment Terms</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Discount Amount ($)</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discount"
                           class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model.number="taxRate"
                           class="term-input">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Client Notes</label>
                    <textarea name="notes" rows="3" placeholder="Notes for the customer..." class="term-input"></textarea>
                </div>

                <div>
                    <label class="term-field-label">Payment Terms</label>
                    <textarea name="terms" rows="3" placeholder="Wire transfer details, Net 14..." class="term-input"></textarea>
                </div>
            </div>

            {{-- Summary Totals Strip --}}
            <div class="p-4 bg-surface-100 dark:bg-navy-800/80 flex flex-wrap items-center justify-between text-sm">
                <div>
                    <span class="text-gray-500">Subtotal:</span>
                    <strong class="font-mono ml-1" x-text="'$' + subtotal().toFixed(2)"></strong>
                </div>
                <div>
                    <span class="text-gray-500">Tax:</span>
                    <strong class="font-mono ml-1" x-text="'$' + tax().toFixed(2)"></strong>
                </div>
                <div class="text-base font-bold text-gray-900 dark:text-white">
                    <span>Invoice Total:</span>
                    <span class="font-mono text-primary-600 dark:text-primary-400 ml-1" x-text="'$' + total().toFixed(2)"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                <a href="{{ route('admin.invoices.index') }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                <button type="submit" class="term-btn term-btn-sm">Save & Create Invoice</button>
            </div>
        </div>
    </form>
</div>

<script>
function invoiceBuilder() {
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
