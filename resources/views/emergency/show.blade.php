@extends('layouts.app')
@section('page-title', 'Emergency detail')
@section('content')
<div class="space-y-6">
<x-page-header :title="$emergency->reference" :subtitle="'Severity ' . $emergency->severity . ' · ' . $emergency->status" sys="EMERGENCY://RESPONSE" :breadcrumbs="['Emergency' => (auth()->user()->isCustomer() ? route('portal.emergency.index') : route('admin.emergency.index')), $emergency->reference => null]">
    <x-status-badge :status="$emergency->status" />
    <x-status-badge :status="$emergency->severity" />
</x-page-header>
<div class="term-panel p-6">
    <p class="term-hint">Raised by {{ $emergency->requester->name }} at {{ $emergency->created_at->format('Y-m-d H:i') }} · Assignee: {{ $emergency->assignee?->name ?? '—' }}</p>
    <p class="mt-3 text-sm text-slate-600 dark:text-term-800 whitespace-pre-wrap">{{ $emergency->description }}</p>
    @if(auth()->user()->isStaff())
    <form method="POST" action="{{ route('admin.emergency.transition', $emergency->id) }}" class="mt-4 flex flex-wrap gap-2">@csrf
        <select name="to" class="term-input w-auto"><option>acknowledged</option><option>assigned</option><option>in_progress</option><option>resolved</option><option>closed</option></select>
        <select name="assignee_id" class="term-input w-auto"><option value="">— assignee —</option>@foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
        <button class="term-btn term-btn-sm">Transition</button>
    </form>
    @endif
</div>
</div>
@endsection
