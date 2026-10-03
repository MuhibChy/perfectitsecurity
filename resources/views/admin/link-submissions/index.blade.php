@extends('layouts.app')
@section('page-title', 'Link Submissions')
@section('content')
<div class="space-y-6">
    <x-page-header title="Link Submissions" sys="CONTENT://LINK-SUBMISSIONS" />
    <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Link Submissions</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review and approve link suggestions submitted by visitors.</p>
    </div>

    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table w-full text-sm term-table">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-6 py-4 font-semibold text-gray-900 dark:text-white">Link</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-900 dark:text-white">Submitted By</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-900 dark:text-white">Category</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-900 dark:text-white">Status</th>
                        <th class="text-left px-6 py-4 font-semibold text-gray-900 dark:text-white">Date</th>
                        <th class="text-right px-6 py-4 font-semibold text-gray-900 dark:text-white">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($submissions as $submission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $submission->status === 'pending' ? 'bg-yellow-50/50 dark:bg-yellow-900/5' : '' }}">
                        <td class="px-6 py-4" data-label="Link">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $submission->title }}</p>
                                <a href="{{ $submission->url }}" target="_blank" class="text-xs text-primary-600 dark:text-primary-400 hover:underline truncate block max-w-xs">{{ $submission->url }}</a>
                                @if($submission->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate max-w-xs">{{ $submission->description }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4" data-label="Submitted By">
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $submission->submitter_name ?: 'Anonymous' }}</p>
                            @if($submission->submitter_email)
                            <p class="text-xs text-gray-500">{{ $submission->submitter_email }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4" data-label="Category">
                            <span class="term-tag inline-flex items-center">
                                {{ ucfirst($submission->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4" data-label="Status">
                            @if($submission->status === 'pending')
                            <span class="term-tag inline-flex items-center gap-1.5">
                                <span class="term-tag w-1.5 h-1.5"></span>
                                Pending
                            </span>
                            @elseif($submission->status === 'approved')
                            <span class="term-tag inline-flex items-center gap-1.5">
                                <span class="term-tag w-1.5 h-1.5"></span>
                                Approved
                            </span>
                            @else
                            <span class="term-tag inline-flex items-center gap-1.5">
                                <span class="term-tag w-1.5 h-1.5"></span>
                                Rejected
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4" data-label="Date">
                            <span class="text-xs text-gray-500">{{ $submission->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="px-6 py-4" data-label="Actions">
                            <div class="flex items-center justify-end gap-2">
                                @if($submission->status === 'pending')
                                <a href="{{ route('admin.link-submissions.show', $submission) }}" class="p-2 rounded-lg text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors" title="Review">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <form action="{{ route('admin.link-submissions.approve', $submission) }}" method="POST" onsubmit="return confirm('Approve this link? It will be added to the Useful Links page.')">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors" title="Approve">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('admin.link-submissions.reject', $submission) }}" method="POST" onsubmit="return confirm('Reject this submission?')">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Reject">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400 italic">Reviewed</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-gray-500 dark:text-gray-400">No submissions yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($submissions->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
            {{ $submissions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
