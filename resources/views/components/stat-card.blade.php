@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'color' => 'blue',
    'trend' => null,
    'trendUp' => true,
    'href' => null
])

@php
$termAccent = [
    'blue' => '#4DA3FF',
    'emerald' => '#00E67A',
    'amber' => '#FFB454',
    'rose' => '#FF5C5C',
    'purple' => '#B48CFF',
    'cyan' => '#4DD8FF',
][$color] ?? '#00E67A';
@endphp

<div class="term-panel p-5 relative overflow-hidden">
    <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-slate-500 dark:text-term-800 truncate">{{ $title }}</div>
    <div class="flex items-start justify-between gap-3 mt-1.5">
        <div class="flex-1 min-w-0">
            <h3 class="text-2xl lg:text-3xl font-bold font-display text-slate-900 dark:text-white tracking-tight tabular-nums">{{ $value }}</h3>

            @if($subtitle || $trend)
            <div class="flex items-center gap-2 mt-2 text-xs">
                @if($trend)
                <span class="inline-flex items-center font-mono font-medium {{ $trendUp ? 'text-emerald-600 dark:text-[#00E67A]' : 'text-red-600 dark:text-[#FF5C5C]' }}">
                    <svg class="w-3.5 h-3.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if($trendUp)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                        @endif
                    </svg>
                    {{ $trend }}
                </span>
                @endif
                @if($subtitle)
                <span class="text-slate-500 dark:text-term-800 truncate font-mono text-[11px]">{{ $subtitle }}</span>
                @endif
            </div>
            @endif
        </div>

        @if($icon)
        <div class="stat-icon-box w-11 h-11 flex items-center justify-center flex-shrink-0 border" data-color="{{ $color }}" style="border-color: {{ $termAccent }}44; background: {{ $termAccent }}11; color: {{ $termAccent }}">
            {!! $icon !!}
        </div>
        @endif
    </div>

    @if($href)
    <a href="{{ $href }}" class="absolute inset-0" aria-label="{{ $title }} details"></a>
    @endif
</div>
