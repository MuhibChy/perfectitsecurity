@extends('layouts.app')
@section('page-title', 'Request a Service')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header title="Request a Service" subtitle="Tell us about your requirements and we'll prepare a tailored proposal." sys="CLIENT://SERVICES" />

    <form method="POST" action="{{ route('portal.service-request.store') }}" class="space-y-6">
        @csrf

        <!-- Service Selection -->
        <div class="term-panel p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Service Details</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Select Service</label>
                    <select name="service_id" class="term-input">
                        <option value="">General Request</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ ($selectedServiceId == $service->id) ? 'selected' : '' }}>
                                {{ $service->name }} @if($service->category) — {{ $service->category->name }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="term-field-label">Your Market / Country</label>
                    <select name="country_id" class="term-input">
                        <option value="">Select market...</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->currency_symbol }} {{ $country->name }} ({{ $country->currency_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="term-field-label">Priority</label>
                    <select name="priority" class="term-input">
                        <option value="low">Low — No rush</option>
                        <option value="medium" selected>Medium — Standard timeline</option>
                        <option value="high">High — Important</option>
                        <option value="urgent">Urgent — ASAP</option>
                    </select>
                </div>
                <div>
                    <label class="term-field-label">Budget Range (optional)</label>
                    <input type="number" name="budget" value="{{ old('budget') }}" min="0" placeholder="Your budget"
                        class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Preferred Start Date</label>
                    <input type="date" name="preferred_start_date" value="{{ old('preferred_start_date') }}"
                        class="term-input">
                </div>
            </div>
        </div>

        <!-- Requirements -->
        <div class="term-panel p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Requirements</h2>
            <div class="space-y-4">
                <div>
                    <label class="term-field-label">Project Requirements *</label>
                    <textarea name="requirements" rows="6" required
                        placeholder="Describe your requirements in detail. Include:
- What you need
- Current situation
- Expected outcomes
- Any technical requirements
- Timeline expectations"
                        class="term-input @error('requirements') border-red-500 @enderror">{{ old('requirements') }}</textarea>
                    @error('requirements') <p class="term-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="term-field-label">Scope Details (optional)</label>
                    <textarea name="scope_details" rows="3" placeholder="Any specific scope details or technical specifications..."
                        class="term-input">{{ old('scope_details') }}</textarea>
                </div>
                <div>
                    <label class="term-field-label">Exclusions (optional)</label>
                    <textarea name="exclusions" rows="2" placeholder="Anything that should NOT be included in scope..."
                        class="term-input">{{ old('exclusions') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('portal.dashboard') }}" class="term-btn term-btn-ghost">Cancel</a>
            <button type="submit" class="term-btn">Submit Request</button>
        </div>
    </form>
</div>
@endsection
