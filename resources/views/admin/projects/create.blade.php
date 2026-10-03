@extends('layouts.app')

@section('title', 'Launch Project — Admin Portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-page-header sys="OPS://PROJECTS"
        title="Launch New Project"
        subtitle="Initialize a client engagement, allocate budgets, and assign lead project managers."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Projects' => route('admin.projects.index'), 'New' => null]"
    />

    <div class="term-panel p-8 lg:p-10 shadow-xl border border-white/10">
        <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-6">
            @csrf

            <div class="grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="term-field-label">Project Title</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. SOC 2 Type II Readiness & Infrastructure Migration"
                           class="term-input">
                    @error('name') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Customer Account</label>
                    <select name="customer_id" required class="term-input">
                        <option value="">Select a client...</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->email }})
                        </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Assigned Project Manager</label>
                    <select name="project_manager_id" class="term-input">
                        <option value="">Assign lead later...</option>
                        @foreach($managers as $m)
                        <option value="{{ $m->id }}" {{ old('project_manager_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('project_manager_id') <p class="term-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-5">
                <div>
                    <label class="term-field-label">Priority Level</label>
                    <select name="priority" required class="term-input">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="term-field-label">Kickoff Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}"
                           class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Target Deadline</label>
                    <input type="date" name="deadline" value="{{ old('deadline', now()->addDays(45)->format('Y-m-d')) }}"
                           class="term-input">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Client Contract Budget ($)</label>
                    <input type="number" step="0.01" min="0" name="budget" value="{{ old('budget') }}" placeholder="e.g. 15000.00"
                           class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Estimated Cost of Delivery ($)</label>
                    <input type="number" step="0.01" min="0" name="estimated_cost" value="{{ old('estimated_cost') }}" placeholder="e.g. 9500.00"
                           class="term-input">
                </div>
            </div>

            <div>
                <label class="term-field-label">Scope of Work & Objectives</label>
                <textarea name="description" rows="4" placeholder="Detailed engineering deliverables, tech stack, SLA targets..."
                          class="term-input"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                <a href="{{ route('admin.projects.index') }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                <button type="submit" class="term-btn term-btn-sm">Create Project</button>
            </div>
        </form>
    </div>
</div>
@endsection
