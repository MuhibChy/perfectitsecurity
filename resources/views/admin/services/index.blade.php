@extends('layouts.app')
@section('page-title', 'Services Catalogue')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Services Catalogue</h1>
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
    <div class="glass-card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Category</label>
                <select name="category_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->icon }} {{ $cat->name }} ({{ $cat->services_count }})</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
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
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
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
                                <span class="inline-block mt-1 px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs rounded-full">Featured</span>
                            @endif
                            @if($service->allows_custom_quote)
                                <span class="inline-block mt-1 px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-xs rounded-full">Custom Quote</span>
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
                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs rounded-full">Inactive</span>
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
