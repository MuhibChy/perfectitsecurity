@extends('layouts.app')
@section('page-title', 'People / Accounts')
@section('content')

    <x-page-header title="People / Accounts" sys="SYSTEM://PEOPLE" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">People / Accounts</h2>
    <form method="GET" class="flex flex-wrap gap-2 mb-4 text-sm">
        <input name="search" value="{{ request('search') }}" placeholder="Name, email, employee no." class="rounded border px-2 py-1 dark:bg-gray-800" />
        <select name="role" class="rounded border px-2 py-1 dark:bg-gray-800"><option value="">All roles</option>@foreach($roles as $key => $label)<option value="{{ $key }}" @selected(request('role')===$key)>{{ $label }}</option>@endforeach</select>
        <input name="country" value="{{ request('country') }}" placeholder="Country" class="rounded border px-2 py-1 dark:bg-gray-800 w-28" />
        <input name="branch" value="{{ request('branch') }}" placeholder="Branch" class="rounded border px-2 py-1 dark:bg-gray-800 w-28" />
        <button class="px-3 py-1 rounded bg-blue-600 text-white">Filter</button>
    </form>
    <table class="data-table min-w-full text-sm term-table">
        <thead><tr><th class="text-left p-2">Name</th><th class="text-left p-2">Role</th><th class="text-left p-2">Country</th><th class="text-left p-2">Status</th><th class="text-left p-2">Last login</th></tr></thead>
        <tbody>
        @foreach($people as $person)
            <tr class="border-t">
                <td class="p-2" data-label="Name"><a class="text-blue-600 underline" href="{{ route('admin.people.show', $person->id) }}">{{ $person->name }}</a><div class="text-xs text-gray-500">{{ $person->email }}</div></td>
                <td class="p-2" data-label="Role">{{ $person->roleDisplayName() }}</td>
                <td class="p-2" data-label="Country">{{ $person->country ?? '—' }}</td>
                <td class="p-2" data-label="Status">{{ $person->verification_status ?? 'pending' }}</td>
                <td class="p-2 text-xs" data-label="Last login">{{ $person->last_login_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $people->links() }}</div>
</div>
@endsection
