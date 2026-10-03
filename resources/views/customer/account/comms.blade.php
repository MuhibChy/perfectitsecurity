@extends('layouts.app')
@section('page-title', 'My Communications')
@section('content')
<div class="space-y-6">
    <x-page-header title="My Communications" subtitle="Messages, call records and business timeline in one place. Internal staff notes are never shown here." sys="CLIENT://COMMS" num="10" />

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="term-panel p-6">
            <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-4">Messages</h2>
            <div class="space-y-3">
                @forelse($messages as $m)
                <a href="{{ route('portal.messages.show', $m->id) }}" class="block term-panel-2 p-3 hover:border-emerald-600/40 dark:hover:border-accent/40 transition-colors">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $m->sender_id === auth()->id() ? ('To ' . $m->recipient->name) : ('From ' . $m->sender->name) }}</span>
                        <span class="font-mono text-slate-500 dark:text-term-700">{{ $m->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-600 dark:text-term-800 line-clamp-2">{{ $m->subject ?? mb_substr($m->body, 0, 120) }}</p>
                    @if($m->related_type)
                    <p class="mt-1 font-mono text-[10px] tracking-[0.14em] uppercase text-emerald-700 dark:text-accent-soft">Linked: {{ class_basename($m->related_type) }} #{{ $m->related_id }}</p>
                    @endif
                </a>
                @empty
                <p class="text-sm text-slate-600 dark:text-term-800">No messages yet.</p>
                @endforelse
            </div>
            <div class="mt-4">{{ $messages->links() }}</div>
            <a href="{{ route('portal.messages.create') }}" class="term-btn term-btn-sm mt-4">New message</a>
        </div>

        <div class="space-y-6">
            <div class="term-panel p-6">
                <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-4">Call history</h2>
                <div class="space-y-2.5">
                    @forelse($callLogs as $c)
                    <div class="term-panel-2 p-3 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $c->caller_id === auth()->id() ? ('To ' . $c->recipient->name) : ('From ' . $c->caller->name) }}</span>
                            <x-status-badge :status="$c->outcome === 'completed' ? 'completed' : ($c->outcome === 'missed' ? 'pending' : 'info')" :label="$c->outcome" />
                        </div>
                        <p class="mt-1 font-mono text-[11px] text-slate-500 dark:text-term-700">{{ optional($c->started_at)->format('Y-m-d H:i') }} · {{ gmdate('H:i:s', $c->duration_seconds) }} · {{ ucfirst($c->direction) }}</p>
                        @if($c->subject)<p class="mt-1 text-slate-600 dark:text-term-800">{{ $c->subject }}</p>@endif
                    </div>
                    @empty
                    <p class="text-sm text-slate-600 dark:text-term-800">No calls recorded.</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $callLogs->links() }}</div>
            </div>

            <div class="term-panel p-6">
                <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-4">Business timeline</h2>
                <x-timeline :timeline="$timeline" />
            </div>
        </div>
    </div>
</div>
@endsection
