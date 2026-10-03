@extends('layouts.app')
@section('page-title', 'Services Catalogue')

@section('content')
<div class="space-y-6">
    <x-page-header title="Services Catalogue" sys="OPS://SERVICES" />
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Services Catalogue</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $services->total() }} services in {{ $categories->count() }} categories</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.service-requests.index') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm font-medium">
                📋 Service Requests
            </a>
            <a href="{{ route('admin.services.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                + Add Service
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="term-panel p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="term-field-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="term-field-label">Category</label>
                <select name="category_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->icon }} {{ $cat->name }} ({{ $cat->services_count }})</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="term-field-label">Status</label>
                <select name="is_active" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">Filter</button>
                <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-sm">Reset</a>
            </div>
        </form>
    </div>

    <!-- Services Table -->
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table w-full text-sm term-table">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Service</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">UK (£)</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">US ($)</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">BD (৳)</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($services as $service)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $service->name }}</div>
                            @if($service->is_featured)
                                <span class="term-tag inline-block mt-1">Featured</span>
                            @endif
                            @if($service->allows_custom_quote)
                                <span class="term-tag inline-block mt-1">Custom Quote</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $service->category->name ?? '-' }}</td>
                        @php
                            $ukPrice = $service->countryPrices->firstWhere('country.code', 'UK');
                            $usPrice = $service->countryPrices->firstWhere('country.code', 'US');
                            $bdPrice = $service->countryPrices->firstWhere('country.code', 'BD');
                        @endphp
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            @if($ukPrice)
                                <div class="font-medium">£{{ number_format($ukPrice->price, 0) }}</div>
                                <div class="text-xs text-gray-400">{{ $ukPrice->pricing_type }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            @if($usPrice)
                                <div class="font-medium">${{ number_format($usPrice->price, 0) }}</div>
                                <div class="text-xs text-gray-400">{{ $usPrice->pricing_type }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            @if($bdPrice)
                                <div class="font-medium">৳{{ number_format($bdPrice->price, 0) }}</div>
                                <div class="text-xs text-gray-400">{{ $bdPrice->pricing_type }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($service->is_active)
                                <span class="term-tag">Active</span>
                            @else
                                <span class="term-tag">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('admin.services.show', $service->id) }}" class="px-2 py-1 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded text-xs">View</a>
                                <a href="{{ route('admin.services.edit', $service->id) }}" class="px-2 py-1 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded text-xs">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No services found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
