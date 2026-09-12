@extends('layouts.public')

@section('title', 'Case Studies — IT Solutions in Action')
@section('description', 'Educational demo case studies showing how structured IT, cybersecurity and software practices solve common business technology challenges.')

@section('content')

{{-- HERO --}}
<section class="relative w-full py-24 lg:py-32 bg-space-deep overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="relative z-10 max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="max-w-2xl text-left">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-6">
            Case Studies
        </div>
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-tight mb-6">
            Client Success<br>
            <span class="gradient-text-cyber">Stories</span>
        </h1>
        <p class="text-lg sm:text-xl text-slate-300 leading-relaxed">
            How structured IT, cybersecurity, and software practices solve common business technology challenges.
        </p>
        </div>
    </div>
</section>

{{-- CASE STUDIES (database-driven; every entry is labeled DEMO/learning) --}}
<section class="relative w-full py-20 lg:py-28 bg-[#020617] border-y border-white/10 z-10">
    <div class="max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <p class="text-sm text-slate-400 mb-6 max-w-3xl">Real-world patterns distilled into practical studies — each one highlights the lessons we apply in client work.</p>
        <form method="GET" action="{{ route('case-studies') }}" class="flex flex-wrap gap-3 mb-8">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search case studies…"
                   class="px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-brand-500/60">
            <select name="industry" class="px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500/60">
                <option value="" class="bg-[#020617]">All industries</option>
                @foreach($industries as $ind)
                    <option value="{{ $ind }}" class="bg-[#020617]" @selected(request('industry') === $ind)>{{ $ind }}</option>
                @endforeach
            </select>
            <button class="btn-primary btn-sm">Filter</button>
            @if(request('search') || request('industry'))
                <a href="{{ route('case-studies') }}" class="btn-ghost btn-sm">Clear</a>
            @endif
        </form>
        <div class="space-y-10">
            @forelse($items as $item)
            <article class="cosmic-card p-8 lg:p-12">
                <div class="grid lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            @if($item->industry)<span class="text-xs font-semibold px-3 py-1 rounded-full bg-white/5 text-slate-300 border border-white/10">{{ $item->industry }}</span>@endif
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white mb-4">
                            <a href="{{ route('case-studies.show', $item->slug) }}" class="hover:text-brand-300 transition-colors">{{ $item->title }}</a>
                        </h2>
                        @if($item->summary)<p class="text-sm text-slate-400 leading-relaxed mb-6">{{ $item->summary }}</p>@endif
                        <a href="{{ route('case-studies.show', $item->slug) }}" class="text-sm font-bold text-brand-400 hover:text-brand-300">Read full study &rarr;</a>
                    </div>
                    <div class="p-8 rounded-2xl bg-black/40 border border-white/10">
                        <div class="text-xs font-mono text-slate-400 mb-3 uppercase tracking-wider">Challenge → Solution → Lessons</div>
                        <div class="space-y-4 text-sm">
                            @if($item->challenge)<div class="p-3 rounded-lg bg-red-500/5 border border-red-500/20"><span class="font-bold text-red-400">Challenge:</span><span class="text-slate-300"> {{ \Illuminate\Support\Str::limit(strip_tags($item->challenge), 220) }}</span></div>@endif
                            @if($item->solution)<div class="p-3 rounded-lg bg-cyber-500/5 border border-cyber-500/20"><span class="font-bold text-cyber-400">Solution:</span><span class="text-slate-300"> {{ \Illuminate\Support\Str::limit(strip_tags($item->solution), 220) }}</span></div>@endif
                            @if($item->results)<div class="p-3 rounded-lg bg-emerald-500/5 border border-emerald-500/20"><span class="font-bold text-emerald-400">Lessons:</span><span class="text-slate-300"> {{ \Illuminate\Support\Str::limit(strip_tags($item->results), 220) }}</span></div>@endif
                        </div>
                    </div>
                </div>
            </article>
            @empty
            <div class="cosmic-glass p-12 rounded-3xl border border-white/10 text-center text-slate-400">No case studies match your filters.</div>
            @endforelse
        </div>
        <div class="mt-8">{{ $items->links() }}</div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-24 bg-space-radial overflow-hidden z-10">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-cyan-500/10 blur-[120px]"></div>
    </div>
    <div class="relative z-10 max-w-[1000px] mx-auto px-6 text-center">
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-6">Discuss Your Own Scenario</h2>
        <p class="text-lg text-slate-300 mb-8 leading-relaxed">Tell us about your environment and we will propose a tailored assessment.</p>
        <a href="{{ route('contact') }}" class="btn btn-lg text-white rounded-2xl px-10 py-5 font-bold" style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 12px 40px rgba(37,99,235,0.5);">
            Start Your Project
        </a>
    </div>
</section>

@endsection
