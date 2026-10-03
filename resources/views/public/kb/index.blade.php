@extends('layouts.public')

@section('title', 'Knowledge Base & Technical Guides — PerfectITSecurity')
@section('description', 'Comprehensive technical documentation, cybersecurity runbooks, infrastructure guides, and FAQs.')

@section('content')

{{-- HERO — KNOWLEDGE://BASE --}}
<section class="relative w-full overflow-hidden" aria-labelledby="kb-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">KNOWLEDGE://BASE</span>
                <span class="term-tag">Runbooks + Guides</span>
            </div>
            <h1 id="kb-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                TECHNICAL INTELLIGENCE, <span class="text-accent-soft">SYSTEM GUIDES.</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-2xl">
                Explore architectural blueprints, incident response playbooks, and troubleshooting guides prepared by our senior engineering staff.
            </p>

            {{-- Search --}}
            <form action="{{ route('kb.index') }}" method="GET" role="search" class="mt-8 flex flex-col sm:flex-row gap-2.5 max-w-xl">
                @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <label for="kb-search" class="sr-only">Search articles, security guides, or error codes</label>
                <input type="text" id="kb-search" name="search" value="{{ request('search') }}" placeholder="search articles, guides, error codes…"
                       class="term-input flex-1 font-mono text-sm">
                <button type="submit" class="term-btn flex-shrink-0">Search -></button>
            </form>
        </div>
    </div>
</section>

{{-- Main content --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Knowledge base articles">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

        {{-- Category filter --}}
        @if(isset($categories) && $categories->isNotEmpty())
        <div class="flex items-center gap-1.5 overflow-x-auto pb-4 mb-10" role="navigation" aria-label="Filter by topic">
            <a href="{{ route('kb.index', array_filter(['search' => request('search')])) }}"
               class="term-tag flex-shrink-0 {{ !request('category') ? 'term-tag-accent' : '' }}">
                ALL TOPICS ({{ $categories->sum('articles_count') }})
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('kb.index', array_filter(['category' => $cat->id, 'search' => request('search')])) }}"
               class="term-tag flex-shrink-0 {{ request('category') == $cat->id ? 'term-tag-accent' : '' }}">
                {{ strtoupper($cat->name) }} [{{ $cat->articles_count }}]
            </a>
            @endforeach
        </div>
        @endif

        {{-- Articles grid --}}
        @if($articles->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($articles as $article)
            <a href="{{ route('kb.show', $article->slug) }}" class="term-panel p-6 group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="term-tag">{{ strtoupper($article->category->name ?? 'INFRASTRUCTURE') }}</span>
                        <span class="font-mono text-[11px] text-term-700">VIEWS://{{ number_format($article->views_count ?? 0) }}</span>
                    </div>

                    <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors leading-snug">
                        {{ $article->title }}
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-term-800 line-clamp-3">
                        {{ $article->excerpt ?? Str::limit(strip_tags($article->content), 120) }}
                    </p>
                </div>

                <div class="flex items-center justify-between gap-3 pt-4 mt-5 border-t border-term-300 dark:border-white/5 font-mono text-[11px]">
                    <span class="text-term-700">UPDATED://{{ $article->updated_at?->diffForHumans() ?? 'recently' }}</span>
                    <span class="term-link text-xs">Read guide
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $articles->withQueryString()->links() }}
        </div>
        @else
        <div class="term-panel p-12 sm:p-16 text-center max-w-lg mx-auto">
            <div class="font-mono text-[11px] tracking-[0.24em] text-term-700 mb-3">QUERY://EMPTY</div>
            <h3 class="font-display text-lg font-bold text-navy-900 dark:text-white mb-2">No matching documentation found</h3>
            <p class="text-sm text-slate-600 dark:text-term-800 mb-6">Try broadening your search query or removing the active category filter.</p>
            <a href="{{ route('kb.index') }}" class="term-btn term-btn-ghost term-btn-sm">Clear search filter</a>
        </div>
        @endif

    </div>
</section>

@endsection
