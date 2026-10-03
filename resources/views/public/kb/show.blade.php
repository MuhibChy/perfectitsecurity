@extends('layouts.public')

@section('title', $article->title . ' — Knowledge Base | PerfectITSecurity')
@section('description', $article->excerpt ?? 'Technical runbook and guide for enterprise systems.')

@section('content')

{{-- Header — KNOWLEDGE://ENTRY --}}
<section class="relative w-full overflow-hidden border-b border-term-300 dark:border-white/5" aria-labelledby="kb-article-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-20 sm:pt-24 pb-10 lg:pb-14">
        <nav class="flex flex-wrap items-center gap-2 font-mono text-[11px] tracking-wider text-term-700 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-accent-soft transition-colors">HOME</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('kb.index') }}" class="hover:text-accent-soft transition-colors">KNOWLEDGE-BASE</a>
            <span aria-hidden="true">/</span>
            <span class="text-term-900 dark:text-term-950 truncate max-w-xs">{{ $article->category->name ?? 'Article' }}</span>
        </nav>

        <div class="max-w-4xl">
            <span class="term-tag term-tag-accent">KNOWLEDGE://{{ strtoupper($article->category->name ?? 'GUIDE') }}</span>
            <h1 id="kb-article-heading" class="mt-5 font-display font-extrabold tracking-tight leading-tight text-3xl sm:text-4xl lg:text-5xl text-navy-900 dark:text-white text-balance">
                {{ $article->title }}
            </h1>
            <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 font-mono text-[11px] tracking-wider text-term-700">
                <span>PUBLISHED:// {{ $article->created_at?->format('M d, Y') }}</span>
                <span>READ:// ~{{ max(1, ceil(str_word_count(strip_tags($article->content)) / 200)) }} min</span>
                <span>VIEWS:// {{ number_format($article->views_count) }}</span>
            </div>
        </div>
    </div>
</section>

{{-- Content & Sidebar --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24" aria-label="Article content">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- Main article body --}}
            <article class="lg:col-span-8 min-w-0">
                @if($article->excerpt)
                <div class="term-panel-2 px-5 sm:px-6 py-5 mb-8 text-base text-slate-600 dark:text-term-800 leading-relaxed">
                    {{ $article->excerpt }}
                </div>
                @endif

                <div class="term-panel p-6 sm:p-8 lg:p-10">
                    <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-term-800">
                        {!! $article->content !!}
                    </div>

                    @if($article->tags && $article->tags->isNotEmpty())
                    <div class="mt-10 pt-6 border-t border-term-300 dark:border-white/5 flex flex-wrap items-center gap-1.5">
                        <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-term-700 mr-2">TAGS://</span>
                        @foreach($article->tags as $tag)
                        <span class="term-tag">
                            #{{ $tag->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Helpful vote --}}
                <div class="term-panel p-6 sm:p-8 mt-6 text-center" x-data="{ voted: false, helpful: null }">
                    <div class="term-sec-label mb-2">FEEDBACK://SIGNAL</div>
                    <h3 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-2">Was this article helpful?</h3>
                    <p class="text-sm text-slate-600 dark:text-term-800 mb-6">Your feedback helps us improve our documentation.</p>

                    <div x-show="!voted" class="flex flex-col sm:flex-row justify-center gap-3">
                        <button @click="vote(true)" class="term-btn term-btn-sm">
                            Yes, helpful
                        </button>
                        <button @click="vote(false)" class="term-btn term-btn-sm term-btn-ghost">
                            Not helpful
                        </button>
                    </div>

                    <div x-show="voted" x-transition class="text-center" style="display:none;">
                        <p class="font-mono text-xs tracking-[0.18em] text-accent-soft">SYS://ACK — THANK YOU FOR YOUR FEEDBACK</p>
                    </div>

                    <script>
                    function vote(helpful) {
                        fetch('{{ route('kb.vote', $article->slug) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ helpful: helpful })
                        })
                        .then(r => r.json())
                        .then(data => { this.voted = true; this.helpful = helpful; })
                        .catch(() => { this.voted = true; });
                    }
                    </script>
                </div>
            </article>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4 space-y-4 sm:space-y-5">
                @if(isset($related) && $related->isNotEmpty())
                <div class="term-panel p-6">
                    <div class="term-sec-label mb-4">KNOWLEDGE://RELATED</div>
                    <div class="space-y-4">
                        @foreach($related as $rel)
                        <a href="{{ route('kb.show', $rel->slug) }}" class="block group pb-4 border-b border-term-300/60 dark:border-white/5 last:border-0 last:pb-0">
                            <h4 class="text-sm font-semibold text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors line-clamp-2">
                                {{ $rel->title }}
                            </h4>
                            <span class="font-mono text-[10px] tracking-wider text-term-700 mt-1 block">{{ $rel->updated_at?->diffForHumans() }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="term-panel p-6 text-center">
                    <div class="term-sec-label mb-3">SUPPORT://ESCALATE</div>
                    <h4 class="font-display text-base font-bold text-navy-900 dark:text-white mb-1">Need direct assistance?</h4>
                    <p class="text-xs leading-relaxed text-slate-600 dark:text-term-800 mb-4">
                        Our Tier 3 engineers are on standby 24/7 to resolve technical incidents.
                    </p>
                    <a href="{{ route('contact') }}" class="term-btn term-btn-sm w-full justify-center">
                        Open Support Ticket
                    </a>
                </div>
            </aside>

        </div>
    </div>
</section>

@endsection
