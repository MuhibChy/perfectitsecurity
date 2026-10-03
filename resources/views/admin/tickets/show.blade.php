@extends('layouts.app')
@section('page-title', 'Ticket ' . $ticket->ticket_number)

@section('content')

    <x-page-header :title="'Ticket ' . $ticket->ticket_number" :subtitle="$ticket->subject" sys="OPS://TICKETS" />
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Ticket Header --}}
        <div class="term-panel p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono mono-id text-sm text-gray-500">{{ $ticket->ticket_number }}</span>
                        <x-status-badge :status="$ticket->priority" />
                        <x-status-badge :status="$ticket->status" />
                        @if($slaStatus === 'breached')
                        <x-status-badge status="critical" label="SLA Breached" />
                        @elseif($slaStatus === 'warning')
                        <x-status-badge status="pending" label="SLA Warning" />
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $ticket->subject }}</h2>
                </div>
            </div>
            <p class="mt-4 text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $ticket->description }}</p>
        </div>

        {{-- Conversation --}}
        <div class="term-panel p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Conversation</h3>
            <div class="space-y-4">
                @foreach($ticket->messages as $message)
                <div class="flex gap-4 {{ $message->is_internal_note ? 'bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-xl p-4' : '' }}">
                    <img src="{{ $message->user->avatar_url }}" class="w-10 h-10 rounded-lg flex-shrink-0" alt="">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900 dark:text-white text-sm">{{ $message->user->name }}</span>
                            <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                            @if($message->is_internal_note)
                            <span class="term-tag text-xs">Internal Note</span>
                            @endif
                        </div>
                        <p class="mt-1 text-gray-600 dark:text-gray-400 text-sm whitespace-pre-wrap">{{ $message->message }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Reply Form --}}
            <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="mt-6">
                @csrf
                <textarea name="message" rows="4" class="term-input" placeholder="Type your reply..." required></textarea>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="term-btn term-btn-sm">Send Reply</button>
                </div>
            </form>

            {{-- Internal Note Form --}}
            <form method="POST" action="{{ route('admin.tickets.note', $ticket) }}" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                @csrf
                <textarea name="note" rows="2" class="term-input" placeholder="Add internal note..."></textarea>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="term-btn term-btn-ghost term-btn-sm">Add Note</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Status --}}
        <div class="term-panel p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Update Status</h4>
            <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}">
                @csrf
                <select name="status" class="term-input">
                    @foreach(['new','open','assigned','in_progress','waiting_customer','waiting_third_party','escalated','resolved','closed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="term-btn term-btn-sm w-full mt-2">Update</button>
            </form>
        </div>

        {{-- Assignment --}}
        <div class="term-panel p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Assignment</h4>
            <form method="POST" action="{{ route('admin.tickets.assign', $ticket) }}">
                @csrf
                <select name="assigned_to" class="term-input">
                    <option value="">Unassigned</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="term-btn term-btn-sm w-full mt-2">Assign</button>
            </form>
        </div>

        {{-- Tags --}}
        <div class="term-panel p-4" x-data="{ tags: {{ json_encode($ticket->tags ?? []) }}, newTag: '', editing: false }">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold text-gray-900 dark:text-white">Tags</h4>
                <button @click="editing = !editing" class="text-xs text-blue-500 hover:text-blue-400">
                    <span x-text="editing ? 'Done' : 'Edit'"></span>
                </button>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="(tag, index) in tags" :key="index">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                        <span x-text="tag"></span>
                        <button x-show="editing" @click="tags.splice(index, 1)" class="ml-0.5 text-blue-300 hover:text-red-400">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                </template>
                @if(!count($ticket->tags ?? []))
                <span class="text-xs text-gray-400">No tags</span>
                @endif
            </div>
            <div x-show="editing" class="mt-3">
                <form @submit.prevent="addTag()" class="flex gap-2">
                    <input x-model="newTag" type="text" placeholder="Add tag..." class="term-input flex-1">
                    <button type="submit" class="term-btn term-btn-sm">Add</button>
                </form>
                <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}" class="hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tags" :value="JSON.stringify(tags)">
                </form>
            </div>
        </div>

        {{-- Details --}}
        <div class="term-panel p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Details</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Customer</dt><dd class="font-medium">{{ $ticket->customer->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd>{{ $ticket->category->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Priority</dt><dd>{{ ucfirst($ticket->priority) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Severity</dt><dd>{{ ucfirst($ticket->severity) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">SLA Deadline</dt><dd>{{ $ticket->sla_resolution_deadline?->format('M d, H:i') ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Time Tracked</dt><dd>{{ $ticket->total_time_tracked }} min</dd></div>
            </dl>
        </div>

        {{-- Time Tracking --}}
        <div class="term-panel p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Time Tracking</h4>
            @if($ticket->timeEntries->count() > 0)
            <div class="space-y-2 mb-3">
                @foreach($ticket->timeEntries->sortByDesc('date') as $entry)
                <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-white/[0.02] border border-white/5">
                    <div>
                        <span class="text-white font-medium">{{ $entry->minutes }} min</span>
                        <span class="text-gray-400 ml-1">by {{ $entry->user->name ?? 'Unknown' }}</span>
                    </div>
                    <span class="text-gray-500">{{ $entry->date?->format('M d') ?? $entry->created_at->format('M d') }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-400 mb-3">No time entries recorded.</p>
            @endif
            <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="add_time_entry" value="1">
                <div class="flex gap-2">
                    <input type="number" name="minutes" min="1" max="480" placeholder="Min" class="term-input w-20" required>
                    <input type="text" name="time_description" placeholder="What did you work on?" class="term-input flex-1">
                </div>
            </form>
        </div>

        {{-- Reopen (for resolved/closed tickets) --}}
        @if(in_array($ticket->status, ['resolved', 'closed']))
        <div class="term-panel p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Reopen Ticket</h4>
            <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}">
                @csrf
                <textarea name="reason" rows="2" class="term-input" placeholder="Reason for reopening..." required></textarea>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="term-btn term-btn-sm">Reopen</button>
                </div>
            </form>
        </div>
        @endif

        {{-- Merge Ticket --}}
        @if(auth()->user()->isSupportManager() || auth()->user()->isAdmin())
        <div class="term-panel p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Merge Ticket</h4>
            <form method="POST" action="{{ route('admin.tickets.merge', $ticket) }}">
                @csrf
                <div class="space-y-3">
                    <input type="text" name="merge_ticket_id" placeholder="Enter ticket ID to merge into this one" class="term-input" required>
                    <textarea name="reason" rows="2" class="term-input" placeholder="Reason for merge (optional)"></textarea>
                </div>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="btn btn-destructive term-btn-sm" onclick="return confirm('Are you sure? This will merge the other ticket into this one.')">Merge</button>
                </div>
            </form>
        </div>
        @endif

        {{-- AI Suggestions (additive ITSM): related tickets + grounded KB articles. Read-only. --}}
        @if(isset($suggestions) && (($suggestions['tickets'] ?? collect())->count() || count($suggestions['articles'] ?? [])))
        <div class="term-panel p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Suggested Context</h4>
            @if(($suggestions['tickets'] ?? collect())->count())
            <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Related tickets</p>
            <ul class="space-y-1 mb-3 text-sm">
                @foreach($suggestions['tickets'] as $rel)
                <li><a href="{{ route('admin.tickets.show', $rel) }}" class="font-mono text-primary-600 hover:underline">{{ $rel->ticket_number }}</a> <span class="opacity-70">{{ \Illuminate\Support\Str::limit($rel->subject, 60) }}</span></li>
                @endforeach
            </ul>
            @endif
            @if(count($suggestions['articles'] ?? []))
            <p class="text-xs uppercase tracking-wide opacity-60 mb-2">Knowledge base</p>
            <ul class="space-y-1 text-sm">
                @foreach($suggestions['articles'] as $article)
                <li><a href="{{ route('kb.show', is_object($article) ? $article->slug : ($article['slug'] ?? '#')) }}" class="text-primary-600 hover:underline" target="_blank" rel="noopener">{{ is_object($article) ? $article->title : ($article['title'] ?? 'Article') }}</a></li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
