@extends('layouts.public')

@section('title', $service->name . ' — TechSupport Solutions')
@section('description', $service->seo_description ?? $service->short_description)

@section('content')

{{-- Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white relative overflow-hidden">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-20 lg:py-28 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-px bg-brand-400"></div>
                    <span class="label text-brand-400">{{ $service->category->name ?? 'Our Services' }}</span>
                </div>
                <h1 class="heading-xl text-white mb-6">{{ $service->name }}</h1>
                <p class="text-lg text-surface-400 leading-relaxed mb-6">{{ $service->short_description }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}" class="btn bg-brand-600 text-white hover:bg-brand-700 px-6 py-3 text-sm font-semibold">
                        🛒 Order Now
                    </a>
                    @if($service->allows_custom_quote)
                        <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}" class="btn bg-white/10 text-white hover:bg-white/20 px-6 py-3 text-sm font-semibold border border-white/20">
                            📝 Get Custom Quote
                        </a>
                    @endif
                    <a href="{{ route('contact') }}" class="btn bg-white/10 text-white hover:bg-white/20 px-6 py-3 text-sm font-semibold border border-white/20">
                        📞 Contact Us
                    </a>
                </div>
            </div>
            <div class="text-center lg:text-right">
                {{-- Price Display --}}
                <div class="inline-block bg-white/5 backdrop-blur rounded-2xl p-8 border border-white/10">
                    <div class="text-sm text-surface-400 mb-2">Starting from</div>
                    @foreach($service->countryPrices as $cp)
                        @if($cp->country)
                        <div data-country-price="{{ $cp->country->code }}" style="display:none">
                            @if($cp->pricing_type === 'custom_quote')
                                <div class="text-4xl font-bold text-white mb-1">Custom Quote</div>
                                <div class="text-surface-400 text-sm">Tailored to your requirements</div>
                            @elseif($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                                <div class="text-sm text-green-400 mb-1 line-through opacity-60">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</div>
                                <div class="text-4xl font-bold text-green-400 mb-1">{{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }}</div>
                                <div class="text-sm">
                                    <span class="bg-green-500/20 text-green-300 px-2 py-0.5 rounded-full">25% OFF</span>
                                    <span class="text-surface-400 ml-1">until {{ \Carbon\Carbon::parse($cp->discount_valid_until)->format('M d, Y') }}</span>
                                </div>
                            @else
                                <div class="text-4xl font-bold text-white mb-1">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</div>
                                <div class="text-surface-400 text-sm">{{ ucfirst($cp->pricing_type) }}</div>
                            @endif
                        </div>
                        @endif
                    @endforeach
                    @if($service->countryPrices->isEmpty())
                        <div class="text-4xl font-bold text-white mb-1">Custom Quote</div>
                        <div class="text-surface-400 text-sm">Tailored to your requirements</div>
                    @endif
                    @if($service->estimated_completion)
                        <div class="mt-4 pt-4 border-t border-white/10 text-sm text-surface-400">
                            ⏱ {{ $service->estimated_completion }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- All Country Prices --}}
@if($service->countryPrices->count() > 0)
<section class="bg-white dark:bg-navy-900 border-b border-surface-200 dark:border-white/5">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-8">
        <div class="flex items-center gap-8 overflow-x-auto">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Pricing by market:</span>
            @foreach($service->countryPrices as $cp)
            <div class="flex items-center gap-3 whitespace-nowrap px-4 py-2 rounded-xl {{ $loop->first ? 'bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800' : 'bg-surface-50 dark:bg-white/5' }}">
                <span class="text-xl">{{ $cp->country->currency_symbol }}</span>
                <div>
                    <div class="font-bold text-gray-900 dark:text-white">
                        @if($cp->pricing_type === 'custom_quote')
                            Custom Quote
                        @elseif($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                            <span class="line-through opacity-50 text-gray-400 text-sm mr-1">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                            <span class="text-green-600 dark:text-green-400">{{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }}</span>
                            <span class="ml-1 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-1.5 py-0.5 rounded-full">-25%</span>
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
                    <div class="text-xs text-gray-500">{{ $cp->country->name }} ({{ $cp->country->currency_code }})</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Description --}}
@if($service->description)
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="max-w-3xl">
            <div class="prose dark:prose-invert prose-lg prose-surface max-w-none">
                {!! $service->description !!}
            </div>
        </div>
    </div>
</section>
@endif

