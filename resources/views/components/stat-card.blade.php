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
$colorClasses = [
    'blue' => [
        'bg' => 'bg-blue-50 dark:bg-blue-900/20',
        'border' => 'border-blue-500/10 dark:border-blue-500/20',
        'icon' => 'text-blue-600 dark:text-blue-400',
        'iconBg' => 'bg-blue-100 dark:bg-blue-900/40',
        'glow' => 'hover:shadow-blue-500/10'
    ],
    'emerald' => [
        'bg' => 'bg-emerald-50/50 dark:bg-emerald-950/20',
        'border' => 'border-emerald-500/10 dark:border-emerald-500/20',
        'icon' => 'text-emerald-600 dark:text-emerald-400',
        'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/40',
        'glow' => 'hover:shadow-emerald-500/10'
    ],
    'amber' => [
        'bg' => 'bg-amber-50/50 dark:bg-amber-950/20',
        'border' => 'border-amber-500/10 dark:border-amber-500/20',
        'icon' => 'text-amber-600 dark:text-amber-400',
        'iconBg' => 'bg-amber-100 dark:bg-amber-900/40',
        'glow' => 'hover:shadow-amber-500/10'
    ],
    'rose' => [
        'bg' => 'bg-rose-50/50 dark:bg-rose-950/20',
        'border' => 'border-rose-500/10 dark:border-rose-500/20',
        'icon' => 'text-rose-600 dark:text-rose-400',
        'iconBg' => 'bg-rose-100 dark:bg-rose-900/40',
        'glow' => 'hover:shadow-rose-500/10'
    ],
    'purple' => [
        'bg' => 'bg-purple-50/50 dark:bg-purple-950/20',
        'border' => 'border-purple-500/10 dark:border-purple-500/20',
        'icon' => 'text-purple-600 dark:text-purple-400',
        'iconBg' => 'bg-purple-100 dark:bg-purple-900/40',
        'glow' => 'hover:shadow-purple-500/10'
    ],
    'cyan' => [
        'bg' => 'bg-cyan-50/50 dark:bg-cyan-950/20',
        'border' => 'border-cyan-500/10 dark:border-cyan-500/20',
        'icon' => 'text-cyan-600 dark:text-cyan-400',
        'iconBg' => 'bg-cyan-100 dark:bg-cyan-900/40',
        'glow' => 'hover:shadow-cyan-500/10'
    ],
][$color] ?? [
    'bg' => 'bg-surface-50 dark:bg-navy-800/40',
    'border' => 'border-surface-200 dark:border-white/10',
    'icon' => 'text-primary-600 dark:text-primary-400',
    'iconBg' => 'bg-primary-100 dark:bg-primary-900/40',
    'glow' => 'hover:shadow-primary-500/10'
];
@endphp

<div class="glass-card p-5 relative overflow-hidden transition-all duration-300 hover:-translate-y-0.5 border {{ $colorClasses['border'] }} {{ $colorClasses['glow'] }}">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate">{{ $title }}</p>
            <h3 class="text-2xl lg:text-3xl font-bold font-mono text-gray-900 dark:text-white mt-1 tracking-tight">{{ $value }}</h3>

            @if($subtitle || $trend)
            <div class="flex items-center gap-2 mt-2 text-xs">
                @if($trend)
                <span class="inline-flex items-center font-semibold {{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
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
                <span class="text-gray-500 dark:text-gray-400 truncate">{{ $subtitle }}</span>
                @endif
            </div>
            @endif
        </div>

        @if($icon)
        <div class="w-12 h-12 rounded-xl {{ $colorClasses['iconBg'] }} flex items-center justify-center flex-shrink-0 shadow-sm">
            <div class="{{ $colorClasses['icon'] }}">
                {!! $icon !!}
            </div>
        </div>
        @endif
    </div>

    @if($href)
    <a href="{{ $href }}" class="absolute inset-0" aria-label="{{ $title }} details"></a>
    @endif
</div>
