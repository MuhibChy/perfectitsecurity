@extends('layouts.app')
@section('page-title', 'New Article')
@section('content')

    <x-page-header title="New Article" sys="CONTENT://KNOWLEDGE-BASE" />
<div class="term-panel p-6 max-w-3xl">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">New Knowledge Base Article</h2>
    <form method="POST" action="{{ route('admin.knowledge-base.store') }}" class="space-y-4">
        @csrf
        <div><label class="term-field-label">Title *</label><input name="title" required class="term-input" value="{{ old('title') }}">@error('title')<p class="term-error">{{ $message }}</p>@enderror</div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="term-field-label">Category *</label><select name="category_id" required class="term-input"><option value="">—</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>@endforeach</select>@error('category_id')<p class="term-error">{{ $message }}</p>@enderror</div>
            <div><label class="term-field-label">Visibility *</label><select name="visibility" required class="term-input">@foreach(['public','customer','employee','admin'] as $v)<option value="{{ $v }}" @selected(old('visibility', 'public') === $v)>{{ ucfirst($v) }}</option>@endforeach</select><p class="text-xs text-gray-500 mt-1">Public = everyone; customer = logged-in customers; employee = staff; admin = admins only.</p></div>
            <div><label class="term-field-label">Language</label><input name="language" maxlength="5" class="term-input" value="{{ old('language', 'en') }}"></div>
            <div><label class="term-field-label">Difficulty</label><input name="difficulty" maxlength="50" class="term-input" value="{{ old('difficulty') }}" placeholder="beginner / intermediate / advanced"></div>
        </div>
        <div><label class="term-field-label">Excerpt</label><input name="excerpt" maxlength="500" class="term-input" value="{{ old('excerpt') }}"></div>
        <div><label class="term-field-label">Content *</label><textarea name="content" rows="10" required class="term-input">{{ old('content') }}</textarea>@error('content')<p class="term-error">{{ $message }}</p>@enderror</div>
        <div>
            <label class="term-field-label">Tags</label>
            <div class="flex flex-wrap gap-2">@foreach($tags as $t)<label class="term-field-label"><input type="checkbox" name="tags[]" value="{{ $t->id }}" class="rounded"> {{ $t->name }}</label>@endforeach</div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="term-field-label">Meta title</label><input name="meta_title" maxlength="255" class="term-input" value="{{ old('meta_title') }}"></div>
            <div><label class="term-field-label">Meta description</label><input name="meta_description" maxlength="500" class="term-input" value="{{ old('meta_description') }}"></div>
        </div>
        <div class="flex items-center gap-6 text-sm">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked(old('is_published')) class="rounded"> Publish immediately</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured')) class="rounded"> Featured</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="ai_readable" value="1" @checked(old('ai_readable', true)) class="rounded"> AI-readable</label>
        </div>
        <button class="term-btn">Create Article</button>
    </form>
</div>
@endsection
