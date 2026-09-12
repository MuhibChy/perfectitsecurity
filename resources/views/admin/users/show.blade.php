@extends('layouts.app')

@section('title', 'User Profile: ' . $user->name . ' — Admin Portal')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <x-page-header
        :title="$user->name"
        :subtitle="$user->email . ' • Role: ' . ucfirst(str_replace('_', ' ', $user->role))"
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Users' => route('admin.users.index'), 'Profile' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">
                &larr; Back to Directory
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary btn-sm">
                Edit User
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- User Profile Overview Card --}}
    <div class="glass-card p-6 lg:p-8 rounded-2xl shadow-xl border border-white/10">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-cyber-500 text-white flex items-center justify-center font-bold text-2xl shadow-lg shadow-primary-500/20">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                        <x-status-badge :status="$user->is_active ? 'active' : 'inactive'" />
                        @if($user->isFullyVerified())
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full">✓ Verified</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 font-mono mt-0.5">{{ $user->email }}</p>
                    @if($user->company_name)
                    <p class="text-xs text-gray-400 mt-1">Company: {{ $user->company_name }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 border-t md:border-t-0 md:border-l border-gray-100 dark:border-white/5 pt-4 md:pt-0 md:pl-6">
                <div>
                    <span class="block text-gray-400">Phone:</span>
                    <strong class="text-gray-800 dark:text-gray-200">{{ $user->phone ?: 'None' }}</strong>
                </div>
                <div>
                    <span class="block text-gray-400">Created:</span>
                    <strong class="text-gray-800 dark:text-gray-200">{{ $user->created_at->format('M d, Y') }}</strong>
                </div>
                <div>
                    <span class="block text-gray-400">Last Login:</span>
                    <strong class="text-gray-800 dark:text-gray-200">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</strong>
                </div>
                <div>
                    <span class="block text-gray-400">Login IP:</span>
                    <strong class="text-gray-800 dark:text-gray-200 font-mono">{{ $user->last_login_ip ?: 'None' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Associated Records Tabs/Grids --}}
    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Tickets Card --}}
        <div class="glass-card p-6 rounded-2xl">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                <span>Associated Tickets</span>
                <span class="text-xs font-mono text-primary-600">{{ $user->tickets->count() }} total</span>
            </h3>
            @if($user->tickets->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($user->tickets->take(5) as $ticket)
                <div class="py-3 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $ticket->subject }}</div>
                        <span class="text-gray-400 font-mono">{{ $ticket->ticket_number }}</span>
                    </div>
                    <x-status-badge :status="$ticket->status" />
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-500 py-4 text-center">No tickets associated with this profile.</p>
            @endif
        </div>

        {{-- Projects Card --}}
        <div class="glass-card p-6 rounded-2xl">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                <span>Projects In Scope</span>
                <span class="text-xs font-mono text-primary-600">{{ $user->projects->count() }} total</span>
            </h3>
            @if($user->projects->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($user->projects->take(5) as $proj)
                <div class="py-3 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $proj->name }}</div>
                        <span class="text-gray-400 font-mono">{{ $proj->project_number }}</span>
                    </div>
                    <x-status-badge :status="$proj->status" />
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-500 py-4 text-center">No projects assigned or owned.</p>
            @endif
        </div>
    </div>
</div>
@endsection
