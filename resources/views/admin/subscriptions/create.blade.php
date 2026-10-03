@extends('layouts.app')
@section('page-title', 'New Subscription')
@section('content')

    <x-page-header title="New Subscription" sys="SYSTEM://SUBSCRIPTIONS" />
<div class="term-panel p-6 max-w-2xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Subscription</h2>
    <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="space-y-4">
        @csrf
        <div><label class="term-field-label">Name *</label><input name="name" required class="term-input" value="{{ old('name') }}">@error('name')<p class="term-error">{{ $message }}</p>@enderror</div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="term-field-label">Customer *</label><select name="customer_id" required class="term-input"><option value="">—</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>@endforeach</select></div>
            <div><label class="term-field-label">Service</label><select name="service_id" class="term-input"><option value="">—</option>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Interval *</label><select name="interval" class="term-input"><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>
            <div><label class="term-field-label">Amount *</label><input name="amount" type="number" step="0.01" min="0" required class="term-input" value="{{ old('amount') }}">@error('amount')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">Currency *</label><input name="currency" maxlength="3" required class="term-input" value="{{ old('currency', 'USD') }}"></div>
            <div><label class="term-field-label">Country (auto tax)</label><select name="country_id" class="term-input"><option value="">—</option>@foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Starts at</label><input name="starts_at" type="date" class="term-input"></div>
        </div>
        <button class="term-btn">Create Subscription</button>
    </form>
</div>
@endsection
