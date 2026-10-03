@extends('layouts.app')
@section('page-title', 'Services Catalogue')
@section('description', 'Browse our complete IT services catalogue')

@section('content')
<div class="space-y-6">

    <x-page-header title="Services Catalogue" :subtitle="'Browse all ' . $totalServices . ' services across ' . $categories->count() . ' categories'" sys="CLIENT://SERVICES" num="12">
        <x-slot:actions>
            <a href="{{ route('portal.service-request.create') }}" class="term-btn term-btn-sm">
                Request a Service
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Country Selector --}}
    <div class="term-panel p-4">
        <div class="flex flex-wrap items-center gap-4">
            <span class="term-field-label !mb-0">View pricing in:</span>
            <div class="flex gap-2 flex-wrap" id="countrySelector">
                @foreach($countries as $country)
                <button
                    type="button"
                    data-country="{{ $country->code }}"
                    class="country-btn term-btn term-btn-sm {{ $loop->first ? '' : 'term-btn-ghost' }}"
                >
                    {{ $country->currency_symbol }} {{ $country->code }} — {{ $country->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Category Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($categories as $cat)
        <div class="term-panel-2 p-3 text-center cursor-pointer" onclick="document.getElementById('cat-{{ $cat->id }}').scrollIntoView({behavior:'smooth'})">
            <div class="font-semibold text-sm text-slate-900 dark:text-white">{{ $cat->name }}</div>
            <div class="term-hint">{{ $cat->services_count }} services</div>
        </div>
        @endforeach
    </div>

    {{-- Search --}}
    <div class="term-panel p-4">
        <div class="relative">
            <svg class="w-5 h-5 text-slate-600 dark:text-term-800 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="serviceSearch" placeholder="Search services..." class="term-input !pl-10">
        </div>
    </div>

    {{-- All Categories with Services --}}
    @foreach($categories as $category)
    @if($category->services->count() > 0)
    <div id="cat-{{ $cat->id ?? $category->id }}" class="category-section">
        <div class="term-panel overflow-hidden">
            {{-- Category Header --}}
            <div class="p-5 border-b border-white/10">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $category->name }}</h2>
                        <p class="term-hint">{{ $category->services->count() }} services available</p>
                    </div>
                    <span class="term-tag">{{ $category->services->count() }} UNITS</span>
                </div>
            </div>

            {{-- Services Table --}}
            <div class="term-table-wrap !border-0">
                <table class="data-table term-table w-full">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Pricing</th>
                            <th>Delivery</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->services as $service)
                        <tr class="service-row" data-name="{{ strtolower($service->name) }}" data-desc="{{ strtolower($service->short_description) }}">
                            <td data-label="Service">
                                <a href="{{ route('portal.services.show', $service->slug) }}" class="font-semibold text-slate-900 dark:text-white hover:text-emerald-700 dark:hover:text-accent-soft transition-colors">
                                    {{ $service->name }}
                                </a>
                                <p class="text-sm text-slate-600 dark:text-term-800 mt-0.5 line-clamp-1">{{ $service->short_description }}</p>
                            </td>
                            <td data-label="Pricing">
                                <div class="pricing-display">
                                    @forelse($service->countryPrices as $cp)
                                        @if($cp->country)
                                        <span data-country-price="{{ $cp->country->code }}" style="display:none">
                                            @if($cp->pricing_type === 'custom_quote')
                                                <span class="term-tag">Custom Quote</span>
                                            @elseif($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                                                <span class="text-sm text-slate-600 dark:text-term-800 line-through font-mono">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                                <span class="text-sm font-bold tabular-nums ml-1"><span class="fin-tag fin-tag-income">{{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }}</span></span>
                                            @else
                                                <span class="text-sm font-bold tabular-nums">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                                <span class="term-hint ml-1">{{ ucfirst($cp->pricing_type) }}</span>
                                            @endif
                                        </span>
                                        @endif
                                    @empty
                                        <span class="term-hint">Contact for pricing</span>
                                    @endforelse
                                </div>
                            </td>
                            <td data-label="Delivery">
                                <span class="text-sm text-slate-600 dark:text-term-800">{{ $service->estimated_delivery_time ?? 'Contact us' }}</span>
                            </td>
                            <td data-label="Action">
                                <div class="flex gap-2 flex-wrap">
                                    <a href="{{ route('portal.services.show', $service->slug) }}" class="term-btn term-btn-ghost term-btn-sm">
                                        View Details
                                    </a>
                                    <a href="{{ route('portal.service-request.create', ['service_id' => $service->id]) }}" class="term-btn term-btn-sm">
                                        Order
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    {{-- Bottom CTA --}}
    <div class="term-panel p-6 text-center">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Can't find what you need?</h3>
        <p class="text-slate-600 dark:text-term-800 mb-4 text-sm">Request a custom quote and our team will design a solution tailored to your needs.</p>
        <div class="flex justify-center gap-3 flex-wrap">
            <a href="{{ route('portal.service-request.create') }}" class="term-btn term-btn-sm">
                Request Custom Quote
            </a>
            <a href="{{ route('portal.tickets.create') }}" class="term-btn term-btn-ghost term-btn-sm">
                Contact Support
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('serviceSearch');
    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.service-row').forEach(row => {
            const name = row.dataset.name || '';
            const desc = row.dataset.desc || '';
            row.style.display = (name.includes(term) || desc.includes(term)) ? '' : 'none';
        });
    });

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
