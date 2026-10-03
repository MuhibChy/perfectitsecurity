@extends('layouts.app')
@section('title', 'Training Reports')
@section('page-title', 'Reports')

@section('content')
<x-page-header sys="CONTENT://TRAINING" title="Training Reports" subtitle="Completion rate, average score, failures, overdue and popularity per course." :breadcrumbs="['Training' => route('admin.training.dashboard'), 'Reports' => null]" />

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Course</th><th>Assigned</th><th>Completed</th><th>Rate</th><th>Avg Score</th><th>Failed</th><th>Overdue</th></tr></thead>
    <tbody>
        @foreach($rows as $r)
        <tr>
            <td class="font-medium" data-label="Course">{{ $r['course']->title }}</td>
            <td data-label="Assigned">{{ $r['assigned'] }}</td>
            <td data-label="Completed">{{ $r['completed'] }}</td>
            <td data-label="Rate">{{ $r['assigned'] ? (int) round($r['completed'] * 100 / $r['assigned']) : 0 }}%</td>
            <td data-label="Avg Score">{{ $r['avg_score'] !== null ? $r['avg_score'].'%' : '—' }}</td>
            <td data-label="Failed">{{ $r['failed'] }}</td>
            <td data-label="Overdue">{{ $r['overdue'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
@endsection
