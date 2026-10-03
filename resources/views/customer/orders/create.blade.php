@extends('layouts.app')
@section('page-title', $discussPrice ? 'Discuss Service Pricing' : 'Order Service')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header :title="$discussPrice ? 'Discuss Service Price & Custom Scope' : 'Order IT Service'" subtitle="Provide your project requirements. Our engineering team will review scope, agree on deliverables, and schedule execution." sys="ORDER://CREATE" :breadcrumbs="['Orders' => route('portal.orders.index'), ($discussPrice ? 'Discuss Price' : 'New Order') => null]" />

    @if(!$user->isFullyVerified())
    <div class="term-alert term-alert-warn">
        <span class="term-alert-tag">VERIFY</span>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Account Verification Required for Confirmation</h4>
            <p class="text-xs text-slate-600 dark:text-term-800 mt-0.5">You can submit your requirements for price discussion today. Both email and phone verification must be completed before order financial confirmation.</p>
        </div>
        <a href="{{ route('portal.verification.phone') }}" class="term-btn term-btn-sm flex-shrink-0">
            Verify Now
        </a>
    </div>
    @endif

    <div class="term-panel p-6">
        <form action="{{ route('portal.orders.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="negotiate" value="{{ $discussPrice ? 1 : 0 }}">

            {{-- Service Selection --}}
            <div>
                <label class="term-field-label">Select Service *</label>
                <select name="service_id" required class="term-input">
                    <option value="">-- Choose an IT Service --</option>
                    @foreach($services as $svc)
                    <option value="{{ $svc->id }}" {{ ($selectedService && $selectedService->id == $svc->id) || old('service_id') == $svc->id ? 'selected' : '' }}>
                        {{ $svc->name }} (Base: ${{ number_format($svc->base_price ?: 0, 2) }})
                    </option>
                    @endforeach
                </select>
                @error('service_id') <p class="term-error">{{ $message }}</p> @enderror
            </div>

            {{-- Requirements & Problem Description --}}
            <div>
                <label class="term-field-label">Detailed Requirements &amp; Desired Outcome *</label>
                <textarea name="requirements" rows="4" required placeholder="Describe your technical problem, current environment, goals, and specific deliverables..."
                          class="term-input">{{ old('requirements') }}</textarea>
                @error('requirements') <p class="term-error">{{ $message }}</p> @enderror
            </div>

            {{-- Urgency & Preferred Date --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Urgency Level</label>
                    <select name="urgency" class="term-input">
                        <option value="low" {{ old('urgency') == 'low' ? 'selected' : '' }}>Low — Standard delivery</option>
                        <option value="medium" {{ old('urgency', 'medium') == 'medium' ? 'selected' : '' }}>Medium — Normal priority</option>
                        <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>High — Expedited response</option>
                        <option value="critical" {{ old('urgency') == 'critical' ? 'selected' : '' }}>Critical — Urgent intervention</option>
                    </select>
                </div>
                <div>
                    <label class="term-field-label">Preferred Target Date</label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date') }}"
                           class="term-input">
                </div>
            </div>

            {{-- Proposed Price if Discussing --}}
            @if($discussPrice)
            <div>
                <label class="term-field-label">Your Proposed Budget / Offer (USD) (Optional)</label>
                <input type="number" step="0.01" min="1" name="proposed_price" value="{{ old('proposed_price') }}" placeholder="e.g. 500.00"
                       class="term-input">
                <span class="term-hint block">Leave blank if you would like our engineers to suggest an initial quote based on your requirements.</span>
            </div>
            @endif

            {{-- Additional Notes --}}
            <div>
                <label class="term-field-label">Additional Notes / Access Details</label>
                <textarea name="customer_notes" rows="2" placeholder="Any server access, credentials requirements, or specific tools..."
                          class="term-input">{{ old('customer_notes') }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/10">
                <a href="{{ route('portal.orders.index') }}" class="term-btn term-btn-ghost">
                    Cancel
                </a>
                <button type="submit" class="term-btn">
                    {{ $discussPrice ? 'Submit for Price Discussion' : 'Place Order' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
