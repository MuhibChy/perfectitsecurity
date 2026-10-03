@extends('layouts.app')
@section('page-title', 'Contact Directory')
@section('content')
<div class="space-y-6">
    <x-page-header title="Contact Directory" subtitle="Authorized contacts with presence-aware cards. Compensation details never appear here." sys="ADMIN://DIRECTORY" />

    <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700">Team</h2>
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($staff as $s)
        <a href="{{ route('admin.directory.show', $s) }}" class="term-panel p-5 hover:border-emerald-600/40 dark:hover:border-accent/40 transition-colors block">
            <div class="flex items-center gap-3">
                <img src="{{ $s->avatar_url }}" alt="" class="w-11 h-11 rounded-full object-cover border border-slate-200 dark:border-white/10">
                <div class="min-w-0">
                    <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $s->name }}</div>
                    <div class="text-xs text-slate-600 dark:text-term-800 truncate">{{ $s->job_title ?? $s->roleDisplayName() }}{{ $s->department ? ' · ' . $s->department : '' }}</div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    <div>{{ $staff->links() }}</div>

    @if($customers)
    <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700">Customers</h2>
    <div class="term-panel p-6">
        <div class="term-table-wrap"><table class="term-table">
            <thead><tr><th>Customer</th><th>Company</th><th>Contact</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($customers as $c)
                <tr>
                    <td><a href="{{ route('admin.people.show', $c) }}" class="term-link">{{ $c->name }}</a><div class="mono-id">{{ $c->member_number }}</div></td>
                    <td>{{ $c->company_name ?? '—' }}</td>
                    <td class="mono-id">{{ $c->email }}<br>{{ $c->phone ?? '—' }}</td>
                    <td><x-status-badge :status="$c->is_active ? 'active' : 'inactive'" /></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="mt-3">{{ $customers->links() }}</div>
    </div>
    @endif
</div>
@endsection
