@props([
    'title' => 'No records found',
    'message' => 'There are no items matching your criteria at this moment.',
    'icon' => null,
    'actionText' => null,
    'actionUrl' => null,
    'actionType' => 'primary'
])

<div class="glass-card p-12 text-center rounded-2xl border border-dashed border-gray-300 dark:border-gray-800 my-4">
    <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-950/30 text-primary-600 dark:text-primary-400 mx-auto flex items-center justify-center mb-4 shadow-inner">
        @if($icon)
            {!! $icon !!}
        @else
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>

    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">
        {{ $title }}
    </h3>

    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">
        {{ $message }}
    </p>

    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="{{ $actionType === 'secondary' ? 'btn-secondary' : 'btn-primary' }} btn-sm inline-flex items-center gap-2">
            <span>{{ $actionText }}</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    @elseif($slot->isNotEmpty())
        <div>
            {{ $slot }}
        </div>
    @endif
</div>
