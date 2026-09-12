@extends('layouts.app')
@section('page-title', 'Conversation #' . $conversation->id)
@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="glass-card p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="font-mono text-sm text-gray-500">Session: {{ substr($conversation->session_id, 0, 8) }}...</span>
                @php $s = ['active'=>'badge-success','escalated'=>'badge-warning','closed'=>'badge-info','archived'=>'badge-purple']; @endphp
                <span class="badge {{ $s[$conversation->status] ?? '' }}">{{ ucfirst($conversation->status) }}</span>
                <span class="badge badge-info">{{ ucfirst($conversation->source) }}</span>
            </div>

            <div class="space-y-4">
                @foreach($messages as $msg)
                <div class="flex gap-4 {{ $msg->role === 'user' ? 'justify-end' : '' }}">
                    <div class="max-w-[75%] {{ $msg->role === 'user'
                        ? 'bg-primary-600 text-white rounded-2xl rounded-br-md px-4 py-3'
                        : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl rounded-bl-md px-4 py-3' }}">
                        <div class="text-sm font-semibold mb-1">{{ $msg->role === 'user' ? 'User' : 'AI' }}</div>
                        <div class="text-sm whitespace-pre-wrap">{{ $msg->content }}</div>
                        <div class="text-xs mt-2 {{ $msg->role === 'user' ? 'text-white/50' : 'text-gray-500' }}">
                            {{ $msg->created_at->format('M d, H:i') }}
                            @if($msg->tokens_used) • {{ $msg->tokens_used }} tokens @endif
                            @if($msg->response_time_ms) • {{ $msg->response_time_ms }}ms @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="glass-card p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Details</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">User</dt><dd class="font-medium">{{ $conversation->user->name ?? $conversation->guest_name ?? 'Guest' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd>{{ $conversation->user->email ?? $conversation->guest_email ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Messages</dt><dd>{{ $conversation->message_count }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Satisfied</dt><dd>{{ $conversation->satisfied === null ? 'Not rated' : ($conversation->satisfied ? 'Yes' : 'No') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Related Ticket</dt><dd>{{ $conversation->relatedTicket?->ticket_number ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Created</dt><dd>{{ $conversation->created_at->format('M d, Y H:i') }}</dd></div>
            </dl>
        </div>

        @if($conversation->escalations->isNotEmpty())
        <div class="glass-card p-4">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Escalation</h4>
            @foreach($conversation->escalations as $esc)
            <div class="p-3 bg-amber-50 dark:bg-amber-900/10 rounded-xl mb-2">
                <p class="text-sm font-medium">{{ $esc->reason }}</p>
                <p class="text-xs text-gray-500 mt-1">Status: {{ ucfirst($esc->status) }} • {{ $esc->created_at->diffForHumans() }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
