@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number . ' — Support Portal')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <x-page-header
        :title="'Ticket #' . $ticket->ticket_number"
        :subtitle="$ticket->subject"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Tickets' => route('portal.tickets.index'), 'Thread' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('portal.tickets.index') }}" class="btn-ghost btn-sm">
                &larr; Back to Tickets
            </a>
            <x-status-badge :status="$ticket->status" />
        </x-slot:actions>
    </x-page-header>

    {{-- Main Issue Header Card --}}
    <div class="glass-card p-6 lg:p-8 rounded-2xl border border-white/10 shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-white/5">
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-surface-100 dark:bg-navy-800 text-surface-700 dark:text-surface-300">
                    Category: {{ $ticket->category->name ?? 'Technical Issue' }}
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold
                    {{ in_array($ticket->priority, ['critical', 'urgent', 'high']) ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400' }}">
                    Priority: {{ ucfirst($ticket->priority) }}
                </span>
            </div>

            <div class="text-xs text-gray-500 flex items-center gap-4">
                <span>Opened: <strong class="text-gray-800 dark:text-gray-200">{{ $ticket->created_at->format('M d, Y H:i') }}</strong></span>
                @if($ticket->assignee)
                <span>Assigned Agent: <strong class="text-gray-800 dark:text-gray-200">{{ $ticket->assignee->name }}</strong></span>
                @endif
            </div>
        </div>

        <div class="mt-4 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
            {{ $ticket->description }}
        </div>

        {{-- Initial Ticket Attachments --}}
        @if($ticket->attachments && $ticket->attachments->count() > 0)
        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-white/5">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Case Evidence & Artifacts</h4>
            <div class="flex flex-wrap gap-2.5">
                @foreach($ticket->attachments as $att)
                <a href="{{ route('portal.tickets.attachments.download', [$ticket, $att]) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface-100 dark:bg-navy-800 hover:bg-surface-200 dark:hover:bg-navy-700 text-xs font-medium text-gray-700 dark:text-gray-300 transition-colors">
                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>{{ $att->filename ?? 'Attachment' }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Conversation Thread --}}
    <div class="space-y-4">
        <h3 class="text-base font-bold text-gray-900 dark:text-white px-2 flex items-center justify-between">
            <span>Engineering Discussion Thread</span>
            <span class="text-xs font-normal text-gray-500">{{ $ticket->messages->where('is_internal_note', false)->count() }} messages</span>
        </h3>

        @forelse($ticket->messages as $message)
            @unless($message->is_internal_note)
            @php $isMe = $message->user_id === auth()->id(); @endphp
            <div class="flex gap-3.5 {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                <div class="w-9 h-9 rounded-xl {{ $isMe ? 'bg-primary-600' : 'bg-cyan-600' }} text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-md">
                    {{ substr($message->user->name ?? 'User', 0, 1) }}
                </div>

                <div class="max-w-2xl {{ $isMe ? 'items-end' : 'items-start' }} flex flex-col">
                    <div class="flex items-center gap-2 mb-1 text-xs text-gray-500">
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $message->user->name }}</span>
                        @if(!$isMe && $message->user && !$message->user->isCustomer())
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-primary-100 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">Support Staff</span>
                        @endif
                        <span>•</span>
                        <span>{{ $message->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="p-4 rounded-2xl text-sm leading-relaxed whitespace-pre-wrap {{ $isMe ? 'bg-primary-600 text-white rounded-tr-none' : 'glass-card text-gray-900 dark:text-white rounded-tl-none' }}">
                        {{ $message->message }}
                    </div>
                </div>
            </div>
            @endunless
        @empty
        <div class="text-center py-8 text-xs text-gray-500 glass-card p-6 rounded-xl">
            No replies recorded yet. An assigned engineer will investigate your ticket shortly.
        </div>
        @endforelse
    </div>

    {{-- Reply Form --}}
    @if(!in_array($ticket->status, ['closed', 'cancelled', 'resolved']))
    <div class="glass-card p-6 lg:p-8 rounded-2xl border border-white/10 shadow-lg">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-3">Send Response</h3>
        <form method="POST" action="{{ route('portal.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <textarea name="message" rows="4" required placeholder="Type your follow-up message, questions, or clarification..."
                          class="w-full p-4 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-navy-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <label class="btn-ghost btn-sm cursor-pointer inline-flex items-center gap-1.5 text-xs text-gray-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span>Attach Diagnostic Files</span>
                        <input type="file" name="attachments[]" multiple class="hidden">
                    </label>
                </div>

                <button type="submit" class="btn-primary btn-sm px-6">
                    Post Message &rarr;
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="p-4 rounded-xl bg-gray-100 dark:bg-navy-800 text-center text-xs text-gray-500">
        This ticket has been marked as {{ $ticket->status }}. If you require further assistance, please submit a new ticket.
    </div>
    @endif
</div>
@endsection
