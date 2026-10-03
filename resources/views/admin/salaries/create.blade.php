@extends('layouts.app')
@section('page-title', 'New payroll record')
@section('content')

    <x-page-header title="New payroll record" sys="FINANCE://SALARIES" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold mb-4">New payroll record</h2>
    <form method="POST" action="{{ route('admin.salaries.store') }}">@csrf
        <label class="term-field-label">Employee</label>
        <select name="user_id" class="block rounded border px-2 py-1 mb-2 dark:bg-gray-800">@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }} ({{ $e->role }})</option>@endforeach</select>
        <div class="flex gap-2">
            <div><label class="term-field-label">Base</label><input name="base_salary" type="number" step="0.01" min="0" required class="block rounded border px-2 py-1 dark:bg-gray-800" /></div>
            <div><label class="term-field-label">Bonus</label><input name="bonus" type="number" step="0.01" min="0" class="block rounded border px-2 py-1 dark:bg-gray-800" /></div>
            <div><label class="term-field-label">Deductions</label><input name="deductions" type="number" step="0.01" min="0" class="block rounded border px-2 py-1 dark:bg-gray-800" /></div>
        </div>
        <label class="term-field-label">Period</label><input name="period" required value="2026-09" class="rounded border px-2 py-1 dark:bg-gray-800" maxlength="30" />
        <label class="term-field-label">Notes</label><input name="notes" class="block w-full rounded border px-2 py-1 dark:bg-gray-800" maxlength="1000" />
        <button class="mt-3 px-3 py-1 rounded bg-blue-600 text-white">Create (pending approval)</button>
    </form>
</div>
@endsection
