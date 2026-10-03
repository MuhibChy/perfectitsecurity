@extends('layouts.app')
@section('title', 'Test Users & Role Testing')
@section('page-title', 'Role Testing')

@section('content')
<x-page-header sys="SYSTEM://ROLE-TESTING" title="Test Users & Role Testing" subtitle="Synthetic accounts, pending role requests, registry lifecycle and the documented permission matrix. Passwords are never shown here." badge="ADMIN ONLY">
    <a href="{{ route('admin.users.index') }}" class="term-btn term-btn-ghost term-btn-sm">User Management</a>
</x-page-header>

<h2 class="heading-sm mb-3">Pending Role Requests</h2>
<div class="card p-0 overflow-hidden mb-8"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>User</th><th>Current</th><th>Requested</th><th>Actions</th></tr></thead>
    <tbody>
        @forelse($pending as $u)
        <tr>
            <td class="font-medium" data-label="User">{{ $u->name }}<div class="text-xs text-slate-500">{{ $u->email }}</div></td>
            <td data-label="Current"><x-role-badge :role="$u->role" /></td>
            <td data-label="Requested"><x-role-badge :role="$u->requested_role" /></td>
            <td class="whitespace-nowrap" data-label="Actions">
                <form method="POST" action="{{ route('admin.role-testing.approve', $u) }}" class="inline">@csrf<button class="term-btn term-btn-sm">Approve</button></form>
                <form method="POST" action="{{ route('admin.role-testing.reject', $u) }}" class="inline">@csrf<input name="reason" class="term-input inline-block w-40" placeholder="Reason (optional)"><button class="term-btn term-btn-ghost term-btn-sm ml-1">Reject</button></form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center text-slate-500 py-4">No pending requests.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>

<h2 class="heading-sm mb-3">Synthetic Test Accounts <span class="text-xs font-normal text-slate-500">(@example.test · credentials issued via protected channel only)</span></h2>
<div class="card p-0 overflow-hidden mb-8"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Dashboard</th><th>Test purpose</th><th>Actions</th></tr></thead>
    <tbody>
        @foreach($testUsers as $u)
        @php $reg = \App\Support\RoleRegistry::for($u->role); @endphp
        <tr>
            <td class="font-medium" data-label="User">{{ $u->name }}<div class="text-xs text-slate-500">{{ $u->email }}</div></td>
            <td data-label="Role"><x-role-badge :role="$u->role" /></td>
            <td data-label="Status"><span class="term-tag {{ $u->is_active ? '' : '' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
            <td class="text-sm" data-label="Dashboard">{{ $reg['dashboard_label'] ?? '—' }}</td>
            <td class="text-sm text-slate-600 dark:text-slate-300" data-label="Test purpose">
                @switch($u->role)
                    @case('customer') Customer journey: request → quote → order → payment → project @break
                    @case('support_agent') Tickets, SLA, responses @break
                    @case('project_manager') Projects, milestones, tasks @break
                    @case('finance_manager') Invoices, payments, income, expenses @break
                    @case('employee') Assigned tasks and work updates @break
                    @case('freelancer') Contractor tasks and commission @break
                    @case('admin') Administration and RBAC @break
                    @default Staff workflow @break
                @endswitch
            </td>
            <td class="whitespace-nowrap" data-label="Actions">
                <a href="{{ route('admin.users.show', $u) }}" class="link-arrow text-xs">View →</a>
                <form method="POST" action="{{ route('admin.role-testing.toggle', $u) }}" class="inline">@csrf<button class="text-xs {{ $u->is_active ? 'text-red-600' : 'text-emerald-600' }} ml-2">{{ $u->is_active ? 'Deactivate' : 'Reactivate' }}</button></form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>

<h2 class="heading-sm mb-3">Role Registry Lifecycle</h2>
<div class="card p-0 overflow-hidden mb-8"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Role</th><th>Status</th><th>Registration</th><th>Users</th><th>Change status</th></tr></thead>
    <tbody>
        @foreach($roles as $role)
        <tr>
            <td class="font-medium" data-label="Role">{{ $role->display_name }}<div class="text-xs text-slate-500 font-mono">{{ $role->name }}</div></td>
            <td data-label="Status"><span class="term-tag {{ $role->status === 'active' ? '' : '' }}">{{ ucfirst($role->status) }}</span></td>
            <td class="text-sm" data-label="Registration">{{ $role->registration_allowed ? ($role->self_registration ? 'Self-service' : 'Request + approval') : 'Admin-created only' }}</td>
            <td data-label="Users">{{ $role->users()->count() }}</td>
            <td data-label="Change status"><form method="POST" action="{{ route('admin.role-testing.retire', $role) }}" class="flex gap-1">@csrf<select name="status" class="term-input">@foreach(\App\Models\Role::STATUSES as $s)<option value="{{ $s }}" @selected($role->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="term-btn term-btn-ghost term-btn-sm">Set</button></form></td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<p class="term-hint mb-8">Deprecating or archiving a role blocks new registrations only — existing users and all historical records keep working. Migrate users explicitly before archiving.</p>

<h2 class="heading-sm mb-3">Permission Matrix (documented from code)</h2>
<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Feature</th>@foreach($matrix['roles'] as $r)<th>{{ \App\Support\RoleRegistry::displayName($r) }}</th>@endforeach</tr></thead>
    <tbody>
        @foreach($matrix['rows'] as $row)
        <tr><td class="font-medium" data-label="Feature">{{ $row['feature'] }}</td>@foreach($matrix['roles'] as $r)<td class="text-center" data-label="Role Access">{{ $row[$r] ? '✓' : '—' }}</td>@endforeach</tr>
        @endforeach
    </tbody>
</table>
</div></div>
@endsection
