@extends('layouts.app')

@section('title', 'Edit Quotation ' . ($quotation->quotation_number ?? $quotation->id) . ' — Admin Portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-page-header sys="FINANCE://QUOTATIONS"
        :title="'Edit Quotation ' . ($quotation->quotation_number ?? 'QUO-' . $quotation->id)"
        subtitle="Modify quotation details, validity, and commercial terms."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Quotations' => route('admin.quotations.index'), 'Edit' => null]"
    />

    <form method="POST" action="{{ route('admin.quotations.update', $quotation) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="term-panel p-6 lg:p-8 shadow-xl border border-white/10 space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">Commercial Terms & Schedule</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '') }}" required
                           class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Status</label>
                    <select name="status" class="term-input">
                        @foreach(['draft' => 'Draft', 'sent' => 'Sent to Client', 'accepted' => 'Accepted by Client', 'rejected' => 'Rejected', 'converted' => 'Converted to Invoice'] as $stKey => $stLabel)
                        <option value="{{ $stKey }}" {{ old('status', $quotation->status) === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Discount Amount ($)</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', $quotation->discount_amount) }}"
                           class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $quotation->tax_rate) }}"
                           class="term-input">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Notes</label>
                    <textarea name="notes" rows="3" class="term-input">{{ old('notes', $quotation->notes) }}</textarea>
                </div>

                <div>
                    <label class="term-field-label">Terms</label>
                    <textarea name="terms" rows="3" class="term-input">{{ old('terms', $quotation->terms) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                <a href="{{ route('admin.quotations.show', $quotation) }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                <button type="submit" class="term-btn term-btn-sm">Save Changes</button>
            </div>
        </div>
    </form>
</div>
@endsection
