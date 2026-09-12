@extends('layouts.public')

@section('title', $article->title . ' — Knowledge Base | TechSupport Solutions')
@section('description', $article->excerpt ?? 'Technical runbook and guide for enterprise systems.')

@section('content')

{{-- Header --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white py-16 lg:py-20 border-b border-white/5 relative overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-surface-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
            <svg class="w-3 h-3 text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('kb.index') }}" class="hover:text-white transition-colors">Knowledge Base</a>
            <svg class="w-3 h-3 text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-surface-300 truncate max-w-xs">{{ $article->category->name ?? 'Article' }}</span>
        </nav>

        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-brand-500/10 border border-brand-500/30 text-brand-300 mb-4">
                {{ $article->category->name ?? 'System Documentation' }}
            </div>
            <h1 class="text-3xl lg:text-5xl font-black text-white tracking-tight leading-tight mb-6">
                {{ $article->title }}
            </h1>
            <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-xs text-surface-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-surface-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Published {{ $article->created_at?->format('M d, Y') }}
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-surface-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    ~{{ max(1, ceil(str_word_count(strip_tags($article->content)) / 200)) }} min read
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-surface-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    {{ number_format($article->views_count) }} views
                </span>
            </div>
        </div>
    </div>
</section>

{{-- Content & Sidebar --}}
<section class="section bg-white dark:bg-navy-900 py-16 lg:py-24">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10">
        <div class="grid lg:grid-cols-12 gap-12">

            {{-- Main Article Body --}}
            <article class="lg:col-span-8">
                @if($article->excerpt)
                <div class="p-6 rounded-2xl bg-surface-50 dark:bg-navy-800/50 border border-surface-200 dark:border-white/10 mb-10 text-base lg:text-lg text-surface-700 dark:text-surface-300 leading-relaxed font-medium">
                    {{ $article->excerpt }}
                </div>
                @endif

                <div class="prose prose-lg dark:prose-invert max-w-none text-navy-900 dark:text-surface-200">
                    {!! $article->content !!}
                </div>

                @if($article->tags && $article->tags->isNotEmpty())
                <div class="mt-12 pt-8 border-t border-surface-200 dark:border-white/10 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-surface-500 uppercase tracking-wider mr-2">Tags:</span>
                    @foreach($article->tags as $tag)
                    <span class="px-3 py-1 rounded-lg text-xs font-medium bg-surface-100 dark:bg-navy-800 text-surface-700 dark:text-surface-300">
                        #{{ $tag->name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </article>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4 space-y-8">
                {{-- Related Articles Card --}}
                @if(isset($related) && $related->isNotEmpty())
                <div class="glass-card p-6 rounded-2xl">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-navy-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Related Documentation
                    </h3>
                    <div class="space-y-4">
                        @foreach($related as $rel)
                        <a href="{{ route('kb.show', $rel->slug) }}" class="block group">
                            <h4 class="text-sm font-semibold text-surface-800 dark:text-surface-200 group-hover:text-brand-500 transition-colors line-clamp-2">
                                {{ $rel->title }}
                            </h4>
                            <span class="text-[11px] text-surface-400 mt-1 block">{{ $rel->updated_at?->diffForHumans() }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Direct Help Card --}}
                <div class="glass-card p-6 rounded-2xl bg-gradient-to-br from-brand-600/10 to-cyber-500/10 border-brand-500/30 text-center">
                    <div class="w-12 h-12 rounded-xl bg-brand-600 text-white flex items-center justify-center mx-auto mb-3 shadow-lg shadow-brand-600/30">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-navy-900 dark:text-white mb-1">Need Direct Assistance?</h4>
                    <p class="text-xs text-surface-600 dark:text-surface-400 mb-4">
                        Our Tier 3 engineers are on standby 24/7 to resolve technical incidents.
                    </p>
                    <a href="{{ route('contact') }}" class="btn-primary btn-sm w-full">
                        Open Support Ticket
                    </a>
                </div>
            </aside>

        </div>
    </div>

    {{-- Helpful Vote Section --}}
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 py-12">
        <div class="max-w-3xl mx-auto text-center p-8 rounded-3xl border border-white/10 bg-white/[0.02]" x-data="{ voted: false, helpful: null }">
            <h3 class="text-xl font-bold text-white mb-2">Was this article helpful?</h3>
            <p class="text-slate-400 text-sm mb-6">Your feedback helps us improve our documentation.</p>

            <div x-show="!voted" class="flex justify-center gap-4">
                <button @click="vote(true)" class="px-6 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                    Yes, helpful
                </button>
                <button @click="vote(false)" class="px-6 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v2a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/></svg>
                    Not helpful
                </button>
            </div>

            <div x-show="voted" x-transition class="text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-white font-semibold">Thank you for your feedback!</p>
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
    </div>
</section>

@endsection
