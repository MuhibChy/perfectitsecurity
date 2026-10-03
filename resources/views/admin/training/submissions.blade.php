@extends('layouts.app')
@section('title', 'Practical Submissions')
@section('page-title', 'Submissions')

@section('content')
<x-page-header sys="CONTENT://TRAINING" title="Practical Submissions" subtitle="Review employee work against checklists, then pass or request improvement." :breadcrumbs="['Training' => route('admin.training.dashboard'), 'Submissions' => null]" />

<div class="space-y-4">
    @forelse($submissions as $s)
    <div class="card p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <div><strong>{{ $s->user->name }}</strong> · {{ $s->assessment->title }} <span class="text-xs text-slate-500">({{ $s->assessment->course->title }})</span></div>
            <span class="term-tag {{ $s->status === 'passed' ? '' : ($s->status === 'needs_improvement' ? '' : '') }}">{{ ucfirst(str_replace('_',' ',$s->status)) }}</span>
        </div>
        <div class="text-sm space-y-1 mb-3">
            @foreach(($s->responses ?? []) as $i => $r)
            <p><span class="text-slate-500">{{ $s->assessment->checklist[$i] ?? ('Item '.($i+1)) }}:</span> {{ is_string($r) ? Str::limit($r, 300) : json_encode($r) }}</p>
            @endforeach
        </div>
        <form method="POST" action="{{ route('admin.training.submissions.review', $s) }}" class="flex flex-wrap gap-2 items-end">
            @csrf
            <div><label class="term-field-label">Result</label><select name="status" class="term-input"><option value="passed">Passed</option><option value="needs_improvement">Needs improvement</option></select></div>
            <div><label class="term-field-label">Score (0–100)</label><input type="number" name="score" min="0" max="100" class="term-input w-28"></div>
            <div class="flex-1 min-w-[200px]"><label class="term-field-label">Feedback (required)</label><input name="feedback" class="term-input" placeholder="What was good, what must improve…" required></div>
            <button class="term-btn term-btn-sm">Save Review</button>
        </form>
    </div>
    @empty
    <p class="body-md">No submissions yet.</p>
    @endforelse
</div>
<div class="mt-4">{{ $submissions->links() }}</div>
@endsection
