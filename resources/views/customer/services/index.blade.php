@extends('layouts.app')
@section('page-title', 'Services Catalogue')
@section('description', 'Browse our complete IT services catalogue')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-gray-500 dark:text-gray-400">Browse all {{ $totalServices }} services across {{ $categories->count() }} categories</p>
        </div>
        <a href="{{ route('portal.service-request.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Request a Service
        </a>
    </div>

    {{-- Country Selector --}}
    <div class="glass-card p-4">
        <div class="flex flex-wrap items-center gap-4">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View pricing in:</span>
            <div class="flex gap-2" id="countrySelector">
                @foreach($countries as $country)
                <button
                    type="button"
                    data-country="{{ $country->code }}"
                    class="country-btn px-4 py-1.5 rounded-full text-sm font-medium transition-all {{ $loop->first ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}"
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
        <div class="glass-card p-3 text-center hover:shadow-md transition-all cursor-pointer" onclick="document.getElementById('cat-{{ $cat->id }}').scrollIntoView({behavior:'smooth'})">
            <div class="text-2xl mb-1">{{ $cat->icon }}</div>
            <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ $cat->name }}</div>
            <div class="text-xs text-gray-500">{{ $cat->services_count }} services</div>
        </div>
        @endforeach
    </div>

    {{-- Search --}}
    <div class="glass-card p-4">
        <div class="relative">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="serviceSearch" placeholder="Search services..." class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>
    </div>

    {{-- All Categories with Services --}}
    @foreach($categories as $category)
    @if($category->services->count() > 0)
    <div id="cat-{{ $cat->id ?? $category->id }}" class="category-section">
        <div class="glass-card overflow-hidden">
            {{-- Category Header --}}
            <div class="p-5 border-b border-gray-100 dark:border-gray-800" style="border-left: 4px solid {{ $category->color }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ $category->icon }}</span>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $category->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $category->services->count() }} services available</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Services Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Service</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pricing</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Delivery</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($category->services as $service)
                        <tr class="service-row hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors" data-name="{{ strtolower($service->name) }}" data-desc="{{ strtolower($service->short_description) }}">
                            <td class="px-5 py-4">
                                <a href="{{ route('portal.services.show', $service->slug) }}" class="font-semibold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    {{ $service->name }}
                                </a>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">{{ $service->short_description }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <div class="pricing-display">
                                    @forelse($service->countryPrices as $cp)
                                        @if($cp->country)
                                        <span data-country-price="{{ $cp->country->code }}" style="display:none">
                                            @if($cp->pricing_type === 'custom_quote')
                                                <span class="text-sm text-purple-600 dark:text-purple-400 font-medium">Custom Quote</span>
                                            @elseif($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                                                <span class="text-sm text-gray-400 line-through">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                                <span class="text-sm font-bold text-green-600 dark:text-green-400 ml-1">{{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }}</span>
                                                <span class="ml-1 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-1.5 py-0.5 rounded-full">-25%</span>
                                            @else
                                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $cp->country->currency_symbol }}{{ number_format($cp->price, 0) }}</span>
                                                <span class="text-xs text-gray-400 ml-1">{{ ucfirst($cp->pricing_type) }}</span>
                                            @endif
                                        </span>
                                        @endif
                                    @empty
                                        <span class="text-sm text-gray-400">Contact for pricing</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $service->estimated_delivery_time ?? 'Contact us' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('portal.services.show', $service->slug) }}" class="px-3 py-1.5 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-lg text-xs font-medium hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors">
                                        View Details
                                    </a>
                                    <a href="{{ route('portal.service-request.create', ['service_id' => $service->id]) }}" class="px-3 py-1.5 bg-brand-600 text-white rounded-lg text-xs font-medium hover:bg-brand-700 transition-colors">
                                        🛒 Order
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
    <div class="glass-card p-6 text-center">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Can't find what you need?</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">Request a custom quote and our team will design a solution tailored to your needs.</p>
        <div class="flex justify-center gap-3">
            <a href="{{ route('portal.service-request.create') }}" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 text-sm font-medium transition-colors">
                🛒 Request Custom Quote
            </a>
            <a href="{{ route('portal.tickets.create') }}" class="px-6 py-2.5 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium transition-colors">
                📞 Contact Support
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('serviceSearch');
    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.service-row').forEach(row => {
            const name = row.dataset.name || '';
            const desc = row.dataset.desc || '';
            row.style.display = (name.includes(term) || desc.includes(term)) ? '' : 'none';
        });
    });

    // Country selector
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
        // Update active button highlight
        document.querySelectorAll('.country-btn').forEach(btn => {
            if (btn.dataset.country === code) {
                btn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                btn.classList.add('bg-primary-600', 'text-white', 'shadow-sm');
            } else {
                btn.classList.remove('bg-primary-600', 'text-white', 'shadow-sm');
                btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            }
        });

        // Show prices for selected country, hide all others
        document.querySelectorAll('[data-country-price]').forEach(el => {
            el.style.display = (el.dataset.countryPrice === code) ? '' : 'none';
        });

        // Fallback: if a service has no price for selected country, show its first available
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
