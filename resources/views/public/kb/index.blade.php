@extends('layouts.public')

@section('title', 'Knowledge Base & Technical Guides — TechSupport Solutions')
@section('description', 'Comprehensive technical documentation, cybersecurity runbooks, infrastructure guides, and FAQs.')

@section('content')

{{-- Hero Section --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white relative overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(6,182,212,0.1),transparent_50%)] pointer-events-none"></div>
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-20 lg:py-28 relative z-10">
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-px bg-brand-400"></div>
                <span class="label text-brand-400 font-semibold uppercase tracking-wider text-xs">Knowledge Base & Documentation</span>
            </div>
            <h1 class="heading-xl text-white mb-6">Technical intelligence,<br>system guides & runbooks.</h1>
            <p class="text-lg text-surface-400 leading-relaxed mb-8">
                Explore architectural blueprints, incident response playbooks, and troubleshooting guides prepared by our senior engineering staff.
            </p>

            {{-- Search Bar --}}
            <form action="{{ route('kb.index') }}" method="GET" class="relative max-w-xl">
                @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="relative flex items-center">
                    <div class="absolute left-4 text-surface-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles, security guides, or error codes..."
                           class="w-full pl-12 pr-28 py-4 rounded-xl bg-white/10 dark:bg-white/5 border border-white/20 text-white placeholder-surface-400 focus:outline-none focus:ring-2 focus:ring-brand-500 backdrop-blur-md text-sm transition-all shadow-xl">
                    <button type="submit" class="absolute right-2.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold tracking-wide transition-colors">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- Main Content Section --}}
<section class="section bg-surface-50 dark:bg-navy-900/60 py-16 lg:py-24">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10">

        {{-- Category Chips Filter --}}
        @if(isset($categories) && $categories->isNotEmpty())
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-10 scrollbar-none">
            <a href="{{ route('kb.index', array_filter(['search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ !request('category') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'bg-white dark:bg-navy-800 border border-surface-200 dark:border-white/10 text-surface-700 dark:text-surface-300 hover:border-brand-500' }}">
                All Topics ({{ $categories->sum('articles_count') }})
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('kb.index', array_filter(['category' => $cat->id, 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all flex items-center gap-2 {{ request('category') == $cat->id ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'bg-white dark:bg-navy-800 border border-surface-200 dark:border-white/10 text-surface-700 dark:text-surface-300 hover:border-brand-500' }}">
                <span>{{ $cat->name }}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ request('category') == $cat->id ? 'bg-white/20 text-white' : 'bg-surface-100 dark:bg-navy-700 text-surface-500' }}">{{ $cat->articles_count }}</span>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Articles Grid --}}
        @if($articles->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($articles as $article)
            <a href="{{ route('kb.show', $article->slug) }}" class="glass-card group p-6 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-brand-500/40">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 border border-brand-500/20">
                            {{ $article->category->name ?? 'Infrastructure' }}
                        </span>
                        <div class="flex items-center gap-1.5 text-xs text-surface-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>{{ number_format($article->views_count ?? 0) }}</span>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2 group-hover:text-brand-500 transition-colors leading-snug">
                        {{ $article->title }}
                    </h3>

                    <p class="text-sm text-surface-600 dark:text-surface-400 leading-relaxed line-clamp-3 mb-6">
                        {{ $article->excerpt ?? Str::limit(strip_tags($article->content), 120) }}
                    </p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-surface-200 dark:border-white/5 text-xs">
                    <span class="text-surface-500">Updated {{ $article->updated_at?->diffForHumans() ?? 'recently' }}</span>
                    <span class="font-semibold text-brand-600 dark:text-brand-400 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                        Read Guide &rarr;
                    </span>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $articles->withQueryString()->links() }}
        </div>
        @else
        <div class="glass-card p-16 text-center rounded-2xl max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-surface-100 dark:bg-navy-800 text-surface-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2">No Matching Documentation Found</h3>
            <p class="text-sm text-surface-500 mb-6">Try broadening your search query or removing the active category filter.</p>
            <a href="{{ route('kb.index') }}" class="btn-secondary btn-sm">Clear Search Filter</a>
        </div>
        @endif

    </div>
</section>

@endsection
