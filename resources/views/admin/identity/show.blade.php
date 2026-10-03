@extends('layouts.app')
@section('page-title', 'Review Identity Document')
@section('content')

    <x-page-header title="Review Identity Document" sys="SYSTEM://IDENTITY" />
<div class="term-panel p-6 mb-4">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Review — {{ $document->user->name }} ({{ $document->user->member_number }})</h2>
    <div class="text-sm text-gray-600 dark:text-gray-300 mt-2 space-y-1">
        <p>Type: {{ ucwords(str_replace('_', ' ', $document->document_type)) }} · Country: {{ $document->issuing_country ?? '—' }} · Status: <strong>{{ strtoupper($document->status) }}</strong></p>
        <p>Submitted: {{ $document->created_at->format('Y-m-d H:i') }} · Reviewer: {{ $document->reviewer->name ?? '—' }}</p>
        @if($document->rejection_reason)<p class="text-amber-600 dark:text-amber-400">Rejection reason: {{ $document->rejection_reason }}</p>@endif
    </div>
    @if(session('success'))<div class="mt-3 term-alert term-alert-ok"><span class="term-alert-tag">SYS.OK</span><span>{{ session('success') }}</span></div>@endif
    <div class="flex flex-wrap gap-2 mt-4">
        <a href="{{ route('admin.identity.download', $document->id) }}" class="text-xs px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">Download document (audited)</a>
        @if($document->status === 'submitted')
            <form method="POST" action="{{ route('admin.identity.review', $document->id) }}">@csrf<button class="term-btn term-btn-sm term-btn-ghost">Start review</button></form>
        @endif
        @if(in_array($document->status, ['submitted', 'under_review'], true))
            <form method="POST" action="{{ route('admin.identity.approve', $document->id) }}">@csrf<button class="term-btn term-btn-sm">Verify identity</button></form>
        @endif
    </div>
    @if(in_array($document->status, ['submitted', 'under_review'], true))
        <form method="POST" action="{{ route('admin.identity.reject', $document->id) }}" class="mt-4 max-w-lg space-y-2">
            @csrf
            <label class="term-field-label">Rejection reason (required, shown to member)</label>
            <textarea name="reason" required rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-sm"></textarea>
            <button class="btn btn-destructive term-btn-sm">Reject / request resubmission</button>
        </form>
    @endif
</div>
@endsection
