@extends('layouts.app')
@section('page-title', 'Changes')

@section('content')
<div class="space-y-6">
    <x-page-header title="Change Requests" sys="OPS://CHANGES" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search changes..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs"><option value="">All Status</option>
                @foreach(['requested','assessed','approved','scheduled','implementing','completed','failed','cancelled'] as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select>
            <select name="risk" class="term-input max-w-xs"><option value="">All Risk</option>
                @foreach(['low','medium','high'] as $r)<option value="{{ $r }}" {{ request('risk') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>@endforeach</select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
            <a href="{{ route('admin.changes.create') }}" class="term-btn term-btn-sm">New Change</a>
        </form>
    </div>
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>Change #</th><th>Title</th><th>Type</th><th>Risk</th><th>Status</th><th>Scheduled</th><th class="text-right">Actions</th></tr></thead>
        <tbody>@forelse($changes as $c)<tr>
            <td data-label="Change #"><a href="{{ route('admin.changes.show', $c) }}" class="font-mono text-primary-600 hover:underline">{{ $c->change_number }}</a></td>
            <td class="font-medium max-w-xs truncate" data-label="Title">{{ $c->title }}</td>
            <td data-label="Type">{{ ucfirst($c->type) }}</td><td data-label="Risk"><x-status-badge :status="$c->risk" /></td>
            <td data-label="Status"><x-status-badge :status="$c->status" /></td>
            <td data-label="Scheduled">{{ $c->scheduled_start?->format('Y-m-d H:i') ?? '-' }}</td>
            <td class="text-right" data-label="Actions"><a href="{{ route('admin.changes.show', $c) }}" class="term-btn term-btn-sm">Open</a></td>
        </tr>@empty<tr><td colspan="7" class="text-center py-6">No change requests.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $changes->links() }}</div></div>
</div>
@endsection
