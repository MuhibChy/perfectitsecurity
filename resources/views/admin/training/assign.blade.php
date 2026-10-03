@extends('layouts.app')
@section('title', 'Assign Training')
@section('page-title', 'Assign')

@section('content')
<x-page-header sys="CONTENT://TRAINING" title="Assign Training" subtitle="Assign published courses to staff by person or by entire role." :breadcrumbs="['Training' => route('admin.training.dashboard'), 'Assign' => null]" />

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Employee</th><th>Role</th><th>Assign course</th></tr></thead>
    <tbody>
        @foreach($staff as $employee)
        <tr>
            <td class="font-medium" data-label="Employee">{{ $employee->name }}<div class="text-xs text-slate-500">{{ $employee->email }}</div></td>
            <td data-label="Role">{{ ucfirst(str_replace('_',' ',$employee->role ?? '')) }}</td>
            <td data-label="Assign course">
                <form method="POST" action="#" onsubmit="return assignCourse(event, {{ $employee->id }})" class="flex gap-2">
                    <select id="course-{{ $employee->id }}" class="term-input">@foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->title }}</option>@endforeach</select>
                    <button class="term-btn term-btn-ghost term-btn-sm whitespace-nowrap">Assign</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>

<script>
const courseAssignUrls = { @foreach($courses as $c)"{{ $c->id }}": "{{ route('admin.training.assign', $c) }}",@endforeach };
function assignCourse(e, userId) {
    e.preventDefault();
    const courseId = document.getElementById('course-' + userId).value;
    const form = document.createElement('form');
    form.method = 'POST'; form.action = courseAssignUrls[courseId];
    const token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}';
    const uid = document.createElement('input'); uid.type = 'hidden'; uid.name = 'user_ids[]'; uid.value = userId;
    form.appendChild(token); form.appendChild(uid); document.body.appendChild(form); form.submit();
    return false;
}
</script>
@endsection
