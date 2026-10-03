@extends('layouts.app')
@section('title', ($course->exists ? 'Edit' : 'New') . ' Course')
@section('page-title', ($course->exists ? 'Edit' : 'New') . ' Course')

@section('content')
<x-page-header sys="CONTENT://TRAINING" :title="($course->exists ? 'Edit: ' : 'New Course')" :breadcrumbs="['Training' => route('admin.training.dashboard'), 'Courses' => route('admin.training.courses'), 'Form' => null]" />

<form method="POST" action="{{ $course->exists ? route('admin.training.courses.update', $course) : route('admin.training.courses.store') }}" class="card p-6 space-y-4 max-w-3xl">
    @csrf @if($course->exists) @method('PUT') @endif
    <div><label class="term-field-label">Title</label><input name="title" class="term-input" value="{{ old('title', $course->title) }}" required></div>
    <div><label class="term-field-label">Description</label><textarea name="description" rows="3" class="term-input">{{ old('description', $course->description) }}</textarea></div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="term-field-label">Role target (blank = all staff)</label><input name="role_target" class="term-input" value="{{ old('role_target', $course->role_target) }}" placeholder="sales_agent"></div>
        <div><label class="term-field-label">Difficulty</label><select name="difficulty" class="term-input">@foreach(['beginner','intermediate','advanced'] as $d)<option value="{{ $d }}" @selected(old('difficulty', $course->difficulty ?? 'beginner') === $d)>{{ ucfirst($d) }}</option>@endforeach</select></div>
        <div><label class="term-field-label">Duration (minutes)</label><input type="number" name="duration_minutes" class="term-input" value="{{ old('duration_minutes', $course->duration_minutes ?? 30) }}" required></div>
        <div><label class="term-field-label">Version</label><input name="version" class="term-input" value="{{ old('version', $course->version ?? '1.0') }}" required></div>
    </div>
    <label class="term-field-label"><input type="checkbox" name="is_mandatory" value="1" @checked(old('is_mandatory', $course->is_mandatory)) class="rounded accent-green-600"> Mandatory</label>
    <label class="term-field-label"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $course->is_published)) class="rounded accent-green-600"> Published</label>
    <button class="term-btn">{{ $course->exists ? 'Save Changes' : 'Create Course' }}</button>
</form>
@endsection
