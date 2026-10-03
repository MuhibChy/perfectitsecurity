@extends('layouts.public')

@section('title', $item->title . ' — Portfolio')
@section('description', $item->summary ?? $item->title)

@section('content')
<section class="relative w-full overflow-hidden" aria-labelledby="portfolio-show-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 pt-20 sm:pt-24 pb-14 lg:pb-20">
        <a href="{{ route('portfolio.index') }}" class="term-link text-sm">&larr; Back to Portfolio</a>
        <div class="mt-6 flex flex-wrap items-center gap-2.5">
            <span class="term-tag term-tag-accent">CASEFILE://{{ strtoupper($item->category ?? 'PROJECT') }}</span>
            @if($item->is_demo)<span class="term-tag">DEMO / SAMPLE PROFILE</span>@endif
        </div>
        <h1 id="portfolio-show-heading" class="mt-4 font-display font-extrabold tracking-tight leading-tight text-3xl sm:text-5xl text-navy-900 dark:text-white text-balance">{{ $item->title }}</h1>
        @if($item->client_name)<p class="mt-3 font-mono text-xs tracking-wider text-term-700">CLIENT:// {{ $item->client_name }}</p>@endif
        @if($item->summary)<p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">{{ $item->summary }}</p>@endif
        @if($item->description)
        <div class="term-panel p-6 sm:p-8 mt-8">
            <div class="text-sm leading-relaxed text-slate-600 dark:text-term-800 whitespace-pre-line">{{ $item->description }}</div>
        </div>
        @endif
        @if($item->project_url)
        <a href="{{ $item->project_url }}" target="_blank" rel="noopener" class="term-btn mt-6">Visit Live Project
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
        @endif
        <div class="mt-8 pt-6 border-t border-term-300 dark:border-white/5">
            <a href="{{ route('get-quote') }}" class="term-link">Want results like these? Get a quote
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection
