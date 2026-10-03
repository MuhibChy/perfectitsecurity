@extends('layouts.app')
@section('page-title', 'Change ' . $change->change_number)

@section('content')
<div class="space-y-6">
    <x-page-header title="Change {{ $change->change_number }}" sys="OPS://CHANGES/SHOW" />
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="term-panel p-6">
                <h2 class="text-lg font-semibold mb-2">{{ $change->title }}</h2>
                <div class="flex flex-wrap gap-2 mb-4"><x-status-badge :status="$change->type" /><x-status-badge :status="$change->risk" /><x-status-badge :status="$change->status" /></div>
                <p class="whitespace-pre-line">{{ $change->description }}</p>
                @if($change->implementation_plan)<h3 class="font-semibold mt-4">Implementation plan</h3><p class="whitespace-pre-line">{{ $change->implementation_plan }}</p>@endif
                @if($change->rollback_plan)<h3 class="font-semibold mt-4">Rollback plan</h3><p class="whitespace-pre-line">{{ $change->rollback_plan }}</p>@endif
                @if($change->implementation_result)<h3 class="font-semibold mt-4">Result</h3><p class="whitespace-pre-line">{{ $change->implementation_result }}</p>@endif
                @if($change->failure_notes)<h3 class="font-semibold mt-4">Lessons learned</h3><p class="whitespace-pre-line">{{ $change->failure_notes }}</p>@endif
                <p class="text-sm mt-4">Window: {{ $change->scheduled_start?->format('Y-m-d H:i') ?? '-' }} → {{ $change->scheduled_end?->format('Y-m-d H:i') ?? '-' }}</p>
            </div>
            <div class="term-panel p-6">
                <h3 class="font-semibold mb-3">Approvals</h3>
                <div class="overflow-x-auto"><table class="data-table term-table">
                    <thead><tr><th>Approver</th><th>Decision</th><th>Comments</th><th>Decided</th></tr></thead>
                    <tbody>@forelse($change->approvals as $a)<tr><td>{{ $a->approver->name ?? '-' }}</td><td><x-status-badge :status="$a->decision" /></td><td>{{ $a->comments }}</td><td>{{ $a->decided_at?->format('Y-m-d H:i') }}</td></tr>
                    @empty<tr><td colspan="4" class="text-center py-4">No approvals yet.</td></tr>@endforelse</tbody>
                </table></div>
                <form method="POST" action="{{ route('admin.changes.approve', $change) }}" class="flex flex-wrap gap-3 mt-4">@csrf
                    <select name="decision" class="term-input"><option value="approved">Approve</option><option value="rejected">Reject</option></select>
                    <input type="text" name="comments" placeholder="Comments (optional)" class="term-input flex-1">
                    <button class="term-btn term-btn-sm">Decide</button>
                </form>
            </div>
        </div>
        <div class="space-y-6">
            <div class="term-panel p-6">
                <h3 class="font-semibold mb-3">Lifecycle</h3>
                <form method="POST" action="{{ route('admin.changes.transition', $change) }}" class="space-y-3">@csrf
                    <select name="to" class="term-input w-full">@foreach(['requested','assessed','approved','scheduled','implementing','completed','failed','cancelled'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select>
                    <textarea name="implementation_result" rows="2" placeholder="Implementation result (for completion)" class="term-input w-full"></textarea>
                    <textarea name="failure_notes" rows="2" placeholder="Failure notes / lessons (if failed)" class="term-input w-full"></textarea>
                    <button class="term-btn term-btn-sm w-full">Transition</button>
                </form>
                <p class="text-xs mt-3 opacity-70">High-risk changes require an approval before scheduling.</p>
            </div>
            <div class="term-panel p-6">
                <h3 class="font-semibold mb-3">Assignment</h3>
                <form method="POST" action="{{ route('admin.changes.update', $change) }}" class="space-y-3">@csrf @method('PATCH')
                    <select name="assigned_to" class="term-input w-full"><option value="">Unassigned</option>@foreach($agents as $a)<option value="{{ $a->id }}" {{ $change->assigned_to == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>@endforeach</select>
                    <button class="term-btn term-btn-sm w-full">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
