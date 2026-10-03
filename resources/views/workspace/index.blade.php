@extends('layouts.app')
@section('page-title', 'My Workspace')
@section('content')
<div class="space-y-6">
<x-page-header :title="'My Workspace — ' . $user->name" :subtitle="$user->member_number . ' · ' . $user->roleDisplayName()" sys="WORKSPACE://OPS" num="18">
    <x-slot:actions>
        <a href="{{ route('workspace.work.report', ['format' => 'pdf']) }}" class="term-btn term-btn-sm">Work Report</a>
        <a href="{{ route('workspace.commissions.report', ['format' => 'pdf']) }}" class="term-btn term-btn-ghost term-btn-sm">Commission Report</a>
    </x-slot:actions>
</x-page-header>
<div class="term-panel p-6 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-1">
        <div class="term-panel-2 p-3"><p class="term-field-label !mb-1">Commission paid</p><p class="text-sm font-semibold tabular-nums"><span class="fin-tag fin-tag-income">{{ number_format($earnings['paid'] ?? 0, 2) }}</span></p></div>
        <div class="term-panel-2 p-3"><p class="term-field-label !mb-1">Commission pending</p><p class="text-sm font-semibold tabular-nums"><span class="fin-tag fin-tag-expense">{{ number_format(($earnings['pending'] ?? 0) + ($earnings['submitted'] ?? 0) + ($earnings['under_review'] ?? 0) + ($earnings['approved'] ?? 0) + ($earnings['payable'] ?? 0), 2) }}</span></p></div>
        <div class="term-panel-2 p-3"><p class="term-field-label !mb-1">Salary paid</p><p class="text-sm font-semibold tabular-nums"><span class="fin-tag fin-tag-profit">{{ number_format($salaryPaid, 2) }}</span></p></div>
        <div class="term-panel-2 p-3"><p class="term-field-label !mb-1">Tasks</p><p class="text-sm font-semibold tabular-nums">{{ $tasks->total() }}</p></div>
    </div>
    <div class="flex flex-wrap gap-2 mt-4">
        <a href="{{ route('workspace.commissions') }}" class="term-btn term-btn-ghost term-btn-sm">My commissions</a>
        <a href="{{ route('idcard.show') }}" class="term-btn term-btn-ghost term-btn-sm">My digital ID</a>
        <a href="{{ route('security.dashboard') }}" class="term-btn term-btn-ghost term-btn-sm">Security</a>
    </div>
</div>
<div class="term-panel p-6">
    <h3 class="font-bold mb-2 text-slate-900 dark:text-white">Assigned tasks</h3>
    @forelse($tasks as $t)
        <div class="text-sm border-t border-white/5 py-2 flex flex-wrap gap-x-3 text-slate-600 dark:text-term-800">
            <a href="{{ route('workspace.tasks.show', $t->id) }}" class="font-medium text-accent-soft hover:underline">{{ $t->title ?? ('Task #' . $t->id) }}</a>
            <x-status-badge :status="$t->status" /><span class="font-mono">{{ (int) ($t->progress ?? 0) }}%</span>
        </div>
    @empty<x-empty-state-3d type="tasks" title="No tasks assigned" message="New assignments from your team will appear here with full detail." />@endforelse
    <div class="mt-3">{{ $tasks->links() }}</div>
</div>
</div>
@endsection
