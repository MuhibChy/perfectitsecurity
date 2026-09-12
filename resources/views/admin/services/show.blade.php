@extends('layouts.app')
@section('page-title', $service->name)

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex justify-between items-start">
        <div>
            <a href="{{ route('admin.services.index') }}" class="text-blue-600 hover:underline text-sm">← Back to Services</a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $service->name }}</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">{{ $service->category->name ?? '-' }} · {{ ucfirst($service->complexity_level ?? 'standard') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.services.edit', $service->id) }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm">Edit</a>
            <form method="POST" action="{{ route('admin.services.destroy', $service->id) }}" onsubmit="return confirm('Delete this service?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Delete</button>
            </form>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="flex gap-2">
        @if($service->is_active)
            <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs rounded-full font-medium">Active</span>
        @else
            <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs rounded-full font-medium">Inactive</span>
        @endif
        @if($service->is_featured)
            <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs rounded-full font-medium">Featured</span>
        @endif
        @if($service->allows_custom_quote)
            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-xs rounded-full font-medium">Custom Quote Allowed</span>
        @endif
    </div>

    <!-- Description -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h2>
        <p class="text-gray-600 dark:text-gray-400">{{ $service->short_description }}</p>
        @if($service->description)
            <div class="mt-3 text-gray-600 dark:text-gray-400 text-sm leading-relaxed">{!! nl2br(e($service->description)) !!}</div>
        @endif
    </div>

    <!-- Country Pricing -->
    <div class="glass-card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🌍 Country-Specific Pricing</h2>
        <div class="grid md:grid-cols-3 gap-4">
            @forelse($service->countryPrices as $cp)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                <div class="text-3xl mb-2">{{ $cp->country->currency_symbol }}</div>
                <div class="font-bold text-2xl text-gray-900 dark:text-white">
                    @if($cp->pricing_type === 'custom_quote')
                        Custom Quote
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
                <div class="text-xs text-gray-500 mt-1">{{ $cp->country->name }} · {{ ucfirst($cp->pricing_type) }}</div>
                @if($cp->discount_price && $cp->discount_valid_until && \Carbon\Carbon::parse($cp->discount_valid_until)->isFuture())
                    <div class="mt-2 px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs rounded-full inline-block">
                        Discount: {{ $cp->country->currency_symbol }}{{ number_format($cp->discount_price, 0) }} until {{ $cp->discount_valid_until->format('M d, Y') }}
                    </div>
                @endif
            </div>
            @empty
            <p class="text-gray-400 text-sm col-span-3">No country pricing configured.</p>
            @endforelse
        </div>
    </div>

    <!-- Details -->
    <div class="grid md:grid-cols-2 gap-6">
        @if($service->deliverables && count($service->deliverables) > 0)
        <div class="glass-card p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">📋 Deliverables</h3>
            <ul class="space-y-2">
                @foreach($service->deliverables as $item)
                    <li class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">✓</span> {{ $item }}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($service->features && count($service->features) > 0)
        <div class="glass-card p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">⚡ Features</h3>
            <ul class="space-y-2">
                @foreach($service->features as $item)
                    <li class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                        <span class="text-blue-500 mt-0.5">•</span> {{ $item }}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Meta -->
    <div class="glass-card p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">⚙️ Metadata</h3>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">Slug:</span> <span class="text-gray-900 dark:text-white">{{ $service->slug }}</span></div>
            <div><span class="text-gray-500">Estimated Delivery:</span> <span class="text-gray-900 dark:text-white">{{ $service->estimated_completion ?? 'N/A' }}</span></div>
            <div><span class="text-gray-500">Sort Order:</span> <span class="text-gray-900 dark:text-white">{{ $service->sort_order }}</span></div>
        </div>
        @if($service->tags && count($service->tags) > 0)
            <div class="mt-3 flex flex-wrap gap-1">
                @foreach($service->tags as $tag)
                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
