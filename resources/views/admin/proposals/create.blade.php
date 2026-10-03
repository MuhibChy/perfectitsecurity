@extends('layouts.app')
@section('page-title', 'New Proposal')
@section('content')

    <x-page-header title="New Proposal" sys="OPS://PROPOSALS" />
<div class="term-panel p-6 max-w-3xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Proposal</h2>
    <form method="POST" action="{{ route('admin.proposals.store') }}" class="space-y-4">
        @csrf
        <div><label class="term-field-label">Title *</label><input name="title" required class="term-input" value="{{ old('title') }}">@error('title')<p class="term-error">{{ $message }}</p>@enderror</div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="term-field-label">Customer</label><select name="customer_id" class="term-input"><option value="">—</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>@endforeach</select></div>
            <div><label class="term-field-label">Lead</label><select name="lead_id" class="term-input"><option value="">—</option>@foreach($leads as $l)<option value="{{ $l->id }}">{{ $l->lead_number }} — {{ $l->name }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Subtotal</label><input name="subtotal" type="number" step="0.01" min="0" class="term-input" value="{{ old('subtotal', 0) }}"></div>
            <div><label class="term-field-label">Tax rate %</label><input name="tax_rate" type="number" step="0.01" min="0" class="term-input" value="{{ old('tax_rate', 0) }}"></div>
            <div><label class="term-field-label">Currency</label><input name="currency" maxlength="3" class="term-input" value="{{ old('currency', 'USD') }}"></div>
            <div><label class="term-field-label">Valid until</label><input name="valid_until" type="date" class="term-input" value="{{ old('valid_until') }}"></div>
        </div>
        <div><label class="term-field-label">Scope of work</label><textarea name="scope_of_work" rows="3" class="term-input">{{ old('scope_of_work') }}</textarea></div>
        <div><label class="term-field-label">Deliverables</label><textarea name="deliverables" rows="3" class="term-input">{{ old('deliverables') }}</textarea></div>
        <div><label class="term-field-label">Timeline</label><textarea name="timeline" rows="2" class="term-input">{{ old('timeline') }}</textarea></div>
        <div><label class="term-field-label">Terms</label><textarea name="terms" rows="2" class="term-input">{{ old('terms') }}</textarea></div>
        <button class="term-btn">Create Proposal</button>
    </form>
</div>
@endsection
