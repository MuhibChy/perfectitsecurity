@extends('layouts.app')
@section('page-title', 'Site Visits')
@section('content')
<div class="space-y-6">
    <x-page-header title="Site Visits" subtitle="Scheduled onsite visits and completion confirmation." sys="SUPPORT://VISITS" />
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>Visit #</th><th>Scheduled</th><th>Technician</th><th>Status</th><th>Confirmed</th><th class="text-right">Actions</th></tr></thead>
        <tbody>@forelse($visits as $v)<tr>
            <td data-label="Visit #" class="font-mono">{{ $v->visit_number }}</td>
            <td data-label="Scheduled">{{ $v->scheduled_at?->format('Y-m-d H:i') ?? '-' }}</td>
            <td data-label="Technician">{{ $v->technician->name ?? '-' }}</td>
            <td data-label="Status"><x-status-badge :status="$v->status" /></td>
            <td data-label="Confirmed">{{ $v->customer_confirmed_at ? 'Yes' : 'No' }}</td>
            <td class="text-right" data-label="Actions">@if($v->status === 'completed' && !$v->customer_confirmed_at)
                <form method="POST" action="{{ route('portal.itsm.visits.confirm', $v) }}" class="inline-flex gap-2">@csrf
                    <input type="text" name="customer_signature_name" required placeholder="Your name (signature)" class="term-input max-w-[12rem]">
                    <button class="term-btn term-btn-sm">Confirm</button>
                </form>@else<span class="text-sm opacity-60">—</span>@endif</td>
        </tr>@empty<tr><td colspan="6" class="text-center py-6">No site visits scheduled.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $visits->links() }}</div></div>
</div>
@endsection
