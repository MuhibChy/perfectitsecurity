@extends('layouts.app')
@section('page-title', 'AI Usage & Costs')
@section('content')
<div class="space-y-6">
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="stat-card"><p class="text-sm text-gray-500">Total Cost</p><p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($totalCost, 4) }}</p></div>
        <div class="stat-card"><p class="text-sm text-gray-500">Total Tokens</p><p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalTokens) }}</p></div>
        <div class="stat-card"><p class="text-sm text-gray-500">Total Requests</p><p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($records->total()) }}</p></div>
    </div>

    <div class="glass-card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field max-w-xs">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field max-w-xs">
            <select name="success" class="input-field max-w-xs">
                <option value="">All</option>
                <option value="1" {{ request('success') === '1' ? 'selected' : '' }}>Successful</option>
                <option value="0" {{ request('success') === '0' ? 'selected' : '' }}>Failed</option>
            </select>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
        </form>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Date</th><th>User</th><th>Type</th><th>Model</th><th>Input</th><th>Output</th><th>Cost</th><th>Time</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($records as $r)
                    <tr>
                        <td class="text-sm">{{ $r->recorded_date->format('M d') }}</td>
                        <td class="text-sm">{{ $r->user->name ?? 'Guest' }}</td>
                        <td><span class="badge badge-info text-xs">{{ $r->request_type }}</span></td>
                        <td class="text-sm text-gray-500">{{ $r->model }}</td>
                        <td class="text-sm">{{ number_format($r->input_tokens) }}</td>
                        <td class="text-sm">{{ number_format($r->output_tokens) }}</td>
                        <td class="text-sm font-medium">${{ number_format($r->cost, 4) }}</td>
                        <td class="text-sm text-gray-500">{{ $r->response_time_ms }}ms</td>
                        <td><span class="badge {{ $r->success ? 'badge-success' : 'badge-danger' }} text-xs">{{ $r->success ? 'OK' : 'Failed' }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-gray-500 py-8">No usage records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">{{ $records->links() }}</div>
    </div>
</div>
@endsection
