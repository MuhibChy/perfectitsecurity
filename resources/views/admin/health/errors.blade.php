@extends('layouts.app')
@section('page-title', 'Error Center')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Centralized Error Center</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Aggregated tracking of backend exceptions, 404/500 errors, database anomalies, and frontend JavaScript runtime issues.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.health.index') }}" class="btn-secondary px-4 py-2 rounded-xl text-sm font-medium">Overview</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card p-4">
        <form method="GET" action="{{ route('admin.health.errors') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search error message or file..." class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
            </div>
            <div>
                <select name="module" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="">-- All Modules --</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <option value="unresolved" {{ request('status', 'unresolved') === 'unresolved' ? 'selected' : '' }}>Unresolved Only</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved Only</option>
                    <option value="" {{ request('status') === '' ? 'selected' : '' }}>All Statuses</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full btn-secondary py-2 text-xs font-semibold rounded-xl">Filter</button>
                <a href="{{ route('admin.health.errors') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-900 dark:hover:text-white rounded-xl">Reset</a>
            </div>
        </form>
    </div>

    {{-- Error List --}}
    <div class="space-y-4">
        @forelse($errors as $err)
        <div x-data="{ expanded: false, showNoteModal: false }" class="glass-card p-5 border {{ $err->status === 'unresolved' ? 'border-red-200 dark:border-red-900/40' : 'border-gray-200 dark:border-gray-800' }}">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="space-y-2 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge {{ $err->status === 'unresolved' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $err->module }}
                        </span>
                        <span class="badge badge-primary">{{ $err->error_type }}</span>
                        @if($err->route)
                        <span class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $err->route }}</span>
                        @endif
                        <span class="text-xs font-bold text-red-600 bg-red-50 dark:bg-red-950/40 px-2 py-0.5 rounded-full">{{ $err->occurrences }} occurrences</span>
                    </div>

                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $err->message }}</h3>

                    @if($err->file)
                    <p class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $err->file }}:{{ $err->line ?? '' }}</p>
                    @endif

                    <div class="flex items-center gap-4 text-xs text-gray-400 pt-1">
                        <span>First seen: {{ $err->first_seen_at ? $err->first_seen_at->format('M d, H:i') : '-' }}</span>
                        <span>Last seen: {{ $err->last_seen_at ? $err->last_seen_at->diffForHumans() : '-' }}</span>
                        @if($err->resolved_at)
                        <span class="text-emerald-600 font-semibold">Resolved {{ $err->resolved_at->diffForHumans() }}</span>
                        @endif
                    </div>

                    @if($err->notes)
                    <div class="mt-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/50 text-xs text-gray-700 dark:text-gray-300">
                        <strong class="text-gray-900 dark:text-white">Admin Notes:</strong>
                        <p class="mt-1 whitespace-pre-line">{{ $err->notes }}</p>
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap md:flex-col items-end gap-2">
                    @if($err->status === 'unresolved')
                    <form action="{{ route('admin.health.resolve-error', $err->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                            Mark Resolved
                        </button>
                    </form>
                    @endif

                    @if($err->trace)
                    <button @click="expanded = !expanded" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                        <span x-text="expanded ? 'Hide Trace' : 'View Trace'">View Trace</span>
                    </button>
                    @endif

                    <button @click="showNoteModal = !showNoteModal" class="px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl transition-colors">
                        + Add Note
                    </button>
                </div>
            </div>

            {{-- Note Add Box --}}
            <div x-show="showNoteModal" x-transition class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800" style="display: none;">
                <form action="{{ route('admin.health.add-error-note', $err->id) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="note" required placeholder="Add troubleshooting note or ticket reference..." class="flex-1 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl">Save Note</button>
                </form>
            </div>

            {{-- Stack Trace Detail --}}
            @if($err->trace)
            <div x-show="expanded" x-transition class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800" style="display: none;">
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Stack Trace:</h4>
                <pre class="p-4 rounded-xl bg-gray-900 text-gray-200 text-[11px] font-mono overflow-x-auto max-h-64 select-all leading-relaxed whitespace-pre-wrap">{{ $err->trace }}</pre>
            </div>
            @endif
        </div>
        @empty
        <div class="glass-card p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mx-auto mb-3 text-emerald-600 dark:text-emerald-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="font-bold text-base text-gray-900 dark:text-white">No matching errors recorded</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">The application error center is currently clear for the selected filters.</p>
        </div>
        @endforelse

        <div class="p-4">
            {{ $errors->links() }}
        </div>
    </div>
</div>
@endsection
