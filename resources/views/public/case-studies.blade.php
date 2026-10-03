@extends('layouts.public')

@section('title', 'Case Studies — IT Solutions in Action')
@section('description', 'Educational demo case studies showing how structured IT, cybersecurity and software practices solve common business technology challenges.')

@section('content')

{{-- HERO — WORK://STUDIES --}}
<section class="relative w-full overflow-hidden" aria-labelledby="cs-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-2xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">WORK://STUDIES</span>
                <span class="term-tag">Client Success</span>
            </div>
            <h1 id="cs-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                CLIENT SUCCESS <span class="text-accent-soft">STORIES</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800">
                How structured IT, cybersecurity, and software practices solve common business technology challenges.
            </p>
        </div>
    </div>
</section>

{{-- Case studies list --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Case studies">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <p class="text-sm text-slate-600 dark:text-term-800 mb-6 max-w-3xl">Real-world patterns distilled into practical studies — each one highlights the lessons we apply in client work.</p>
        <form method="GET" action="{{ route('case-studies') }}" class="flex flex-col sm:flex-row flex-wrap gap-2.5 mb-10" role="search">
            <label for="cs-search" class="sr-only">Search case studies</label>
            <input type="text" id="cs-search" name="search" value="{{ request('search') }}" placeholder="search case studies…"
                   class="term-input sm:max-w-xs font-mono text-sm">
            <label for="cs-industry" class="sr-only">Filter by industry</label>
            <select name="industry" id="cs-industry" class="term-input sm:max-w-xs text-sm">
                <option value="">All industries</option>
                @foreach($industries as $ind)
                    <option value="{{ $ind }}" @selected(request('industry') === $ind)>{{ $ind }}</option>
                @endforeach
            </select>
            <div class="flex gap-2.5">
                <button class="term-btn term-btn-sm">Filter</button>
                @if(request('search') || request('industry'))
                    <a href="{{ route('case-studies') }}" class="term-btn term-btn-sm term-btn-ghost">Clear</a>
                @endif
            </div>
        </form>
        <div class="space-y-4 sm:space-y-5">
            @forelse($items as $item)
            <article class="term-panel p-4 sm:p-5">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-10 items-center">
                    <div>
                        <div class="flex flex-wrap items-center gap-1.5 mb-4">
                            @if($item->industry)<span class="term-tag">{{ strtoupper($item->industry) }}</span>@endif
                            <span class="font-mono text-[10px] tracking-[0.2em] text-term-700">FILE_{{ str_pad((string)($loop->iteration), 3, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-navy-900 dark:text-white">
                            <a href="{{ route('case-studies.show', $item->slug) }}" class="hover:text-accent-soft transition-colors">{{ $item->title }}</a>
                        </h2>
                        @if($item->summary)<p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-term-800">{{ $item->summary }}</p>@endif
                        <a href="{{ route('case-studies.show', $item->slug) }}" class="term-link mt-5 text-sm">Read full study
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
                    <div class="term-panel-2 p-5 sm:p-6">
                        <div class="font-mono text-[10px] tracking-[0.2em] text-term-700 uppercase mb-4">Challenge -&gt; Solution -&gt; Lessons</div>
                        <div class="space-y-3 text-sm">
                            @if($item->challenge)<div class="px-4 py-3 border border-term-300 dark:border-white/10"><span class="font-mono text-[11px] font-bold text-accent-soft">CHALLENGE://</span><span class="text-slate-600 dark:text-term-800 text-[13px]"> {{ \Illuminate\Support\Str::limit(strip_tags($item->challenge), 220) }}</span></div>@endif
                            @if($item->solution)<div class="px-4 py-3 border border-term-300 dark:border-white/10"><span class="font-mono text-[11px] font-bold text-accent-soft">SOLUTION://</span><span class="text-slate-600 dark:text-term-800 text-[13px]"> {{ \Illuminate\Support\Str::limit(strip_tags($item->solution), 220) }}</span></div>@endif
                            @if($item->results)<div class="px-4 py-3 border border-term-300 dark:border-white/10"><span class="font-mono text-[11px] font-bold text-accent-soft">LESSONS://</span><span class="text-slate-600 dark:text-term-800 text-[13px]"> {{ \Illuminate\Support\Str::limit(strip_tags($item->results), 220) }}</span></div>@endif
                        </div>
                    </div>
                </div>
            </article>
            @empty
            <div class="term-panel p-12 text-center text-sm text-slate-600 dark:text-term-800">No case studies match your filters.</div>
            @endforelse
        </div>
        <div class="mt-8">{{ $items->links() }}</div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-labelledby="cs-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">WORK://YOUR-SCENARIO</span>
        <h2 id="cs-cta-heading" class="mt-4 font-display text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 dark:text-white">Discuss your own scenario</h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-term-800">Tell us about your environment and we will propose a tailored assessment.</p>
        <a href="{{ route('contact') }}" class="term-btn term-btn-lg mt-8">
            Start Your Project
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

@endsection
