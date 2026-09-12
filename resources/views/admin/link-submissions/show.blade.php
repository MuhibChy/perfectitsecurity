@extends('layouts.app')
@section('page-title', 'Review Submission: ' . $submission->title)
@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.link-submissions.index') }}" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Review Submission</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Submitted {{ $submission->created_at->format('M d, Y \\a\\t g:i A') }}</p>
        </div>
    </div>

    <div class="glass-card p-8 space-y-6">
        <div>
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</label>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ $submission->title }}</p>
        </div>

        <div>
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</label>
            <a href="{{ $submission->url }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline mt-1 block break-all">{{ $submission->url }}</a>
        </div>

        @if($submission->description)
        <div>
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</label>
            <p class="text-gray-700 dark:text-gray-300 mt-1">{{ $submission->description }}</p>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</label>
                <p class="text-gray-700 dark:text-gray-300 mt-1">{{ ucfirst($submission->category) }}</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</label>
                <div class="mt-1">
                    @if($submission->status === 'pending')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">Pending Review</span>
                    @elseif($submission->status === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">Approved</span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Rejected</span>
                    @endif
                </div>
            </div>
        </div>

        @if($submission->submitter_name || $submission->submitter_email)
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4">
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitted By</label>
            <p class="text-gray-700 dark:text-gray-300 mt-1">{{ $submission->submitter_name ?: 'Anonymous' }} {{ $submission->submitter_email ? '(' . $submission->submitter_email . ')' : '' }}</p>
        </div>
        @endif

        @if($submission->status === 'pending')
        <div class="flex items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <form action="{{ route('admin.link-submissions.approve', $submission) }}" method="POST" onsubmit="return confirm('Approve this link? It will be added to the Useful Links page.')">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Approve & Add to Links
                </button>
            </form>
            <form action="{{ route('admin.link-submissions.reject', $submission) }}" method="POST" onsubmit="return confirm('Reject this submission?')">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
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
