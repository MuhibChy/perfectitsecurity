@extends('layouts.app')
@section('title', 'PerfectITSecurity Training Center')
@section('page-title', 'Training Center')

@section('content')
<x-page-header sys="CONTENT://HELP" title="PerfectITSecurity Training Center" subtitle="Complete practical guidance for customer management, IT services, projects, payments, finance, support, work updates and daily operations." badge="HTML GUIDES" />

<div class="term-panel p-6 lg:p-8 mb-6">
    <h2 class="heading-md mb-1">Learn the PerfectITSecurity platform step by step.</h2>
    <p class="body-md mb-4">Open any guide below. Every page follows the same structure: what it is, why it matters, who owns it, exact steps, a synthetic [TRAINING] example, expected results, mistakes to avoid, linked troubleshooting and the next lesson.</p>
    @php $recommended = \App\Support\RoleRegistry::trainingFor(auth()->user()->role ?? null); @endphp
    @if($recommended)
    <div class="border border-emerald-500/25 bg-emerald-500/5 p-4 mb-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-300 mb-2">Recommended for your role ({{ auth()->user()->roleDisplayName() }})</p>
        <div class="flex flex-wrap gap-2">
            @foreach($recommended as $slug)
            @php $rec = \App\Support\TrainingCenterLessons::find($slug); @endphp
            @if($rec)<a href="{{ route('admin.help.training.show', $slug) }}" class="term-tag">{{ $rec['title'] }}</a>@endif
            @endforeach
        </div>
    </div>
    @endif
    <form method="GET" action="{{ route('admin.help.training.index') }}" class="flex flex-col sm:flex-row gap-2">
        <input type="search" name="q" value="{{ $q }}" class="term-input flex-1" placeholder="Search training: e.g. How to create an order, check payment, close a project…">
        <button class="term-btn whitespace-nowrap">Search Training</button>
    </form>
    <div class="flex flex-wrap gap-2 mt-3">
        <a href="{{ route('admin.help.training.index') }}" class="term-tag {{ $category === 'all' ? '' : '' }}">All ({{ $allCount }})</a>
        @foreach($categories as $key => $label)
        <a href="{{ route('admin.help.training.index', ['category' => $key]) }}" class="term-tag {{ $category === $key ? '' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

@if($q)<p class="body-sm mb-3">{{ count($lessons) }} result(s) for “{{ $q }}”. <a href="{{ route('admin.help.training.index') }}" class="link-arrow text-sm">Clear</a></p>@endif

@php $grouped = []; foreach ($lessons as $l) { $grouped[$l['category']][] = $l; } @endphp
@foreach($grouped as $cat => $items)
<h2 class="heading-sm mt-6 mb-3">{{ $categories[$cat] ?? $cat }}</h2>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($items as $i => $lesson)
    <a href="{{ route('admin.help.training.show', $lesson['slug']) }}" class="card card-hover p-5 block">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ str_pad(array_search($lesson, $lessons) + 1, 2, '0', STR_PAD_LEFT) }}</div>
        <h3 class="font-semibold text-slate-900 dark:text-white mb-1">{{ $lesson['title'] }}</h3>
        <p class="body-sm line-clamp-2 mb-2">{{ $lesson['summary'] }}</p>
        <span class="link-arrow text-sm">Open guide →</span>
    </a>
    @endforeach
</div>
@endforeach

@if(!count($lessons))
<div class="card p-8 text-center"><p class="body-md">No guides match. Try “order”, “payment”, “ticket” or “finance” — or browse the <a href="{{ route('admin.help.problems.index') }}" class="link-arrow text-sm">Problem & Solution Center →</a></p></div>
@endif
@endsection
