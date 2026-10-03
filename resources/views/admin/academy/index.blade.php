@extends('layouts.app')
@section('title', 'PerfectITSecurity Academy')
@section('page-title', 'PerfectITSecurity Academy')

@section('content')
<x-page-header sys="CONTENT://ACADEMY" title="PerfectITSecurity Academy" subtitle="Learn how our platform works. Practice real-world workflows. Complete role-specific training." badge="TRAINING" badgeColor="emerald">
    <a href="{{ route('admin.academy.my') }}" class="term-btn term-btn-sm">My Learning</a>
    <a href="{{ route('admin.academy.certificates') }}" class="term-btn term-btn-ghost term-btn-sm">My Records</a>
</x-page-header>

<div class="term-panel p-6 mb-6">
    <h2 class="heading-sm mb-1">Welcome to the Academy</h2>
    <p class="body-md">Follow your required path for your role, study each lesson step-by-step, pass the quizzes, submit the practical exercises, and earn completion records. All scenarios use synthetic <span class="term-tag">[TRAINING]</span> data — never real customer information.</p>
</div>

<h2 class="heading-sm mb-3">Required Training for Your Role</h2>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
    @forelse($required as $course)
    <a href="{{ route('admin.academy.course', $course) }}" class="card card-hover p-5 block">
        <div class="flex items-center justify-between mb-2">
            <span class="term-tag">{{ ucfirst($course->difficulty) }}</span>
            @if($course->is_mandatory)<span class="term-tag">Required</span>@endif
        </div>
        <h3 class="font-semibold text-slate-900 dark:text-white mb-1">{{ $course->title }}</h3>
        <p class="body-sm line-clamp-2 mb-3">{{ $course->description }}</p>
        <span class="link-arrow text-sm">Start learning →</span>
    </a>
    @empty
    <p class="body-md">No required courses assigned to your role yet.</p>
    @endforelse
</div>

@if($myAssignments->count())
<h2 class="heading-sm mb-3">Continue Learning</h2>
<div class="card p-0 overflow-hidden mb-8">
    <table class="data-table term-table">
        <thead><tr><th>Course</th><th>Progress</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach($myAssignments as $a)
            <tr>
                <td class="font-medium" data-label="Course">{{ $a->course->title }}</td>
                <td data-label="Progress"><div class="w-32 h-2 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden"><div class="h-full rounded-full" style="width: {{ $a->progress['percent'] }}%; background: linear-gradient(90deg,#16A34A,#2563EB);"></div></div><span class="text-xs text-slate-500">{{ $a->progress['lessons_done'] }}/{{ $a->progress['lessons_total'] }} lessons</span></td>
                <td data-label="Status"><span class="term-tag">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></td>
                <td data-label=""><a href="{{ route('admin.academy.course', $a->course) }}" class="link-arrow text-sm">Continue →</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<h2 class="heading-sm mb-3">Explore the Academy</h2>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($featured as $course)
    <a href="{{ route('admin.academy.course', $course) }}" class="card card-hover p-5 block">
        <div class="flex items-center justify-between mb-2">
            <span class="term-tag">{{ ucfirst($course->difficulty) }}</span>
            <span class="text-xs text-slate-500">{{ $course->duration_minutes }} min · v{{ $course->version }}</span>
        </div>
        <h3 class="font-semibold text-slate-900 dark:text-white mb-1">{{ $course->title }}</h3>
        <p class="body-sm line-clamp-2">{{ $course->description }}</p>
    </a>
    @endforeach
</div>
@endsection
