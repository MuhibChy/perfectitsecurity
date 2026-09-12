@extends('layouts.app')

@section('title', 'Edit Quotation ' . ($quotation->quotation_number ?? $quotation->id) . ' — Admin Portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-page-header
        :title="'Edit Quotation ' . ($quotation->quotation_number ?? 'QUO-' . $quotation->id)"
        subtitle="Modify quotation details, validity, and commercial terms."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Quotations' => route('admin.quotations.index'), 'Edit' => null]"
    />

    <form method="POST" action="{{ route('admin.quotations.update', $quotation) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="glass-card p-6 lg:p-8 rounded-2xl shadow-xl border border-white/10 space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">Commercial Terms & Schedule</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white">
                        @foreach(['draft' => 'Draft', 'sent' => 'Sent to Client', 'accepted' => 'Accepted by Client', 'rejected' => 'Rejected', 'converted' => 'Converted to Invoice'] as $stKey => $stLabel)
                        <option value="{{ $stKey }}" {{ old('status', $quotation->status) === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Discount Amount ($)</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', $quotation->discount_amount) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $quotation->tax_rate) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white">{{ old('notes', $quotation->notes) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Terms</label>
                    <textarea name="terms" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white">{{ old('terms', $quotation->terms) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn-ghost btn-sm">Cancel</a>
                <button type="submit" class="btn-primary btn-sm px-8">Save Changes</button>
            </div>
        </div>
    </form>
</div>
@endsection
