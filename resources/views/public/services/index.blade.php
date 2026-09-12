@extends('layouts.public')

@section('title', 'IT Services Catalog — TechSupport Solutions')
@section('description', 'Enterprise IT services catalog: cybersecurity defense, cloud infrastructure, managed IT support, web development, and digital automation.')

@section('content')

{{-- Hero Section --}}
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Standardized Enterprise Solutions
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                Engineered IT Services for <span class="gradient-text-cyber">Every Enterprise Layer.</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal max-w-3xl">
                From 24/7 managed SOC cybersecurity to scalable cloud DevOps and custom business portals — explore our standardized catalog of high-impact IT services.
            </p>
        </div>
    </div>
</section>

{{-- Sticky Currency / Country Selector --}}
<section class="sticky top-0 z-30 bg-[#040816]/95 backdrop-blur-2xl border-b border-white/10 py-3.5">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-mono uppercase tracking-widest text-slate-400">Regional Pricing:</span>
            <div class="flex flex-wrap gap-2 country-selector" id="countrySelector">
                @foreach($countries as $country)
                <button
                    type="button"
                    data-country="{{ $country->code }}"
                    class="country-btn px-4 py-1.5 rounded-full text-xs font-semibold transition-all border border-white/10 text-slate-300 hover:border-cyan-400/50"
                >
                    {{ $country->currency_symbol }} {{ $country->code }} — {{ $country->name }}
                </button>
                @endforeach
            </div>
        </div>
        <div class="text-xs text-slate-400 hidden sm:block font-mono">
            Showing verified multi-currency pricing
        </div>
    </div>
</section>

