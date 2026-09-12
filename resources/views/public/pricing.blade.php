@extends('layouts.public')

@section('title', 'Enterprise Pricing & SLA Plans — TechSupport Solutions')
@section('description', 'Predictable, transparent enterprise IT service plans. Proactive cybersecurity, cloud administration, and dedicated 24/7 SOC infrastructure support.')

@section('content')

{{-- Hero --}}
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Transparent SLA Economics
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                Predictable Pricing for <span class="gradient-text-cyber">Uncompromising IT.</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal max-w-3xl">
                Zero ambiguous hourly billing. Choose standardized enterprise support tiers with fixed SLA commitments, continuous proactive SOC monitoring, and scalable engineering capacity.
            </p>
        </div>
    </div>
</section>

{{-- Pricing Plans Grid --}}
<section class="relative w-full py-24 lg:py-32 bg-[#030712] border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid lg:grid-cols-3 gap-8 items-stretch">
            @foreach($plans as $plan)
            <div class="cosmic-glass rounded-3xl p-8 sm:p-10 flex flex-col justify-between relative transition-all duration-300 {{ $plan->is_popular ? 'border-cyan-400/60 shadow-[0_0_40px_rgba(6,182,212,0.25)]' : 'border-white/10' }}">
                @if($plan->is_popular)
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                    <span class="bg-gradient-to-r from-cyan-500 to-blue-600 text-black font-black text-xs uppercase tracking-widest px-4 py-1 rounded-full shadow-lg">
                        ★ Recommended Tier
                    </span>
                </div>
                @endif

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-black text-white">{{ $plan->name }}</h3>
                        <span class="text-xs font-mono text-cyan-400 px-2.5 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20">
                            {{ ucfirst($plan->billing_cycle) }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-400 leading-relaxed mb-8 min-h-[44px]">
                        {{ $plan->description }}
                    </p>

                    <div class="mb-8 p-6 rounded-2xl bg-white/[0.03] border border-white/5 flex items-baseline gap-2">
                        <span class="text-5xl font-black text-white">${{ number_format($plan->price) }}</span>
                        <span class="text-sm font-mono text-slate-400">/ {{ $plan->billing_cycle }}</span>
                    </div>

                    <a href="{{ route('contact') }}?plan={{ Str::slug($plan->name) }}"
                       class="btn w-full py-4 text-center rounded-xl text-sm font-bold block transition-all {{ $plan->is_popular ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-cyan-500/30 hover:scale-[1.02]' : 'bg-white/10 text-white hover:bg-white/20 border border-white/10' }}">
                        Deploy This Plan
                    </a>

                    @if($plan->features)
                    <div class="mt-8 pt-6 border-t border-white/10">
                        <div class="text-xs font-mono uppercase tracking-widest text-slate-400 mb-4">Core Inclusions</div>
                        <ul class="space-y-3.5">
                            @foreach($plan->features as $feature)
                            <li class="flex items-start gap-3 text-sm text-slate-300">
                                <div class="w-4 h-4 rounded-full bg-cyan-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span>{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ Section (Full Width 2-Column Grid) --}}
<section class="relative w-full py-24 bg-space-deep border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="max-w-3xl mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-3">
                Questions & Clarity
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">Frequently Asked Questions.</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            @foreach([
                ['q' => 'How are enterprise SLAs calculated and enforced?', 'a' => 'Our SLAs are backed by contractually guaranteed response times ranging from 15 minutes for critical P1 events to 2 hours for standard P3 requests, monitored live via automated telemetry.'],
                ['q' => 'Can we customize scope across multi-regional branches?', 'a' => 'Yes. We frequently architect hybrid agreements combining 24/7 centralized SOC coverage with regional localized field dispatch across North America, Europe, and Asia.'],
                ['q' => 'What is the contract duration and cancellation flexibility?', 'a' => 'We offer both monthly and discounted multi-year master service agreements. All agreements include standard 30-day satisfaction exit terms.'],
                ['q' => 'Are onboarding audits and infrastructure migrations included?', 'a' => 'Enterprise tiers include a complimentary comprehensive vulnerability audit and onboarding transition blueprint managed by a principal DevOps architect.'],
            ] as $faq)
            <div class="cosmic-glass p-8 rounded-3xl border border-white/10">
                <h3 class="text-lg font-bold text-white mb-3 flex items-start gap-2.5">
                    <span class="text-cyan-400 font-mono">Q.</span>
                    {{ $faq['q'] }}
                </h3>
                <p class="text-sm text-slate-400 leading-relaxed pl-6">
                    {{ $faq['a'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-24 bg-space-radial z-10 text-center">
    <div class="w-full max-w-4xl mx-auto px-6">
        <h2 class="text-3xl sm:text-5xl font-black text-white mb-6">Need a Specialized Enterprise Quote?</h2>
        <p class="text-slate-300 text-lg mb-8 max-w-2xl mx-auto">Our solutions engineering team can configure an exact hybrid package for your infrastructure scale.</p>
        <a href="{{ route('contact') }}" class="btn btn-lg text-white font-bold px-8 py-4 rounded-2xl" style="background: linear-gradient(135deg, #16A34A, #2563EB);">
            Request Custom Architecture Proposal
        </a>
    </div>
</section>

@endsection
