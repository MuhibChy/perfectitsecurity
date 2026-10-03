@extends('layouts.app')
@section('title', 'My Learning')
@section('page-title', 'My Learning')

@section('content')
<x-page-header sys="CONTENT://ACADEMY" title="My Learning" subtitle="Required training, progress, assessments and completion records." :breadcrumbs="['Academy' => route('admin.academy.index'), 'My Learning' => null]">
    <a href="{{ route('admin.academy.index') }}" class="term-btn term-btn-ghost term-btn-sm">Academy Home</a>
</x-page-header>

<h2 class="heading-sm mb-3">My Courses</h2>
<div class="card p-0 overflow-hidden mb-8">
    <div class="overflow-x-auto term-table-wrap">
    <table class="data-table term-table">
        <thead><tr><th>Course</th><th>Progress</th><th>Quizzes</th><th>Practicals</th><th>Status</th><th>Due</th><th></th></tr></thead>
        <tbody>
            @forelse($assignments as $a)
            <tr>
                <td class="font-medium" data-label="Course">{{ $a->course->title }}</td>
                <td data-label="Progress"><div class="w-28 h-2 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden"><div class="h-full rounded-full" style="width: {{ $a->progress['percent'] }}%; background: linear-gradient(90deg,#16A34A,#2563EB);"></div></div><span class="text-xs text-slate-500">{{ $a->progress['percent'] }}%</span></td>
                <td data-label="Quizzes">{{ $a->progress['quizzes_passed'] }}/{{ $a->progress['quizzes_total'] }}</td>
                <td data-label="Practicals">{{ $a->progress['practicals_passed'] }}/{{ $a->progress['practicals_total'] }}</td>
                <td data-label="Status"><span class="term-tag {{ in_array($a->status,['completed','passed']) ? '' : ($a->isOverdue() ? '' : '') }}">{{ ucfirst(str_replace('_',' ',$a->status)) }}{{ $a->isOverdue() ? ' · Overdue' : '' }}</span></td>
                <td class="text-sm" data-label="Due">{{ $a->due_at?->format('M d, Y') ?? '—' }}</td>
                <td data-label=""><a href="{{ route('admin.academy.course', $a->course) }}" class="link-arrow text-sm">Open →</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-slate-500 py-6">No training assigned yet. Your trainer will assign your role path.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="heading-sm mb-3">Completion Records</h2>
        <div class="card p-5 space-y-3">
            @forelse($certificates as $cert)
            <div class="flex items-center justify-between gap-3">
                <div><div class="font-medium text-slate-900 dark:text-white">{{ $cert->course->title }}</div><div class="text-xs text-slate-500">{{ $cert->certificate_no }} · {{ $cert->completed_at?->format('M d, Y') }}</div></div>
                <a href="{{ route('admin.academy.certificate', $cert) }}" class="term-btn term-btn-ghost term-btn-sm">View</a>
            </div>
            @empty
            <p class="body-sm">No completions yet — finish a course to earn your first record.</p>
            @endforelse
        </div>
    </div>
    <div>
        <h2 class="heading-sm mb-3">Trainer Messages</h2>
        <div class="card p-5 space-y-3">
            @forelse($notes as $note)
            <div class="border border-slate-200 dark:border-white/10 p-3">
                <p class="text-sm text-slate-700 dark:text-slate-200">{{ $note->note }}</p>
                <div class="text-xs text-slate-500 mt-1">{{ $note->author->name }} · {{ $note->created_at->format('M d, Y') }}</div>
            </div>
            @empty
            <p class="body-sm">No trainer messages.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
