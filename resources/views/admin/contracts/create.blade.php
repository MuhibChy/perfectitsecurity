@extends('layouts.app')
@section('page-title', 'New Contract')
@section('content')

    <x-page-header title="New Contract" sys="OPS://CONTRACTS" />
<div class="term-panel p-6 max-w-2xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Contract</h2>
    <form method="POST" action="{{ route('admin.contracts.store') }}" class="space-y-4">
        @csrf
        <div><label class="term-field-label">Title *</label><input name="title" required class="term-input" value="{{ old('title') }}">@error('title')<p class="term-error">{{ $message }}</p>@enderror</div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="term-field-label">Customer *</label><select name="customer_id" required class="term-input"><option value="">—</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>@endforeach</select></div>
            <div><label class="term-field-label">Proposal</label><select name="proposal_id" class="term-input"><option value="">—</option>@foreach($proposals as $p)<option value="{{ $p->id }}">{{ $p->proposal_number }} — {{ $p->title }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Status *</label><select name="status" class="term-input">@foreach(['draft','sent','active','expired','terminated'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Value</label><input name="value" type="number" step="0.01" min="0" class="term-input" value="{{ old('value', 0) }}"></div>
            <div><label class="term-field-label">Currency</label><input name="currency" maxlength="3" class="term-input" value="{{ old('currency', 'USD') }}"></div>
            <div><label class="term-field-label">Start date</label><input name="start_date" type="date" class="term-input"></div>
            <div><label class="term-field-label">End date</label><input name="end_date" type="date" class="term-input"></div>
        </div>
        <div><label class="term-field-label">Body</label><textarea name="body" rows="5" class="term-input">{{ old('body') }}</textarea></div>
        <button class="term-btn">Create Contract</button>
    </form>
</div>
@endsection
