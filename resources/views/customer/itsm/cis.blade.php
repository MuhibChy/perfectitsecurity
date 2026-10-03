@extends('layouts.app')
@section('page-title', 'My Systems')
@section('content')
<div class="space-y-6">
    <x-page-header title="My Systems" subtitle="Monitored servers, workstations and services." sys="SUPPORT://SYSTEMS" />
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>CI #</th><th>Name</th><th>Type</th><th>Status</th><th>Criticality</th></tr></thead>
        <tbody>@forelse($cis as $ci)<tr>
            <td data-label="CI #" class="font-mono">{{ $ci->ci_number }}</td>
            <td class="font-medium" data-label="Name">{{ $ci->name }}@if($ci->asset)<span class="block text-xs opacity-60">Asset {{ $ci->asset->asset_tag }}</span>@endif</td>
            <td data-label="Type">{{ ucfirst($ci->ci_type) }}</td><td data-label="Status"><x-status-badge :status="$ci->status" /></td>
            <td data-label="Criticality"><x-status-badge :status="$ci->criticality" /></td>
        </tr>@empty<tr><td colspan="5" class="text-center py-6">No systems registered to your account yet.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $cis->links() }}</div></div>
</div>
@endsection