{{-- Deliverables & Features --}}
@if(($service->deliverables && count($service->deliverables) > 0) || ($service->features && count($service->features) > 0))
<section class="section bg-surface-50 dark:bg-navy-800/30">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="grid lg:grid-cols-2 gap-12">
            @if($service->deliverables && count($service->deliverables) > 0)
            <div>
                <h2 class="heading-md mb-6">📋 What's Included</h2>
                <ul class="space-y-3">
                    @foreach($service->deliverables as $item)
                    <li class="flex items-start gap-3">
                        <span class="text-green-500 mt-0.5">✓</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if($service->features && count($service->features) > 0)
            <div>
                <h2 class="heading-md mb-6">⚡ Key Features</h2>
                <ul class="space-y-3">
                    @foreach($service->features as $item)
                    <li class="flex items-start gap-3">
                        <span class="text-brand-500 mt-0.5">•</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
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
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="max-w-2xl mb-12">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-px bg-brand-500"></div>
                <span class="label">Our Process</span>
            </div>
            <h2 class="heading-lg">How we deliver {{ strtolower($service->name) }}.</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach(['Consult' => 'We discuss your requirements and provide a tailored proposal.', 'Plan' => 'Our team creates a detailed project plan with clear milestones.', 'Deliver' => 'Execution follows strict quality standards with regular updates.', 'Support' => 'Ongoing support and optimization after delivery.'] as $title => $desc)
            <div>
                <div class="text-4xl font-bold text-surface-200 dark:text-white/5 mb-4">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                <h3 class="heading-sm mb-2">{{ $title }}</h3>
                <p class="body-sm">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Related Services --}}
@if($related->isNotEmpty())
<section class="section bg-surface-50 dark:bg-navy-800/30">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="max-w-2xl mb-12">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-px bg-brand-500"></div>
                <span class="label">Related Services</span>
            </div>
            <h2 class="heading-lg">Explore our capabilities.</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($related as $rel)
            <a href="{{ route('services.show', $rel->slug) }}" class="group card-hover p-6">
                <h3 class="heading-sm mb-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $rel->name }}</h3>
                <p class="body-sm">{{ $rel->short_description }}</p>
                @php
                    $relPrice = $rel->countryPrices->firstWhere('country.code', 'UK') ?? $rel->countryPrices->firstWhere('country.code', 'US') ?? $rel->countryPrices->first();
                @endphp
                @if($relPrice && $relPrice->pricing_type !== 'custom_quote')
                    <div class="mt-3">
                        @if($relPrice->discount_price && $relPrice->discount_valid_until && \Carbon\Carbon::parse($relPrice->discount_valid_until)->isFuture())
                            <span class="text-sm text-gray-400 line-through">{{ $relPrice->country->currency_symbol }}{{ number_format($relPrice->price, 0) }}</span>
                            <span class="font-bold text-green-600 dark:text-green-400 ml-1">{{ $relPrice->country->currency_symbol }}{{ number_format($relPrice->discount_price, 0) }}</span>
                            <span class="ml-1 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-1.5 py-0.5 rounded-full">-25%</span>
                        @else
                            <span class="font-bold text-brand-600 dark:text-brand-400">{{ $relPrice->country->currency_symbol }}{{ number_format($relPrice->price, 0) }}</span>
                        @endif
                    </div>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="section bg-navy-900 dark:bg-navy-950">
    <div class="max-w-4xl mx-auto px-6 lg:px-10 py-16 lg:py-24 text-center">
        <h2 class="heading-lg text-white mb-5">Ready to get started?</h2>
        <p class="text-lg text-surface-400 mb-8">Let's discuss how {{ strtolower($service->name) }} can support your organization.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}" class="btn bg-brand-600 text-white hover:bg-brand-700 px-8 py-4 text-base font-semibold">
                    🛒 Order Now
                </a>
                @if($service->allows_custom_quote)
                    <a href="{{ auth()->check() ? route('portal.service-request.create', ['service_id' => $service->id]) : route('login') }}" class="btn bg-white/10 text-white hover:bg-white/20 px-8 py-4 text-base font-semibold border border-white/20">
                        📝 Get Custom Quote
                    </a>
                @endif
                <a href="{{ route('contact') }}" class="btn bg-white text-navy-900 hover:bg-surface-100 px-8 py-4 text-base font-semibold">
                    📞 Contact Us
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
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
