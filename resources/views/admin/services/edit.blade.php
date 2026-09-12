@extends('layouts.app')
@section('page-title', 'Edit Service')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.services.index') }}" class="text-blue-600 hover:underline text-sm">← Back to Services</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">Edit: {{ $service->name }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.services.update', $service->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Info -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Name *</label>
                    <input type="text" name="name" value="{{ old('name', $service->name) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                    <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->icon }} {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subcategory</label>
                    <input type="text" name="subcategory" value="{{ old('subcategory', $service->subcategory) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Complexity Level</label>
                    <select name="complexity_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        @foreach(['basic', 'standard', 'advanced', 'enterprise'] as $level)
                            <option value="{{ $level }}" {{ old('complexity_level', $service->complexity_level) == $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price Type *</label>
                    <select name="price_type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        @foreach(['fixed' => 'Fixed Price', 'hourly' => 'Hourly Rate', 'monthly' => 'Monthly', 'custom' => 'Custom Quote'] as $val => $label)
                            <option value="{{ $val }}" {{ old('price_type', $service->price_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Delivery</label>
                    <input type="text" name="estimated_completion" value="{{ old('estimated_completion', $service->estimated_completion) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Short Description</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $service->short_description) }}" maxlength="500" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Description</label>
                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('description', $service->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Country Pricing -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🌍 Country-Specific Pricing</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Independent prices per market. Existing prices are pre-filled.</p>
            <div class="space-y-4">
                @foreach($countries as $country)
                @php
                    $existingPrice = $service->countryPrices->firstWhere('country_id', $country->id);
                @endphp
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-2xl">{{ $country->currency_symbol }}</span>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $country->name }}</div>
                            <div class="text-xs text-gray-500">{{ $country->currency_code }}</div>
                        </div>
                    </div>
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Pricing Type</label>
                            <select name="country_prices[{{ $country->id }}][pricing_type]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                @foreach(['fixed' => 'Fixed Price', 'starting_from' => 'Starting From', 'hourly' => 'Hourly', 'daily' => 'Daily', 'monthly' => 'Monthly', 'recurring' => 'Recurring', 'custom_quote' => 'Custom Quote'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($existingPrice->pricing_type ?? 'fixed') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Price ({{ $country->currency_code }})</label>
                            <input type="number" name="country_prices[{{ $country->id }}][price]" step="0.01" min="0" value="{{ $existingPrice->price ?? 0 }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Country ID</label>
                            <input type="hidden" name="country_prices[{{ $country->id }}][country_id]" value="{{ $country->id }}">
                            <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-600 dark:text-gray-400">{{ $country->code }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Deliverables & Scope -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deliverables & Scope</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deliverables (one per line)</label>
                    <textarea name="deliverables" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('deliverables', $service->deliverables) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Features (one per line)</label>
                    <textarea name="features" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('features', $service->features) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Scope</label>
                    <textarea name="scope" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('scope', $service->scope) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exclusions</label>
                    <textarea name="exclusions" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('exclusions', $service->exclusions) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tags (one per line)</label>
                    <textarea name="tags" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('tags', $service->tags) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SEO & Settings -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">SEO & Settings</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SEO Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $service->seo_title) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon</label>
                    <input type="text" name="icon" value="{{ old('icon', $service->icon) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SEO Description</label>
                    <textarea name="seo_description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('seo_description', $service->seo_description) }}</textarea>
                </div>
                <div class="md:col-span-2 flex gap-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $service->is_featured) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Featured</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="allows_custom_quote" value="1" {{ old('allows_custom_quote', $service->allows_custom_quote) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Allow Custom Quote</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order) }}" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.services.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Update Service</button>
        </div>
    </form>
</div>
@endsection
