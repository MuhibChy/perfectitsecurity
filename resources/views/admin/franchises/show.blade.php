@extends('layouts.app')
@section('page-title', 'Franchise detail')
@section('content')

    <x-page-header title="Franchise detail" sys="SYSTEM://FRANCHISES" />
<div class="term-panel p-6 mb-4">
    <h2 class="text-xl font-bold">{{ $franchise->name }} ({{ $franchise->franchise_code }})</h2>
    <p class="text-sm text-gray-500">Owner: {{ $franchise->owner->name }} · Territory: {{ $franchise->territory ?? '—' }} · Members: {{ $franchise->members->count() }} · Status: {{ $franchise->status }}</p>
    <p class="text-sm mt-2">Member-customer revenue (completed payments): <strong>{{ $revenue }}</strong> — company ledger stays separate; franchise shares move only via audited transfers.</p>
</div>
<div class="term-panel p-6 mb-4">
    <h3 class="font-bold mb-2">Member orders</h3>
    @forelse($orders as $o)<div class="text-sm border-t py-1">{{ $o->order_number }} · {{ $o->customer->name }} · {{ $o->service->name ?? '' }} · {{ $o->total }} · {{ $o->status }}</div>@empty<p class="text-sm text-gray-500">None.</p>@endforelse
</div>
<div class="term-panel p-6">
    <h3 class="font-bold mb-2">Franchise transfers</h3>
    @forelse($franchise->transfers as $t)<div class="text-sm border-t py-1">{{ $t->reference }} · {{ $t->currency }} {{ $t->amount }} · {{ $t->status }}</div>@empty<p class="text-sm text-gray-500">None.</p>@endforelse
</div>
@endsection
