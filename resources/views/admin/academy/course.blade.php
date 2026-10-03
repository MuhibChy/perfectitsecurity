@extends('layouts.app')
@section('title', $course->title)
@section('page-title', $course->title)

@section('content')
<x-page-header sys="CONTENT://ACADEMY" :title="$course->title" :subtitle="$course->description" :breadcrumbs="['Academy' => route('admin.academy.index'), $course->title => null]" :badge="'v'.$course->version">
    @if($assignment)<span class="term-tag">{{ ucfirst(str_replace('_',' ',$assignment->status)) }} · {{ $progress['percent'] }}%</span>@endif
</x-page-header>

<div class="w-full h-2.5 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden mb-6">
    <div class="h-full rounded-full" style="width: {{ $progress['percent'] }}%; background: linear-gradient(90deg,#16A34A,#2563EB);"></div>
</div>
<p class="body-sm mb-6">Lessons {{ $progress['lessons_done'] }}/{{ $progress['lessons_total'] }} · Quizzes {{ $progress['quizzes_passed'] }}/{{ $progress['quizzes_total'] }} · Practicals {{ $progress['practicals_passed'] }}/{{ $progress['practicals_total'] }}</p>

@foreach($course->modules as $module)
<div class="card p-5 mb-4">
    <h2 class="font-semibold text-slate-900 dark:text-white mb-1">{{ $loop->iteration }}. {{ $module->title }}</h2>
    @if($module->description)<p class="body-sm mb-3">{{ $module->description }}</p>@endif
    <div class="divide-y divide-slate-100 dark:divide-white/5">
        @foreach($module->lessons as $lesson)
        <a href="{{ route('admin.academy.lesson', $lesson) }}" class="flex items-center justify-between gap-3 py-2.5 group">
            <span class="flex items-center gap-3">
                <span class="term-tag w-6 h-6 flex items-center justify-center font-bold {{ in_array($lesson->id, $completedLessons) ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-white/10 text-slate-500' }}">{{ in_array($lesson->id, $completedLessons) ? '✓' : $loop->iteration }}</span>
                <span class="text-sm font-medium text-slate-800 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-cyber-300">{{ $lesson->title }}</span>
            </span>
            <span class="text-xs text-slate-500">{{ $lesson->duration_minutes }} min · {{ ucfirst($lesson->lesson_type) }}</span>
        </a>
        @endforeach
    </div>
</div>
@endforeach

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Assessments</h2>
        @forelse($course->quizzes as $quiz)
        <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 dark:border-white/5 last:border-0">
            <div><div class="font-medium text-sm">{{ $quiz->title }}</div><div class="text-xs text-slate-500">Pass {{ $quiz->pass_score }}% · {{ isset($quizBest[$quiz->id]) && $quizBest[$quiz->id]->passed ? 'Passed ('.$quizBest[$quiz->id]->percent().'%)' : 'Not passed yet' }}</div></div>
            <a href="{{ route('admin.academy.quiz', $quiz) }}" class="term-btn term-btn-ghost term-btn-sm">Take quiz</a>
        </div>
        @empty
        <p class="body-sm">No quizzes in this course.</p>
        @endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Practical Exercises</h2>
        @forelse($course->practicals as $p)
        <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 dark:border-white/5 last:border-0">
            <div class="font-medium text-sm">{{ $p->title }}</div>
            <a href="{{ route('admin.academy.practical', $p) }}" class="term-btn term-btn-ghost term-btn-sm">Open exercise</a>
        </div>
        @empty
        <p class="body-sm">No practical exercises in this course.</p>
        @endforelse
    </div>
</div>
@endsection
