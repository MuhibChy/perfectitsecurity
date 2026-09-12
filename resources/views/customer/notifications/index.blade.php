@extends('layouts.app')

@section('title', 'Notifications — Customer Portal')
@section('page-title', 'Notifications')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-slate-400 mt-1">Stay updated on your tickets, invoices, and projects.</p>
        </div>
        <a href="{{ route('portal.notifications.preferences') }}" class="px-4 py-2 rounded-xl text-sm text-slate-400 border border-white/10 hover:text-white hover:border-white/20 transition-all">
            ⚙️ Preferences
        </a>
        @if($notifications->count() > 0)
        <form action="{{ route('admin.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm text-slate-400 hover:text-white transition-colors">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    {{-- Notifications List --}}
    @if($notifications->count() > 0)
    <div class="space-y-3">
        @foreach($notifications as $notification)
        <div class="p-4 rounded-xl border transition-all {{ $notification->read_at ? 'bg-white/[0.02] border-white/5' : 'bg-cyan-500/5 border-cyan-500/20' }}">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                    @if(str_contains($notification->type, 'breach')) bg-red-500/10 text-red-400
                    @elseif(str_contains($notification->type, 'warning')) bg-amber-500/10 text-amber-400
                    @elseif(str_contains($notification->type, 'ticket')) bg-blue-500/10 text-blue-400
                    @elseif(str_contains($notification->type, 'invoice')) bg-green-500/10 text-green-400
                    @else bg-slate-500/10 text-slate-400 @endif">
                    @if(str_contains($notification->type, 'breach'))
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @elseif(str_contains($notification->type, 'warning'))
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @elseif(str_contains($notification->type, 'ticket'))
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    @elseif(str_contains($notification->type, 'invoice'))
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    @else
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold text-white">{{ $notification->data['title'] ?? $notification->type }}</p>
                        <span class="text-xs text-slate-500 flex-shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-400 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                    @if(isset($notification->data['url']))
                    <a href="{{ $notification->data['url'] }}" class="text-xs text-cyan-400 hover:text-cyan-300 mt-2 inline-flex items-center gap-1">
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
    {{-- Empty State --}}
    <div class="text-center py-16">
        <div class="w-16 h-16 rounded-2xl bg-slate-800/50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">No Notifications</h3>
        <p class="text-slate-400 text-sm">You're all caught up! Notifications about your tickets, invoices, and projects will appear here.</p>
    </div>
    @endif
</div>
@endsection
