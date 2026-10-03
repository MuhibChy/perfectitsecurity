@extends('layouts.app')

@section('title', 'Edit Invoice ' . $invoice->invoice_number . ' — Admin Portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-page-header sys="FINANCE://INVOICES"
        :title="'Edit Invoice: ' . $invoice->invoice_number"
        subtitle="Modify invoice schedule, terms, and notes."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Invoices' => route('admin.invoices.index'), 'Edit' => null]"
    />

    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="term-panel p-6 lg:p-8 shadow-xl border border-white/10 space-y-5">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">Schedule & Billing Parameters</h3>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}" required
                           class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Status</label>
                    <select name="status" class="term-input">
                        @foreach(['draft' => 'Draft', 'sent' => 'Sent to Client', 'partially_paid' => 'Partially Paid', 'paid' => 'Fully Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled'] as $stKey => $stLabel)
                        <option value="{{ $stKey }}" {{ old('status', $invoice->status) === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Internal Notes</label>
                    <textarea name="notes" rows="3" class="term-input">{{ old('notes', $invoice->notes) }}</textarea>
                </div>

                <div>
                    <label class="term-field-label">Payment Terms</label>
                    <textarea name="terms" rows="3" class="term-input">{{ old('terms', $invoice->terms) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                <a href="{{ route('admin.invoices.show', $invoice) }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                <button type="submit" class="term-btn term-btn-sm">Save Invoice</button>
            </div>
        </div>
    </form>
</div>
@endsection
