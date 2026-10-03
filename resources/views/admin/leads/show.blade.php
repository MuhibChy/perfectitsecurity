@extends('layouts.app')
@section('page-title', 'Lead ' . $lead->lead_number)
@section('content')

    <x-page-header title="Leads" sys="OPS://LEADS" />
<div class="grid lg:grid-cols-3 gap-6">
    <div class="term-panel p-6 lg:col-span-2">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $lead->name }} <span class="text-sm font-normal text-gray-500">{{ $lead->lead_number }}</span></h2>
        <p class="text-sm text-gray-500">{{ $lead->company_name }} · {{ $lead->email }} · {{ $lead->phone }} · {{ $lead->country?->name }}</p>
        <div class="mt-3 flex items-center gap-2">
            @if($lead->customer_id)
            <span class="term-tag">Customer #{{ $lead->customer_id }}</span>
            @else
            <form method="POST" action="{{ route('admin.leads.convert', $lead) }}" onsubmit="return confirm('Convert this lead into a customer account?')">
                @csrf
                <button class="term-btn term-btn-ghost term-btn-sm">Convert to Customer</button>
            </form>
            @endif
        </div>
        <div class="mt-4 text-sm whitespace-pre-line">{{ $lead->notes }}</div>
        <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="grid sm:grid-cols-2 gap-4 mt-6">
            @csrf @method('PUT')
            <div><label class="term-field-label">Status</label><select name="status" class="term-input">@foreach(['new','contacted','qualified','proposal','won','lost'] as $s)<option value="{{ $s }}" @selected($lead->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Priority</label><select name="priority" class="term-input">@foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected($lead->priority === $p)>{{ ucfirst($p) }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Assign to</label><select name="assigned_to" class="term-input"><option value="">Unassigned</option>@foreach($staff as $u)<option value="{{ $u->id }}" @selected($lead->assigned_to == $u->id)>{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="term-field-label">Estimated value</label><input name="estimated_value" type="number" step="0.01" class="term-input" value="{{ $lead->estimated_value }}"></div>
            <div><label class="term-field-label">Notes</label><input name="notes" class="term-input" value="{{ $lead->notes }}"></div>
            <div><label class="term-field-label">Next follow-up</label><input name="next_follow_up_at" type="datetime-local" class="term-input" value="{{ $lead->next_follow_up_at?->format('Y-m-d\TH:i') }}"></div>
            <div class="sm:col-span-2"><button class="term-btn term-btn-sm">Update Lead</button></div>
        </form>
    </div>
    <div class="term-panel p-6">
        <h3 class="font-bold mb-3">Activity</h3>
        <form method="POST" action="{{ route('admin.leads.activities.store', $lead) }}" class="space-y-2 mb-4">
            @csrf
            <select name="type" class="term-input">@foreach(['note','call','email','meeting'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select>
            <input name="subject" placeholder="Subject" class="term-input">
            <textarea name="body" required rows="3" placeholder="Log a call, email or note…" class="term-input"></textarea>
            <button class="term-btn term-btn-ghost term-btn-sm">Log activity</button>
        </form>
        <div class="space-y-3 text-sm">
            @forelse($lead->activities as $a)
                <div class="border-t border-gray-100 dark:border-gray-800 pt-2"><div class="font-semibold">{{ $a->subject ?? ucfirst($a->type) }} <span class="text-xs text-gray-500">{{ $a->created_at?->diffForHumans() }} · {{ $a->user?->name }}</span></div><div class="text-gray-600 dark:text-gray-300">{{ $a->body }}</div></div>
            @empty
                <p class="text-gray-500">No activity yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
