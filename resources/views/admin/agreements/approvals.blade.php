@extends('layouts.app')
@section('page-title', 'Approvals')

@section('content')
<div class="space-y-6">
    <x-page-header title="Approval Queue" sys="OPS://APPROVALS" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="term-input max-w-xs"><option value="">All Status</option>
                @foreach(['pending','approved','rejected','cancelled'] as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach</select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
        </form>
    </div>
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>Approval #</th><th>Type</th><th>Subject</th><th>Requested by</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
        <tbody>@forelse($approvals as $a)<tr>
            <td data-label="Approval #" class="font-mono">{{ $a->approval_number }}</td>
            <td data-label="Type">{{ ucfirst(str_replace('_', ' ', $a->type)) }}</td>
            <td data-label="Subject" class="max-w-xs truncate">{{ class_basename($a->approvable_type) }} #{{ $a->approvable_id }}</td>
            <td data-label="Requested by">{{ $a->requester->name ?? '-' }}</td>
            <td data-label="Status"><x-status-badge :status="$a->status" /></td>
            <td class="text-right" data-label="Actions">@if($a->status === 'pending')
                <form method="POST" action="{{ route('admin.approvals.decide', $a) }}" class="inline-flex gap-2">@csrf
                    <select name="decision" class="term-input"><option value="approved">Approve</option><option value="rejected">Reject</option><option value="cancelled">Cancel</option></select>
                    <button class="term-btn term-btn-sm">Decide</button>
                </form>@else<span class="text-sm opacity-70">{{ $a->decided_at?->format('Y-m-d H:i') }}</span>@endif</td>
        </tr>@empty<tr><td colspan="6" class="text-center py-6">No approvals queued.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $approvals->links() }}</div></div>
</div>
@endsection
