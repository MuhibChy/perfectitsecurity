@extends('layouts.app')
@section('page-title', 'My work')
@section('content')

    <x-page-header title="My work" sys="ADMIN://PROFILE" />
<div class="term-panel p-6 mb-4">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">My work — {{ $user->name }}</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
        @foreach($overview as $stat => $value)
            <div class="bg-gray-50 dark:bg-gray-800/60 p-3">
                <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $stat) }}</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ is_scalar($value) ? $value : '—' }}</p>
            </div>
        @endforeach
    </div>
</div>
@if(isset($earnings))
<div class="term-panel p-6 mb-4">
    <h3 class="font-bold mb-1 text-gray-900 dark:text-white">Lifetime earnings</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">From authoritative payroll and commission rows only — customer payments are never counted as income.</p>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-green-50 dark:bg-green-900/20 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Lifetime paid</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($earnings['lifetime_paid'], 2) }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/60 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Salary paid</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($earnings['salary_paid'], 2) }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/60 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Salary pending</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($earnings['salary_pending'], 2) }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/60 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Commission paid</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($earnings['commission_paid'], 2) }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800/60 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Commission pending</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($earnings['commission_pending'], 2) }}</p>
        </div>
    </div>
</div>
@endif
<div class="term-panel p-6 mb-4">
    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Service contributions</h3>
    @forelse($contributions as $task)<div class="text-sm border-t border-gray-200 dark:border-gray-700 py-1 text-gray-600 dark:text-gray-300">{{ $task->title ?? ('Task #'.$task->id) }} · {{ $task->pivot->role ?? '—' }} · {{ $task->status }}</div>@empty<p class="text-sm text-gray-500">None.</p>@endforelse
</div>
<div class="term-panel p-6">
    <h3 class="font-bold mb-2 text-gray-900 dark:text-white">Timeline</h3>
    @forelse($timeline as $event)
        <div class="text-xs border-t border-gray-200 dark:border-gray-700 py-1.5 text-gray-600 dark:text-gray-300">
            <span class="text-gray-400">{{ $event['at'] instanceof \DateTimeInterface ? $event['at']->format('Y-m-d H:i') : ($event['at'] ?? '') }}</span>
            · <span class="font-medium">{{ $event['label'] ?? 'Event' }}</span>
            · {{ $event['detail'] ?? '' }}
        </div>
    @empty<p class="text-sm text-gray-500">No recorded events yet.</p>@endforelse
</div>
@endsection
