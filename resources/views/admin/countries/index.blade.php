@extends('layouts.app')
@section('page-title', 'Countries & Currencies')
@section('content')
<div class="space-y-6">
    <x-page-header title="Countries & Currencies" sys="SYSTEM://COUNTRIES" />
    <p class="text-sm text-gray-500">Enable/disable supported currencies, set decimal precision and regions. Changes apply to formatting, catalogs, and finance grouping immediately.</p>
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
                <thead><tr><th>Country</th><th>Currency</th><th>Symbol</th><th>Decimals</th><th>Region</th><th>Tax %</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($countries as $country)
                    <tr>
                        <td class="font-medium" data-label="Country">{{ $country->name }}<div class="text-xs text-gray-500 font-mono">{{ $country->code }}</div></td>
                        <td class="font-mono" data-label="Currency">{{ $country->currency_code }}<div class="text-xs text-gray-500">{{ $country->currency_name }}</div></td>
                        <td class="font-mono" data-label="Symbol">{{ $country->currency_symbol }}</td>
                        <td data-label="Decimals">{{ $country->decimal_places }}</td>
                        <td data-label="Region">{{ $country->region ?? '—' }}</td>
                        <td data-label="Tax %">{{ $country->tax_rate }}</td>
                        <td data-label="Status"><span class="term-tag {{ $country->is_active ? '' : '' }}">{{ $country->is_active ? 'Active' : 'Disabled' }}</span></td>
                        <td data-label="Actions"><a href="{{ route('admin.countries.edit', $country) }}" class="term-btn term-btn-ghost term-btn-sm">Edit</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-500 py-8">No countries configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">{{ $countries->links() }}</div>
    </div>
</div>
@endsection
