@extends('layouts.app')
@section('page-title', 'Review Submission: ' . $submission->title)
@section('content')

    <x-page-header title="Link Submissions" sys="CONTENT://LINK-SUBMISSIONS" />
<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.link-submissions.index') }}" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Review Submission</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Submitted {{ $submission->created_at->format('M d, Y \\a\\t g:i A') }}</p>
        </div>
    </div>

    <div class="term-panel p-8 space-y-6">
        <div>
            <label class="term-field-label">Title</label>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $submission->title }}</p>
        </div>

        <div>
            <label class="term-field-label">URL</label>
            <a href="{{ $submission->url }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline mt-1 block break-all">{{ $submission->url }}</a>
        </div>

        @if($submission->description)
        <div>
            <label class="term-field-label">Description</label>
            <p class="text-gray-700 dark:text-gray-300 mt-1">{{ $submission->description }}</p>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="term-field-label">Category</label>
                <p class="text-gray-700 dark:text-gray-300 mt-1">{{ ucfirst($submission->category) }}</p>
            </div>
            <div>
                <label class="term-field-label">Status</label>
                <div class="mt-1">
                    @if($submission->status === 'pending')
                    <span class="term-tag inline-flex items-center gap-1.5">Pending Review</span>
                    @elseif($submission->status === 'approved')
                    <span class="term-tag inline-flex items-center gap-1.5">Approved</span>
                    @else
                    <span class="term-tag inline-flex items-center gap-1.5">Rejected</span>
                    @endif
                </div>
            </div>
        </div>

        @if($submission->submitter_name || $submission->submitter_email)
        <div class="bg-gray-50 dark:bg-gray-800/50 p-4">
            <label class="term-field-label">Submitted By</label>
            <p class="text-gray-700 dark:text-gray-300 mt-1">{{ $submission->submitter_name ?: 'Anonymous' }} {{ $submission->submitter_email ? '(' . $submission->submitter_email . ')' : '' }}</p>
        </div>
        @endif

        @if($submission->status === 'pending')
        <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <form action="{{ route('admin.link-submissions.approve', $submission) }}" method="POST" onsubmit="return confirm('Approve this link? It will be added to the Useful Links page.')">
                @csrf
                <button type="submit" class="term-btn term-btn-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Approve & Add to Links
                </button>
            </form>
            <form action="{{ route('admin.link-submissions.reject', $submission) }}" method="POST" onsubmit="return confirm('Reject this submission?')">
                @csrf
                <button type="submit" class="btn btn-destructive term-btn-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\"/></svg>
                    Reject
                </button>
            </form>
            <a href="{{ route('admin.link-submissions.index') }}" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-4 py-2.5 text-sm font-medium transition-colors">Back</a>
        </div>
        @else
        <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
            <a href="{{ route('admin.link-submissions.index') }}" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-4 py-2.5 text-sm font-medium transition-colors">← Back to submissions</a>
        </div>
        @endif
    </div>
</div>
@endsection
