@extends('layouts.app')
@section('page-title', $discussPrice ? 'Discuss Service Pricing' : 'Order Service')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('portal.orders.index') }}" class="hover:text-primary-600">Orders</a>
        <span>/</span>
        <span class="text-gray-900 dark:text-white font-medium">{{ $discussPrice ? 'Discuss Price' : 'New Order' }}</span>
    </div>

    @if(!$user->isFullyVerified())
    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800/40 dark:bg-amber-950/20 flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="text-amber-600 text-xl">⚠️</span>
            <div>
                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">Account Verification Required for Confirmation</h4>
                <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">You can submit your requirements for price discussion today. Both email and phone verification must be completed before order financial confirmation.</p>
            </div>
        </div>
        <a href="{{ route('portal.verification.phone') }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg flex-shrink-0">
            Verify Now
        </a>
    </div>
    @endif

    <div class="glass-card p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $discussPrice ? '💬 Discuss Service Price & Custom Scope' : '🛒 Order IT Service' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">Provide your project requirements. Our engineering team will review scope, agree on deliverables, and schedule execution.</p>

        <form action="{{ route('portal.orders.store') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="negotiate" value="{{ $discussPrice ? 1 : 0 }}">

            {{-- Service Selection --}}
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Select Service *</label>
                <select name="service_id" required class="mt-1.5 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">
                    <option value="">-- Choose an IT Service --</option>
                    @foreach($services as $svc)
                    <option value="{{ $svc->id }}" {{ ($selectedService && $selectedService->id == $svc->id) || old('service_id') == $svc->id ? 'selected' : '' }}>
                        {{ $svc->name }} (Base: ${{ number_format($svc->base_price ?: 0, 2) }})
                    </option>
                    @endforeach
                </select>
                @error('service_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Requirements & Problem Description --}}
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Detailed Requirements & Desired Outcome *</label>
                <textarea name="requirements" rows="4" required placeholder="Describe your technical problem, current environment, goals, and specific deliverables..."
                          class="mt-1.5 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">{{ old('requirements') }}</textarea>
                @error('requirements') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Urgency & Preferred Date --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Urgency Level</label>
                    <select name="urgency" class="mt-1.5 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">
                        <option value="low" {{ old('urgency') == 'low' ? 'selected' : '' }}>Low — Standard delivery</option>
                        <option value="medium" {{ old('urgency', 'medium') == 'medium' ? 'selected' : '' }}>Medium — Normal priority</option>
                        <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>High — Expedited response</option>
                        <option value="critical" {{ old('urgency') == 'critical' ? 'selected' : '' }}>Critical — Urgent intervention</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Preferred Target Date</label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date') }}"
                           class="mt-1.5 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">
                </div>
            </div>

            {{-- Proposed Price if Discussing --}}
            @if($discussPrice)
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Your Proposed Budget / Offer (USD) (Optional)</label>
                <input type="number" step="0.01" min="1" name="proposed_price" value="{{ old('proposed_price') }}" placeholder="e.g. 500.00"
                       class="mt-1.5 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">
                <span class="text-xs text-gray-400 mt-1 block">Leave blank if you would like our engineers to suggest an initial quote based on your requirements.</span>
            </div>
            @endif

            {{-- Additional Notes --}}
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">Additional Notes / Access Details</label>
                <textarea name="customer_notes" rows="2" placeholder="Any server access, credentials requirements, or specific tools..."
                          class="mt-1.5 block w-full px-3 py-2.5 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">{{ old('customer_notes') }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('portal.orders.index') }}" class="px-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    {{ $discussPrice ? 'Submit for Price Discussion' : 'Place Order' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
