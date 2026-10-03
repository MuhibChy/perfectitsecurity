@extends('layouts.app')
@section('page-title', 'Service Agreements')

@section('content')
<div class="space-y-6">
    <x-page-header title="Service Agreements" sys="OPS://AGREEMENTS" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search agreements..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs"><option value="">All Status</option>
                @foreach(['draft','active','suspended','expired','terminated'] as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
            <a href="{{ route('admin.sla-breaches.index') }}" class="term-btn term-btn-sm">SLA Breach Log</a>
            <a href="{{ route('admin.approvals.index') }}" class="term-btn term-btn-sm">Approvals</a>
        </form>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
            <thead><tr><th>Agreement #</th><th>Title</th><th>Customer</th><th>Status</th><th>Ends</th><th class="text-right">Actions</th></tr></thead>
            <tbody>@forelse($agreements as $a)<tr>
                <td data-label="Agreement #"><a href="{{ route('admin.agreements.show', $a) }}" class="font-mono text-primary-600 hover:underline">{{ $a->agreement_number }}</a></td>
                <td class="font-medium" data-label="Title">{{ $a->title }}</td><td data-label="Customer">{{ $a->customer->name ?? '-' }}</td>
                <td data-label="Status"><x-status-badge :status="$a->status" /></td>
                <td data-label="Ends">{{ $a->ends_at?->format('Y-m-d') ?? '-' }}</td>
                <td class="text-right" data-label="Actions"><a href="{{ route('admin.agreements.show', $a) }}" class="term-btn term-btn-sm">Open</a></td>
            </tr>@empty<tr><td colspan="6" class="text-center py-6">No service agreements.</td></tr>@endforelse</tbody>
        </table></div><div class="p-4">{{ $agreements->links() }}</div></div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">New agreement</h3>
            <form method="POST" action="{{ route('admin.agreements.store') }}" class="space-y-3">@csrf
                <select name="customer_id" class="term-input w-full" required><option value="">Select customer...</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                <input type="text" name="title" required placeholder="Agreement title" class="term-input w-full">
                <textarea name="scope" rows="3" placeholder="Scope of covered services" class="term-input w-full"></textarea>
                <input type="text" name="coverage_hours" placeholder="Coverage hours (e.g. Mon–Fri 9–17)" class="term-input w-full">
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="response_target_minutes" min="1" placeholder="Response (min)" class="term-input">
                    <input type="number" name="resolution_target_minutes" min="1" placeholder="Resolution (min)" class="term-input">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="starts_at" class="term-input"><input type="date" name="ends_at" class="term-input">
                </div>
                <select name="status" class="term-input w-full"><option value="draft">Draft</option><option value="active">Active</option><option value="suspended">Suspended</option></select>
                <button class="term-btn term-btn-sm w-full">Record Agreement</button>
            </form>
        </div>
    </div>
</div>
@endsection
