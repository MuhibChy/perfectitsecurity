@extends('layouts.public')

@section('title', $item->title . ' — Careers')
@section('description', $item->summary ?? $item->title)

@section('content')
<section class="relative w-full overflow-hidden" aria-labelledby="career-show-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-10 pt-20 sm:pt-24 pb-14 lg:pb-20">
        <a href="{{ route('careers') }}" class="term-link text-sm">&larr; Back to Careers</a>
        <div class="mt-6 flex flex-wrap items-center gap-2.5">
            <span class="term-tag term-tag-accent">CAREERS://{{ strtoupper($item->department ?? 'OPEN-ROLE') }}</span>
            @if($item->location)<span class="term-tag">{{ strtoupper($item->location) }}</span>@endif
            @if($item->employment_type)<span class="term-tag">{{ strtoupper(str_replace('_', ' ', $item->employment_type)) }}</span>@endif
        </div>
        <h1 id="career-show-heading" class="mt-4 font-display font-extrabold tracking-tight leading-tight text-3xl sm:text-5xl text-navy-900 dark:text-white text-balance">{{ $item->title }}</h1>
        @if($item->summary)<p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">{{ $item->summary }}</p>@endif
        @if($item->description)
        <div class="term-panel p-6 sm:p-8 mt-8">
            <div class="term-sec-label mb-3">ROLE://ABOUT</div>
            <div class="text-sm leading-relaxed text-slate-600 dark:text-term-800 whitespace-pre-line">{{ $item->description }}</div>
        </div>
        @endif
        @if($item->requirements)
        <div class="term-panel p-6 sm:p-8 mt-4">
            <div class="term-sec-label mb-3">ROLE://REQUIREMENTS</div>
            <div class="text-sm leading-relaxed text-slate-600 dark:text-term-800 whitespace-pre-line">{{ $item->requirements }}</div>
        </div>
        @endif
        <div class="mt-8">
            <a href="{{ route('contact') }}" class="term-btn">Apply via Contact
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection
