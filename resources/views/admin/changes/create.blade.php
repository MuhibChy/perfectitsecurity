@extends('layouts.app')
@section('page-title', 'New Change')

@section('content')
<div class="space-y-6">
    <x-page-header title="New Change Request" sys="OPS://CHANGES/NEW" />
    <div class="term-panel p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.changes.store') }}" class="space-y-4">@csrf
            <div><label class="block text-sm mb-1">Title</label><input type="text" name="title" value="{{ old('title') }}" required class="term-input w-full"></div>
            <div><label class="block text-sm mb-1">Description</label><textarea name="description" rows="4" required class="term-input w-full">{{ old('description') }}</textarea></div>
            <div class="grid md:grid-cols-3 gap-4">
                <div><label class="block text-sm mb-1">Type</label><select name="type" class="term-input w-full">@foreach(['standard','normal','emergency'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">Risk</label><select name="risk" class="term-input w-full">@foreach(['low','medium','high'] as $r)<option value="{{ $r }}">{{ ucfirst($r) }}</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">Impact</label><select name="impact" class="term-input w-full">@foreach(['low','medium','high','critical'] as $i)<option value="{{ $i }}">{{ ucfirst($i) }}</option>@endforeach</select></div>
            </div>
            <div><label class="block text-sm mb-1">Implementation plan</label><textarea name="implementation_plan" rows="3" class="term-input w-full">{{ old('implementation_plan') }}</textarea></div>
            <div><label class="block text-sm mb-1">Rollback plan</label><textarea name="rollback_plan" rows="3" class="term-input w-full">{{ old('rollback_plan') }}</textarea></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Maintenance window start</label><input type="datetime-local" name="scheduled_start" class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Maintenance window end</label><input type="datetime-local" name="scheduled_end" class="term-input w-full"></div>
            </div>
            <div><label class="block text-sm mb-1">Assign to (optional)</label><select name="assigned_to" class="term-input w-full"><option value="">Unassigned</option>@foreach($agents as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
            <button type="submit" class="term-btn">Record Change</button>
        </form>
    </div>
</div>
@endsection
