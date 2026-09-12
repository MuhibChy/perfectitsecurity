@extends('layouts.app')
@section('page-title', 'Commission Rules')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Commission Calculation Rules</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configure technician, sales agent, and contractor commission policies.</p>
        </div>
        <a href="{{ route('admin.commissions.index') }}" class="btn-secondary px-4 py-2 text-sm rounded-xl">Back to Commissions</a>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rule Name</th>
                        <th>Type</th>
                        <th>Rate / Value</th>
                        <th>Applicable Service / Role</th>
                        <th>Status</th>
                        <th>Effective Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules ?? [] as $rule)
                    <tr>
                        <td class="font-semibold text-gray-900 dark:text-white">{{ $rule->name }}</td>
                        <td><span class="badge badge-primary">{{ ucfirst($rule->type ?? 'percentage') }}</span></td>
                        <td class="font-bold text-gray-900 dark:text-white">
                            {{ ($rule->type ?? 'percentage') === 'percentage' ? ($rule->rate ?? $rule->value ?? 0).'%' : '$'.number_format($rule->rate ?? $rule->value ?? 0, 2) }}
                        </td>
                        <td class="text-sm text-gray-600 dark:text-gray-400">{{ $rule->applies_to ?? 'All Eligible Staff' }}</td>
                        <td>
                            <span class="badge {{ ($rule->is_active ?? true) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-gray-100 text-gray-700' }}">
                                {{ ($rule->is_active ?? true) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-xs text-gray-500">{{ $rule->created_at ? $rule->created_at->format('M d, Y') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500">No custom commission rules found. Standard company commission policies apply.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
