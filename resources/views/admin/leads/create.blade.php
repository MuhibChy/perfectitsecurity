@extends('layouts.app')
@section('page-title', 'Add Lead')
@section('content')

    <x-page-header title="Add Lead" sys="OPS://LEADS" />
<div class="term-panel p-6 max-w-2xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Lead</h2>
    <form method="POST" action="{{ route('admin.leads.store') }}" class="space-y-4">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="term-field-label">Name *</label><input name="name" required class="term-input" value="{{ old('name') }}">@error('name')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">Email</label><input name="email" type="email" class="term-input" value="{{ old('email') }}">@error('email')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">Phone</label><input name="phone" class="term-input" value="{{ old('phone') }}"></div>
            <div><label class="term-field-label">Company</label><input name="company_name" class="term-input" value="{{ old('company_name') }}"></div>
            <div><label class="term-field-label">Status *</label><select name="status" class="term-input">@foreach(['new','contacted','qualified','proposal','won','lost'] as $s)<option value="{{ $s }}" @selected(old('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Priority *</label><select name="priority" class="term-input">@foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ ucfirst($p) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Estimated value</label><input name="estimated_value" type="number" step="0.01" min="0" class="term-input" value="{{ old('estimated_value') }}"></div>
            <div><label class="term-field-label">Currency</label><input name="currency" maxlength="3" class="term-input" value="{{ old('currency', 'USD') }}"></div>
            <div><label class="term-field-label">Assign to</label><select name="assigned_to" class="term-input"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Country</label><select name="country_id" class="term-input"><option value="">—</option>@foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        </div>
        <div><label class="term-field-label">Source</label><input name="source" class="term-input" value="{{ old('source', 'manual') }}"></div>
        <div><label class="term-field-label">Notes</label><textarea name="notes" rows="3" class="term-input">{{ old('notes') }}</textarea></div>
        <div><label class="term-field-label">Next follow-up</label><input name="next_follow_up_at" type="datetime-local" class="term-input"></div>
        <button class="term-btn">Save Lead</button>
    </form>
</div>
@endsection
