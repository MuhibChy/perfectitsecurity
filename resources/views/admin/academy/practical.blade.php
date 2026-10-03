@extends('layouts.app')
@section('title', $practical->title)
@section('page-title', $practical->title)

@section('content')
<x-page-header sys="CONTENT://ACADEMY" :title="$practical->title" :subtitle="$practical->course->title" :breadcrumbs="['Academy' => route('admin.academy.index'), $practical->course->title => route('admin.academy.course', $practical->course), $practical->title => null]" :badge="$submission ? ucfirst(str_replace('_',' ',$submission->status)) : 'Not Submitted'" />

<div class="card p-6 mb-4">
    <h2 class="heading-sm mb-2">Instructions</h2>
    <p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $practical->instructions }}</p>
    <p class="term-hint mt-2">Use synthetic [TRAINING] data only. Never real customer data or real payments.</p>
</div>

@if($submission)
<div class="term-panel p-5 mb-4">
    <h2 class="heading-sm mb-1">Latest Submission — {{ ucfirst(str_replace('_',' ',$submission->status)) }}</h2>
    <p class="text-xs text-slate-500 mb-2">Submitted {{ $submission->submitted_at?->format('M d, Y H:i') }}</p>
    @if($submission->feedback)<p class="text-sm"><strong>Trainer feedback:</strong> {{ $submission->feedback }}</p>@endif
</div>
@endif

<form method="POST" action="{{ route('admin.academy.practical.submit', $practical) }}" class="card p-6 space-y-4">
    @csrf
    @foreach(($practical->checklist ?? ['Describe each required step and the evidence.']) as $i => $item)
    <div>
        <label class="term-field-label">{{ $i + 1 }}. {{ $item }}</label>
        <textarea name="responses[{{ $i }}]" rows="4" class="term-input" required minlength="10" placeholder="Describe what you did, which records you checked, and the outcome…">{{ old('responses.'.$i, is_array($submission->responses ?? null) ? ($submission->responses[$i] ?? '') : '') }}</textarea>
    </div>
    @endforeach
    <button type="submit" class="term-btn">Submit for Trainer Review</button>
</form>
@endsection
