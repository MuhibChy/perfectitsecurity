@extends('layouts.app')
@section('page-title', 'New Contract')
@section('content')
<div class="glass-card p-6 max-w-2xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Contract</h2>
    <form method="POST" action="{{ route('admin.contracts.store') }}" class="space-y-4">
        @csrf
        <div><label class="form-label">Title *</label><input name="title" required class="form-input w-full" value="{{ old('title') }}">@error('title')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="form-label">Customer *</label><select name="customer_id" required class="form-input w-full"><option value="">—</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>@endforeach</select></div>
            <div><label class="form-label">Proposal</label><select name="proposal_id" class="form-input w-full"><option value="">—</option>@foreach($proposals as $p)<option value="{{ $p->id }}">{{ $p->proposal_number }} — {{ $p->title }}</option>@endforeach</select></div>
            <div><label class="form-label">Status *</label><select name="status" class="form-input w-full">@foreach(['draft','sent','active','expired','terminated'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="form-label">Value</label><input name="value" type="number" step="0.01" min="0" class="form-input w-full" value="{{ old('value', 0) }}"></div>
            <div><label class="form-label">Currency</label><input name="currency" maxlength="3" class="form-input w-full" value="{{ old('currency', 'USD') }}"></div>
            <div><label class="form-label">Start date</label><input name="start_date" type="date" class="form-input w-full"></div>
            <div><label class="form-label">End date</label><input name="end_date" type="date" class="form-input w-full"></div>
        </div>
        <div><label class="form-label">Body</label><textarea name="body" rows="5" class="form-input w-full">{{ old('body') }}</textarea></div>
        <button class="btn-primary">Create Contract</button>
    </form>
</div>
@endsection
