@extends('layouts.app')
@section('page-title', 'Configuration Items')

@section('content')
<div class="space-y-6">
    <x-page-header title="Configuration Items (CMDB Lite)" sys="OPS://CMDB" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search CIs..." class="term-input max-w-xs">
            <select name="ci_type" class="term-input max-w-xs"><option value="">All Types</option>
                @foreach(['server','workstation','network','application','cloud','dns','backup','other'] as $t)<option value="{{ $t }}" {{ request('ci_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>@endforeach</select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
        </form>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
            <thead><tr><th>CI #</th><th>Name</th><th>Type</th><th>Customer</th><th>Status</th></tr></thead>
            <tbody>@forelse($cis as $ci)<tr>
                <td data-label="CI #"><a href="{{ route('admin.ci.show', $ci) }}" class="font-mono text-primary-600 hover:underline">{{ $ci->ci_number }}</a></td>
                <td class="font-medium" data-label="Name">{{ $ci->name }}</td><td data-label="Type">{{ ucfirst($ci->ci_type) }}</td>
                <td data-label="Customer">{{ $ci->customer->name ?? '-' }}</td><td data-label="Status"><x-status-badge :status="$ci->status" /></td>
            </tr>@empty<tr><td colspan="5" class="text-center py-6">No configuration items.</td></tr>@endforelse</tbody>
        </table></div><div class="p-4">{{ $cis->links() }}</div></div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">New CI</h3>
            <form method="POST" action="{{ route('admin.ci.store') }}" class="space-y-3">@csrf
                <input type="text" name="name" required placeholder="CI name" class="term-input w-full">
                <select name="ci_type" class="term-input w-full">@foreach(['server','workstation','network','application','cloud','dns','backup','other'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select>
                <select name="customer_id" class="term-input w-full"><option value="">No customer</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                <input type="text" name="identifier" placeholder="IP / hostname / URL" class="term-input w-full">
                <div class="grid grid-cols-3 gap-2">
                    <select name="environment" class="term-input"><option value="production">Prod</option><option value="staging">Staging</option><option value="development">Dev</option><option value="test">Test</option></select>
                    <select name="status" class="term-input"><option value="active">Active</option><option value="maintenance">Maint.</option><option value="decommissioned">Retired</option></select>
                    <select name="criticality" class="term-input"><option value="medium">Med</option><option value="low">Low</option><option value="high">High</option><option value="critical">Crit</option></select>
                </div>
                <button class="term-btn term-btn-sm w-full">Record CI</button>
            </form>
        </div>
    </div>
</div>
@endsection
