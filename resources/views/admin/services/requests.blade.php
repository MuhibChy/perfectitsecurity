@extends('layouts.app')
@section('page-title', 'Service Requests')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Service Requests</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $serviceRequests->total() }} total requests</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.service-requests.pipeline') }}" class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 text-sm font-medium flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Kanban Pipeline View
            </a>
            <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">← Back to Services</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search requests..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Review Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">All Statuses</option>
                    @foreach(['new', 'under_review', 'awaiting_info', 'scope_clarification', 'pricing_in_progress', 'pending_approval', 'sent_to_customer', 'accepted', 'rejected', 'expired', 'cancelled', 'converted'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                <select name="priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    <option value="">All</option>
                    @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                        <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm">Filter</button>
                <a href="{{ route('admin.service-requests.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm">Reset</a>
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Request #</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Customer</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Service</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Assigned</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($serviceRequests as $req)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white">{{ $req->request_number }}</td>
                        <td class="px-4 py-3">
                            <div class="text-gray-900 dark:text-white">{{ $req->name }}</div>
                            <div class="text-xs text-gray-500">{{ $req->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $req->service->name ?? 'General' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $priorityColors = ['low' => 'gray', 'medium' => 'blue', 'high' => 'amber', 'urgent' => 'red'];
                                $color = $priorityColors[$req->priority] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-400 text-xs rounded-full">{{ ucfirst($req->priority) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = ['new' => 'blue', 'under_review' => 'amber', 'accepted' => 'green', 'rejected' => 'red', 'converted' => 'purple'];
                                $sc = $statusColors[$req->review_status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 bg-{{ $sc }}-100 dark:bg-{{ $sc }}-900/30 text-{{ $sc }}-700 dark:text-{{ $sc }}-400 text-xs rounded-full">{{ str_replace('_', ' ', ucfirst($req->review_status)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $req->assignedTo->name ?? 'Unassigned' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $req->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.service-requests.show', $req->id) }}" class="text-blue-600 hover:underline text-xs">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No service requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $serviceRequests->links() }}
        </div>
    </div>
</div>
@endsection
