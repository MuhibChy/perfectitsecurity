@extends('layouts.app')
@section('title', $quiz->title)
@section('page-title', $quiz->title)

@section('content')
<x-page-header sys="CONTENT://ACADEMY" :title="$quiz->title" :subtitle="'Pass mark ' . $quiz->pass_score . '% · ' . $quiz->questions->count() . ' questions · Max ' . $quiz->max_attempts . ' attempts'" :breadcrumbs="['Academy' => route('admin.academy.index'), $quiz->course->title => route('admin.academy.course', $quiz->course), $quiz->title => null]" />

<form method="POST" action="{{ route('admin.academy.quiz.submit', $quiz) }}" class="space-y-4">
    @csrf
    @foreach($quiz->questions as $i => $q)
    <div class="card p-5">
        <p class="font-medium text-slate-900 dark:text-white mb-3">Q{{ $i + 1 }}. {{ $q->prompt }} <span class="text-xs text-slate-500">({{ $q->points }} pt · {{ $q->type }})</span></p>
        @if($q->type === 'ordering')
            <p class="term-hint mb-2">Enter the correct order as comma-separated option numbers (e.g. 2,0,3,1).</p>
            <ol class="text-sm text-slate-600 dark:text-slate-300 mb-2 space-y-1">@foreach($q->options as $oi => $opt)<li>{{ $oi }}. {{ $opt }}</li>@endforeach</ol>
            <input type="text" name="q_{{ $q->id }}" class="term-input" placeholder="e.g. 1,3,0,2" required>
        @elseif($q->type === 'multiple')
            <p class="term-hint mb-2">Select all correct answers.</p>
            @foreach($q->options as $oi => $opt)
            <label class="term-field-label"><input type="checkbox" name="q_{{ $q->id }}[]" value="{{ $oi }}" class="rounded accent-green-600"> {{ $opt }}</label>
            @endforeach
        @elseif($q->type === 'boolean')
            @foreach($q->options as $oi => $opt)
            <label class="term-field-label"><input type="radio" name="q_{{ $q->id }}" value="{{ $oi }}" class="accent-green-600" required> {{ $opt }}</label>
            @endforeach
        @else
            @foreach($q->options as $oi => $opt)
            <label class="term-field-label"><input type="radio" name="q_{{ $q->id }}" value="{{ $oi }}" class="accent-green-600" required> {{ $opt }}</label>
            @endforeach
        @endif
    </div>
    @endforeach
    <button type="submit" class="term-btn">Submit Assessment</button>
</form>
@endsection
