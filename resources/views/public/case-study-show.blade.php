@extends('layouts.public')

@section('title', $item->title . ' — Case Study')
@section('description', $item->summary ?? $item->title)

@section('content')
<section class="relative w-full py-16 lg:py-24 bg-[#030712] z-10 overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="w-full max-w-4xl mx-auto px-6 sm:px-10 relative z-10">
        <a href="{{ route('case-studies') }}" class="text-sm text-cyan-400 hover:text-cyan-300">&larr; Back to Case Studies</a>
        <div class="text-xs font-semibold uppercase tracking-widest text-cyan-400 mt-6 mb-3">{{ $item->industry ?? 'Case Study' }}@if($item->country) · {{ $item->country }} @endif</div>
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-4">{{ $item->title }}</h1>
        @if($item->client_name)<p class="text-slate-400 mb-2">Client: {{ $item->client_name }}</p>@endif
        @if($item->summary)<p class="text-lg text-slate-300 mb-8">{{ $item->summary }}</p>@endif
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([['Challenge', $item->challenge], ['Solution', $item->solution], ['Results', $item->results]] as [$heading, $body])
            @if($body)
            <div class="cosmic-glass p-6 rounded-3xl border border-white/10">
                <h2 class="text-sm font-bold uppercase tracking-widest text-cyan-400 mb-3">{{ $heading }}</h2>
                <div class="text-sm text-slate-300 whitespace-pre-line">{{ $body }}</div>
            </div>
            @endif
            @endforeach
        </div>
        <div class="mt-8">
            <a href="{{ route('get-quote') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold">Facing a similar challenge? Get a quote &rarr;</a>
        </div>
    </div>
</section>
@endsection
