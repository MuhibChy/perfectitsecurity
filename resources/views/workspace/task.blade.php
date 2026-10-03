@extends('layouts.app')
@section('page-title', 'Task')
@section('content')
<div class="space-y-6 max-w-3xl">
<x-page-header :title="$task->title ?? ('Task #' . $task->id)" :subtitle="($task->task_number ?? '') . ' · ' . $task->status . ' · ' . (int) ($task->progress ?? 0) . '%'" sys="WORKSPACE://OPS" :breadcrumbs="['Workspace' => route('workspace.index'), 'Task' => null]" />
<div class="term-panel p-6 mb-4">
    <div class="text-sm text-slate-600 dark:text-term-800 mt-1 space-y-1">
        <p>Project: {{ $task->project->name ?? '—' }}</p>
        <p>Customer: {{ $task->project?->customer?->name ?? $task->customer?->name ?? $task->serviceOrder?->customer?->name ?? '—' }}</p>
        <p>Priority: <x-status-badge :status="$task->priority ?? 'medium'" /></p>
        <p>Status: <x-status-badge :status="$task->status" /></p>
    </div>
    @if($task->description)
        <div class="mt-3 term-panel-2 p-3 text-sm text-slate-600 dark:text-term-800">{{ $task->description }}</div>
    @endif
    <a href="{{ route('workspace.index') }}" class="term-btn term-btn-ghost term-btn-sm inline-flex mt-4">Back to workspace</a>
</div>
</div>
@endsection
