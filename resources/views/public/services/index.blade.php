@extends('layouts.public')

@section('title', 'IT Services Catalog — PerfectITSecurity')
@section('description', 'Enterprise IT services catalog: cybersecurity defense, cloud infrastructure, managed IT support, web development, and digital automation.')

@section('content')

{{-- HERO — SERVICES://CATALOG --}}
<section class="relative w-full overflow-hidden" aria-labelledby="services-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">SERVICES://CATALOG</span>
                <span class="term-status text-accent-soft"><span class="term-status-dot" aria-hidden="true"></span>Status: Online</span>
            </div>
            <h1 id="services-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                ENGINEERED IT SERVICES FOR <span class="text-accent-soft">EVERY ENTERPRISE LAYER.</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl">
                From 24/7 managed SOC cybersecurity to scalable cloud DevOps and custom business portals — explore our standardized catalog of high-impact IT services.
            </p>
        </div>
    </div>
</section>

{{-- Regional pricing selector (hooks preserved: countrySelector, country-btn, data-country-price) --}}
<section class="dark-island sticky top-14 z-30 bg-term-0/95 backdrop-blur-md border-y border-term-300 py-3.5" aria-label="Regional pricing">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-mono text-[10px] uppercase tracking-[0.24em] text-term-700">REGIONAL PRICING:</span>
            <div class="flex flex-wrap gap-1.5 country-selector" id="countrySelector">
                @foreach($countries as $country)
                <button
                    type="button"
                    data-country="{{ $country->code }}"
                    class="country-btn term-tag hover:border-accent/50 hover:text-accent-soft transition-colors"
                >
                    {{ $country->currency_symbol }} {{ $country->code }} — {{ $country->name }}
                </button>
                @endforeach
            </div>
        </div>
        <div class="font-mono text-[11px] tracking-wider text-term-700 hidden sm:block">
            Showing verified multi-currency pricing
        </div>
    </div>
</section>

{{-- Featured services --}}
@if($featured->count() > 0)
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-b border-term-300 dark:border-white/5" aria-labelledby="featured-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="01" label="SERVICES://FEATURED"
            title="Most in-demand solutions."
            desc="High-impact deployments ordered most often by enterprise clients — verified multi-currency pricing on every module." />
        <span id="featured-heading" class="sr-only">Featured services</span>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($featured->take(6) as $service)
            <article class="term-panel p-4 flex flex-col justify-between group">
                <div class="block">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        @if($service->category)
                            <span class="term-tag">{{ $service->category->name }}</span>
                        @endif
                        <span class="font-mono text-[10px] tracking-[0.2em] text-term-700">MOD://{{ str_pad((string)($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">
                        <a href="{{ route('services.show', $service->slug) }}">{{ $service->name }}</a>
                    </h3>
                    <p class="mt-1 text-sm leading-snug text-slate-600 dark:text-term-800 line-clamp-2">
                        {{ $service->short_description }}
                    </p>

                    <div class="flex items-end justify-between gap-2 pt-2 mt-2 border-t border-term-300 dark:border-white/5">
                        <a href="{{ route('services.show', $service->slug) }}" class="pricing-display min-w-0" aria-label="Details for {{ $service->name }}">
                            @forelse($service->countryPrices as $cp)
                                @if($cp->country)
                                <span data-country-price="{{ $cp->country->code }}" style="display:none">
                                    <x-service-price :service="$service" :row="$cp" size="card" />
                                </span>
                                @endif
                            @empty
                                @if($service->allows_custom_quote)
                                    <span class="font-mono text-[11px] font-bold text-accent-soft uppercase tracking-wider">Custom Quote</span>
                                @endif
                            @endforelse
                        </a>
                        <div class="flex flex-shrink-0 flex-col gap-1.5 w-[92px]">
                            <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('get-quote', ['service_id' => $service->id]) }}"
                               class="term-btn term-btn-sm justify-center" aria-label="Order {{ $service->name }}">
                                Order
                            </a>
                            <a href="{{ route('contact') }}"
                               class="term-btn term-btn-sm term-btn-ghost justify-center" aria-label="Consult about {{ $service->name }}">
                                Consult
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- All categories --}}
@foreach($categories as $category)
@if($category->services->count() > 0)
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-b border-term-300 dark:border-white/5" aria-labelledby="cat-{{ $category->id }}">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="max-w-3xl">
            <div class="term-sec-head" aria-hidden="true">
                <span class="term-sec-num">{{ str_pad((string)($loop->iteration + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <span class="term-sec-label">SERVICES://{{ strtoupper($category->slug ?? $category->name) }}</span>
            </div>
            <h2 id="cat-{{ $category->id }}" class="term-sec-title text-3xl sm:text-4xl">{{ $category->description ?? $category->name }}</h2>
            <p class="term-sec-desc mt-3">{{ $category->services->count() }} module{{ $category->services->count() === 1 ? '' : 's' }} in {{ $category->name }}</p>
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
            @foreach($category->services as $service)
            <article class="term-panel p-4 flex flex-col justify-between group">
                <div class="block">
                    <div class="font-mono text-[10px] tracking-[0.2em] text-term-700 mb-1.5">MODULE://{{ strtoupper($service->slug ?? $loop->iteration) }}</div>
                    <h3 class="font-display text-base font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors"><a href="{{ route('services.show', $service->slug) }}">{{ $service->name }}</a></h3>
                    <p class="mt-1 text-[13px] leading-snug text-slate-600 dark:text-term-800 line-clamp-2">{{ $service->short_description }}</p>

                    <div class="flex items-end justify-between gap-2 pt-2 mt-2 border-t border-term-300 dark:border-white/5">
                        <a href="{{ route('services.show', $service->slug) }}" class="pricing-display min-w-0" aria-label="Details for {{ $service->name }}">
                            @forelse($service->countryPrices as $cp)
                                @if($cp->country)
                                <span data-country-price="{{ $cp->country->code }}" style="display:none">
                                    <x-service-price :service="$service" :row="$cp" size="card" />
                                </span>
                                @endif
                            @empty
                                @if($service->allows_custom_quote)
                                    <span class="font-mono text-[11px] text-accent-soft font-bold">Custom Quote</span>
                                @else
                                    <span class="text-xs text-term-700">Contact for pricing</span>
                                @endif
                            @endforelse
                        </a>
                        <div class="flex flex-shrink-0 flex-col gap-1.5 w-[92px]">
                            <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('get-quote', ['service_id' => $service->id]) }}"
                               class="term-btn term-btn-sm justify-center" aria-label="Order {{ $service->name }}">
                                Order
                            </a>
                            <a href="{{ route('contact') }}"
                               class="term-btn term-btn-sm term-btn-ghost justify-center" aria-label="Consult about {{ $service->name }}">
                                Consult
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif
@endforeach

{{-- Custom work CTA --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24" aria-labelledby="custom-cta-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="term-panel p-8 sm:p-10 lg:p-12 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div class="max-w-2xl">
                <span class="term-tag term-tag-accent">SERVICES://CUSTOM</span>
                <h2 id="custom-cta-heading" class="mt-4 font-display text-2xl sm:text-3xl font-bold tracking-tight text-navy-900 dark:text-white">Require a custom architecture or SLA package?</h2>
                <p class="mt-3 text-sm sm:text-base leading-relaxed text-slate-600 dark:text-term-800">
                    Can't find an exact match for your infrastructure requirements? Our principal architects design tailored engineering and multi-year support agreements with guaranteed SLAs.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row md:flex-col lg:flex-row flex-shrink-0 gap-3">
                <a href="{{ auth()->check() ? route('portal.service-request.create') : route('login') }}"
                   class="term-btn">
                    Order Custom Work
                </a>
                <a href="{{ route('contact') }}" class="term-btn term-btn-ghost">
                    Free Consultation
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Country selector with local storage persistence
document.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem('selected_country') || 'UK';
    setActiveCountry(saved);

    document.querySelectorAll('.country-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const country = this.dataset.country;
            localStorage.setItem('selected_country', country);
            setActiveCountry(country);
        });
    });

    function setActiveCountry(code) {
        document.querySelectorAll('.country-btn').forEach(btn => {
            if (btn.dataset.country === code) {
                btn.classList.remove('text-slate-300', 'border-white/10');
                btn.classList.add('bg-accent', 'text-black', 'border-accent', 'font-bold');
            } else {
                btn.classList.remove('bg-accent', 'text-black', 'border-accent', 'font-bold');
                btn.classList.add('text-slate-300', 'border-white/10');
            }
        });

        document.querySelectorAll('[data-country-price]').forEach(el => {
            el.style.display = (el.dataset.countryPrice === code) ? '' : 'none';
        });

        document.querySelectorAll('.pricing-display').forEach(function(display) {
            var priceSpans = display.querySelectorAll('[data-country-price]');
            if (priceSpans.length > 0) {
                var hasVisible = Array.from(priceSpans).some(function(el) { return el.style.display !== 'none'; });
                if (!hasVisible) {
                    priceSpans[0].style.display = '';
                }
            }
        });
    }
});
</script>

@endsection
