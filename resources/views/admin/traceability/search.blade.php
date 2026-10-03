@extends('layouts.app')
@section('title', 'Global History Search')
@section('page-title', 'Search')

@section('content')
<x-page-header sys="OPS://TRACEABILITY" title="Global History Search" subtitle="One search across customers, employees and every reference number." />

<div class="term-panel p-5 mb-6">
    <form method="GET" action="{{ route('admin.search.index') }}" class="flex flex-col sm:flex-row gap-2">
        <input type="search" name="q" value="{{ $q }}" class="term-input flex-1" placeholder="Customer, employee, TK-, INV-, ORD-, PRJ-, TSK-, PAY-…">
        <button class="term-btn whitespace-nowrap">Search</button>
    </form>
</div>

@if($results)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="card p-5">
        <h2 class="heading-sm mb-2">Customers ({{ count($results['customers']) }})</h2>
        @forelse($results['customers'] as $c)<a href="{{ route('admin.history.customer', $c) }}" class="block py-2 border-b border-slate-100 dark:border-white/5 last:border-0 link-arrow text-sm">{{ $c->name }} →</a>@empty<p class="body-sm">None.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-2">Employees ({{ count($results['employees']) }})</h2>
        @forelse($results['employees'] as $e)<a href="{{ route('admin.history.employee', $e) }}" class="block py-2 border-b border-slate-100 dark:border-white/5 last:border-0 link-arrow text-sm">{{ $e->name }} →</a>@empty<p class="body-sm">None.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-2">References ({{ count($results['references']) }})</h2>
        @forelse($results['references'] as $r)<a href="{{ $r['url'] }}" class="block py-2 border-b border-slate-100 dark:border-white/5 last:border-0 link-arrow text-sm">{{ $r['label'] }} {{ $r['ref'] }} →</a>@empty<p class="body-sm">None.</p>@endforelse
    </div>
</div>
@endif
@endsection
