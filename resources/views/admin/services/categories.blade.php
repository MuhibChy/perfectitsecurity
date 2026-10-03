@extends('layouts.app')
@section('page-title', 'Service Categories')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <x-page-header title="Service Categories" subtitle="Service Categories" sys="OPS://SERVICES" />

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($categories as $category)
        <div class="term-panel p-5">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="w-2.5 h-2.5 flex-shrink-0" style="background-color: {{ $category->color ?? '#00E67A' }}" aria-hidden="true"></span>
                <h3 class="font-display font-bold text-slate-900 dark:text-white tracking-tight">{{ $category->name }}</h3>
            </div>
            @if($category->description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">{{ $category->description }}</p>
            @endif

            <!-- Stats -->
            <div class="flex items-center gap-4 text-xs text-gray-400 mb-4">
                <span class="flex items-center gap-1">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $category->services_count }}</span> services
                </span>
                <span class="flex items-center gap-1">
                    Sort: <span class="font-medium text-gray-900 dark:text-white">{{ $category->sort_order }}</span>
                </span>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.service-categories.edit', $category->id) }}"
                    class="flex-1 px-3 py-1.5 text-center text-xs font-medium text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.service-categories.destroy', $category->id) }}"
                    onsubmit="return confirm('Delete category &quot;{{ addslashes($category->name) }}&quot;?{{ $category->services_count > 0 ? ' It has services — cannot delete.' : '' }}')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors {{ $category->services_count > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $category->services_count > 0 ? 'disabled' : '' }}>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="sm:col-span-2 lg:col-span-3 term-panel p-12 text-center">
            <div class="text-4xl mb-3">📂</div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No categories yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Create your first service category to start organizing services.</p>
            <a href="{{ route('admin.service-categories.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                + Create Category
            </a>
        </div>
        @endforelse
    </div>

    <!-- Summary Table -->
    @if($categories->count() > 0)
    <div class="term-panel overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">All Categories</h3>
        </div>
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table w-full text-sm term-table">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Order</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Icon</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Slug</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Services</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($categories as $category)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ str_pad($category->sort_order, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-5 py-3 text-xl">{{ $category->icon ?? '' }}</td>
                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" style="background-color: {{ $category->color ?? '#6366f1' }}"></div>
                                {{ $category->name }}
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 font-mono">{{ $category->slug }}</td>
                        <td class="px-5 py-3">
                            <span class="term-tag">{{ $category->services_count }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if($category->is_active)
                                <span class="term-tag">Active</span>
                            @else
                                <span class="term-tag">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('admin.service-categories.edit', $category->id) }}" class="px-2 py-1 text-xs text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded">Edit</a>
                                <form method="POST" action="{{ route('admin.service-categories.destroy', $category->id) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded {{ $category->services_count > 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $category->services_count > 0 ? 'disabled' : '' }}>Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
