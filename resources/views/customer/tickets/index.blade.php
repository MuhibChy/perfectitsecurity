@extends('layouts.app')
@section('page-title', 'My Tickets')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Support Tickets</h2>
        <a href="{{ route('portal.tickets.create') }}" class="btn-primary btn-sm">Create Ticket</a>
    </div>
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Ticket #</th><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Created</th><th></th></tr></thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td><a href="{{ route('portal.tickets.show', $ticket) }}" class="font-mono text-primary-600">{{ $ticket->ticket_number }}</a></td>
                        <td class="font-medium">{{ $ticket->subject }}</td>
                        <td>{{ $ticket->category->name ?? '-' }}</td>
                        <td><span class="badge {{ ['low'=>'badge-info','medium'=>'badge-warning','high'=>'badge-danger','urgent'=>'badge-danger','critical'=>'badge-danger'][$ticket->priority] ?? '' }}">{{ ucfirst($ticket->priority) }}</span></td>
                        <td><span class="badge badge-purple">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td>
                        <td class="text-gray-500">{{ $ticket->created_at->diffForHumans() }}</td>
                        <td><a href="{{ route('portal.tickets.show', $ticket) }}" class="btn-ghost btn-sm">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-500 py-8">No tickets yet. <a href="{{ route('portal.tickets.create') }}" class="text-primary-600">Create one</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection
