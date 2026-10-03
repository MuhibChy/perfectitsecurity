@extends('layouts.app')
@section('page-title', $service->name)
@section('description', $service->short_description)

@section('content')
<div class="space-y-6">

    <x-page-header :title="$service->name" :subtitle="$service->short_description" sys="CLIENT://SERVICES" :breadcrumbs="['Services' => route('portal.services.index'), $service->name => null]" />

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Service Header --}}
            <div class="term-panel p-6">
                <div class="flex items-start gap-2 flex-wrap">
                    @if($service->category)
                    <span class="term-tag">{{ $service->category->name }}</span>
                    @endif
                    @if($service->is_featured)
                    <span class="term-tag term-tag-accent">Featured</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-4 mb-3">{{ $service->name }}</h1>
                <p class="text-slate-600 dark:text-term-800 leading-relaxed text-sm">{{ $service->full_description ?: $service->short_description }}</p>

                @if($service->estimated_delivery_time)
                <div class="mt-4 flex items-center gap-2 text-sm text-slate-600 dark:text-term-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Estimated delivery: {{ $service->estimated_delivery_time }}
                </div>
                @endif

                @if($service->complexity_level)
                <div class="mt-2 flex items-center gap-2 text-sm text-slate-600 dark:text-term-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Complexity: {{ ucfirst($service->complexity_level) }}
                </div>
                @endif
            </div>

            {{-- Deliverables --}}
            @if($service->deliverables && count($service->deliverables) > 0)
            <div class="term-panel p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">What You'll Get</h2>
                <ul class="space-y-2">
                    @foreach($service->deliverables as $item)
                    <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-term-800">
                        <span class="text-accent-soft font-mono">[+]</span>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Features --}}
            @if($service->features && count($service->features) > 0)
            <div class="term-panel p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Key Features</h2>
                <ul class="space-y-2">
                    @foreach($service->features as $item)
                    <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-term-800">
                        <span class="text-accent-soft font-mono">[✓]</span>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Service Scope --}}
            @if($service->service_scope)
            <div class="term-panel p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Scope</h2>
                <div class="text-sm text-slate-600 dark:text-term-800 leading-relaxed whitespace-pre-line">{{ $service->service_scope }}</div>
            </div>
            @endif

            {{-- Process --}}
            <div class="term-panel p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">How It Works</h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0 text-sm font-bold font-mono">1</div>
                        <div><h4 class="font-medium text-slate-900 dark:text-white text-sm">Submit Request</h4><p class="text-sm text-slate-600 dark:text-term-800">Choose the service and describe your requirements</p></div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0 text-sm font-bold font-mono">2</div>
                        <div><h4 class="font-medium text-slate-900 dark:text-white text-sm">Receive Quote</h4><p class="text-sm text-slate-600 dark:text-term-800">Our team reviews your requirements and prepares a quote</p></div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0 text-sm font-bold font-mono">3</div>
                        <div><h4 class="font-medium text-slate-900 dark:text-white text-sm">Approve &amp; Start</h4><p class="text-sm text-slate-600 dark:text-term-800">Accept the quote and we begin delivering</p></div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 border border-accent/30 bg-accent/10 text-accent-soft flex items-center justify-center flex-shrink-0 text-sm font-bold font-mono">4</div>
                        <div><h4 class="font-medium text-slate-900 dark:text-white text-sm">Review &amp; Complete</h4><p class="text-sm text-slate-600 dark:text-term-800">Review deliverables and mark as complete</p></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Pricing Card --}}
            <div class="term-panel p-6 lg:sticky lg:top-20">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Pricing</h3>

                @foreach($countries as $country)
                @php $price = $service->countryPrices->firstWhere('country.code', $country->code); @endphp
                @if($price)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-white/5' : '' }} gap-2">
                    <span class="font-mono text-[11px] text-slate-600 dark:text-term-800">{{ $country->currency_symbol }} {{ $country->code }}</span>
                    @if($price->pricing_type === 'custom_quote')
                        <span class="term-tag">Custom Quote</span>
                    @elseif($price->discount_price && $price->discount_valid_until && \Carbon\Carbon::parse($price->discount_valid_until)->isFuture())
                        <div class="text-right">
                            <span class="text-xs text-slate-600 dark:text-term-800 line-through font-mono">{{ $country->currency_symbol }}{{ number_format($price->price, 0) }}</span>
                            <span class="text-sm font-bold tabular-nums ml-1"><span class="fin-tag fin-tag-income">{{ $country->currency_symbol }}{{ number_format($price->discount_price, 0) }}</span></span>
                        </div>
                    @else
                        <span class="text-sm font-bold tabular-nums">{{ $country->currency_symbol }}{{ number_format($price->price, 0) }}</span>
                    @endif
                </div>
                @endif
                @endforeach

                <div class="term-hint mt-3 mb-4">
                    @if($service->pricing_type === 'custom_quote')
                        Custom pricing — contact us for a quote
                    @else
                        Pricing type: {{ ucfirst($service->pricing_type) }}
                    @endif
                </div>

                <div class="space-y-3">
                    <a href="{{ route('portal.orders.create', ['service_id' => $service->id]) }}" class="term-btn term-btn-sm block w-full text-center">
                        Order This Service
                    </a>
                    <a href="{{ route('portal.orders.create', ['service_id' => $service->id, 'discuss' => 1]) }}" class="term-btn term-btn-ghost term-btn-sm block w-full text-center">
                        Discuss Price / Custom Quote
                    </a>
                    <a href="{{ route('portal.tickets.create') }}" class="term-btn term-btn-ghost term-btn-sm block w-full text-center">
                        Contact Us
                    </a>
                </div>
            </div>

            {{-- Related Services --}}
            @if($related->count() > 0)
            <div class="term-panel p-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Related Services</h3>
                <div class="space-y-3">
                    @foreach($related as $rel)
                    <a href="{{ route('portal.services.show', $rel->slug) }}" class="block term-panel-2 p-3">
                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $rel->name }}</h4>
                        @php $rp = $rel->countryPrices->firstWhere('country.code', 'UK') ?? $rel->countryPrices->first(); @endphp
                        @if($rp && $rp->pricing_type !== 'custom_quote')
                        <span class="term-hint">From {{ $rp->country->currency_symbol }}{{ number_format($rp->discount_price && $rp->discount_valid_until && \Carbon\Carbon::parse($rp->discount_valid_until)->isFuture() ? $rp->discount_price : $rp->price, 0) }}</span>
                        @else
                        <span class="term-hint">Custom Quote</span>
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
