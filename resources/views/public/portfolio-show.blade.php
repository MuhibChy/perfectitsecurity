@extends('layouts.public')

@section('title', $item->title . ' — Portfolio')
@section('description', $item->summary ?? $item->title)

@section('content')
<section class="relative w-full py-16 lg:py-24 bg-[#030712] z-10 overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="w-full max-w-4xl mx-auto px-6 sm:px-10 relative z-10">
        <a href="{{ route('portfolio.index') }}" class="text-sm text-cyan-400 hover:text-cyan-300">&larr; Back to Portfolio</a>
        <div class="text-xs font-semibold uppercase tracking-widest text-cyan-400 mt-6 mb-3">{{ $item->category ?? 'Project' }}</div>
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-4">{{ $item->title }}</h1>
        @if($item->client_name)<p class="text-slate-400 mb-2">Client: {{ $item->client_name }}</p>@endif
        @if($item->summary)<p class="text-lg text-slate-300 mb-8">{{ $item->summary }}</p>@endif
        @if($item->description)<div class="cosmic-glass p-8 rounded-3xl border border-white/10 text-slate-300 whitespace-pre-line">{{ $item->description }}</div>@endif
        @if($item->project_url)
        <a href="{{ $item->project_url }}" target="_blank" rel="noopener" class="inline-block mt-6 px-8 py-3 rounded-2xl text-white font-bold" style="background: linear-gradient(135deg, #16A34A, #2563EB);">Visit Live Project</a>
        @endif
        <div class="mt-8">
            <a href="{{ route('get-quote') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold">Want results like these? Get a quote &rarr;</a>
        </div>
    </div>
</section>
@endsection
