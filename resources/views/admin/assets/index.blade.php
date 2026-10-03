@extends('layouts.app')
@section('page-title', 'Assets')

@section('content')
<div class="space-y-6">
    <x-page-header title="IT Assets" sys="OPS://ASSETS" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tag, name, serial..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs"><option value="">All Status</option>
                @foreach(['in_stock','deployed','maintenance','retired'] as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>@endforeach</select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
            <a href="{{ route('admin.assets.create') }}" class="term-btn term-btn-sm">New Asset</a>
            <a href="{{ route('admin.ci.index') }}" class="term-btn term-btn-sm">Configuration Items</a>
        </form>
    </div>
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>Asset Tag</th><th>Name</th><th>Category</th><th>Customer</th><th>Status</th><th>Warranty</th><th class="text-right">Actions</th></tr></thead>
        <tbody>@forelse($assets as $a)<tr>
            <td data-label="Tag"><a href="{{ route('admin.assets.show', $a) }}" class="font-mono text-primary-600 hover:underline">{{ $a->asset_tag }}</a></td>
            <td class="font-medium" data-label="Name">{{ $a->name }}</td><td data-label="Category">{{ $a->category ?? '-' }}</td>
            <td data-label="Customer">{{ $a->customer->name ?? '-' }}</td><td data-label="Status"><x-status-badge :status="$a->status" /></td>
            <td data-label="Warranty">{{ $a->warranty_expires?->format('Y-m-d') ?? '-' }}</td>
            <td class="text-right" data-label="Actions"><a href="{{ route('admin.assets.show', $a) }}" class="term-btn term-btn-sm">Open</a></td>
        </tr>@empty<tr><td colspan="7" class="text-center py-6">No assets recorded.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $assets->links() }}</div></div>
</div>
@endsection
