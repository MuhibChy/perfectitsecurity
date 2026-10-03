@extends('layouts.app')
@section('page-title', 'Compensation')
@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header :title="'Compensation — ' . $employee->name" subtitle="Current model: {{ $compensation?->modelLabel() ?? 'Not configured' }}. Configuration only — payouts run through salaries, commissions and bank transfers." sys="ADMIN://COMPENSATION" :breadcrumbs="['People' => route('admin.people.index'), $employee->name => null]" />

    <form method="POST" action="{{ route('admin.ecosystem.compensation.update', $employee) }}" class="term-panel p-6 sm:p-8 space-y-5">
        @csrf @method('PUT')

        <div class="grid sm:grid-cols-3 gap-4">
            <label class="term-panel-2 p-3 flex items-center gap-2.5 text-sm cursor-pointer">
                <input type="checkbox" name="has_salary" value="1" {{ old('has_salary', $compensation?->has_salary) ? 'checked' : '' }} class="accent-[#00E67A]">
                <span class="text-slate-900 dark:text-white font-medium">Salary based</span>
            </label>
            <label class="term-panel-2 p-3 flex items-center gap-2.5 text-sm cursor-pointer">
                <input type="checkbox" name="has_commission" value="1" {{ old('has_commission', $compensation?->has_commission) ? 'checked' : '' }} class="accent-[#00E67A]">
                <span class="text-slate-900 dark:text-white font-medium">Commission based</span>
            </label>
            <label class="term-panel-2 p-3 flex items-center gap-2.5 text-sm cursor-pointer">
                <input type="checkbox" name="has_project_pay" value="1" {{ old('has_project_pay', $compensation?->has_project_pay) ? 'checked' : '' }} class="accent-[#00E67A]">
                <span class="text-slate-900 dark:text-white font-medium">Project based</span>
            </label>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="term-field-label">Salary amount</label>
                <input type="number" step="0.01" min="0" name="salary_amount" value="{{ old('salary_amount', $compensation?->salary_amount) }}" class="term-input">
            </div>
            <div>
                <label class="term-field-label">Frequency</label>
                <select name="salary_frequency" class="term-input">
                    <option value="">—</option>
                    @foreach(['weekly', 'biweekly', 'monthly', 'yearly'] as $f)
                    <option value="{{ $f }}" {{ old('salary_frequency', $compensation?->salary_frequency) === $f ? 'selected' : '' }}>{{ ucfirst($f) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="term-field-label">Start date</label>
                <input type="date" name="salary_start_date" value="{{ old('salary_start_date', optional($compensation?->salary_start_date)->format('Y-m-d')) }}" class="term-input">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="term-field-label">Commission type</label>
                <select name="commission_type" class="term-input">
                    <option value="">—</option>
                    @foreach(['percentage', 'fixed'] as $t)
                    <option value="{{ $t }}" {{ old('commission_type', $compensation?->commission_type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="term-field-label">Commission value</label>
                <input type="number" step="0.0001" min="0" name="commission_value" value="{{ old('commission_value', $compensation?->commission_value) }}" class="term-input">
            </div>
            <div>
                <label class="term-field-label">Commission rule</label>
                <select name="commission_rule_id" class="term-input">
                    <option value="">— None —</option>
                    @foreach($rules as $r)
                    <option value="{{ $r->id }}" {{ (int) old('commission_rule_id', $compensation?->commission_rule_id) === (int) $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="term-field-label">Project terms</label>
            <textarea name="project_terms" rows="3" class="term-input" maxlength="2000">{{ old('project_terms', $compensation?->project_terms) }}</textarea>
        </div>

        <div>
            <label class="term-field-label">Status</label>
            <select name="status" class="term-input" required>
                @foreach(['active', 'suspended'] as $s)
                <option value="{{ $s }}" {{ old('status', $compensation?->status ?? 'active') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>

        <button class="term-btn" type="submit">Save compensation model</button>
    </form>
</div>
@endsection
