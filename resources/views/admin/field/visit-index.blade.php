@extends('layouts.app')
@section('page-title', 'Site Visits')

@section('content')
<div class="space-y-6">
    <x-page-header title="Onsite Visits" sys="OPS://FIELD/VISITS" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search visit #..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs"><option value="">All Status</option>
                @foreach(['scheduled','en_route','on_site','completed','cancelled'] as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>@endforeach</select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
        </form>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
            <thead><tr><th>Visit #</th><th>Customer</th><th>Technician</th><th>Scheduled</th><th>Status</th><th>Confirmed</th><th class="text-right">Actions</th></tr></thead>
            <tbody>@forelse($visits as $v)<tr>
                <td data-label="Visit #"><a href="{{ route('admin.visits.show', $v) }}" class="font-mono text-primary-600 hover:underline">{{ $v->visit_number }}</a></td>
                <td data-label="Customer">{{ $v->customer->name ?? '-' }}</td><td data-label="Technician">{{ $v->technician->name ?? '-' }}</td>
                <td data-label="Scheduled">{{ $v->scheduled_at?->format('Y-m-d H:i') ?? '-' }}</td>
                <td data-label="Status"><x-status-badge :status="$v->status" /></td>
                <td data-label="Confirmed">{{ $v->customer_confirmed_at ? 'Yes' : 'No' }}</td>
                <td class="text-right" data-label="Actions"><a href="{{ route('admin.visits.show', $v) }}" class="term-btn term-btn-sm">Open</a></td>
            </tr>@empty<tr><td colspan="7" class="text-center py-6">No site visits.</td></tr>@endforelse</tbody>
        </table></div><div class="p-4">{{ $visits->links() }}</div></div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">Schedule visit</h3>
            <form method="POST" action="{{ route('admin.visits.store') }}" class="space-y-3">@csrf
                <select name="customer_id" class="term-input w-full" required><option value="">Select customer...</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                <textarea name="address" rows="2" required placeholder="Site address" class="term-input w-full"></textarea>
                <input type="datetime-local" name="scheduled_at" class="term-input w-full">
                <button class="term-btn term-btn-sm w-full">Schedule</button>
            </form>
        </div>
    </div>
</div>
@endsection
