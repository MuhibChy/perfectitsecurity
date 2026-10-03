@extends('layouts.app')
@section('title', $course->title)
@section('page-title', $course->title)

@section('content')
<x-page-header sys="CONTENT://TRAINING" :title="$course->title" :subtitle="'v'.$course->version . ' · ' . ($course->is_published ? 'Published' : 'Draft')" :breadcrumbs="['Training' => route('admin.training.dashboard'), 'Courses' => route('admin.training.courses'), $course->title => null]">
    <a href="{{ route('admin.training.courses.edit', $course) }}" class="term-btn term-btn-ghost term-btn-sm">Edit Course</a>
    <a href="{{ route('admin.training.lessons.create', $course) }}" class="term-btn term-btn-sm">New Lesson</a>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <h2 class="heading-sm mb-3">Modules & Lessons</h2>
        @foreach($course->modules as $module)
        <div class="card p-4 mb-3">
            <div class="flex items-center justify-between mb-2"><strong class="text-sm">{{ $module->title }}</strong>
                <form method="POST" action="{{ route('admin.training.modules.destroy', $module) }}" onsubmit="return confirm('Delete module and its lessons?')">@csrf @method('DELETE')<button class="text-xs text-red-600">Delete</button></form>
            </div>
            @foreach($module->lessons as $lesson)
            <div class="flex items-center justify-between text-sm py-1.5 border-t border-slate-100 dark:border-white/5"><span>{{ $lesson->title }} <span class="text-xs text-slate-500">({{ $lesson->is_published ? 'live' : 'draft' }})</span></span><a href="{{ route('admin.training.lessons.edit', $lesson) }}" class="link-arrow text-xs">Edit →</a></div>
            @endforeach
        </div>
        @endforeach
        <form method="POST" action="{{ route('admin.training.modules.store', $course) }}" class="card p-4 flex gap-2">
            @csrf<input name="title" class="term-input" placeholder="New module title…" required><button class="term-btn term-btn-ghost term-btn-sm whitespace-nowrap">Add Module</button>
        </form>
        <a href="{{ route('admin.training.lessons.create', $course) }}" class="term-btn term-btn-ghost term-btn-sm mt-3">+ New Lesson</a>
    </div>

    <div>
        <h2 class="heading-sm mb-3">Quizzes & Questions</h2>
        @foreach($course->quizzes as $quiz)
        <div class="card p-4 mb-3">
            <strong class="text-sm">{{ $quiz->title }}</strong><span class="text-xs text-slate-500"> · pass {{ $quiz->pass_score }}% · {{ $quiz->questions->count() }} questions</span>
            <ul class="text-xs text-slate-600 dark:text-slate-300 mt-1 space-y-0.5">@foreach($quiz->questions as $q)<li class="flex justify-between gap-2"><span>{{ Str::limit($q->prompt, 70) }} ({{ $q->type }})</span><form method="POST" action="{{ route('admin.training.questions.destroy', $q) }}">@csrf @method('DELETE')<button class="text-red-600">✕</button></form></li>@endforeach</ul>
            <form method="POST" action="{{ route('admin.training.questions.store', $quiz) }}" class="mt-2 space-y-2 border-t border-slate-100 dark:border-white/5 pt-2">
                @csrf
                <div class="grid grid-cols-2 gap-2"><select name="type" class="term-input"><option value="single">Single choice</option><option value="multiple">Multiple correct</option><option value="boolean">True/False</option><option value="ordering">Ordering</option></select><input name="points" type="number" min="1" max="10" value="1" class="term-input"></div>
                <input name="prompt" class="term-input" placeholder="Question prompt…" required>
                <textarea name="options" rows="2" class="term-input" placeholder="Options, one per line" required></textarea>
                <input name="correct" class="term-input" placeholder="Correct: option index(es), e.g. 0 or 0,2" required>
                <input name="explanation" class="term-input" placeholder="Explanation (shown after attempt)">
                <button class="term-btn term-btn-ghost term-btn-sm">Add Question</button>
            </form>
        </div>
        @endforeach
        <form method="POST" action="{{ route('admin.training.quizzes.store', $course) }}" class="card p-4 space-y-2">
            @csrf<h3 class="font-semibold text-sm">New Quiz</h3>
            <input name="title" class="term-input" placeholder="Quiz title…" required>
            <div class="grid grid-cols-2 gap-2"><input name="pass_score" type="number" min="1" max="100" value="70" class="term-input"><input name="max_attempts" type="number" min="1" max="20" value="3" class="term-input"></div>
            <label class="term-field-label"><input type="checkbox" name="is_published" value="1" checked class="rounded accent-green-600"> Published</label>
            <button class="term-btn term-btn-ghost term-btn-sm">Create Quiz</button>
        </form>

        <h2 class="heading-sm mt-6 mb-3">Practical Assessments</h2>
        @foreach($course->practicals as $p)<div class="card p-3 mb-2 text-sm"><strong>{{ $p->title }}</strong><span class="text-xs text-slate-500"> ({{ $p->is_published ? 'live' : 'draft' }})</span></div>@endforeach
        <form method="POST" action="{{ route('admin.training.practicals.store', $course) }}" class="card p-4 space-y-2">
            @csrf<input name="title" class="term-input" placeholder="Practical title…" required>
            <textarea name="instructions" rows="2" class="term-input" placeholder="Instructions…" required></textarea>
            <textarea name="checklist" rows="2" class="term-input" placeholder="Checklist items, one per line"></textarea>
            <label class="term-field-label"><input type="checkbox" name="is_published" value="1" checked class="rounded accent-green-600"> Published</label>
            <button class="term-btn term-btn-ghost term-btn-sm">Create Practical</button>
        </form>

        <h2 class="heading-sm mt-6 mb-3">Assign Training</h2>
        <form method="POST" action="{{ route('admin.training.assign', $course) }}" class="card p-4 space-y-2">
            @csrf
            <label class="term-field-label">Employee IDs (comma-separated) and/or role</label>
            <input name="user_ids" class="term-input" placeholder="e.g. 5, 9 (parsed below)" id="assign-ids">
            <input type="hidden" name="user_ids_arr" id="assign-ids-arr">
            <div class="grid grid-cols-2 gap-2"><input name="role" class="term-input" placeholder="Role, e.g. support_agent"><input name="due_at" type="date" class="term-input"></div>
            <button class="term-btn term-btn-sm">Assign Course</button>
            <p class="term-hint">Enter employee IDs like “5,9”, a role, or both.</p>
        </form>

        <h2 class="heading-sm mt-6 mb-3">Assigned Employees</h2>
        <div class="card p-0 overflow-hidden"><table class="data-table term-table"><thead><tr><th>Employee</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach($course->assignments as $a)
            <tr><td data-label="Employee">{{ $a->user->name }}</td><td data-label="Status"><span class="term-tag">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span></td>
            <td class="whitespace-nowrap" data-label=""><a href="{{ route('admin.training.employees.show', $a->user) }}" class="link-arrow text-xs">Progress →</a>
            <form method="POST" action="{{ route('admin.training.assignments.reset', $a) }}" class="inline" onsubmit="return confirm('Reset this employee?')">@csrf<button class="text-xs text-red-600 ml-2">Reset</button></form></td></tr>
            @endforeach
        </tbody></table></div>
    </div>
</div>
@endsection
