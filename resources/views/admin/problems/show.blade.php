@extends('layouts.app')
@section('page-title', 'Problem ' . $problem->problem_number)

@section('content')
<div class="space-y-6">
    <x-page-header title="Problem {{ $problem->problem_number }}" sys="OPS://PROBLEMS/SHOW" />
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="term-panel p-6">
                <h2 class="text-lg font-semibold mb-2">{{ $problem->title }}</h2>
                <div class="flex flex-wrap gap-2 mb-4"><x-status-badge :status="$problem->priority" /><x-status-badge :status="$problem->status" /></div>
                <p class="whitespace-pre-line">{{ $problem->description }}</p>
                @if($problem->root_cause)<h3 class="font-semibold mt-4">Root cause</h3><p class="whitespace-pre-line">{{ $problem->root_cause }}</p>@endif
                @if($problem->workaround)<h3 class="font-semibold mt-4">Workaround</h3><p class="whitespace-pre-line">{{ $problem->workaround }}</p>@endif
                @if($problem->resolution)<h3 class="font-semibold mt-4">Resolution</h3><p class="whitespace-pre-line">{{ $problem->resolution }}</p>@endif
            </div>
            <div class="term-panel p-6">
                <h3 class="font-semibold mb-3">Linked incidents ({{ $problem->tickets->count() }})</h3>
                <div class="overflow-x-auto"><table class="data-table term-table">
                    <thead><tr><th>Ticket #</th><th>Subject</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($problem->tickets as $t)
                        <tr><td class="font-mono">{{ $t->ticket_number }}</td><td>{{ $t->subject }}</td><td><x-status-badge :status="$t->status" /></td>
                        <td class="text-right"><form method="POST" action="{{ route('admin.problems.unlink-ticket', [$problem, $t]) }}">@csrf @method('DELETE')<button class="term-btn term-btn-sm">Unlink</button></form></td></tr>
                        @empty<tr><td colspan="4" class="text-center py-4">No linked incidents.</td></tr>@endforelse
                    </tbody>
                </table></div>
                <form method="POST" action="{{ route('admin.problems.link-ticket', $problem) }}" class="flex gap-3 mt-4">@csrf
                    <select name="ticket_id" class="term-input flex-1" required><option value="">Select incident...</option>@foreach($openTickets as $t)<option value="{{ $t->id }}">{{ $t->ticket_number }} — {{ $t->subject }}</option>@endforeach</select>
                    <button class="term-btn term-btn-sm">Link</button>
                </form>
            </div>
        </div>
        <div class="space-y-6">
            <div class="term-panel p-6">
                <h3 class="font-semibold mb-3">Lifecycle</h3>
                <form method="POST" action="{{ route('admin.problems.transition', $problem) }}" class="space-y-3">@csrf
                    <select name="to" class="term-input w-full">@foreach(['open','investigating','known_error','resolved','closed'] as $s)<option value="{{ $s }}">{{ str_replace('_', ' ', ucfirst($s)) }}</option>@endforeach</select>
                    <textarea name="root_cause" rows="2" placeholder="Root cause (optional)" class="term-input w-full"></textarea>
                    <textarea name="workaround" rows="2" placeholder="Workaround (optional)" class="term-input w-full"></textarea>
                    <textarea name="resolution" rows="2" placeholder="Resolution (optional)" class="term-input w-full"></textarea>
                    <button class="term-btn term-btn-sm w-full">Transition</button>
                </form>
            </div>
            <div class="term-panel p-6">
                <h3 class="font-semibold mb-3">Details</h3>
                <form method="POST" action="{{ route('admin.problems.update', $problem) }}" class="space-y-3">@csrf @method('PATCH')
                    <select name="priority" class="term-input w-full">@foreach(['low','medium','high','urgent','critical'] as $p)<option value="{{ $p }}" {{ $problem->priority == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>@endforeach</select>
                    <select name="assigned_to" class="term-input w-full"><option value="">Unassigned</option>@foreach($agents as $a)<option value="{{ $a->id }}" {{ $problem->assigned_to == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>@endforeach</select>
                    <button class="term-btn term-btn-sm w-full">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
