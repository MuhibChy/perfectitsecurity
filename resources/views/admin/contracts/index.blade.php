@extends('layouts.app')
@section('page-title', 'Contracts Management')
@section('content')
<div class="glass-card p-6">
    <div class="flex items-center justify-between mb-4">
        <div><h2 class="text-xl font-bold text-gray-900 dark:text-white">Contracts</h2><p class="text-sm text-gray-500">Accepted proposals become contracts with start/end dates and value.</p></div>
        <a href="{{ route('admin.contracts.create') }}" class="btn-primary btn-sm">New Contract</a>
    </div>
    <div class="overflow-x-auto"><table class="min-w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2 pr-4">Contract</th><th class="py-2 pr-4">Customer</th><th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Value</th><th class="py-2">Period</th></tr></thead>
        <tbody>@forelse($contracts as $c)<tr class="border-t border-gray-100 dark:border-gray-800">
            <td class="py-2 pr-4"><a href="{{ route('admin.contracts.show', $c) }}" class="font-semibold text-primary-600">{{ $c->contract_number }} — {{ $c->title }}</a></td>
            <td class="py-2 pr-4">{{ $c->customer?->name ?? '—' }}</td><td class="py-2 pr-4"><span class="badge">{{ $c->status }}</span></td>
            <td class="py-2 pr-4">{{ $c->currency }} {{ number_format($c->value, 2) }}</td><td class="py-2 text-gray-500">{{ $c->start_date }} → {{ $c->end_date }}</td>
        </tr>@empty<tr><td colspan="5" class="py-8 text-center text-gray-500">No contracts yet.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="mt-4">{{ $contracts->links() }}</div>
</div>
@endsection
