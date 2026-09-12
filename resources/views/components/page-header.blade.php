@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeColor' => 'primary',
    'breadcrumbs' => []
])

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        @if(!empty($breadcrumbs))
        <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
            @foreach($breadcrumbs as $label => $url)
                @if(!$loop->last && $url)
                    <a href="{{ $url }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ $label }}</a>
                    <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @else
                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $label }}</span>
                @endif
            @endforeach
        </nav>
        @endif

        <div class="flex items-center gap-3">
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-gray-900 dark:text-white font-sans">
                {{ $title }}
            </h1>
            @if($badge)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                {{ $badgeColor === 'emerald' || $badgeColor === 'success' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-500/20' : '' }}
                {{ $badgeColor === 'primary' || $badgeColor === 'blue' ? 'bg-primary-100 text-primary-800 dark:bg-primary-900/40 dark:text-primary-300 border border-primary-500/20' : '' }}
                {{ $badgeColor === 'amber' || $badgeColor === 'warning' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-500/20' : '' }}
                {{ $badgeColor === 'rose' || $badgeColor === 'danger' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-500/20' : '' }}
            ">
                {{ $badge }}
            </span>
            @endif
        </div>

        @if($subtitle)
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-3xl">
            {{ $subtitle }}
        </p>
        @endif
    </div>

    @if(isset($actions) && $actions->isNotEmpty())
    <div class="flex items-center flex-wrap gap-2.5 sm:self-center">
        {{ $actions }}
    </div>
    @elseif($slot->isNotEmpty())
    <div class="flex items-center flex-wrap gap-2.5 sm:self-center">
        {{ $slot }}
    </div>
    @endif
</div>
