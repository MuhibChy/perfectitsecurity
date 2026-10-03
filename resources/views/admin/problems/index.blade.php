@extends('layouts.app')
@section('page-title', 'Problems')

@section('content')
<div class="space-y-6">
    <x-page-header title="Problems" sys="OPS://PROBLEMS" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search problems..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs">
                <option value="">All Status</option>
                @foreach(['open','investigating','known_error','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
            <a href="{{ route('admin.problems.create') }}" class="term-btn term-btn-sm">New Problem</a>
        </form>
    </div>
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
                <thead><tr><th>Problem #</th><th>Title</th><th>Priority</th><th>Status</th><th>Assignee</th><th>Incidents</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($problems as $problem)
                    <tr>
                        <td data-label="Problem #"><a href="{{ route('admin.problems.show', $problem) }}" class="font-mono text-primary-600 hover:underline">{{ $problem->problem_number }}</a></td>
                        <td class="font-medium max-w-xs truncate" data-label="Title">{{ $problem->title }}</td>
                        <td data-label="Priority"><x-status-badge :status="$problem->priority" /></td>
                        <td data-label="Status"><x-status-badge :status="$problem->status" /></td>
                        <td data-label="Assignee">{{ $problem->assignee->name ?? '-' }}</td>
                        <td data-label="Incidents">{{ $problem->tickets_count ?? $problem->tickets()->count() }}</td>
                        <td class="text-right" data-label="Actions"><a href="{{ route('admin.problems.show', $problem) }}" class="term-btn term-btn-sm">Open</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-6">No problems recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $problems->links() }}</div>
    </div>
</div>
@endsection
