@extends('layouts.app')
@section('page-title', 'Support Directory')
@section('content')
<div class="space-y-6">
    <x-page-header title="Support Directory" subtitle="Reach the team through your preferred channel. Direct numbers unlock once we are actively working together." sys="CLIENT://DIRECTORY" num="11" />

    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($staff as $s)
        <a href="{{ route('portal.directory.show', $s) }}" class="term-panel p-5 hover:border-emerald-600/40 dark:hover:border-accent/40 transition-colors block">
            <div class="flex items-center gap-3">
                <img src="{{ $s->avatar_url }}" alt="" class="w-11 h-11 rounded-full object-cover border border-slate-200 dark:border-white/10">
                <div class="min-w-0">
                    <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $s->name }}</div>
                    <div class="text-xs text-slate-600 dark:text-term-800 truncate">{{ $s->job_title ?? $s->roleDisplayName() }}{{ $s->department ? ' · ' . $s->department : '' }}</div>
                </div>
            </div>
        </a>
        @empty
        <p class="text-sm text-slate-600 dark:text-term-800">No contacts available.</p>
        @endforelse
    </div>
    <div>{{ $staff->links() }}</div>
</div>
@endsection
