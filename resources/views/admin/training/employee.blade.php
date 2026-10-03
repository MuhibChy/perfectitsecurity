@extends('layouts.app')
@section('title', 'Employee Training Record')
@section('page-title', 'Employee Record')

@section('content')
<x-page-header sys="CONTENT://TRAINING" :title="$user->name . ' — Training Record'" :subtitle="ucfirst(str_replace('_',' ',$user->role ?? '')) . ' · ' . $user->email" :breadcrumbs="['Training' => route('admin.training.dashboard'), 'Employee' => null]">
    <a href="{{ route('admin.training.assign.index') }}" class="term-btn term-btn-ghost term-btn-sm">Assign More</a>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Assigned Courses</h2>
        @forelse($assignments as $a)
        <div class="py-2 border-b border-slate-100 dark:border-white/5 last:border-0">
            <div class="flex justify-between text-sm"><strong>{{ $a->course->title }}</strong><span class="term-tag">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></div>
            <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-white/10 mt-1"><div class="h-full rounded-full" style="width: {{ $a->progress['percent'] }}%; background: linear-gradient(90deg,#16A34A,#2563EB);"></div></div>
            <div class="text-xs text-slate-500">Lessons {{ $a->progress['lessons_done'] }}/{{ $a->progress['lessons_total'] }} · Quizzes {{ $a->progress['quizzes_passed'] }}/{{ $a->progress['quizzes_total'] }} · Practicals {{ $a->progress['practicals_passed'] }}/{{ $a->progress['practicals_total'] }}</div>
        </div>
        @empty<p class="body-sm">No assignments.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Quiz Results</h2>
        @forelse($attempts as $att)
        <div class="text-sm py-1.5 border-b border-slate-100 dark:border-white/5 last:border-0">{{ $att->quiz->title }} — <strong>{{ $att->percent() }}%</strong> <span class="term-tag {{ $att->passed ? '' : '' }}">{{ $att->passed ? 'Passed' : 'Failed' }}</span> <span class="text-xs text-slate-500">{{ $att->created_at->format('M d') }}</span></div>
        @empty<p class="body-sm">No attempts yet.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Completion Records</h2>
        @forelse($certificates as $cert)<div class="text-sm py-1">{{ $cert->course->title }} · {{ $cert->certificate_no }} · {{ $cert->completed_at?->format('M d, Y') }}</div>
        @empty<p class="body-sm">None yet.</p>@endforelse
    </div>
    <div class="card p-5">
        <h2 class="heading-sm mb-3">Trainer Notes</h2>
        @foreach($notes as $note)<div class="border border-slate-200 dark:border-white/10 p-3 mb-2 text-sm">{{ $note->note }}<div class="text-xs text-slate-500 mt-1">{{ $note->author->name }} · {{ $note->created_at->format('M d, Y') }}</div></div>@endforeach
        <form method="POST" action="{{ route('admin.training.notes.store', $user) }}" class="mt-2 flex gap-2">
            @csrf<input name="note" class="term-input" placeholder="Add feedback or improvement note…" required><button class="term-btn term-btn-ghost term-btn-sm whitespace-nowrap">Add Note</button>
        </form>
    </div>
</div>
@endsection
