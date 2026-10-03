@extends('layouts.app')
@section('title', 'Training Courses')
@section('page-title', 'Courses')

@section('content')
<x-page-header sys="CONTENT://TRAINING" title="Training Courses" subtitle="Create, version and publish Academy courses." :breadcrumbs="['Training' => route('admin.training.dashboard'), 'Courses' => null]">
    <a href="{{ route('admin.training.courses.create') }}" class="term-btn term-btn-sm">New Course</a>
</x-page-header>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Course</th><th>Role</th><th>Modules</th><th>Assigned</th><th>Published</th><th>Version</th><th></th></tr></thead>
    <tbody>
        @foreach($courses as $course)
        <tr>
            <td class="font-medium" data-label="Course">{{ $course->title }}</td>
            <td data-label="Role">{{ $course->role_target ?? 'All staff' }}</td>
            <td data-label="Modules">{{ $course->modules_count }}</td>
            <td data-label="Assigned">{{ $course->assignments_count }}</td>
            <td data-label="Published"><span class="term-tag {{ $course->is_published ? '' : '' }}">{{ $course->is_published ? 'Published' : 'Draft' }}</span></td>
            <td data-label="Version">v{{ $course->version }}</td>
            <td class="whitespace-nowrap" data-label=""><a href="{{ route('admin.training.courses.show', $course) }}" class="link-arrow text-sm">Manage →</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
<div class="mt-4">{{ $courses->links() }}</div>
@endsection
