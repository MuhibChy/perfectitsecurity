{{-- EmptyState3D: professional empty state with one contextual IT object.
     The object is secondary to the message + action (decorative only,
     aria-hidden, never intercepts input). --}}
@props([
    'type' => 'default',
    'title' => 'Nothing here yet',
    'message' => 'There is nothing to show in this area right now.',
    'actionText' => null,
    'actionUrl' => null,
])
@php
    $object = \App\Support\DecorativeZone::emptyObject($type);
@endphp
<div class="glass-card p-10 sm:p-12 text-center rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 my-4 relative overflow-hidden">
    <div class="empty-decor mb-4" aria-hidden="true">
        <x-decor-it-object :object="$object" />
    </div>
    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">{{ $title }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">{{ $message }}</p>
    @if($actionText && $actionUrl)
    <a href="{{ $actionUrl }}" class="term-btn term-btn-sm inline-flex items-center gap-2">
        <span>{{ $actionText }}</span>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
    </a>
    @elseif($slot->isNotEmpty())
    <div>{{ $slot }}</div>
    @endif
</div>
