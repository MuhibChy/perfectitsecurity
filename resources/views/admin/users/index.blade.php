@extends('layouts.app')

@section('title', 'User Directory & Access Control — Admin Portal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="User & Staff Management"
        subtitle="Manage user accounts, enforce role-based access control (RBAC), and review identity verification statuses."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Users' => null]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Create New User
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter & Search Card --}}
    <div class="glass-card p-4 rounded-2xl">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-none">
                @php $currentRole = request('role'); @endphp
                <a href="{{ route('admin.users.index', array_filter(['search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ !$currentRole ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300' }}">
                    All Users
                </a>
                @foreach(['admin' => 'Admins', 'support_agent' => 'Support', 'project_manager' => 'PMs', 'finance_manager' => 'Finance', 'employee' => 'Employees', 'customer' => 'Customers', 'freelancer' => 'Freelancers'] as $rKey => $rLabel)
                <a href="{{ route('admin.users.index', array_filter(['role' => $rKey, 'search' => request('search')])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap {{ $currentRole === $rKey ? 'bg-primary-600 text-white' : 'bg-surface-100 dark:bg-navy-800 text-gray-600 dark:text-gray-300 hover:bg-surface-200' }}">
                    {{ $rLabel }}
                </a>
                @endforeach
            </div>

            <div class="relative w-full md:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white dark:bg-navy-800 border border-surface-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="glass-card overflow-hidden">
        @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Assigned Role</th>
                        <th>Organization</th>
                        <th>Account Status</th>
                        <th>Verification</th>
                        <th>Last Login</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-cyber-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.show', $u) }}" class="font-bold text-gray-900 dark:text-white hover:text-primary-600 transition-colors">
                                        {{ $u->name }}
                                    </a>
                                    <div class="text-xs text-gray-500 font-mono">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-surface-100 dark:bg-navy-800 text-gray-700 dark:text-gray-300">
                                {{ ucfirst(str_replace('_', ' ', $u->role)) }}
                            </span>
                        </td>
                        <td class="text-xs text-gray-600 dark:text-gray-300">
                            {{ $u->company->name ?? $u->company_name ?? '—' }}
                        </td>
                        <td>
                            <x-status-badge :status="$u->is_active ? 'active' : 'inactive'" />
                        </td>
                        <td>
                            @if($u->isFullyVerified())
                                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">✓ Verified</span>
                            @else
                                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">Pending</span>
                            @endif
                        </td>
                        <td class="text-xs text-gray-500">
                            {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.users.show', $u) }}" class="btn-ghost btn-sm text-xs">
                                    View
                                </a>
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn-secondary btn-sm text-xs">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
            {{ $users->withQueryString()->links() }}
        </div>
        @else
        <x-empty-state
            title="No Users Found"
            message="No users match the selected role filter or search query."
        />
        @endif
    </div>
</div>
@endsection