{{-- Featured Services (Wide Edge-to-Edge Grid) --}}
@if($featured->count() > 0)
<section class="relative w-full py-20 lg:py-28 bg-[#030712] border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="max-w-3xl mb-12">
            <div class="inline-flex items-center gap-2 text-xs font-mono font-bold text-cyan-400 mb-3 uppercase tracking-widest">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                High-Impact Deployments
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">Most In-Demand Solutions.</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @foreach($featured->take(6) as $service)
            <div class="cosmic-card p-7 flex flex-col justify-between group">
                <a href="{{ route('services.show', $service->slug) }}" class="block">
                    <div class="flex items-center justify-between mb-4">
                        @if($service->category)
                            <span class="text-xs font-mono font-semibold px-3 py-1 rounded-full border border-cyan-500/20 bg-cyan-500/10 text-cyan-300">
                                {{ $service->category->name }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-cyan-300 transition-colors">
                        {{ $service->name }}
                    </h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6 line-clamp-2">
                        {{ $service->short_description }}
                    </p>

                    <div class="flex items-center justify-between pt-4 border-t border-white/10">
                        <div class="pricing-display">
                            @forelse($service->countryPrices as $cp)
                                @if($cp->country)
                                <span data-country-price="{{ $cp->country->code }}" style="display:none">
                                    @if($cp->pricing_type === 'custom_quote')
                                        <span class="text-xs font-mono font-bold text-violet-400 uppercase tracking-wide">Custom Quote</span>
                                    @elseif($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                                        <span class="text-xs text-slate-500 line-through">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                        <span class="text-base font-bold text-emerald-400 ml-1">{{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }}</span>
                                        <span class="ml-1 text-[10px] bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded-full border border-emerald-500/30">PROMO</span>
                                    @else
                                        <span class="text-base font-bold text-cyan-400">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                        <span class="text-[11px] text-slate-400 ml-1">/ {{ ucfirst($cp->pricing_type) }}</span>
                                    @endif
                                </span>
                                @endif
                            @empty
                                @if($service->allows_custom_quote)
                                    <span class="text-xs font-mono font-bold text-violet-400 uppercase tracking-wide">Custom Quote</span>
                                @endif
                            @endforelse
                        </div>
                        <span class="text-xs font-semibold text-cyan-400 group-hover:text-white flex items-center gap-1 transition-colors">
                            Details →
                        </span>
                    </div>
                </a>

                <div class="flex gap-2.5 mt-5 pt-4 border-t border-white/10">
                    <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}"
                       class="flex-1 text-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-cyan-500/20">
                        Order Now
                    </a>
                    <a href="{{ route('contact') }}"
                       class="flex-1 text-center px-4 py-2.5 border border-white/15 text-slate-300 hover:border-cyan-400 hover:text-white rounded-xl text-xs font-semibold transition-all">
                        Consult
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- All Categories --}}
@foreach($categories as $category)
@if($category->services->count() > 0)
<section class="relative w-full py-20 lg:py-24 {{ $loop->even ? 'bg-[#020617]' : 'bg-[#030712]' }} border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="max-w-3xl mb-12">
            <div class="inline-flex items-center gap-2 text-xs font-mono font-bold text-cyan-400 mb-3 uppercase tracking-widest">
                <span class="w-2 h-2 rounded-full" style="background-color: {{ $category->color ?? '#16A34A' }}"></span>
                {{ $category->name }}
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">{{ $category->description ?? $category->name }}</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($category->services as $service)
            <div class="cosmic-card p-6 flex flex-col justify-between group">
                <a href="{{ route('services.show', $service->slug) }}" class="block">
                    <h3 class="font-bold text-white text-lg mb-2 group-hover:text-cyan-300 transition-colors">{{ $service->name }}</h3>
                    <p class="text-xs text-slate-400 mb-4 line-clamp-2 leading-relaxed">{{ $service->short_description }}</p>
                    
                    <div class="flex items-center justify-between pt-3 border-t border-white/10">
                        <div class="pricing-display">
                            @forelse($service->countryPrices as $cp)
                                @if($cp->country)
                                <span data-country-price="{{ $cp->country->code }}" style="display:none">
                                    @if($cp->pricing_type === 'custom_quote')
                                        <span class="text-xs font-mono text-violet-400 font-bold">Custom Quote</span>
                                    @elseif($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                                        <span class="text-xs text-slate-500 line-through">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                        <span class="text-sm font-bold text-emerald-400 ml-1">{{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }}</span>
                                    @else
                                        <span class="text-sm font-bold text-cyan-400">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                        <span class="text-[10px] text-slate-400 ml-1">{{ ucfirst($cp->pricing_type) }}</span>
                                    @endif
                                </span>
                                @endif
                            @empty
                                @if($service->allows_custom_quote)
                                    <span class="text-xs font-mono text-violet-400 font-bold">Custom Quote</span>
                                @else
                                    <span class="text-xs text-slate-400">Contact for pricing</span>
                                @endif
                            @endforelse
                        </div>
                        <span class="text-xs text-cyan-400 group-hover:text-white font-semibold">View →</span>
                    </div>
                </a>

                <div class="flex gap-2 mt-4 pt-3 border-t border-white/10">
                    <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}"
                       class="flex-1 text-center px-3 py-2 bg-cyan-500/20 hover:bg-cyan-500 hover:text-black text-cyan-300 rounded-xl text-xs font-bold transition-all border border-cyan-500/30">
                        Order
                    </a>
                    <a href="{{ route('contact') }}"
                       class="flex-1 text-center px-3 py-2 border border-white/10 text-slate-400 hover:text-white rounded-xl text-xs font-semibold transition-all">
                        Contact
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endforeach

{{-- Ready to Order Banner --}}
<section class="relative w-full py-20 bg-space-radial border-t border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8 cosmic-glass p-10 rounded-3xl border border-cyan-500/30">
            <div class="text-white max-w-2xl">
                <h2 class="text-2xl sm:text-3xl font-black mb-3">Require a Custom Architecture or SLA Package?</h2>
                <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                    Can't find an exact match for your infrastructure requirements? Our principal architects design tailored engineering and multi-year support agreements with guaranteed SLAs.
                </p>
            </div>
            <div class="flex flex-wrap gap-4">
                <a href="{{ auth()->check() ? route('portal.service-request.create') : route('login') }}"
                   class="btn btn-lg text-white rounded-2xl text-sm font-bold"
                   style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 8px 30px rgba(37,99,235,0.4);">
                    Order Custom Work
                </a>
                <a href="{{ route('contact') }}" class="btn btn-lg btn-glass rounded-2xl text-sm font-semibold">
                    Schedule Free Consultation
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
                btn.classList.add('bg-cyan-500', 'text-black', 'border-cyan-400', 'font-bold');
            } else {
                btn.classList.remove('bg-cyan-500', 'text-black', 'border-cyan-400', 'font-bold');
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
