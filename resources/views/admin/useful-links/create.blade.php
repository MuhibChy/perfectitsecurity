@extends('layouts.app')
@section('page-title', 'Add New Link')
@section('content')

    <x-page-header title="Add New Link" sys="CONTENT://RESOURCES" />
<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.useful-links.index') }}" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add New Link</h2>
    </div>

    <form action="{{ route('admin.useful-links.store') }}" method="POST" class="term-panel p-6 space-y-6">
        @csrf

        <div>
            <label class="term-field-label">Title *</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="term-input" placeholder="e.g. Kaspersky Cybermap">
            @error('title') <p class="term-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="term-field-label">URL *</label>
            <input type="url" name="url" value="{{ old('url') }}" required class="term-input" placeholder="https://example.com">
            @error('url') <p class="term-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="term-field-label">Description</label>
            <textarea name="description" rows="3" class="term-input" placeholder="Brief description of this link">{{ old('description') }}</textarea>
            @error('description') <p class="term-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="term-field-label">Category</label>
                <select name="category" class="term-input">
                    <option value="cybersecurity" {{ old('category') === 'cybersecurity' ? 'selected' : '' }}>Cybersecurity</option>
                    <option value="tools" {{ old('category') === 'tools' ? 'selected' : '' }}>Tools</option>
                    <option value="monitoring" {{ old('category') === 'monitoring' ? 'selected' : '' }}>Monitoring</option>
                    <option value="cloud" {{ old('category') === 'cloud' ? 'selected' : '' }}>Cloud</option>
                    <option value="learning" {{ old('category') === 'learning' ? 'selected' : '' }}>Learning</option>
                    <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General</option>
                </select>
            </div>
            <div>
                <label class="term-field-label">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="term-input">
            </div>
        </div>

        <div>
            <label class="term-field-label">Image URL (thumbnail)</label>
            <input type="url" name="image" value="{{ old('image') }}" class="term-input" placeholder="https://example.com/image.jpg (optional)">
            @error('image') <p class="term-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                <span class="text-sm text-gray-700 dark:text-gray-300">Featured</span>
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="term-btn term-btn-sm">
                Save Link
            </button>
            <a href="{{ route('admin.useful-links.index') }}" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-4 py-2.5 text-sm font-medium transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
