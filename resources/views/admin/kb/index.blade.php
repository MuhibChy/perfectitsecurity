@extends('layouts.app')
@section('page-title', 'Knowledge Base')
@section('content')

    <x-page-header title="Knowledge Base" sys="CONTENT://KNOWLEDGE-BASE" />
<div class="term-panel p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Knowledge Base Articles</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Visibility controls who can read each article: public, customer, employee, or admin.</p>
        </div>
        <a href="{{ route('admin.knowledge-base.create') }}" class="term-btn term-btn-sm">New Article</a>
    </div>
    <div class="overflow-x-auto term-table-wrap">
        <table class="min-w-full text-sm data-table term-table">
            <thead><tr><th>Title</th><th>Category</th><th>Visibility</th><th>Status</th><th>Views</th><th>Updated</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($articles as $article)
                <tr>
                    <td class="font-semibold" data-label="Title">{{ $article->title }}<div class="text-xs text-gray-500 font-normal">{{ $article->slug }}</div></td>
                    <td data-label="Category">{{ $article->category?->name ?? '—' }}</td>
                    <td data-label="Visibility"><span class="term-tag">{{ $article->visibility }}</span></td>
                    <td data-label="Status"><span class="term-tag">{{ $article->is_published ? 'Published' : 'Draft' }}</span></td>
                    <td data-label="Views">{{ number_format($article->views_count) }}</td>
                    <td class="text-gray-500" data-label="Updated">{{ $article->updated_at?->diffForHumans() }}</td>
                    <td class="whitespace-nowrap" data-label="Actions">
                        <a href="{{ route('admin.knowledge-base.edit', $article) }}" class="text-primary-600 text-xs mr-2">Edit</a>
                        <form method="POST" action="{{ route('admin.knowledge-base.destroy', $article) }}" class="inline" onsubmit="return confirm('Delete this article?')">@csrf @method('DELETE')<button class="text-red-500 text-xs">Delete</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-gray-500">No articles yet. Create the first one.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $articles->links() }}</div>
</div>
@endsection
