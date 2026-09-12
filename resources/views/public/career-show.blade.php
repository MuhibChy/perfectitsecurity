@extends('layouts.public')

@section('title', $item->title . ' — Careers')
@section('description', $item->summary ?? $item->title)

@section('content')
<section class="relative w-full py-16 lg:py-24 bg-[#030712] z-10 overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="w-full max-w-4xl mx-auto px-6 sm:px-10 relative z-10">
        <a href="{{ route('careers') }}" class="text-sm text-cyan-400 hover:text-cyan-300">&larr; Back to Careers</a>
        <div class="text-xs font-semibold uppercase tracking-widest text-cyan-400 mt-6 mb-3">{{ $item->department ?? 'Open Role' }}@if($item->location) · {{ $item->location }} @endif @if($item->employment_type) · {{ str_replace('_', ' ', $item->employment_type) }} @endif</div>
        <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-4">{{ $item->title }}</h1>
        @if($item->summary)<p class="text-lg text-slate-300 mb-8">{{ $item->summary }}</p>@endif
        @if($item->description)
        <div class="cosmic-glass p-8 rounded-3xl border border-white/10 mb-6">
            <h2 class="text-sm font-bold uppercase tracking-widest text-cyan-400 mb-3">About the Role</h2>
            <div class="text-sm text-slate-300 whitespace-pre-line">{{ $item->description }}</div>
        </div>
        @endif
        @if($item->requirements)
        <div class="cosmic-glass p-8 rounded-3xl border border-white/10">
            <h2 class="text-sm font-bold uppercase tracking-widest text-cyan-400 mb-3">Requirements</h2>
            <div class="text-sm text-slate-300 whitespace-pre-line">{{ $item->requirements }}</div>
        </div>
        @endif
        <div class="mt-8">
            <a href="{{ route('contact') }}" class="inline-block px-8 py-3 rounded-2xl text-white font-bold" style="background: linear-gradient(135deg, #16A34A, #2563EB);">Apply via Contact</a>
        </div>
    </div>
</section>
@endsection
