@extends('layouts.app')
@section('page-title', 'Remote Sessions')

@section('content')
<div class="space-y-6">
    <x-page-header title="Remote Support Sessions" sys="OPS://FIELD/REMOTE" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search session #..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs"><option value="">All Status</option>
                @foreach(['requested','scheduled','active','completed','expired','cancelled'] as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
        </form>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
            <thead><tr><th>Session #</th><th>Customer</th><th>Technician</th><th>Status</th><th>Consent</th><th class="text-right">Actions</th></tr></thead>
            <tbody>@forelse($sessions as $s)<tr>
                <td data-label="Session #"><a href="{{ route('admin.remote.show', $s) }}" class="font-mono text-primary-600 hover:underline">{{ $s->session_number }}</a></td>
                <td data-label="Customer">{{ $s->customer->name ?? '-' }}</td><td data-label="Technician">{{ $s->technician->name ?? '-' }}</td>
                <td data-label="Status"><x-status-badge :status="$s->status" /></td>
                <td data-label="Consent">{{ $s->consent_given ? 'Yes (' . $s->consent_at?->format('Y-m-d H:i') . ')' : 'No' }}</td>
                <td class="text-right" data-label="Actions"><a href="{{ route('admin.remote.show', $s) }}" class="term-btn term-btn-sm">Open</a></td>
            </tr>@empty<tr><td colspan="6" class="text-center py-6">No remote sessions.</td></tr>@endforelse</tbody>
        </table></div><div class="p-4">{{ $sessions->links() }}</div></div>
        <div class="term-panel p-6 h-fit">
            <h3 class="font-semibold mb-3">Schedule session</h3>
            <form method="POST" action="{{ route('admin.remote.store') }}" class="space-y-3">@csrf
                <select name="customer_id" class="term-input w-full" required><option value="">Select customer...</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                <select name="provider" class="term-input w-full"><option value="support_link">Support link</option><option value="teamviewer">TeamViewer</option><option value="anydesk">AnyDesk</option><option value="other">Other</option></select>
                <input type="url" name="session_url" placeholder="Join link (optional)" class="term-input w-full">
                <input type="datetime-local" name="scheduled_at" class="term-input w-full">
                <button class="term-btn term-btn-sm w-full">Schedule</button>
            </form>
        </div>
    </div>
</div>
@endsection
