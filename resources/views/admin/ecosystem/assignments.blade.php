@extends('layouts.app')
@section('page-title', 'Team Assignments')
@section('content')
<div class="space-y-6">
    <x-page-header title="Team Assignments" subtitle="Structured employee → work links with a preserved history. Completing an assignment stamps the row; re-assignment creates a new row." sys="ADMIN://ASSIGNMENTS" />

    <form method="POST" action="{{ route('admin.ecosystem.assignments.store') }}" class="term-panel p-6 grid sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
        @csrf
        <div class="lg:col-span-2">
            <label class="term-field-label">Employee</label>
            <select name="employee_id" class="term-input" required>
                @foreach($employees as $e)
                <option value="{{ $e->id }}">{{ $e->name }} · {{ $e->roleDisplayName() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="term-field-label">Target type</label>
            <select name="assignable_type" class="term-input" required>
                @foreach($types as $t)
                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="term-field-label">Target ID</label>
            <input type="number" name="assignable_id" class="term-input" required min="1">
        </div>
        <div>
            <button class="term-btn term-btn-sm w-full" type="submit">Assign</button>
        </div>
        <div class="sm:col-span-2 lg:col-span-5">
            <label class="term-field-label">Notes</label>
            <input type="text" name="notes" class="term-input" maxlength="2000">
        </div>
    </form>

    <div class="term-panel p-6">
        <div class="term-table-wrap"><table class="term-table">
            <thead><tr><th>ID</th><th>Employee</th><th>Target</th><th>By</th><th>Status</th><th>Started</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td class="mono-id">#{{ $a->id }}</td>
                    <td>{{ $a->employee->name }}</td>
                    <td>{{ class_basename($a->assignable_type) }} #{{ $a->assignable_id }}</td>
                    <td>{{ $a->assigner->name }}</td>
                    <td><x-status-badge :status="$a->status === 'active' ? 'in_progress' : ($a->status === 'completed' ? 'completed' : 'cancelled')" :label="$a->status" /></td>
                    <td class="mono-id">{{ optional($a->started_at)->format('Y-m-d') }}</td>
                    <td>
                        @if($a->status === 'active')
                        <span class="flex gap-2">
                            <form method="POST" action="{{ route('admin.ecosystem.assignments.transition', $a) }}">@csrf<input type="hidden" name="status" value="completed"><button class="term-btn term-btn-sm" type="submit">Complete</button></form>
                            <form method="POST" action="{{ route('admin.ecosystem.assignments.transition', $a) }}">@csrf<input type="hidden" name="status" value="revoked"><button class="term-btn term-btn-sm term-btn-ghost" type="submit">Revoke</button></form>
                        </span>
                        @else
                        <span class="mono-id">{{ optional($a->completed_at)->format('Y-m-d') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-slate-500">No assignments.</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="mt-3">{{ $assignments->links() }}</div>
    </div>
</div>
@endsection
