@extends('layouts.app')
@section('page-title', 'Leads Management')
@section('content')
<div class="glass-card p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Leads (CRM)</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Website quote requests and contact enquiries land here automatically.</p>
        </div>
        <a href="{{ route('admin.leads.create') }}" class="btn-primary btn-sm">Add Lead</a>
    </div>
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, company…" class="form-input w-64">
        <select name="status" class="form-input">
            <option value="">All statuses</option>
            @foreach(['new','contacted','qualified','proposal','won','lost'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="btn-secondary btn-sm">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-gray-500">
                <th class="py-2 pr-4">Lead</th><th class="py-2 pr-4">Contact</th><th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Value</th><th class="py-2 pr-4">Assignee</th><th class="py-2">Updated</th>
            </tr></thead>
            <tbody>
            @forelse($leads as $lead)
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    <td class="py-2 pr-4"><a href="{{ route('admin.leads.show', $lead) }}" class="font-semibold text-primary-600">{{ $lead->lead_number }} — {{ $lead->name }}</a><div class="text-xs text-gray-500">{{ $lead->company_name }} · {{ $lead->source }}</div></td>
                    <td class="py-2 pr-4">{{ $lead->email }}<div class="text-xs text-gray-500">{{ $lead->phone }}</div></td>
                    <td class="py-2 pr-4"><span class="badge">{{ $lead->status }}</span></td>
                    <td class="py-2 pr-4">{{ $lead->currency }} {{ number_format($lead->estimated_value ?? 0, 2) }}</td>
                    <td class="py-2 pr-4">{{ $lead->assignee?->name ?? '—' }}</td>
                    <td class="py-2 text-gray-500">{{ $lead->updated_at?->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-500">No leads yet. Public quote requests will appear here.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $leads->links() }}</div>
</div>
@endsection
