@extends('layouts.app')
@section('page-title', 'Tickets')

@section('content')
<div class="space-y-6">
    <x-page-header title="Tickets" sys="OPS://TICKETS" />
    {{-- Filters --}}
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="term-input max-w-xs">
            <select name="status" class="term-input max-w-xs">
                <option value="">All Status</option>
                @foreach(['new','open','assigned','in_progress','waiting_customer','waiting_third_party','escalated','resolved','closed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="term-input max-w-xs">
                <option value="">All Priority</option>
                @foreach(['low','medium','high','urgent','critical'] as $p)
                <option value="{{ $p }}" {{ request('priority') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <button type="submit" class="term-btn term-btn-sm">Filter</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>Customer</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td data-label="Ticket #"><a href="{{ route('admin.tickets.show', $ticket) }}" class="font-mono text-primary-600 hover:underline">{{ $ticket->ticket_number }}</a></td>
                        <td class="font-medium max-w-xs truncate" data-label="Subject">{{ $ticket->subject }}</td>
                        <td data-label="Customer">{{ $ticket->customer->name ?? '-' }}</td>
                        <td data-label="Priority"><x-status-badge :status="$ticket->priority" /></td>
                        <td data-label="Status"><x-status-badge :status="$ticket->status" /></td>
                        <td data-label="Assigned To">{{ $ticket->assignee->name ?? 'Unassigned' }}</td>
                        <td class="text-gray-500" data-label="Created">{{ $ticket->created_at->diffForHumans() }}</td>
                        <td data-label="Actions"><a href="{{ route('admin.tickets.show', $ticket) }}" class="term-btn term-btn-ghost term-btn-sm">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-500 py-8">No tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection
