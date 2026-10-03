@extends('layouts.app')
@section('page-title', 'Promotion Campaign')

@section('content')
<div class="space-y-6">
    <x-page-header title="Promotion Campaign" sys="OPS://SETTINGS/PROMO" />
    <div class="term-panel p-6 max-w-3xl">
        <p class="text-sm mb-4">Status: <x-status-badge :status="$isActive ? 'active' : 'inactive'" />
            <span class="text-xs opacity-70 ml-2">Server-side enforced. Disabling or expiry never rewrites historical quotations.</span></p>
        <form method="POST" action="{{ route('admin.settings.promotion.update') }}" class="space-y-4">
            @csrf
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="promo_enabled" value="1" {{ ($campaign['promo.enabled'] ?? '0') === '1' ? 'checked' : '' }}> Campaign enabled</label>
            <div><label class="block text-sm mb-1">Campaign name</label><input type="text" name="promo_name" value="{{ old('promo_name', $campaign['promo.name'] ?? '') }}" required class="term-input w-full"></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Discount percent</label><input type="number" name="promo_percent" min="0" max="100" step="0.01" value="{{ old('promo_percent', $campaign['promo.percent'] ?? '33') }}" required class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Time zone</label><input type="text" name="promo_timezone" value="{{ old('promo_timezone', $campaign['promo.timezone'] ?? 'UTC') }}" class="term-input w-full"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Starts at (optional)</label><input type="datetime-local" name="promo_starts_at" value="{{ old('promo_starts_at', isset($campaign['promo.starts_at']) && $campaign['promo.starts_at'] ? \Carbon\Carbon::parse($campaign['promo.starts_at'])->format('Y-m-d\TH:i') : '') }}" class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Ends at (optional)</label><input type="datetime-local" name="promo_ends_at" value="{{ old('promo_ends_at', isset($campaign['promo.ends_at']) && $campaign['promo.ends_at'] ? \Carbon\Carbon::parse($campaign['promo.ends_at'])->format('Y-m-d\TH:i') : '') }}" class="term-input w-full"></div>
            </div>
            <div><label class="block text-sm mb-1">Eligible service categories</label>
                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach($categories as $cat)
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="promo_category_ids[]" value="{{ $cat->id }}" {{ in_array($cat->id, old('promo_category_ids', $eligible)) ? 'checked' : '' }}> {{ $cat->name }}</label>
                    @endforeach
                </div>
            </div>
            <div><label class="block text-sm mb-1">Promotional terms (shown to customers)</label><textarea name="promo_terms" rows="3" class="term-input w-full">{{ old('promo_terms', $campaign['promo.terms'] ?? '') }}</textarea></div>
            <button type="submit" class="term-btn term-btn-sm">Save Campaign</button>
        </form>
    </div>
</div>
@endsection
