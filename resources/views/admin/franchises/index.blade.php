@extends('layouts.app')
@section('page-title', 'Franchises')
@section('content')

    <x-page-header title="Franchises" sys="SYSTEM://FRANCHISES" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold mb-4">Franchises</h2>
    <a href="{{ route('admin.franchises.create') }}" class="text-sm text-blue-600 underline">New franchise</a>
    <table class="data-table min-w-full text-sm mt-2 term-table">
        <thead><tr><th class="text-left p-2">Code</th><th class="text-left p-2">Name</th><th class="text-left p-2">Owner</th><th class="text-left p-2">Territory</th><th class="text-left p-2">Status</th></tr></thead>
        <tbody>@foreach($franchises as $f)<tr class="border-t"><td class="p-2" data-label="Code"><a class="underline" href="{{ route('admin.franchises.show', $f->id) }}">{{ $f->franchise_code }}</a></td><td class="p-2" data-label="Name">{{ $f->name }}</td><td class="p-2" data-label="Owner">{{ $f->owner->name }}</td><td class="p-2" data-label="Territory">{{ $f->territory ?? '—' }}</td><td class="p-2" data-label="Status">{{ $f->status }}</td></tr>@endforeach</tbody>
    </table>
    <div class="mt-4">{{ $franchises->links() }}</div>
</div>
@endsection
