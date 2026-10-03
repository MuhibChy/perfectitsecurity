@extends('layouts.app')
@section('page-title', 'New Problem')

@section('content')
<div class="space-y-6">
    <x-page-header title="New Problem" sys="OPS://PROBLEMS/NEW" />
    <div class="term-panel p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.problems.store') }}" class="space-y-4">
            @csrf
            <div><label class="block text-sm mb-1">Title</label><input type="text" name="title" value="{{ old('title') }}" required class="term-input w-full"></div>
            <div><label class="block text-sm mb-1">Description</label><textarea name="description" rows="4" required class="term-input w-full">{{ old('description') }}</textarea></div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Priority</label>
                    <select name="priority" class="term-input w-full">@foreach(['low','medium','high','urgent','critical'] as $p)<option value="{{ $p }}" {{ old('priority') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>@endforeach</select></div>
                <div><label class="block text-sm mb-1">Impact</label>
                    <select name="impact" class="term-input w-full">@foreach(['low','medium','high','critical'] as $i)<option value="{{ $i }}" {{ old('impact') == $i ? 'selected' : '' }}>{{ ucfirst($i) }}</option>@endforeach</select></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="block text-sm mb-1">Category (optional)</label><input type="text" name="category" value="{{ old('category') }}" class="term-input w-full"></div>
                <div><label class="block text-sm mb-1">Assign to (optional)</label>
                    <select name="assigned_to" class="term-input w-full"><option value="">Unassigned</option>@foreach($agents as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach</select></div>
            </div>
            <div><label class="block text-sm mb-1">Link incidents (optional)</label>
                <select name="ticket_ids[]" multiple class="term-input w-full" size="5">@foreach($tickets as $t)<option value="{{ $t->id }}">{{ $t->ticket_number }} — {{ $t->subject }}</option>@endforeach</select></div>
            <button type="submit" class="term-btn">Create Problem</button>
        </form>
    </div>
</div>
@endsection
