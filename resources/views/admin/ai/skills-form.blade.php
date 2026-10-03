@extends('layouts.app')
@section('page-title', $skill->exists ? 'Edit Skill' : 'New Skill')
@section('content')

    <x-page-header title="AI" sys="SYSTEM://AI" />
<div class="max-w-3xl mx-auto">
    <div class="term-panel p-6">
        <form method="POST" action="{{ $skill->exists ? route('admin.ai.skills.update', $skill) : route('admin.ai.skills.store') }}" class="space-y-4">
            @csrf
            @if($skill->exists) @method('PUT') @endif
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $skill->name) }}" required maxlength="255" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Slug (auto from name if blank)</label>
                    <input type="text" name="slug" value="{{ old('slug', $skill->slug) }}" maxlength="255" class="term-input font-mono">
                </div>
            </div>
            <div>
                <label class="term-field-label">Description</label>
                <textarea name="description" rows="2" maxlength="2000" class="term-input">{{ old('description', $skill->description) }}</textarea>
            </div>
            <div>
                <label class="term-field-label">System Instructions * (how the assistant behaves under this skill)</label>
                <textarea name="system_instructions" rows="6" required maxlength="8000" class="term-input font-mono">{{ old('system_instructions', $skill->system_instructions) }}</textarea>
            </div>
            <div>
                <label class="term-field-label">Trigger Keywords (comma or line separated)</label>
                <textarea name="trigger_keywords" rows="2" maxlength="2000" class="term-input font-mono">{{ old('trigger_keywords', is_array($skill->trigger_keywords) ? implode(', ', $skill->trigger_keywords) : $skill->trigger_keywords) }}</textarea>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="term-field-label">Priority * (lower matches first)</label>
                    <input type="number" name="priority" value="{{ old('priority', $skill->priority ?? 100) }}" required min="0" max="10000" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Status *</label>
                    <select name="status" class="term-input">
                        <option value="enabled" {{ old('status', $skill->status) === 'enabled' ? 'selected' : '' }}>Enabled</option>
                        <option value="disabled" {{ old('status', $skill->status) === 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                <div>
                    <label class="term-field-label">Category</label>
                    <input type="text" name="category" value="{{ old('category', $skill->category) }}" maxlength="100" class="term-input">
                </div>
            </div>
            <div>
                <label class="term-field-label">Allowed Roles (blank = everyone incl. guests)</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['guest','customer','employee','support_agent','support_manager','project_manager','finance_manager','admin','super_admin'] as $role)
                    <label class="term-field-label"><input type="checkbox" name="allowed_roles[]" value="{{ $role }}" {{ in_array($role, old('allowed_roles', $skill->allowed_roles ?? [])) ? 'checked' : '' }} class="rounded"> {{ $role }}</label>
                    @endforeach
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="term-field-label">Temperature override (blank = provider default)</label>
                    <input type="number" step="0.1" min="0" max="2" name="temperature" value="{{ old('temperature', $skill->temperature) }}" class="term-input">
                </div>
                <div>
                    <label class="term-field-label">Max tokens override</label>
                    <input type="number" min="64" max="8000" name="max_tokens" value="{{ old('max_tokens', $skill->max_tokens) }}" class="term-input">
                </div>
            </div>
            @if($skill->exists)<p class="text-xs text-gray-500">Version v{{ $skill->version }} — saving creates v{{ $skill->version + 1 }}.</p>@endif
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.ai.skills.index') }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                <button type="submit" class="term-btn term-btn-sm">Save Skill</button>
            </div>
        </form>
    </div>
</div>
@endsection
