@extends('layouts.public')

@section('title', $post->title . ' — PerfectITSecurity Blog')
@section('description', $post->excerpt ?? 'Enterprise cybersecurity and IT infrastructure insights.')

@section('content')

{{-- Header — BLOG://ENTRY --}}
<section class="relative w-full overflow-hidden border-b border-term-300 dark:border-white/5" aria-labelledby="blog-show-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-20 sm:pt-24 pb-10 lg:pb-14">
        <nav class="flex flex-wrap items-center gap-2 font-mono text-[11px] tracking-wider text-term-700 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-accent-soft transition-colors">HOME</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('blog.index') }}" class="hover:text-accent-soft transition-colors">BLOG</a>
            <span aria-hidden="true">/</span>
            <span class="text-term-900 dark:text-term-950 truncate max-w-xs">{{ $post->category->name ?? 'Article' }}</span>
        </nav>

        <div class="max-w-3xl">
            <span class="term-tag term-tag-accent">BLOG://{{ strtoupper($post->category->name ?? 'INSIGHTS') }}</span>
            <h1 id="blog-show-heading" class="mt-5 font-display font-extrabold tracking-tight leading-tight text-3xl sm:text-4xl lg:text-5xl text-navy-900 dark:text-white text-balance">
                {{ $post->title }}
            </h1>

            <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-term-700">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 border border-accent/50 bg-accent/10 flex items-center justify-center font-bold text-accent-soft text-xs" aria-hidden="true">
                        {{ substr($post->author->name ?? 'Admin', 0, 1) }}
                    </div>
                    <span class="font-medium text-slate-600 dark:text-term-800">{{ $post->author->name ?? 'PerfectITSecurity Editorial' }}</span>
                </div>
                <span aria-hidden="true">/</span>
                <span class="font-mono text-[11px] tracking-wider">{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</span>
                <span aria-hidden="true">/</span>
                <span class="font-mono text-[11px] tracking-wider">READ:// ~{{ max(1, ceil(str_word_count(strip_tags($post->content)) / 200)) }} min</span>
            </div>
        </div>
    </div>
</section>

{{-- Content body --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24" aria-label="Article">
    <div class="w-full max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-10">

        @if($post->featured_image)
        <div class="term-panel p-0 overflow-hidden aspect-[16/9] mb-8">
            <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-full object-cover" alt="{{ $post->title }}">
        </div>
        @endif

        @if($post->excerpt)
        <div class="term-panel-2 px-5 sm:px-6 py-5 mb-8 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
            {{ $post->excerpt }}
        </div>
        @endif

        <div class="term-panel p-6 sm:p-8 lg:p-10">
            <article class="prose dark:prose-invert max-w-none text-slate-600 dark:text-term-800 leading-relaxed">
                {!! $post->content !!}
            </article>

            @if($post->tags && $post->tags->isNotEmpty())
            <div class="mt-10 pt-6 border-t border-term-300 dark:border-white/5 flex flex-wrap items-center gap-1.5">
                <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-term-700 mr-2">TAGS://</span>
                @foreach($post->tags as $tag)
                <span class="term-tag">
                    #{{ $tag->name }}
                </span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Comments --}}
        <div class="term-panel p-6 sm:p-8 mt-6">
            <div class="term-sec-label mb-2">BLOG://DISCUSSION</div>
            <h3 class="font-display text-2xl font-bold tracking-tight text-navy-900 dark:text-white mb-6">Discussion &amp; comments</h3>

            @if(session('success'))
            <div class="term-alert term-alert-ok mb-6" role="status">
                <span class="term-alert-tag">SYS://OK</span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <form action="{{ route('blog.comment', $post->slug) }}" method="POST" class="space-y-5">
                @csrf
                @guest
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="term-field-label">Your Name</label>
                        <input type="text" name="name" required class="term-input">
                    </div>
                    <div>
                        <label class="term-field-label">Your Email</label>
                        <input type="email" name="email" required class="term-input">
                    </div>
                </div>
                @endguest

                <div>
                    <label class="term-field-label">Your Message</label>
                    <textarea name="comment" rows="4" required placeholder="Share your perspective or feedback..." class="term-input resize-none"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="term-btn term-btn-sm">
                        Submit Comment for Review
                    </button>
                </div>
            </form>
        </div>

        {{-- Related posts --}}
        @if(isset($related) && $related->isNotEmpty())
        <div class="mt-12 pt-10 border-t border-term-300 dark:border-white/5">
            <div class="term-sec-label mb-5">BLOG://RELATED</div>
            <div class="grid sm:grid-cols-3 gap-4 sm:gap-5">
                @foreach($related as $rel)
                <a href="{{ route('blog.show', $rel->slug) }}" class="term-panel p-5 group block">
                    <span class="term-sec-label mb-2 block">
                        {{ strtoupper($rel->category->name ?? 'ARTICLE') }}
                    </span>
                    <h4 class="text-sm font-bold text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors line-clamp-2">
                        {{ $rel->title }}
                    </h4>
                    <span class="font-mono text-[10px] tracking-wider text-term-700 mt-2 block">{{ $rel->published_at?->format('M d, Y') }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

@endsection
