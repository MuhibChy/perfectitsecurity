@extends('layouts.public')

@section('title', 'Blog — TechSupport Solutions')
@section('description', 'Insights on IT infrastructure, cybersecurity, cloud technology, and enterprise IT management.')

@section('content')

{{-- Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white relative overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-20 lg:py-28 relative z-10">
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-px bg-brand-400"></div>
                <span class="label text-brand-400">Blog</span>
            </div>
            <h1 class="heading-xl text-white mb-6">Insights and<br>perspectives.</h1>
            <p class="text-lg text-surface-400 leading-relaxed">Expert perspectives on IT infrastructure, cybersecurity, cloud technology, and digital transformation.</p>
        </div>
    </div>
</section>

{{-- Posts Grid --}}
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-20 lg:py-28">
        @forelse($posts as $post)
        @if($loop->first)
        {{-- Featured first post --}}
        <a href="{{ route('blog.show', $post->slug) }}" class="group grid lg:grid-cols-2 gap-10 mb-16 pb-16 border-b border-surface-200 dark:border-white/5 items-center">
            <div class="rounded-xl overflow-hidden aspect-[16/10]">
                @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" class="img-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $post->title }}">
                @else
                <div class="w-full h-full bg-surface-100 dark:bg-navy-800 flex items-center justify-center">
                    <svg class="w-12 h-12 text-surface-300 dark:text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </div>
                @endif
            </div>
            <div>
                <div class="text-xs text-surface-500 mb-3">{{ $post->category->name ?? 'General' }} · {{ $post->published_at?->format('M d, Y') }}</div>
                <h2 class="heading-md group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors mb-3">{{ $post->title }}</h2>
                <p class="body-lg">{{ $post->excerpt }}</p>
                <span class="link-arrow mt-5">
                    Read article
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </span>
            </div>
        </a>
        @endif
        @endforeach

        {{-- Rest of posts --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($posts as $post)
            @if(!$loop->first)
            <a href="{{ route('blog.show', $post->slug) }}" class="group">
                <div class="rounded-xl overflow-hidden aspect-[16/10] mb-4">
                    @if($post->featured_image)
                    <img src="{{ asset('storage/' . $post->featured_image) }}" class="img-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $post->title }}">
                    @else
                    <div class="w-full h-full bg-surface-100 dark:bg-navy-800 flex items-center justify-center">
                        <svg class="w-10 h-10 text-surface-300 dark:text-surface-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                    @endif
                </div>
                <div class="text-xs text-surface-500 mb-2">{{ $post->category->name ?? 'General' }} · {{ $post->published_at?->format('M d, Y') }}</div>
                <h3 class="heading-sm group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors mb-2">{{ $post->title }}</h3>
                <p class="body-sm line-clamp-2">{{ $post->excerpt }}</p>
            </a>
            @endif
            @empty
            <div class="col-span-full py-20 text-center">
                <p class="text-surface-500">No articles published yet. Check back soon.</p>
            </div>
            @endforelse
        </div>

        @if(method_exists($posts, 'links'))
        <div class="mt-16">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</section>

@endsection
