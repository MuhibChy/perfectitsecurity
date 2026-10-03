@extends('layouts.app')
@section('page-title', 'Career Posts')
@section('content')

    <x-page-header title="Career Posts" sys="CONTENT://CONTENT" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Career Posts</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Publish open positions. Published posts appear on the public site.</p>
    <form method="POST" action="{{ route('admin.content.careers.store') }}" class="grid sm:grid-cols-2 gap-3 mb-6 p-4 bg-gray-50 dark:bg-gray-800/50">
        @csrf
        <div class="sm:col-span-2"><label class="term-field-label">Title *</label><input name="title" required class="term-input" value="{{ old('title') }}">@error('title')<p class="term-error">{{ $message }}</p>@enderror</div>
        <div><label class="term-field-label">Department</label><input name="department" class="term-input"></div>
        <div><label class="term-field-label">Location</label><input name="location" class="term-input"></div>
        <div><label class="term-field-label">Employment type</label><input name="employment_type" class="term-input" placeholder="full_time"></div>
        <div><label class="term-field-label">Summary</label><input name="summary" maxlength="500" class="term-input"></div>
        <div class="sm:col-span-2"><label class="term-field-label">Description</label><textarea name="description" rows="3" class="term-input"></textarea></div>
        <div class="sm:col-span-2"><label class="term-field-label">Requirements</label><textarea name="requirements" rows="3" class="term-input"></textarea></div>
        <div><label class="term-field-label"><input type="checkbox" name="is_published" value="1" class="rounded"> Publish immediately</label></div>
        <div><button class="term-btn term-btn-sm">Save Career Post</button></div>
    </form>
    <div class="overflow-x-auto term-table-wrap"><table class="data-table min-w-full text-sm term-table">
        <thead><tr class="text-left text-gray-500"><th class="py-2 pr-4">Title</th><th class="py-2 pr-4">Department</th><th class="py-2 pr-4">Status</th><th class="py-2">Actions</th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr class="border-t border-gray-100 dark:border-gray-800">
                <td class="py-2 pr-4 font-semibold" data-label="Title">{{ $item->title }}<div class="text-xs text-gray-500 font-normal">{{ $item->slug }}</div></td>
                <td class="py-2 pr-4" data-label="Department">{{ $item->department ?? '—' }}</td>
                <td class="py-2 pr-4" data-label="Status"><span class="term-tag">{{ $item->is_published ? 'Published' : 'Draft' }}</span></td>
                <td class="py-2" data-label="Actions"><form method="POST" action="{{ route('admin.content.careers.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">Delete</button></form></td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-8 text-center text-gray-500">No career posts yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
