@extends('layouts.app')
@section('page-title', 'New franchise')
@section('content')

    <x-page-header title="New franchise" sys="SYSTEM://FRANCHISES" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold mb-4">New franchise</h2>
    <form method="POST" action="{{ route('admin.franchises.store') }}">@csrf
        <label class="term-field-label">Name</label><input name="name" required class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="255" />
        <label class="term-field-label">Legal name</label><input name="legal_name" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="255" />
        <label class="term-field-label">Owner</label><select name="owner_id" class="block rounded border px-2 py-1 mb-2 dark:bg-gray-800">@foreach($owners as $o)<option value="{{ $o->id }}">{{ $o->name }} ({{ $o->role }})</option>@endforeach</select>
        <label class="term-field-label">Territory</label><input name="territory" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="255" />
        <button class="term-btn term-btn-sm mt-2">Create franchise</button>
    </form>
</div>
@endsection
