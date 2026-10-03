@extends('layouts.app')
@section('title', 'Training Manager Dashboard')
@section('page-title', 'Training Manager')

@section('content')
<x-page-header sys="CONTENT://TRAINING" title="Training Manager Dashboard" subtitle="Monitor every employee's Academy progress and pending reviews." badge="TRAINER">
    <a href="{{ route('admin.training.courses.create') }}" class="term-btn term-btn-sm">New Course</a>
    <a href="{{ route('admin.training.reports') }}" class="term-btn term-btn-ghost term-btn-sm">Reports</a>
    <a href="{{ route('admin.training.submissions') }}" class="term-btn term-btn-ghost term-btn-sm">Reviews ({{ $pendingSubmissions->count() }})</a>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach([['Employees',$totalEmployees],['In Training',$inTraining],['Completed',$completed],['Overdue',$overdue],['Avg Progress',$avgProgress.'%']] as [$label,$val])
    <div class="term-panel"><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Pending Practical Reviews</h2>
        @forelse($pendingSubmissions as $s)
        <div class="flex items-center justify-between gap-2 py-2 border-b border-slate-100 dark:border-white/5 last:border-0">
            <div class="text-sm"><strong>{{ $s->user->name }}</strong> · {{ $s->assessment->title }}<div class="text-xs text-slate-500">{{ $s->submitted_at?->format('M d, Y') }}</div></div>
            <a href="{{ route('admin.training.submissions') }}" class="term-btn term-btn-ghost term-btn-sm">Review</a>
        </div>
        @empty<p class="body-sm">Nothing awaiting review.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Failed Assessments</h2>
        @forelse($failedAttempts as $a)
        <div class="text-sm py-2 border-b border-slate-100 dark:border-white/5 last:border-0"><strong>{{ $a->user->name }}</strong> · {{ $a->quiz->title }} — {{ $a->percent() }}%<span class="text-xs text-slate-500"> · {{ $a->created_at->format('M d') }}</span></div>
        @empty<p class="body-sm">No failures recorded.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Recently Completed</h2>
        @forelse($recentCertificates as $cert)
        <div class="text-sm py-2 border-b border-slate-100 dark:border-white/5 last:border-0"><strong>{{ $cert->user->name }}</strong> · {{ $cert->course->title }}<span class="text-xs text-slate-500"> · {{ $cert->completed_at?->format('M d') }}</span></div>
        @empty<p class="body-sm">No completions yet.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Assignments by Role</h2>
        @forelse($byRole as $role => $count)<div class="flex justify-between text-sm py-1.5"><span>{{ ucfirst(str_replace('_',' ',$role)) }}</span><strong>{{ $count }}</strong></div>
        @empty<p class="body-sm">No assignments yet.</p>@endforelse
        <a href="{{ route('admin.training.assign.index') }}" class="term-btn term-btn-ghost term-btn-sm mt-3">Assign Training</a>
    </div>
</div>
@endsection
