@extends('layouts.app')
@section('page-title', 'Identity Verification Queue')
@section('content')

    <x-page-header title="Identity Verification Queue" sys="SYSTEM://IDENTITY" />
<div class="term-panel p-6 mb-4">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Identity Verification Queue</h2>
    <div class="flex flex-wrap gap-3 mt-3 text-sm">
        @foreach($stats as $k => $v)<span class="term-tag">{{ ucfirst(str_replace('_', ' ', $k)) }}: <strong>{{ $v }}</strong></span>@endforeach
    </div>
    @if(session('success'))<div class="mt-3 term-alert term-alert-ok"><span class="term-alert-tag">SYS.OK</span><span>{{ session('success') }}</span></div>@endif
</div>
<div class="term-panel p-6">
    <form method="GET" class="mb-3 flex gap-2">
        <input type="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Name, email or member #" aria-label="Search applicants" class="text-sm px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
        <select name="status" class="text-sm px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
            <option value="">All statuses</option>
            @foreach(['submitted', 'under_review', 'verified', 'rejected', 'resubmission_required'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="text-sm px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800">Filter</button>
    </form>
    @forelse($documents as $d)
        <div class="text-sm border-t border-gray-200 dark:border-gray-700 py-2 flex flex-wrap gap-x-3 items-center text-gray-600 dark:text-gray-300">
            <a href="{{ route('admin.identity.show', $d->id) }}" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">#{{ $d->id }} {{ $d->user->name ?? '—' }} ({{ $d->user->member_number ?? '—' }})</a>
            <span>{{ ucwords(str_replace('_', ' ', $d->document_type)) }}</span>
            <span><strong>{{ strtoupper($d->status) }}</strong></span>
            <span>{{ $d->created_at->format('Y-m-d H:i') }}</span>
        </div>
    @empty<p class="text-sm text-gray-500">Queue empty.</p>@endforelse
    <div class="mt-3">{{ $documents->links() }}</div>
</div>
@endsection
