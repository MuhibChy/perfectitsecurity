@extends('layouts.app')
@section('page-title', 'My Assets')
@section('content')
<div class="space-y-6">
    <x-page-header title="My Assets" subtitle="Systems and equipment registered under your account." sys="SUPPORT://ASSETS" />
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>Asset Tag</th><th>Name</th><th>Category</th><th>Status</th><th>Warranty</th></tr></thead>
        <tbody>@forelse($assets as $a)<tr>
            <td data-label="Tag" class="font-mono">{{ $a->asset_tag }}</td>
            <td class="font-medium" data-label="Name">{{ $a->name }}@if($a->configurationItems->count())<span class="block text-xs opacity-60">{{ $a->configurationItems->count() }} monitored system(s)</span>@endif</td>
            <td data-label="Category">{{ $a->category ?? '-' }}</td><td data-label="Status"><x-status-badge :status="$a->status" /></td>
            <td data-label="Warranty">{{ $a->warranty_expires?->format('Y-m-d') ?? '-' }}</td>
        </tr>@empty<tr><td colspan="5" class="text-center py-6">No assets registered to your account yet.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $assets->links() }}</div></div>
</div>
@endsection
