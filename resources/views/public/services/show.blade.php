@extends('layouts.public')

@section('title', $service->name . ' — PerfectITSecurity')
@section('description', $service->seo_description ?? $service->short_description)

@section('content')

{{-- HERO — MODULE://... --}}
<section class="relative w-full overflow-hidden" aria-labelledby="service-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <nav class="flex flex-wrap items-center gap-2 font-mono text-[11px] tracking-wider text-term-700 mb-7" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-accent-soft transition-colors">HOME</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('services.index') }}" class="hover:text-accent-soft transition-colors">SERVICES</a>
            <span aria-hidden="true">/</span>
            <span class="text-term-900 dark:text-term-950">{{ strtoupper($service->slug ?? $service->name) }}</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <div>
                <div class="flex flex-wrap items-center gap-2.5 mb-6">
                    <span class="term-tag term-tag-accent">MODULE://{{ strtoupper($service->category->slug ?? 'SERVICE') }}</span>
                    <span class="term-tag">{{ $service->category->name ?? 'Our Services' }}</span>
                </div>
                <h1 id="service-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-5xl lg:text-6xl text-navy-900 dark:text-white text-balance">{{ $service->name }}</h1>
                <p class="mt-5 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">{{ $service->short_description }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}" class="term-btn">
                        Order Now
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    @if($service->allows_custom_quote)
                        <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}" class="term-btn term-btn-ghost">
                            Get Custom Quote
                        </a>
                    @endif
                    <a href="{{ route('contact') }}" class="term-btn term-btn-ghost">
                        Contact Us
                    </a>
                </div>
            </div>
            <div>
                <div class="term-panel p-8 sm:p-10 text-center lg:text-right">
                    <div class="font-mono text-[10px] uppercase tracking-[0.24em] text-term-700 mb-3">PRICING://STARTING-FROM</div>
                    @foreach($service->countryPrices as $cp)
                        @if($cp->country)
                        <div data-country-price="{{ $cp->country->code }}" style="display:none">
                            @if($cp->pricing_type === 'custom_quote')
                                <div class="font-display text-4xl font-bold text-navy-900 dark:text-white mb-1">Custom Quote</div>
                                <div class="text-sm text-slate-600 dark:text-term-800">Tailored to your requirements</div>
                            @else
                                <x-service-price :service="$service" :row="$cp" size="hero" />
                                <div class="text-sm text-term-700 font-mono uppercase tracking-wider mt-1">{{ ucfirst($cp->pricing_type) }}</div>
                            @endif
                        </div>
                        @endif
                    @endforeach
                    @if($service->countryPrices->isEmpty())
                        <div class="font-display text-4xl font-bold text-navy-900 dark:text-white mb-1">Custom Quote</div>
                        <div class="text-sm text-slate-600 dark:text-term-800">Tailored to your requirements</div>
                    @endif
                    @if($service->estimated_completion)
                        <div class="mt-4 pt-4 border-t border-term-300 dark:border-white/5 font-mono text-xs tracking-wider text-term-700">
                            ETA:// {{ $service->estimated_completion }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- All country prices --}}
@if($service->countryPrices->count() > 0)
<section class="border-y border-term-300 dark:border-white/5" aria-label="Pricing by market">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-8">
        <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto">
            <span class="font-mono text-[11px] uppercase tracking-[0.2em] text-term-700 whitespace-nowrap">PRICING://BY-MARKET</span>
            @foreach($service->countryPrices as $cp)
            <div class="term-panel-2 flex items-center gap-3 whitespace-nowrap px-4 py-2.5">
                <span class="font-mono text-sm font-bold text-accent-soft">{{ $cp->country->currency_symbol }}</span>
                <div>
                    <div class="font-bold text-sm text-navy-900 dark:text-white">
                        @if($cp->pricing_type === 'custom_quote')
                            Custom Quote
                        @elseif($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                            <span class="line-through opacity-50 text-term-700 text-xs mr-1">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                            <span class="text-accent-soft">{{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }}</span>
                            <span class="ml-1 term-tag term-tag-accent">-{{ (int) round((1 - $cp->discount_price / max($cp->price, 0.01)) * 100) }}%</span>
                        @elseif(($__promo = app(\App\Services\PromotionService::class)->priceFor($service, $cp)) && $__promo['applies'])
                            <span class="line-through opacity-50 text-term-700 text-xs mr-1">{{ $cp->country->currency_symbol }}{{ number_format($__promo['original'], 0) }}</span>
                            <span class="text-accent-soft">{{ $cp->country->currency_symbol }}{{ number_format($__promo['final'], 0) }}</span>
                            <span class="ml-1 term-tag term-tag-accent">-{{ rtrim(rtrim(number_format($__promo['percent'], 1), '0'), '.') }}%</span>
                        @elseif($cp->pricing_type === 'starting_from')
                            From {{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}
                        @elseif($cp->pricing_type === 'hourly')
                            {{ $cp->country->currency_symbol }}{{ number_format($cp->price, 2) }}/hr
                        @elseif($cp->pricing_type === 'monthly' || $cp->pricing_type === 'recurring')
                            {{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}/mo
                        @else
                            {{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}
                        @endif
                    </div>
                    <div class="font-mono text-[10px] tracking-wider text-term-700">{{ $cp->country->name }} ({{ $cp->country->currency_code }})</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Description --}}
@if($service->description)
<section class="relative w-full py-16 sm:py-20 lg:py-24" aria-label="Service description">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="max-w-3xl">
            <x-section-head num="01" label="MODULE://OVERVIEW" title="Service overview" desc="Full technical scope as published by the engineering team." />
            <div class="term-panel p-6 sm:p-8 mt-8">
                <div class="prose dark:prose-invert prose-lg max-w-none text-slate-600 dark:text-term-800">
                    {!! $service->description !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Deliverables & Features --}}
@if(($service->deliverables && count($service->deliverables) > 0) || ($service->features && count($service->features) > 0))
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Deliverables and features">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="02" label="MODULE://SCOPE" title="Scope of delivery" desc="Every line item is tracked in your service record." />
        <div class="mt-10 grid lg:grid-cols-2 gap-4 sm:gap-5">
            @if($service->deliverables && count($service->deliverables) > 0)
            <div class="term-panel p-6 sm:p-8">
                <div class="font-mono text-[10px] tracking-[0.24em] text-accent-soft uppercase mb-5">INCLUDED://DELIVERABLES</div>
                <ul class="space-y-3">
                    @foreach($service->deliverables as $item)
                    <li class="flex items-start gap-3">
                        <span class="w-1.5 h-1.5 bg-accent flex-shrink-0 mt-1.5" aria-hidden="true"></span>
                        <span class="text-sm text-slate-600 dark:text-term-800">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if($service->features && count($service->features) > 0)
            <div class="term-panel p-6 sm:p-8">
                <div class="font-mono text-[10px] tracking-[0.24em] text-accent-soft uppercase mb-5">FEATURES://KEY</div>
                <ul class="space-y-3">
                    @foreach($service->features as $item)
                    <li class="flex items-start gap-3">
                        <span class="w-1.5 h-1.5 flex-shrink-0 mt-1.5" style="background:#4DA3FF" aria-hidden="true"></span>
                        <span class="text-sm text-slate-600 dark:text-term-800">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- Process --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Delivery process">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="03" label="MODULE://PROCESS" title="How we deliver {{ strtolower($service->name) }}." desc="A fixed four-stage workflow — scoped, tracked, and signed off." />
        <ol class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            @foreach(['Consult' => 'We discuss your requirements and provide a tailored proposal.', 'Plan' => 'Our team creates a detailed project plan with clear milestones.', 'Deliver' => 'Execution follows strict quality standards with regular updates.', 'Support' => 'Ongoing support and optimization after delivery.'] as $title => $desc)
            <li class="term-panel-2 p-5 sm:p-6">
                <div class="font-mono text-[11px] tracking-[0.24em] text-accent-soft">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                <h3 class="mt-2 font-display text-base font-bold tracking-tight text-navy-900 dark:text-white">{{ $title }}</h3>
                <p class="mt-2 text-[13px] leading-relaxed text-slate-600 dark:text-term-800">{{ $desc }}</p>
            </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Related Services --}}
