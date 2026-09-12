@extends('layouts.app')
@section('page-title', 'Tickets')

@section('content')
<div class="space-y-6">
    {{-- Filters --}}
    <div class="glass-card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="input-field max-w-xs">
            <select name="status" class="input-field max-w-xs">
                <option value="">All Status</option>
                @foreach(['new','open','assigned','in_progress','waiting_customer','waiting_third_party','escalated','resolved','closed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                @endforeach
            </select>
            <select name="priority" class="input-field max-w-xs">
                <option value="">All Priority</option>
                @foreach(['low','medium','high','urgent','critical'] as $p)
                <option value="{{ $p }}" {{ request('priority') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Subject</th>
                        <th>Customer</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="font-mono text-primary-600 hover:underline">{{ $ticket->ticket_number }}</a></td>
                        <td class="font-medium max-w-xs truncate">{{ $ticket->subject }}</td>
                        <td>{{ $ticket->customer->name ?? '-' }}</td>
                        <td>
                            @php $colors = ['low'=>'badge-info','medium'=>'badge-warning','high'=>'badge-danger','urgent'=>'badge-danger','critical'=>'badge-danger']; @endphp
                            <span class="badge {{ $colors[$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span>
                        </td>
                        <td><span class="badge badge-purple">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td>
                        <td>{{ $ticket->assignee->name ?? 'Unassigned' }}</td>
                        <td class="text-gray-500">{{ $ticket->created_at->diffForHumans() }}</td>
                        <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="btn-ghost btn-sm">View</a></td>
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
