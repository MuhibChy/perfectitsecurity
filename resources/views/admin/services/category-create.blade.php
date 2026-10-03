@extends('layouts.app')
@section('page-title', 'Create Category')

@section('content')

    <x-page-header title="Create Category" sys="OPS://SERVICES" />
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('admin.service-categories.index') }}" class="text-blue-600 hover:underline text-sm">← Back to Categories</a>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">Create Service Category</h2>
    </div>

    <form method="POST" action="{{ route('admin.service-categories.store') }}" class="space-y-6">
        @csrf

        <div class="term-panel p-6 space-y-4">
            <div>
                <label class="term-field-label">Category Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    placeholder="e.g. Cybersecurity Services"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm @error('name') border-red-500 @enderror">
                @error('name') <p class="term-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="term-field-label">Description</label>
                <textarea name="description" rows="3" placeholder="Brief description of this category..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="term-field-label">Icon (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon') }}" maxlength="10"
                        placeholder="🛡"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm text-center text-2xl">
                </div>
                <div>
                    <label class="term-field-label">Color</label>
                    <div class="flex gap-2">
                        <input type="color" name="color" value="{{ old('color', '#6366f1') }}"
                            class="w-10 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                        <input type="text" name="color_hex" value="{{ old('color', '#6366f1') }}" readonly
                            class="flex-1 px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs font-mono">
                    </div>
                </div>
                <div>
                    <label class="term-field-label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600">
                <label class="term-field-label">Active (visible to customers)</label>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.service-categories.index') }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Create Category</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.querySelector('input[name="color"]');
    const hexInput = document.querySelector('input[name="color_hex"]');
    if (colorInput && hexInput) {
        colorInput.addEventListener('input', function() {
            hexInput.value = this.value;
        });
    }
});
</script>
@endsection
