@extends('layouts.app')
@section('page-title', 'Emergency requests')
@section('content')
<div class="space-y-6">
<x-page-header title="Emergency requests" subtitle="Critical incident channel with audited triage." sys="EMERGENCY://RESPONSE" num="17">
    <x-slot:actions>
        <a href="{{ (auth()->user()->isCustomer() ? route('portal.emergency.create') : '#') }}" class="term-btn term-btn-sm">Raise emergency</a>
    </x-slot:actions>
</x-page-header>
<div class="term-panel overflow-hidden">
    <div class="term-table-wrap !border-0">
    <table class="data-table term-table term-table-cards">
        <thead><tr><th>Ref</th><th>Severity</th><th>Status</th><th>Requester</th><th>Raised</th></tr></thead>
        <tbody>@foreach($emergencies as $e)<tr><td data-label="Ref"><a class="font-mono text-accent-soft hover:underline" href="{{ (auth()->user()->isCustomer() ? route('portal.emergency.show', $e->id) : route('admin.emergency.show', $e->id)) }}">{{ $e->reference }}</a></td><td data-label="Severity"><x-status-badge :status="$e->severity" /></td><td data-label="Status"><x-status-badge :status="$e->status" /></td><td data-label="Requester">{{ $e->requester->name }}</td><td data-label="Raised" class="font-mono text-[11px]">{{ $e->created_at->format('Y-m-d H:i') }}</td></tr>@endforeach</tbody>
    </table>
    </div>
    <div class="p-4">{{ $emergencies->links() }}</div>
</div>
</div>
@endsection
