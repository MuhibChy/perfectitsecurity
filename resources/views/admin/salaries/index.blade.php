@extends('layouts.app')
@section('page-title', 'Payroll')
@section('content')

    <x-page-header title="Payroll" sys="FINANCE://SALARIES" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold mb-4">Payroll</h2>
    <a href="{{ route('admin.salaries.create') }}" class="term-btn term-btn-sm">New payroll record</a>
    <div class="term-table-wrap mt-2">
    <table class="data-table min-w-full text-sm term-table">
        <thead><tr><th class="text-left p-2">ID</th><th class="text-left p-2">Employee</th><th class="text-right p-2">Net</th><th class="text-left p-2">Period</th><th class="text-left p-2">Status</th></tr></thead>
        <tbody>@foreach($salaries as $s)<tr class="border-t"><td class="p-2" data-label="ID"><a class="underline" href="{{ route('admin.salaries.show', $s->id) }}">#{{ $s->id }}</a></td><td class="p-2" data-label="Employee">{{ $s->user->name }}</td><td class="p-2 text-right" data-label="Net">{{ $s->net_salary }}</td><td class="p-2" data-label="Period">{{ $s->period }}</td><td class="p-2" data-label="Status"><x-status-badge :status="$s->status" /></td></tr>@endforeach</tbody>
    </table>
    </div>
    <div class="mt-4">{{ $salaries->links() }}</div>
</div>
@endsection
