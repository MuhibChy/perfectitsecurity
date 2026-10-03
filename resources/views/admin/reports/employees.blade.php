@extends('layouts.app')
@section('page-title', 'Employee Performance Reports')

@section('content')
<div class="space-y-6">
    <x-page-header title="Employee Performance Reports" sys="REPORT://REPORTS" />
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Staff & Technician Productivity</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Task completion rates, ticket resolution velocity, and engineer utilization.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="term-btn term-btn-ghost">All Reports</a>
    </div>

    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Assigned Tickets</th>
                        <th>Resolved Tickets</th>
                        <th>Active Tasks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees ?? [] as $emp)
                    <tr>
                        <td class="font-semibold text-gray-900 dark:text-white" data-label="Employee">{{ $emp->name }}</td>
                        <td class="text-sm text-gray-500" data-label="Role">{{ ucfirst(str_replace('_', ' ', $emp->role)) }}</td>
                        <td data-label="Assigned Tickets">{{ $emp->assigned_tickets_count ?? $emp->assignedTickets->count() ?? 0 }}</td>
                        <td data-label="Resolved Tickets">{{ $emp->resolved_tickets_count ?? 0 }}</td>
                        <td data-label="Active Tasks">{{ $emp->assigned_tasks_count ?? $emp->tasks->count() ?? 0 }}</td>
                        <td data-label="Status">
                            <span class="term-tag {{ ($emp->is_active ?? true) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-gray-100 text-gray-700' }}">
                                {{ ($emp->is_active ?? true) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">No employee records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
