@extends('layouts.app')
@section('page-title', 'Messages')
@section('content')
<div class="space-y-6">
<x-page-header title="Messages" :subtitle="$unread ? $unread . ' unread' : 'Inbox'" sys="COMMS://MESSAGES" num="16">
    <x-slot:actions>
        <a href="{{ (auth()->user()->isCustomer() ? route('portal.messages.create') : route('admin.messages.create')) }}" class="term-btn term-btn-sm">New message</a>
    </x-slot:actions>
</x-page-header>
<div class="term-panel p-6">
    <div class="mt-1 space-y-2">
    @forelse($inbox as $m)
        <div class="term-panel-2 p-3 text-sm @unless($m->read_at) border-accent/40 @endunless">
            @unless($m->read_at)<span class="term-tag term-tag-accent mr-2">Unread</span>@endunless
            <a class="term-link" href="{{ (auth()->user()->isCustomer() ? route('portal.messages.show', $m->id) : route('admin.messages.show', $m->id)) }}">{{ $m->subject ?? '(no subject)' }}</a>
            <span class="term-hint">from {{ $m->sender->name }} · {{ $m->created_at->format('Y-m-d H:i') }}</span>
        </div>
    @empty<p class="text-sm text-slate-600 dark:text-term-800 mt-2">No messages.</p>@endforelse
    </div>
    <div class="mt-4">{{ $inbox->links() }}</div>
</div>
</div>
@endsection