@if($related->isNotEmpty())
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Related services">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="04" label="MODULE://RELATED" title="Explore our capabilities." desc="Adjacent modules frequently scoped together." />
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($related as $rel)
            <a href="{{ route('services.show', $rel->slug) }}" class="term-panel p-6 group block">
                <div class="term-sec-label mb-3">MODULE://RELATED</div>
                <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $rel->name }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-term-800 line-clamp-2">{{ $rel->short_description }}</p>
                @php
                    $relPrice = $rel->countryPrices->firstWhere('country.code', 'UK') ?? $rel->countryPrices->firstWhere('country.code', 'US') ?? $rel->countryPrices->first();
                @endphp
                @if($relPrice && $relPrice->pricing_type !== 'custom_quote')
                    <div class="mt-3 font-mono text-sm">
                        @if($relPrice->discount_price && $relPrice->discount_valid_until && \Carbon\Carbon::parse($relPrice->discount_valid_until)->isFuture())
                            <span class="text-term-700 line-through text-xs">{{ $relPrice->country->currency_symbol }}{{ number_format($relPrice->price, 0) }}</span>
                            <span class="font-bold text-accent-soft ml-1">{{ $relPrice->country->currency_symbol }}{{ number_format($relPrice->discount_price, 0) }}</span>
                            <span class="ml-1 term-tag term-tag-accent">-{{ (int) round((1 - $relPrice->discount_price / max($relPrice->price, 0.01)) * 100) }}%</span>
                        @else
                            <span class="font-bold text-navy-900 dark:text-white">{{ $relPrice->country->currency_symbol }}{{ number_format($relPrice->price, 0) }}</span>
                        @endif
                    </div>
                @endif
                <span class="term-link mt-4 text-sm">Explore
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-labelledby="service-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">MODULE://DEPLOY</span>
        <h2 id="service-cta-heading" class="mt-4 font-display text-3xl sm:text-4xl font-bold tracking-tight text-navy-900 dark:text-white">Ready to get started?</h2>
        <p class="mt-4 text-base leading-relaxed text-slate-600 dark:text-term-800">Let's discuss how {{ strtolower($service->name) }} can support your organization.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('get-quote', ['service_id' => $service->id]) }}" class="term-btn">
                    Order Now
                </a>
                @if($service->allows_custom_quote)
                    <a href="{{ route('get-quote', ['service_id' => $service->id]) }}" class="term-btn term-btn-ghost">
                        Get Custom Quote
                    </a>
                @endif
                <a href="{{ route('contact') }}" class="term-btn term-btn-ghost">
                    Contact Us
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var saved = localStorage.getItem('selected_country') || 'UK';
    showHeroPrice(saved);

    function showHeroPrice(code) {
        var priceEls = document.querySelectorAll('[data-country-price]');
        priceEls.forEach(function(el) {
            el.style.display = (el.dataset.countryPrice === code) ? '' : 'none';
        });
        // Fallback: if no price for selected country, show first available
        if (priceEls.length > 0) {
            var hasVisible = Array.from(priceEls).some(function(el) { return el.style.display !== 'none'; });
            if (!hasVisible) {
                priceEls[0].style.display = '';
            }
        }
    }
});
</script>

@endsection
