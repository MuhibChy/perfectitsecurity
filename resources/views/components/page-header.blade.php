{{-- Terminal page header: SYS:// breadcrumb / section id / large title / desc / actions.
     Props: title, subtitle, badge, badgeColor (deprecated, mapped to term-tag),
     breadcrumbs ([label => url]), sys (e.g. 'CLIENT://TICKETS'), num (e.g. '04'). --}}
@props([
    'title',
    'subtitle' => null,
    'badge' => null,
    'badgeColor' => 'primary',
    'breadcrumbs' => [],
    'sys' => null,
    'num' => null,
])

<div class="mb-6 lg:mb-8">
    @if(!empty($breadcrumbs) || $sys)
    <nav class="flex items-center gap-2 font-mono text-[11px] tracking-[0.14em] uppercase text-slate-500 dark:text-term-700 mb-3" aria-label="Breadcrumb">
        @if($sys)
        <span class="text-emerald-700 dark:text-accent-soft">SYSTEM://{{ strtoupper(str_replace(['://', '/'], ['/', '/'], $sys)) }}</span>
        @else
            @foreach($breadcrumbs as $label => $url)
                @if(!$loop->last && $url)
                    <a href="{{ $url }}" class="hover:text-emerald-700 dark:hover:text-accent-soft transition-colors">{{ $label }}</a>
                    <span class="opacity-50" aria-hidden="true">/</span>
                @else
                    <span class="text-slate-700 dark:text-slate-300">{{ $label }}</span>
                @endif
            @endforeach
        @endif
    </nav>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div class="min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                @if($num)
                <span class="font-mono text-[11px] tracking-[0.2em] text-emerald-700 dark:text-accent-soft border border-emerald-600/30 dark:border-accent/30 bg-emerald-600/5 dark:bg-accent/5 px-2 py-1">SEC.{{ $num }}</span>
                @endif
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold font-display tracking-tight text-slate-900 dark:text-white text-balance">
                    {{ $title }}
                </h1>
                @if($badge)
                <span class="term-tag term-tag-accent">{{ $badge }}</span>
                @endif
            </div>

            @if($subtitle)
            <p class="text-sm text-slate-600 dark:text-term-800 mt-2 max-w-3xl leading-relaxed">
                {{ $subtitle }}
            </p>
            @endif
        </div>

        @if(isset($actions) && $actions->isNotEmpty())
        <div class="flex items-center flex-wrap gap-2.5 sm:self-center sm:flex-shrink-0">
            {{ $actions }}
        </div>
        @elseif($slot->isNotEmpty())
        <div class="flex items-center flex-wrap gap-2.5 sm:self-center sm:flex-shrink-0">
            {{ $slot }}
        </div>
        @endif
    </div>

    <div class="h-px mt-5 bg-gradient-to-r from-emerald-600/30 dark:from-accent/30 via-slate-300 dark:via-white/10 to-transparent" aria-hidden="true"></div>
</div>
