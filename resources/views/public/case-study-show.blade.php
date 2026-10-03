@extends('layouts.public')

@section('title', $item->title . ' — Case Study')
@section('description', $item->summary ?? $item->title)

@section('content')
<section class="relative w-full overflow-hidden" aria-labelledby="cs-show-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 pt-20 sm:pt-24 pb-14 lg:pb-20">
        <a href="{{ route('case-studies') }}" class="term-link text-sm">&larr; Back to Case Studies</a>
        <div class="mt-6 flex flex-wrap items-center gap-2.5">
            <span class="term-tag term-tag-accent">CASEFILE://{{ strtoupper($item->industry ?? 'STUDY') }}</span>
            @if($item->is_demo)<span class="term-tag">ILLUSTRATIVE DEMO SCENARIO</span>@endif
            @if($item->country)<span class="term-tag">{{ strtoupper($item->country) }}</span>@endif
        </div>
        <h1 id="cs-show-heading" class="mt-4 font-display font-extrabold tracking-tight leading-tight text-3xl sm:text-5xl text-navy-900 dark:text-white text-balance">{{ $item->title }}</h1>
        @if($item->client_name)<p class="mt-3 font-mono text-xs tracking-wider text-term-700">CLIENT:// {{ $item->client_name }}</p>@endif
        @if($item->summary)<p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">{{ $item->summary }}</p>@endif
        <div class="mt-8 grid md:grid-cols-3 gap-4 sm:gap-5">
            @foreach([['Challenge', $item->challenge], ['Solution', $item->solution], ['Results', $item->results]] as [$heading, $body])
            @if($body)
            <div class="term-panel p-6">
                <div class="term-sec-label mb-3">{{ strtoupper($heading) }}://</div>
                <div class="text-sm leading-relaxed text-slate-600 dark:text-term-800 whitespace-pre-line">{{ $body }}</div>
            </div>
            @endif
            @endforeach
        </div>
        <div class="mt-8 pt-6 border-t border-term-300 dark:border-white/5">
            <a href="{{ route('get-quote') }}" class="term-link">Facing a similar challenge? Get a quote
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection
