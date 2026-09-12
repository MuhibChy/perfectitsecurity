@extends('layouts.app')
@section('page-title', 'Add Lead')
@section('content')
<div class="glass-card p-6 max-w-2xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Lead</h2>
    <form method="POST" action="{{ route('admin.leads.store') }}" class="space-y-4">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="form-label">Name *</label><input name="name" required class="form-input w-full" value="{{ old('name') }}">@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Email</label><input name="email" type="email" class="form-input w-full" value="{{ old('email') }}">@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Phone</label><input name="phone" class="form-input w-full" value="{{ old('phone') }}"></div>
            <div><label class="form-label">Company</label><input name="company_name" class="form-input w-full" value="{{ old('company_name') }}"></div>
            <div><label class="form-label">Status *</label><select name="status" class="form-input w-full">@foreach(['new','contacted','qualified','proposal','won','lost'] as $s)<option value="{{ $s }}" @selected(old('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="form-label">Priority *</label><select name="priority" class="form-input w-full">@foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ ucfirst($p) }}</option>@endforeach</select></div>
            <div><label class="form-label">Estimated value</label><input name="estimated_value" type="number" step="0.01" min="0" class="form-input w-full" value="{{ old('estimated_value') }}"></div>
            <div><label class="form-label">Currency</label><input name="currency" maxlength="3" class="form-input w-full" value="{{ old('currency', 'USD') }}"></div>
            <div><label class="form-label">Assign to</label><select name="assigned_to" class="form-input w-full"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="form-label">Country</label><select name="country_id" class="form-input w-full"><option value="">—</option>@foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        </div>
        <div><label class="form-label">Source</label><input name="source" class="form-input w-full" value="{{ old('source', 'manual') }}"></div>
        <div><label class="form-label">Notes</label><textarea name="notes" rows="3" class="form-input w-full">{{ old('notes') }}</textarea></div>
        <div><label class="form-label">Next follow-up</label><input name="next_follow_up_at" type="datetime-local" class="form-input w-full"></div>
        <button class="btn-primary">Save Lead</button>
    </form>
</div>
@endsection
