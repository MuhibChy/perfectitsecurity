@extends('layouts.public')

@section('title', 'Portfolio — TechSupport Solutions')
@section('description', 'Demo portfolio: sample IT, cybersecurity, cloud and software concept projects clearly labeled as demonstrations.')

@section('content')
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Selected Work
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                Our <span class="gradient-text-cyber">Portfolio</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal">
                A selection of engagements across cybersecurity, cloud, and software engineering.
            </p>
        </div>
    </div>
</section>

<section class="relative w-full py-16 lg:py-24 bg-[#030712] z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <form method="GET" action="{{ route('portfolio.index') }}" class="flex flex-wrap gap-3 mb-8">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search projects…"
                   class="px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-brand-500/60">
            <select name="category" class="px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500/60">
                <option value="" class="bg-[#030712]">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" class="bg-[#030712]" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
            <button class="btn-primary btn-sm">Filter</button>
            @if(request('search') || request('category'))
                <a href="{{ route('portfolio.index') }}" class="btn-ghost btn-sm">Clear</a>
            @endif
        </form>
        @if($featured->isNotEmpty())
        <h2 class="text-xl font-bold text-white mb-6">Featured</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($featured as $item)
            <a href="{{ route('portfolio.show', $item->slug) }}" class="cosmic-glass p-8 rounded-3xl border border-cyan-500/20 hover:border-cyan-500/40 transition-all group">
                <div class="text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-3">{{ $item->category ?? 'Project' }}</div>
                <h3 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $item->title }}</h3>
                <p class="text-sm text-slate-400 mt-2">{{ $item->summary }}</p>
                @if($item->client_name)<p class="text-xs text-slate-500 mt-3">Client: {{ $item->client_name }}</p>@endif
            </a>
            @endforeach
        </div>
        @endif

        <h2 class="text-xl font-bold text-white mb-6">All Work</h2>
        @if($items->isEmpty())
            <div class="cosmic-glass p-12 rounded-3xl border border-white/10 text-center text-slate-400">Portfolio items are being curated — check back soon.</div>
        @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($items as $item)
            <a href="{{ route('portfolio.show', $item->slug) }}" class="cosmic-glass p-8 rounded-3xl border border-white/10 hover:border-cyan-500/30 transition-all group">
                <div class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-3">{{ $item->category ?? 'Project' }}</div>
                <h3 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $item->title }}</h3>
                <p class="text-sm text-slate-400 mt-2">{{ $item->summary }}</p>
            </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $items->links() }}</div>
        @endif
    </div>
</section>
@endsection
