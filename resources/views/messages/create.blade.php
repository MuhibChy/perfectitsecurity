@extends('layouts.app')
@section('page-title', 'New message')
@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
<x-page-header title="New message" subtitle="Compose a direct message." sys="COMMS://MESSAGES" />
<div class="term-panel p-6">
    <form method="POST" action="{{ (auth()->user()->isCustomer() ? route('portal.messages.store') : route('admin.messages.store')) }}" class="space-y-4">
        @csrf
        <div>
            <label class="term-field-label">Recipient</label>
            <select name="recipient_id" class="term-input">@foreach($candidates as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->role }})</option>@endforeach</select>
        </div>
        <div>
            <label class="term-field-label">Subject</label>
            <input name="subject" class="term-input" maxlength="255" />
        </div>
        <div>
            <label class="term-field-label">Message</label>
            <textarea name="body" required rows="5" class="term-input" maxlength="5000"></textarea>
        </div>
        @if(auth()->user()->isStaff())
        <label class="text-sm text-slate-600 dark:text-term-800"><input type="checkbox" name="is_internal" value="1" class="accent-emerald-500" /> Internal staff note (never visible to customers)</label>
        @endif
        <button class="term-btn term-btn-sm">Send</button>
    </form>
</div>
</div>
@endsection
