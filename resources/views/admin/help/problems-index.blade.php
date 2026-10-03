@extends('layouts.app')
@section('title', 'Problem & Solution Center')
@section('page-title', 'Problem & Solution')

@section('content')
<x-page-header sys="CONTENT://HELP" title="PerfectITSecurity Problem & Solution Center" subtitle="Find the problem, follow the safe steps, verify the result, escalate cleanly if needed." badge="TROUBLESHOOTING" />

<div class="term-panel p-6 lg:p-8 mb-6">
    <form method="GET" action="{{ route('admin.help.problems.index') }}" class="flex flex-col sm:flex-row gap-2">
        <input type="search" name="q" value="{{ $q }}" class="term-input flex-1" placeholder="Search problems: payment, invoice, login, order, ticket, email, network, DNS, Microsoft 365…">
        @if($filter !== 'all')<input type="hidden" name="filter" value="{{ $filter }}">@endif
        <button class="term-btn whitespace-nowrap">Search Solutions</button>
    </form>
    <div class="flex flex-wrap gap-2 mt-3">
        @foreach($filters as $key => $label)
        <a href="{{ route('admin.help.problems.index', array_filter(['filter' => $key === 'all' ? null : $key, 'q' => $q ?: null])) }}" class="term-tag {{ $filter === $key ? '' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<h2 class="heading-sm mb-3">Most Common Problems</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 mb-8">
    @foreach($quickCards as $card)
    <a href="{{ route('admin.help.problems.show', $card['slug']) }}" class="card card-hover p-4 block">
        <h3 class="font-semibold text-sm text-slate-900 dark:text-white mb-1">{{ $card['title'] }}</h3>
        <p class="body-sm line-clamp-2">→ {{ Str::limit($card['steps'][0] ?? '', 90) }}</p>
    </a>
    @endforeach
</div>

@if($q)<p class="body-sm mb-3">{{ count($articles) }} result(s) for “{{ $q }}”. <a href="{{ route('admin.help.problems.index') }}" class="link-arrow text-sm">Clear</a></p>@endif

@php $grouped = []; foreach ($articles as $a) { $grouped[$a['category']][] = $a; } @endphp
@foreach($grouped as $cat => $items)
<h2 class="heading-sm mt-6 mb-3">{{ $categories[$cat] ?? $cat }}</h2>
<div class="card p-0 overflow-hidden mb-2"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Problem</th><th>Short Solution</th><th class="text-right">Actions</th></tr></thead>
    <tbody>
        @foreach($items as $article)
        <tr>
            <td class="font-medium" data-label="Problem">{{ $article['title'] }}</td>
            <td class="text-sm text-slate-600 dark:text-slate-300" data-label="Short Solution">{{ Str::limit($article['steps'][0] ?? '', 110) }}</td>
            <td class="whitespace-nowrap" data-label="Actions"><a href="{{ route('admin.help.problems.show', $article['slug']) }}" class="link-arrow text-sm">Open Article →</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
@endforeach

@if(!count($articles))
<div class="card p-8 text-center"><p class="body-md">No articles match. Try a broader term, or return to <a href="{{ route('admin.help.training.index') }}" class="link-arrow text-sm">Training →</a></p></div>
@endif
@endsection
