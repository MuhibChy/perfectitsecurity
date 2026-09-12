@extends('layouts.app')
@section('page-title', $service->name)
@section('description', $service->short_description)

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('portal.services.index') }}" class="hover:text-primary-600">Services</a>
        <span>/</span>
        @if($service->category)
        <span>{{ $service->category->name }}</span>
        <span>/</span>
        @endif
        <span class="text-gray-900 dark:text-white font-medium">{{ $service->name }}</span>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Service Header --}}
            <div class="glass-card p-6">
                <div class="flex items-start gap-4">
                    @if($service->category)
                    <span class="text-xs font-medium px-3 py-1 rounded-full flex-shrink-0" style="background-color: {{ $service->category->color }}20; color: {{ $service->category->color }}">{{ $service->category->name }}</span>
                    @endif
                    @if($service->is_featured)
                    <span class="text-xs font-medium px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 flex-shrink-0">⭐ Featured</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4 mb-3">{{ $service->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $service->full_description ?: $service->short_description }}</p>

                @if($service->estimated_delivery_time)
                <div class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Estimated delivery: {{ $service->estimated_delivery_time }}
                </div>
                @endif

                @if($service->complexity_level)
                <div class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Complexity: {{ ucfirst($service->complexity_level) }}
                </div>
                @endif
            </div>

            {{-- Deliverables --}}
            @if($service->deliverables && count($service->deliverables) > 0)
            <div class="glass-card p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📦 What You'll Get</h2>
                <ul class="space-y-2">
                    @foreach($service->deliverables as $item)
                    <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Features --}}
            @if($service->features && count($service->features) > 0)
            <div class="glass-card p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">✨ Key Features</h2>
                <ul class="space-y-2">
                    @foreach($service->features as $item)
                    <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-primary-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Service Scope --}}
            @if($service->service_scope)
            <div class="glass-card p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📋 Scope</h2>
                <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line">{{ $service->service_scope }}</div>
            </div>
            @endif

            {{-- Process --}}
            <div class="glass-card p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">🔄 How It Works</h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-primary-600">1</div>
                        <div><h4 class="font-medium text-gray-900 dark:text-white text-sm">Submit Request</h4><p class="text-sm text-gray-500">Choose the service and describe your requirements</p></div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-primary-600">2</div>
                        <div><h4 class="font-medium text-gray-900 dark:text-white text-sm">Receive Quote</h4><p class="text-sm text-gray-500">Our team reviews your requirements and prepares a quote</p></div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-primary-600">3</div>
                        <div><h4 class="font-medium text-gray-900 dark:text-white text-sm">Approve & Start</h4><p class="text-sm text-gray-500">Accept the quote and we begin delivering</p></div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-primary-600">4</div>
                        <div><h4 class="font-medium text-gray-900 dark:text-white text-sm">Review & Complete</h4><p class="text-sm text-gray-500">Review deliverables and mark as complete</p></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Pricing Card --}}
            <div class="glass-card p-6 sticky top-20">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Pricing</h3>

                @foreach($countries as $country)
                @php $price = $service->countryPrices->firstWhere('country.code', $country->code); @endphp
                @if($price)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $country->currency_symbol }} {{ $country->code }}</span>
                    @if($price->pricing_type === 'custom_quote')
                        <span class="text-sm font-medium text-purple-600 dark:text-purple-400">Custom Quote</span>
                    @elseif($price->discount_price && $price->discount_valid_until && \Carbon\Carbon::parse($price->discount_valid_until)->isFuture())
                        <div class="text-right">
                            <span class="text-xs text-gray-400 line-through">{{ $country->currency_symbol }}{{ number_format($price->price, 0) }}</span>
                            <span class="text-sm font-bold text-green-600 dark:text-green-400 ml-1">{{ $country->currency_symbol }}{{ number_format($price->discount_price, 0) }}</span>
                            <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-1.5 py-0.5 rounded-full ml-1">-25%</span>
                        </div>
                    @else
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $country->currency_symbol }}{{ number_format($price->price, 0) }}</span>
                    @endif
                </div>
                @endif
                @endforeach

                <div class="text-xs text-gray-400 mt-3 mb-4">
                    @if($service->pricing_type === 'custom_quote')
                        Custom pricing — contact us for a quote
                    @else
                        Pricing type: {{ ucfirst($service->pricing_type) }}
                    @endif
                </div>

                <div class="space-y-3">
                    <a href="{{ route('portal.orders.create', ['service_id' => $service->id]) }}" class="block w-full text-center px-4 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 text-sm font-semibold transition-colors">
                        🛒 Order This Service
                    </a>
                    <a href="{{ route('portal.orders.create', ['service_id' => $service->id, 'discuss' => 1]) }}" class="block w-full text-center px-4 py-3 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium transition-colors">
                        💬 Discuss Price / Custom Quote
                    </a>
                    <a href="{{ route('portal.tickets.create') }}" class="block w-full text-center px-4 py-3 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium transition-colors">
                        📞 Contact Us
                    </a>
                </div>
            </div>

            {{-- Related Services --}}
            @if($related->count() > 0)
            <div class="glass-card p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Related Services</h3>
                <div class="space-y-3">
                    @foreach($related as $rel)
                    <a href="{{ route('portal.services.show', $rel->slug) }}" class="block p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $rel->name }}</h4>
                        @php $rp = $rel->countryPrices->firstWhere('country.code', 'UK') ?? $rel->countryPrices->first(); @endphp
                        @if($rp && $rp->pricing_type !== 'custom_quote')
                        <span class="text-xs text-gray-500">From {{ $rp->country->currency_symbol }}{{ number_format($rp->discount_price && $rp->discount_valid_until && \Carbon\Carbon::parse($rp->discount_valid_until)->isFuture() ? $rp->discount_price : $rp->price, 0) }}</span>
                        @else
                        <span class="text-xs text-gray-500">Custom Quote</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
