@extends('layouts.app')
@section('page-title', 'Search')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header title="Search" subtitle="Your own records by reference number, plus the support-team directory." sys="CLIENT://SEARCH" />

    <form method="GET" action="{{ route('portal.search.index') }}" class="flex gap-2">
        <label for="portal-search" class="sr-only">Search</label>
        <input id="portal-search" type="search" name="q" value="{{ $q }}" class="term-input flex-1" placeholder="Ticket, invoice, order or project number…" autocomplete="off">
        <button class="term-btn term-btn-sm" type="submit">Search</button>
    </form>

    @if($q !== '')
    <div class="term-panel p-6">
        <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-3">Records</h2>
        <ul class="space-y-2">
            @forelse($results['references'] as $r)
            <li><a href="{{ $r['url'] }}" class="term-link">{{ $r['label'] }} · {{ $r['ref'] }}</a></li>
            @empty
            <li class="text-sm text-slate-600 dark:text-term-800">No matching records.</li>
            @endforelse
        </ul>
    </div>
    <div class="term-panel p-6">
        <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-3">Support team</h2>
        <ul class="space-y-2">
            @forelse($results['staff'] as $s)
            <li class="text-sm text-slate-900 dark:text-white">{{ $s['name'] }} <span class="text-slate-500 dark:text-term-700">· {{ $s['job_title'] ?? $s['role'] }}</span></li>
            @empty
            <li class="text-sm text-slate-600 dark:text-term-800">No matching contacts.</li>
            @endforelse
        </ul>
    </div>
    @endif
</div>
@endsection
