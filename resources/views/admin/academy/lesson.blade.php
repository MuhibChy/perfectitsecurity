@extends('layouts.app')
@section('title', $lesson->title)
@section('page-title', $lesson->title)

@section('content')
<x-page-header sys="CONTENT://ACADEMY" :title="$lesson->title" :subtitle="$lesson->module->course->title . ' · ' . $lesson->module->title" :breadcrumbs="['Academy' => route('admin.academy.index'), $lesson->module->course->title => route('admin.academy.course', $lesson->module->course), $lesson->title => null]" :badge="ucfirst($lesson->lesson_type)">
    @if($completed)<span class="term-tag">Studied ✓</span>@endif
</x-page-header>

{{-- Trainer Presentation Mode controls --}}
<div class="card p-4 mb-6 flex flex-wrap items-center gap-2">
    <span class="text-xs font-semibold uppercase tracking-widest text-slate-500 mr-2">Presentation</span>
    @if($prev)<a href="{{ route('admin.academy.lesson', $prev) }}" class="term-btn term-btn-ghost term-btn-sm">← Previous</a>@endif
    <span class="text-xs text-slate-500">Lesson {{ ($pos ?? 0) + 1 }} of {{ count($ids) }}</span>
    @if($next)<a href="{{ route('admin.academy.lesson', $next) }}" class="term-btn term-btn-ghost term-btn-sm">Next →</a>@endif
    <span class="text-xs text-slate-500 ml-auto">{{ $lesson->duration_minutes }} min · v{{ $lesson->version }}</span>
</div>

@if($lesson->objectives)
<div class="term-panel p-5 mb-4">
    <h2 class="heading-sm mb-2">Learning Objectives</h2>
    <ul class="list-disc list-inside text-sm text-slate-600 dark:text-slate-300 space-y-1">@foreach($lesson->objectives as $o)<li>{{ $o }}</li>@endforeach</ul>
</div>
@endif

<div class="card p-6 mb-4">
    <div class="prose-premium text-slate-700 dark:text-slate-200 text-[15px] leading-relaxed whitespace-pre-line">{{ $lesson->body }}</div>
</div>

@if($lesson->steps)
<div class="card p-6 mb-4">
    <h2 class="heading-sm mb-3">Step-by-Step Procedure</h2>
    <ol class="space-y-2.5">
        @foreach($lesson->steps as $i => $step)
        <li class="flex gap-3 text-sm"><span class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center font-bold text-white text-xs" style="background: linear-gradient(135deg,#16A34A,#2563EB);">{{ $i + 1 }}</span><span class="text-slate-700 dark:text-slate-200">{{ $step }}</span></li>
        @endforeach
    </ol>
</div>
@endif

@if($lesson->why_matters)
<div class="card p-6 mb-4 border-l-4 border-l-brand-500">
    <h2 class="heading-sm mb-2">Why This Matters</h2>
    <ul class="space-y-1.5 text-sm text-slate-700 dark:text-slate-200">@foreach($lesson->why_matters as $w)<li>• {{ $w }}</li>@endforeach</ul>
</div>
@endif

@if($lesson->common_mistakes)
<div class="card p-6 mb-4 border-l-4 border-l-amber-500">
    <h2 class="heading-sm mb-2">Do Not Make These Mistakes</h2>
    <ul class="space-y-1.5 text-sm text-slate-700 dark:text-slate-200">@foreach($lesson->common_mistakes as $mistake)<li> {{ $mistake }}</li>@endforeach</ul>
</div>
@endif

@if($lesson->discussion_questions)
<div class="term-panel p-5 mb-4">
    <h2 class="heading-sm mb-2">Discussion Questions</h2>
    <ul class="list-decimal list-inside text-sm text-slate-600 dark:text-slate-300 space-y-1">@foreach($lesson->discussion_questions as $q)<li>{{ $q }}</li>@endforeach</ul>
</div>
@endif

@if($quizzes->count())
<div class="card p-5 mb-6">
    <h2 class="heading-sm mb-2">Knowledge Check</h2>
    @foreach($quizzes as $quiz)<a href="{{ route('admin.academy.quiz', $quiz) }}" class="term-btn term-btn-ghost term-btn-sm mr-2 mb-2">{{ $quiz->title }} →</a>@endforeach
</div>
@endif

<div class="flex flex-wrap gap-3">
    @if(!$completed)
    <form method="POST" action="{{ route('admin.academy.lesson.complete', $lesson) }}">@csrf<button type="submit" class="term-btn">Complete Lesson ✓</button></form>
    @endif
    @if($next)<a href="{{ route('admin.academy.lesson', $next) }}" class="term-btn term-btn-ghost">Next Lesson →</a>@endif
    <a href="{{ route('admin.academy.course', $lesson->module->course) }}" class="term-btn term-btn-ghost">Back to Course</a>
</div>
@endsection
