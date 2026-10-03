@extends('layouts.app')
@section('page-title', 'Edit ' . $country->name)
@section('content')

    <x-page-header title="Countries" sys="SYSTEM://COUNTRIES" />
<div class="max-w-2xl mx-auto">
    <div class="term-panel p-6">
        <form method="POST" action="{{ route('admin.countries.update', $country) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="text-sm text-gray-500">Currency <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $country->currency_code }} ({{ $country->currency_symbol }})</span> — code and symbol are immutable to protect historical transactions.</div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Decimal Places *</label>
                    <input type="number" name="decimal_places" value="{{ old('decimal_places', $country->decimal_places) }}" required min="0" max="3" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Region</label>
                    <select name="region" class="term-input">
                        @foreach(['UK','Europe','Middle East','North America','South Asia','Other'] as $r)
                        <option value="{{ $r }}" {{ old('region', $country->region) === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="term-field-label">Tax Rate %</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $country->tax_rate) }}" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $country->sort_order) }}" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Timezone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $country->timezone) }}" maxlength="50" class="term-input">
                </div>
                <div class="flex items-end pb-3">
                    <label class="term-field-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $country->is_active)) class="rounded"> Active (enables currency platform-wide)</label>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.countries.index') }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                <button type="submit" class="term-btn term-btn-sm">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
