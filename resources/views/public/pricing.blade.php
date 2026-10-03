@extends('layouts.public')

@section('title', 'Enterprise Pricing & SLA Plans — PerfectITSecurity')
@section('description', 'Predictable, transparent enterprise IT service plans. Proactive cybersecurity, cloud administration, and dedicated 24/7 SOC infrastructure support.')

@section('content')

{{-- HERO — PRICING://PLANS --}}
<section class="relative w-full overflow-hidden" aria-labelledby="pricing-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">PRICING://PLANS</span>
                <span class="term-tag">Transparent SLA Economics</span>
            </div>
            <h1 id="pricing-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                PREDICTABLE PRICING FOR <span class="text-accent-soft">UNCOMPROMISING IT.</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl">
                Zero ambiguous hourly billing. Choose standardized enterprise support tiers with fixed SLA commitments, continuous proactive SOC monitoring, and scalable engineering capacity.
            </p>
        </div>
    </div>
</section>

{{-- Pricing plans grid --}}
@php try { $__promoSvc = app(\App\Services\PromotionService::class); $__promoOn = $__promoSvc->isActive(); $__promoCfg = $__promoSvc->campaign(); $__promoWin = $__promoSvc->window(); } catch (\Throwable $e) { $__promoOn = false; $__promoCfg = []; $__promoWin = []; } @endphp
@if($__promoOn)
<section class="relative w-full border-t border-term-300 dark:border-white/5" aria-label="Current promotion">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-8">
        <div class="term-panel p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="term-tag term-tag-accent">PROMOTION://ACTIVE</span>
                <h2 class="mt-2 font-display text-xl sm:text-2xl font-bold tracking-tight text-navy-900 dark:text-white">
                    {{ $__promoCfg['promo.name'] ?? 'Promotion' }} — {{ rtrim(rtrim(number_format((float) ($__promoCfg['promo.percent'] ?? 0), 1), '0'), '.') }}% off eligible support services
                </h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-term-800">{{ $__promoCfg['promo.terms'] ?? '' }}@if(!empty($__promoWin['ends_at'])) Offer ends {{ $__promoWin['ends_at']->format('M d, Y') }} ({{ $__promoWin['timezone'] ?? 'UTC' }}).@endif</p>
            </div>
            <a href="{{ route('services.index') }}" class="term-btn flex-shrink-0 justify-center">Browse eligible services</a>
        </div>
    </div>
</section>
@endif
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Pricing plans">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid lg:grid-cols-3 gap-4 sm:gap-5 items-stretch">
            @foreach($plans as $plan)
            <div class="term-panel p-8 sm:p-10 flex flex-col justify-between relative">
@if($plan->is_popular)
<div class="absolute -top-3 left-6 z-20">
                    <span class="term-tag term-tag-accent">RECOMMENDED TIER</span>
                </div>
                @endif

                <div>
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="font-display text-2xl font-bold tracking-tight text-navy-900 dark:text-white">{{ $plan->name }}</h3>
                        <span class="term-tag">
                            {{ strtoupper($plan->billing_cycle) }}
                        </span>
                    </div>

                    <p class="text-sm leading-relaxed text-slate-600 dark:text-term-800 mb-8 min-h-[44px]">
                        {{ $plan->description }}
                    </p>

                    <div class="term-panel-2 px-5 py-5 mb-8 flex items-baseline gap-2">
                        <span class="font-mono text-4xl sm:text-5xl font-bold text-navy-900 dark:text-white">${{ number_format($plan->price) }}</span>
                        <span class="font-mono text-xs tracking-wider text-term-700">/ {{ $plan->billing_cycle }}</span>
                    </div>

                    <a href="{{ route('contact') }}?plan={{ Str::slug($plan->name) }}"
                       class="term-btn {{ $plan->is_popular ? '' : 'term-btn-ghost' }} w-full justify-center">
                        Deploy This Plan
                    </a>

                    @if($plan->features)
                    <div class="mt-8 pt-6 border-t border-term-300 dark:border-white/5">
                        <div class="term-sec-label mb-4">INCLUSIONS://CORE</div>
                        <ul class="space-y-3">
                            @foreach($plan->features as $feature)
                            <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-term-800">
                                <span class="w-1.5 h-1.5 bg-accent flex-shrink-0 mt-1.5" aria-hidden="true"></span>
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

{{-- FAQ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-labelledby="pricing-faq-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="01" label="PRICING://FAQ" title="Frequently asked questions." desc="Clarity on SLAs, scope, terms, and onboarding." />
        <span id="pricing-faq-heading" class="sr-only">Pricing questions</span>

        <div class="mt-10 grid md:grid-cols-2 gap-4 sm:gap-5">
            @foreach([
                ['q' => 'How are enterprise SLAs calculated and enforced?', 'a' => 'Our SLAs are backed by contractually guaranteed response times ranging from 15 minutes for critical P1 events to 2 hours for standard P3 requests, monitored live via automated telemetry.'],
                ['q' => 'Can we customize scope across multi-regional branches?', 'a' => 'Yes. We frequently architect hybrid agreements combining 24/7 centralized SOC coverage with regional localized field dispatch across North America, Europe, and Asia.'],
                ['q' => 'What is the contract duration and cancellation flexibility?', 'a' => 'We offer both monthly and discounted multi-year master service agreements. All agreements include standard 30-day satisfaction exit terms.'],
                ['q' => 'Are onboarding audits and infrastructure migrations included?', 'a' => 'Enterprise tiers include a complimentary comprehensive vulnerability audit and onboarding transition blueprint managed by a principal DevOps architect.'],
            ] as $faq)
            <div class="term-panel p-7 sm:p-8">
                <div class="term-sec-label mb-3">FAQ://{{ str_pad((string)($loop->iteration), 3, '0', STR_PAD_LEFT) }}</div>
                <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white">
                    {{ $faq['q'] }}
                </h3>
                <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-term-800">
                    {{ $faq['a'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-labelledby="pricing-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">PRICING://CUSTOM</span>
        <h2 id="pricing-cta-heading" class="mt-4 font-display text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 dark:text-white">Need a specialized enterprise quote?</h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-term-800 max-w-2xl mx-auto">Our solutions engineering team can configure an exact hybrid package for your infrastructure scale.</p>
        <a href="{{ route('contact') }}" class="term-btn term-btn-lg mt-8">
            Request Custom Architecture Proposal
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

@endsection
