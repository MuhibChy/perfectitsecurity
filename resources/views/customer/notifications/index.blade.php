@extends('layouts.app')

@section('title', 'Notifications — Customer Portal')
@section('page-title', 'Notifications')

@section('content')
<div class="space-y-6">
    <x-page-header title="Notifications" subtitle="Stay updated on your tickets, invoices, and projects." sys="CLIENT://NOTIFY" num="13">
        <x-slot:actions>
            <a href="{{ route('portal.notifications.preferences') }}" class="term-btn term-btn-ghost term-btn-sm">
                Preferences
            </a>
            @if($notifications->count() > 0)
            <form action="{{ route('portal.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="term-btn term-btn-ghost term-btn-sm">
                    Mark all as read
                </button>
            </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Notifications List --}}
    @if($notifications->count() > 0)
    <div class="space-y-3">
        @foreach($notifications as $notification)
        <div class="term-panel p-4 {{ $notification->read_at ? '' : 'border-accent/40' }}">
            <div class="flex items-start gap-4">
                <span class="term-status-dot {{ str_contains($notification->type, 'breach') ? 'term-status-dot-red' : (str_contains($notification->type, 'warning') ? 'term-status-dot-amber' : (str_contains($notification->type, 'ticket') ? 'term-status-dot-blue' : '')) }} mt-1.5 flex-shrink-0"></span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $notification->data['title'] ?? $notification->type }}@unless($notification->read_at)<span class="term-tag term-tag-accent ml-2">Unread</span>@endunless</p>
                        <span class="term-hint flex-shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-term-800 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                    @if(isset($notification->data['url']))
                    <a href="{{ $notification->data['url'] }}" class="term-link !text-[11px] mt-2">
                        View Details
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>

    @else
    <x-empty-state-3d type="notifications" title="No Notifications"
        message="You're all caught up! Notifications about your tickets, invoices, and projects will appear here." />
    @endif
</div>
@endsection
