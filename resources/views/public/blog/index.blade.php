@extends('layouts.public')

@section('title', 'Blog — PerfectITSecurity')
@section('description', 'Insights on IT infrastructure, cybersecurity, cloud technology, and enterprise IT management.')

@section('content')

{{-- HERO — BLOG://INDEX --}}
<section class="relative w-full overflow-hidden" aria-labelledby="blog-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-2xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">BLOG://INDEX</span>
                <span class="term-tag">Transmissions</span>
            </div>
            <h1 id="blog-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                INSIGHTS AND <span class="text-accent-soft">PERSPECTIVES.</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800">Expert perspectives on IT infrastructure, cybersecurity, cloud technology, and digital transformation.</p>
        </div>
    </div>
</section>

{{-- Posts grid --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Blog posts">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        @forelse($posts as $post)
        @if($loop->first)
        {{-- Featured first post --}}
        <a href="{{ route('blog.show', $post->slug) }}" class="term-panel p-0 overflow-hidden group grid lg:grid-cols-2 gap-0 mb-10 items-stretch">
            <div class="overflow-hidden aspect-[16/10] lg:aspect-auto lg:min-h-[320px]">
                @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $post->title }}">
                @else
                <div class="w-full h-full min-h-[240px] bg-term-200 dark:bg-white/5 flex items-center justify-center">
                    <span class="font-mono text-[11px] tracking-[0.24em] text-term-700">BLOG://NO-COVER</span>
                </div>
                @endif
            </div>
            <div class="p-6 sm:p-8 lg:p-10 flex flex-col justify-center">
                <div class="font-mono text-[11px] tracking-[0.2em] text-term-700 mb-3">{{ strtoupper($post->category->name ?? 'GENERAL') }} // {{ $post->published_at?->format('M d, Y') }}</div>
                <h2 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $post->title }}</h2>
                <p class="mt-3 text-sm sm:text-base leading-relaxed text-slate-600 dark:text-term-800">{{ $post->excerpt }}</p>
                <span class="term-link mt-5">
                    Read article
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </span>
            </div>
        </a>
        @endif
        @endforeach

        {{-- Rest of posts --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @forelse($posts as $post)
            @if(!$loop->first)
            <a href="{{ route('blog.show', $post->slug) }}" class="term-panel p-0 overflow-hidden group flex flex-col">
                <div class="overflow-hidden aspect-[16/10]">
                    @if($post->featured_image)
                    <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $post->title }}">
                    @else
                    <div class="w-full h-full bg-term-200 dark:bg-white/5 flex items-center justify-center">
                        <span class="font-mono text-[11px] tracking-[0.24em] text-term-700">BLOG://NO-COVER</span>
                    </div>
                    @endif
                </div>
                <div class="p-5 sm:p-6 flex flex-col flex-1">
                    <div class="font-mono text-[10px] tracking-[0.2em] text-term-700 mb-2">{{ strtoupper($post->category->name ?? 'GENERAL') }} // {{ $post->published_at?->format('M d, Y') }}</div>
                    <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $post->title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-term-800 line-clamp-2">{{ $post->excerpt }}</p>
                    <span class="term-link mt-4 text-sm">Read article
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </span>
                </div>
            </a>
            @endif
            @empty
            <div class="term-panel col-span-full p-12 sm:p-20 text-center">
                <div class="font-mono text-[11px] tracking-[0.24em] text-term-700 mb-3">QUERY://EMPTY</div>
                <p class="text-sm text-slate-600 dark:text-term-800">No articles published yet. Check back soon.</p>
            </div>
            @endforelse
        </div>

        @if(method_exists($posts, 'links'))
        <div class="mt-12">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</section>

@endsection
