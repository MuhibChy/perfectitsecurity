@extends('layouts.app')
@section('page-title', 'Edit Article')
@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="glass-card p-6 lg:col-span-2">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Edit: {{ $article->title }}</h2>
        <form method="POST" action="{{ route('admin.knowledge-base.update', $article) }}" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="form-label">Title *</label><input name="title" required class="form-input w-full" value="{{ old('title', $article->title) }}">@error('title')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div><label class="form-label">Category *</label><select name="category_id" required class="form-input w-full">@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $article->category_id) == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
                <div><label class="form-label">Visibility *</label><select name="visibility" required class="form-input w-full">@foreach(['public','customer','employee','admin'] as $v)<option value="{{ $v }}" @selected(old('visibility', $article->visibility) === $v)>{{ ucfirst($v) }}</option>@endforeach</select></div>
                <div><label class="form-label">Language</label><input name="language" maxlength="5" class="form-input w-full" value="{{ old('language', $article->language) }}"></div>
                <div><label class="form-label">Difficulty</label><input name="difficulty" maxlength="50" class="form-input w-full" value="{{ old('difficulty', $article->difficulty) }}"></div>
            </div>
            <div><label class="form-label">Excerpt</label><input name="excerpt" maxlength="500" class="form-input w-full" value="{{ old('excerpt', $article->excerpt) }}"></div>
            <div><label class="form-label">Content *</label><textarea name="content" rows="10" required class="form-input w-full">{{ old('content', $article->content) }}</textarea></div>
            <div>
                <label class="form-label">Tags</label>
                <div class="flex flex-wrap gap-2">@foreach($tags as $t)<label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="tags[]" value="{{ $t->id }}" @checked($article->tags->contains($t->id)) class="rounded"> {{ $t->name }}</label>@endforeach</div>
            </div>
            <div class="flex items-center gap-6 text-sm">
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $article->is_published)) class="rounded"> Published</label>
                <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $article->is_featured)) class="rounded"> Featured</label>
            </div>
            <button class="btn-primary">Save Changes (creates version)</button>
        </form>
    </div>
    <div class="glass-card p-6">
        <h3 class="font-bold mb-3">Version history ({{ $article->versions->count() }})</h3>
        <div class="space-y-2 text-sm">
            @forelse($article->versions->sortByDesc('version_number') as $v)
                <div class="border-t border-gray-100 dark:border-gray-800 pt-2"><span class="font-semibold">v{{ $v->version_number }}</span> <span class="text-gray-500">{{ $v->created_at }} {{ $v->edit_summary ? '· ' . $v->edit_summary : '' }}</span></div>
            @empty
                <p class="text-gray-500">No versions yet — saving creates v1.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
