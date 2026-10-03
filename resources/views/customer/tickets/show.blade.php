@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number . ' — Support Portal')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <x-page-header
        :title="'Ticket #' . $ticket->ticket_number"
        :subtitle="$ticket->subject"
        sys="SUPPORT://TICKETS"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Tickets' => route('portal.tickets.index'), 'Thread' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.tickets.index') }}" class="term-btn term-btn-ghost term-btn-sm">
                &larr; Back to Tickets
            </a>
            <x-status-badge :status="$ticket->status" />
        </x-slot:actions>
    </x-page-header>

    {{-- Main Issue Header Card --}}
    <div class="term-panel p-6 lg:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-white/10">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="term-tag">
                    Category: {{ $ticket->category->name ?? 'Technical Issue' }}
                </span>
                <x-status-badge :status="$ticket->priority" :label="'Priority: ' . ucfirst($ticket->priority)" />
            </div>

            <div class="font-mono text-[11px] uppercase tracking-[0.14em] text-slate-600 dark:text-term-800 flex items-center gap-4">
                <span>Opened: <strong class="text-slate-900 dark:text-white">{{ $ticket->created_at->format('M d, Y H:i') }}</strong></span>
                @if($ticket->assignee)
                <span>Assigned Agent: <strong class="text-slate-900 dark:text-white">{{ $ticket->assignee->name }}</strong></span>
                @endif
            </div>
        </div>

        <div class="mt-4 text-sm text-slate-600 dark:text-term-800 whitespace-pre-wrap leading-relaxed">
            {{ $ticket->description }}
        </div>

        {{-- Initial Ticket Attachments --}}
        @if($ticket->attachments && $ticket->attachments->count() > 0)
        <div class="mt-6 pt-4 border-t border-white/10">
            <h4 class="term-field-label mb-3">Case Evidence &amp; Artifacts</h4>
            <div class="flex flex-wrap gap-2.5">
                @foreach($ticket->attachments as $att)
                <a href="{{ route('portal.tickets.attachments.download', [$ticket, $att]) }}" class="term-btn term-btn-ghost term-btn-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>{{ $att->filename ?? 'Attachment' }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Conversation Thread --}}
    <div class="space-y-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-white px-2 flex items-center justify-between">
            <span>Engineering Discussion Thread</span>
            <span class="font-mono text-[11px] text-slate-600 dark:text-term-800">{{ $ticket->messages->where('is_internal_note', false)->count() }} messages</span>
        </h3>

        @forelse($ticket->messages as $message)
            @unless($message->is_internal_note)
            @php $isMe = $message->user_id === auth()->id(); @endphp
            <div class="flex gap-3.5 {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                <div class="avatar-fallback-blue w-9 h-9 {{ $isMe ? 'bg-accent text-[#04120b]' : 'bg-[#4DA3FF] text-white' }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ substr($message->user->name ?? 'User', 0, 1) }}
                </div>

                <div class="max-w-2xl {{ $isMe ? 'items-end' : 'items-start' }} flex flex-col">
                    <div class="flex items-center gap-2 mb-1 text-xs text-slate-600 dark:text-term-800">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $message->user->name }}</span>
                        @if(!$isMe && $message->user && !$message->user->isCustomer())
                            <span class="term-tag term-tag-accent">Support Staff</span>
                        @endif
                        <span>•</span>
                        <span>{{ $message->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="p-4 text-sm leading-relaxed whitespace-pre-wrap {{ $isMe ? 'bg-accent text-[#04120b]' : 'term-panel-2 text-slate-900 dark:text-white' }}">
                        {{ $message->message }}
                    </div>
                </div>
            </div>
            @endunless
        @empty
        <div class="text-center py-8 font-mono text-xs text-slate-600 dark:text-term-800 term-panel p-6">
            No replies recorded yet. An assigned engineer will investigate your ticket shortly.
        </div>
        @endforelse
    </div>

    {{-- Reply Form --}}
    @if(!in_array($ticket->status, ['closed', 'cancelled', 'resolved']))
    <div class="term-panel p-6 lg:p-8">
        <h3 class="term-field-label mb-3">Send Response</h3>
        <form method="POST" action="{{ route('portal.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <textarea name="message" rows="4" required placeholder="Type your follow-up message, questions, or clarification..."
                          class="term-input"></textarea>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <label class="term-btn term-btn-ghost term-btn-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span>Attach Diagnostic Files</span>
                        <input type="file" name="attachments[]" multiple class="hidden">
                    </label>
                </div>

                <button type="submit" class="term-btn term-btn-sm">
                    Post Message &rarr;
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="term-alert term-alert-warn">
        <span class="term-alert-tag">LOCKED</span>
        <span class="text-slate-600 dark:text-term-800">This ticket has been marked as {{ $ticket->status }}. If you require further assistance, please submit a new ticket.</span>
    </div>
    @endif
</div>
@endsection
