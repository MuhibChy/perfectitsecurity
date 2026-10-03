@extends('layouts.app')
@section('title', ($lesson->exists ? 'Edit' : 'New') . ' Lesson')
@section('page-title', ($lesson->exists ? 'Edit' : 'New') . ' Lesson')

@section('content')
<x-page-header sys="CONTENT://TRAINING" :title="($lesson->exists ? 'Edit Lesson' : 'New Lesson for ' . $course->title)" :breadcrumbs="['Training' => route('admin.training.dashboard'), $course->title => route('admin.training.courses.show', $course), 'Lesson' => null]" />

<form method="POST" action="{{ $lesson->exists ? route('admin.training.lessons.update', $lesson) : route('admin.training.lessons.store', $course) }}" class="card p-6 space-y-4 max-w-4xl">
    @csrf @if($lesson->exists) @method('PUT') @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="term-field-label">Module</label><select name="module_id" class="term-input">@foreach($course->modules as $m)<option value="{{ $m->id }}" @selected(old('module_id', $lesson->module_id) == $m->id)>{{ $m->title }}</option>@endforeach</select></div>
        <div><label class="term-field-label">Type</label><select name="lesson_type" class="term-input">@foreach(['guide'=>'Guide','procedure'=>'Step-by-step procedure','scenario'=>'Real-world scenario','simulation'=>'Interactive simulation','reference'=>'Reference'] as $v=>$l)<option value="{{ $v }}" @selected(old('lesson_type', $lesson->lesson_type ?? 'guide') === $v)>{{ $l }}</option>@endforeach</select></div>
    </div>
    <div><label class="term-field-label">Title</label><input name="title" class="term-input" value="{{ old('title', $lesson->title) }}" required></div>
    <div><label class="term-field-label">Body</label><textarea name="body" rows="8" class="term-input" required>{{ old('body', $lesson->body) }}</textarea></div>
    @foreach(['objectives'=>'Learning objectives (one per line)','steps'=>'Steps — prefix with STEP n (one per line)','why_matters'=>'Why this matters — What/Why/Who/When/Next (one per line)','common_mistakes'=>'Common mistakes (one per line)','discussion_questions'=>'Discussion questions (one per line)'] as $field=>$label)
    <div><label class="term-field-label">{{ $label }}</label><textarea name="{{ $field }}" rows="3" class="term-input">{{ old($field, is_array($lesson->$field ?? null) ? implode("\n", $lesson->$field) : '') }}</textarea></div>
    @endforeach
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="term-field-label">Duration (minutes)</label><input type="number" name="duration_minutes" class="term-input" value="{{ old('duration_minutes', $lesson->duration_minutes ?? 10) }}" required></div>
        <div><label class="term-field-label">Version</label><input name="version" class="term-input" value="{{ old('version', $lesson->version ?? '1.0') }}" required></div>
    </div>
    <label class="term-field-label"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $lesson->is_published)) class="rounded accent-green-600"> Published</label>
    <button class="term-btn">{{ $lesson->exists ? 'Save Lesson' : 'Create Lesson' }}</button>
</form>
@endsection
