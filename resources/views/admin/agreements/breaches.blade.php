@extends('layouts.app')
@section('page-title', 'SLA Breach Log')

@section('content')
<div class="space-y-6">
    <x-page-header title="SLA Breach Log" sys="OPS://SLA/BREACHES" />
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="breach_type" class="term-input max-w-xs"><option value="">All Types</option>
                <option value="response" {{ request('breach_type') == 'response' ? 'selected' : '' }}>Response</option>
                <option value="resolution" {{ request('breach_type') == 'resolution' ? 'selected' : '' }}>Resolution</option></select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
        </form>
    </div>
    <div class="term-panel overflow-hidden"><div class="overflow-x-auto term-table-wrap"><table class="data-table term-table">
        <thead><tr><th>Ticket #</th><th>Type</th><th>Deadline</th><th>Breached</th><th>Acknowledged</th><th class="text-right">Actions</th></tr></thead>
        <tbody>@forelse($breaches as $b)<tr>
            <td data-label="Ticket #" class="font-mono">{{ $b->ticket->ticket_number ?? '#' . $b->ticket_id }}</td>
            <td data-label="Type">{{ ucfirst($b->breach_type) }}</td>
            <td data-label="Deadline">{{ $b->deadline?->format('Y-m-d H:i') ?? '-' }}</td>
            <td data-label="Breached">{{ $b->breached_at?->format('Y-m-d H:i') }}</td>
            <td data-label="Acknowledged">{{ $b->acknowledged_at ? 'Yes' : 'No' }}</td>
            <td class="text-right" data-label="Actions">@if(!$b->acknowledged_at)
                <form method="POST" action="{{ route('admin.sla-breaches.acknowledge', $b) }}" class="inline-flex gap-2">@csrf
                    <input type="text" name="notes" placeholder="Note (optional)" class="term-input max-w-[12rem]">
                    <button class="term-btn term-btn-sm">Acknowledge</button>
                </form>@else<span class="text-sm opacity-70">{{ $b->notes }}</span>@endif</td>
        </tr>@empty<tr><td colspan="6" class="text-center py-6">No breaches logged.</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $breaches->links() }}</div></div>
</div>
@endsection
