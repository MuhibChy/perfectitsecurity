@extends('layouts.app')
@section('page-title', 'AI Skills')
@section('content')
<div class="space-y-6">
    <x-page-header title="AI Skills" sys="SYSTEM://AI" />
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Behavior definitions the assistant matches before searching knowledge or the model.</p>
        <a href="{{ route('admin.ai.skills.create') }}" class="term-btn term-btn-sm">New Skill</a>
    </div>
    <div class="term-panel overflow-hidden">
        <div class="overflow-x-auto term-table-wrap">
            <table class="data-table term-table">
                <thead><tr><th>Name</th><th>Category</th><th>Priority</th><th>Status</th><th>Version</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($skills as $skill)
                    <tr>
                        <td class="font-medium" data-label="Name">{{ $skill->name }}<div class="text-xs text-gray-500 font-mono">{{ $skill->slug }}</div></td>
                        <td data-label="Category">{{ $skill->category ?? '—' }}</td>
                        <td data-label="Priority">{{ $skill->priority }}</td>
                        <td data-label="Status"><span class="term-tag {{ $skill->status === 'enabled' ? '' : '' }}">{{ ucfirst($skill->status) }}</span></td>
                        <td data-label="Version">v{{ $skill->version }}</td>
                        <td data-label="Actions">
                            <div class="inline-flex gap-1 flex-wrap">
                                <a href="{{ route('admin.ai.skills.edit', $skill) }}" class="term-btn term-btn-ghost term-btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.ai.skills.toggle', $skill) }}" class="inline">@csrf<button class="term-btn term-btn-ghost term-btn-sm">{{ $skill->status === 'enabled' ? 'Disable' : 'Enable' }}</button></form>
                                <form method="POST" action="{{ route('admin.ai.skills.duplicate', $skill) }}" class="inline">@csrf<button class="term-btn term-btn-ghost term-btn-sm">Duplicate</button></form>
                                <form method="POST" action="{{ route('admin.ai.skills.destroy', $skill) }}" class="inline" onsubmit="return confirm('Delete this skill?')">@csrf @method('DELETE')<button class="term-btn term-btn-ghost term-btn-sm">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-gray-500 py-8">No skills defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">{{ $skills->links() }}</div>
    </div>
</div>
@endsection
