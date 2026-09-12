@extends('layouts.public')

@section('title', $post->title . ' — TechSupport Blog')
@section('description', $post->excerpt ?? 'Enterprise cybersecurity and IT infrastructure insights.')

@section('content')

{{-- Header / Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white py-16 lg:py-24 border-b border-white/5 relative overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 relative z-10">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-surface-400 mb-6">
            <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
            <svg class="w-3 h-3 text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('blog.index') }}" class="hover:text-white transition-colors">Blog</a>
            <svg class="w-3 h-3 text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-surface-300 truncate max-w-xs">{{ $post->category->name ?? 'Article' }}</span>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-cyber-500/10 border border-cyber-500/30 text-cyber-300 mb-4">
                {{ $post->category->name ?? 'Cybersecurity Insights' }}
            </div>
            <h1 class="text-3xl lg:text-5xl font-black text-white tracking-tight leading-tight mb-6">
                {{ $post->title }}
            </h1>

            <div class="flex items-center gap-4 text-xs text-surface-400">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center font-bold text-white text-xs">
                        {{ substr($post->author->name ?? 'Admin', 0, 1) }}
                    </div>
                    <span class="font-medium text-surface-200">{{ $post->author->name ?? 'TechSupport Editorial' }}</span>
                </div>
                <span>•</span>
                <span>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</span>
                <span>•</span>
                <span>~{{ max(1, ceil(str_word_count(strip_tags($post->content)) / 200)) }} min read</span>
            </div>
        </div>
    </div>
</section>

{{-- Content Body --}}
<section class="section bg-white dark:bg-navy-900 py-16 lg:py-24">
    <div class="max-w-[1000px] mx-auto px-6 lg:px-10">

        {{-- Featured Image --}}
        @if($post->featured_image)
        <div class="rounded-2xl overflow-hidden aspect-[16/9] mb-12 shadow-2xl border border-surface-200 dark:border-white/10">
            <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-full object-cover" alt="{{ $post->title }}">
        </div>
        @endif

        {{-- Excerpt Callout --}}
        @if($post->excerpt)
        <div class="p-6 lg:p-8 rounded-2xl bg-surface-50 dark:bg-navy-800/50 border border-surface-200 dark:border-white/10 mb-12 text-lg text-surface-700 dark:text-surface-300 font-medium leading-relaxed">
            {{ $post->excerpt }}
        </div>
        @endif

        {{-- Article Content --}}
        <article class="prose prose-lg dark:prose-invert max-w-none text-navy-900 dark:text-surface-200 leading-relaxed">
            {!! $post->content !!}
        </article>

        {{-- Tags --}}
        @if($post->tags && $post->tags->isNotEmpty())
        <div class="mt-12 pt-8 border-t border-surface-200 dark:border-white/10 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-surface-500 uppercase tracking-wider mr-2">Tags:</span>
            @foreach($post->tags as $tag)
            <span class="px-3 py-1 rounded-lg text-xs font-medium bg-surface-100 dark:bg-navy-800 text-surface-700 dark:text-surface-300">
                #{{ $tag->name }}
            </span>
            @endforeach
        </div>
        @endif

        {{-- Comments Section --}}
        <div class="mt-16 pt-12 border-t border-surface-200 dark:border-white/10">
            <h3 class="text-2xl font-bold text-navy-900 dark:text-white mb-6">Discussion & Comments</h3>

            @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm mb-6 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            {{-- Comment Form --}}
            <form action="{{ route('blog.comment', $post->slug) }}" method="POST" class="glass-card p-6 lg:p-8 rounded-2xl space-y-5">
                @csrf
                @guest
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-surface-700 dark:text-surface-300 uppercase tracking-wider mb-2">Your Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-navy-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-surface-700 dark:text-surface-300 uppercase tracking-wider mb-2">Your Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-navy-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>
                @endguest

                <div>
                    <label class="block text-xs font-semibold text-surface-700 dark:text-surface-300 uppercase tracking-wider mb-2">Your Message</label>
                    <textarea name="comment" rows="4" required placeholder="Share your perspective or feedback..." class="w-full px-4 py-3 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-navy-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary px-6 py-2.5 text-sm">
                        Submit Comment for Review
                    </button>
                </div>
            </form>
        </div>

        {{-- Related Posts --}}
        @if(isset($related) && $related->isNotEmpty())
        <div class="mt-20 pt-12 border-t border-surface-200 dark:border-white/10">
            <h3 class="text-xl font-bold text-navy-900 dark:text-white mb-6">Related Analysis</h3>
            <div class="grid sm:grid-cols-3 gap-6">
                @foreach($related as $rel)
                <a href="{{ route('blog.show', $rel->slug) }}" class="glass-card group p-5 rounded-2xl block hover:-translate-y-1 transition-all">
                    <span class="text-[11px] font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-2 block">
                        {{ $rel->category->name ?? 'Article' }}
                    </span>
                    <h4 class="text-sm font-bold text-navy-900 dark:text-white group-hover:text-brand-500 transition-colors line-clamp-2">
                        {{ $rel->title }}
                    </h4>
                    <span class="text-xs text-surface-400 mt-2 block">{{ $rel->published_at?->format('M d, Y') }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

@endsection
