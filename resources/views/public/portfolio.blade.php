@extends('layouts.public')

@section('title', 'Portfolio — PerfectITSecurity')
@section('description', 'Demo portfolio: sample IT, cybersecurity, cloud and software concept projects clearly labeled as demonstrations.')

@section('content')

{{-- HERO — WORK://CASEFILES --}}
<section class="relative w-full overflow-hidden" aria-labelledby="portfolio-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">WORK://CASEFILES</span>
                <span class="term-tag">Selected Work</span>
            </div>
            <h1 id="portfolio-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                OUR <span class="text-accent-soft">PORTFOLIO</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl">
                A selection of engagements across cybersecurity, cloud, and software engineering.
            </p>
        </div>
    </div>
</section>

<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Portfolio items">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <form method="GET" action="{{ route('portfolio.index') }}" class="flex flex-col sm:flex-row flex-wrap gap-2.5 mb-10" role="search">
            <label for="portfolio-search" class="sr-only">Search projects</label>
            <input type="text" id="portfolio-search" name="search" value="{{ request('search') }}" placeholder="search projects…"
                   class="term-input sm:max-w-xs font-mono text-sm">
            <label for="portfolio-category" class="sr-only">Filter by category</label>
            <select name="category" id="portfolio-category" class="term-input sm:max-w-xs text-sm">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
            <div class="flex gap-2.5">
                <button class="term-btn term-btn-sm">Filter</button>
                @if(request('search') || request('category'))
                    <a href="{{ route('portfolio.index') }}" class="term-btn term-btn-sm term-btn-ghost">Clear</a>
                @endif
            </div>
        </form>

        @if($featured->isNotEmpty())
        <div class="term-sec-label mb-5">WORK://FEATURED</div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mb-12">
            @foreach($featured as $item)
            <a href="{{ route('portfolio.show', $item->slug) }}" class="term-panel p-4 group block">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="term-tag term-tag-accent">{{ strtoupper($item->category ?? 'PROJECT') }}</span>
                    <span class="font-mono text-[10px] tracking-[0.2em] text-term-700">FILE_{{ str_pad((string)($loop->iteration), 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $item->title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-term-800">{{ $item->summary }}</p>
                @if($item->client_name)<p class="mt-3 font-mono text-[11px] tracking-wider text-term-700">CLIENT:// {{ $item->client_name }}</p>@endif
                <span class="term-link mt-4 text-sm">Open casefile
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
            @endforeach
        </div>
        @endif

        <div class="term-sec-label mb-5">WORK://ALL</div>
        @if($items->isEmpty())
            <div class="term-panel p-12 text-center text-sm text-slate-600 dark:text-term-800">Portfolio items are being curated — check back soon.</div>
        @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($items as $item)
            <a href="{{ route('portfolio.show', $item->slug) }}" class="term-panel p-4 group block">
                <div class="font-mono text-[10px] tracking-[0.2em] text-term-700 mb-3">{{ strtoupper($item->category ?? 'PROJECT') }}</div>
                <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $item->title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-term-800">{{ $item->summary }}</p>
                <span class="term-link mt-4 text-sm">Open casefile
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $items->links() }}</div>
        @endif
    </div>
</section>
@endsection
