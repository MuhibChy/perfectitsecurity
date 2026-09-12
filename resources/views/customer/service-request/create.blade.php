@extends('layouts.app')
@section('page-title', 'Request a Service')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Request a Service</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Tell us about your requirements and we'll prepare a tailored proposal.</p>
    </div>

    <form method="POST" action="{{ route('portal.service-request.store') }}" class="space-y-6">
        @csrf

        <!-- Service Selection -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📋 Service Details</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Service</label>
                    <select name="service_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        <option value="">General Request</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ ($selectedServiceId == $service->id) ? 'selected' : '' }}>
                                {{ $service->category->icon ?? '' }} {{ $service->name }} @if($service->category) — {{ $service->category->name }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Market / Country</label>
                    <select name="country_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        <option value="">Select market...</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->currency_symbol }} {{ $country->name }} ({{ $country->currency_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                        <option value="low">Low — No rush</option>
                        <option value="medium" selected>Medium — Standard timeline</option>
                        <option value="high">High — Important</option>
                        <option value="urgent">Urgent — ASAP</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Budget Range (optional)</label>
                    <input type="number" name="budget" value="{{ old('budget') }}" min="0" placeholder="Your budget"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Preferred Start Date</label>
                    <input type="date" name="preferred_start_date" value="{{ old('preferred_start_date') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
            </div>
        </div>

        <!-- Requirements -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📝 Requirements</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project Requirements *</label>
                    <textarea name="requirements" rows="6" required
                        placeholder="Describe your requirements in detail. Include:
- What you need
- Current situation
- Expected outcomes
- Any technical requirements
- Timeline expectations"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm @error('requirements') border-red-500 @enderror">{{ old('requirements') }}</textarea>
                    @error('requirements') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Scope Details (optional)</label>
                    <textarea name="scope_details" rows="3" placeholder="Any specific scope details or technical specifications..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('scope_details') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exclusions (optional)</label>
                    <textarea name="exclusions" rows="2" placeholder="Anything that should NOT be included in scope..."
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('exclusions') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('portal.dashboard') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Submit Request</button>
        </div>
    </form>
</div>
@endsection
