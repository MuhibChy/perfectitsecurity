@extends('layouts.app')
@section('title', 'Quiz Result')
@section('page-title', 'Quiz Result')

@section('content')
<x-page-header sys="CONTENT://ACADEMY" :title="$attempt->quiz->title . ' — Result'" :subtitle="$attempt->quiz->course->title" :breadcrumbs="['Academy' => route('admin.academy.index'), $attempt->quiz->course->title => route('admin.academy.course', $attempt->quiz->course), 'Result' => null]" :badge="$attempt->passed ? 'Passed' : 'Needs Improvement'" :badgeColor="$attempt->passed ? 'emerald' : 'amber'" />

<div class="term-panel p-6 mb-6 text-center">
    <div class="stat-value">{{ $attempt->percent() }}%</div>
    <div class="stat-label">{{ $attempt->score }}/{{ $attempt->max_score }} points · {{ $attempt->created_at->format('M d, Y H:i') }}</div>
</div>

<div class="space-y-4 mb-6">
    @foreach($attempt->quiz->questions as $i => $q)
    @php $given = ($attempt->answers ?? [])[$q->id] ?? null; $earned = $q->grade(is_string($given) && str_contains($given, ',') ? explode(',', $given) : $given); @endphp
    <div class="card p-5 {{ $earned >= $q->points ? 'border-l-4 border-l-emerald-500' : 'border-l-4 border-l-rose-500' }}">
        <p class="font-medium text-sm mb-1">Q{{ $i + 1 }}. {{ $q->prompt }}</p>
        <p class="text-xs text-slate-500 mb-1">Your answer: {{ is_array($given) ? implode(', ', $given) : ($given ?? '—') }} · Correct: {{ implode(', ', $q->correct ?? []) }} · {{ $earned }}/{{ $q->points }} pts</p>
        @if($q->explanation)<p class="text-sm text-slate-600 dark:text-slate-300">{{ $q->explanation }}</p>@endif
    </div>
    @endforeach
</div>

<a href="{{ route('admin.academy.course', $attempt->quiz->course) }}" class="term-btn term-btn-ghost">Back to Course</a>
@endsection
