@extends('layouts.app')
@section('page-title', 'New Subscription')
@section('content')
<div class="glass-card p-6 max-w-2xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Subscription</h2>
    <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="space-y-4">
        @csrf
        <div><label class="form-label">Name *</label><input name="name" required class="form-input w-full" value="{{ old('name') }}">@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="form-label">Customer *</label><select name="customer_id" required class="form-input w-full"><option value="">—</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>@endforeach</select></div>
            <div><label class="form-label">Service</label><select name="service_id" class="form-input w-full"><option value="">—</option>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div><label class="form-label">Interval *</label><select name="interval" class="form-input w-full"><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>
            <div><label class="form-label">Amount *</label><input name="amount" type="number" step="0.01" min="0" required class="form-input w-full" value="{{ old('amount') }}">@error('amount')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Currency *</label><input name="currency" maxlength="3" required class="form-input w-full" value="{{ old('currency', 'USD') }}"></div>
            <div><label class="form-label">Country (auto tax)</label><select name="country_id" class="form-input w-full"><option value="">—</option>@foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div><label class="form-label">Starts at</label><input name="starts_at" type="date" class="form-input w-full"></div>
        </div>
        <button class="btn-primary">Create Subscription</button>
    </form>
</div>
@endsection
