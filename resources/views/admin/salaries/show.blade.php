@extends('layouts.app')
@section('page-title', 'Salary detail')
@section('content')

    <x-page-header title="Salary detail" sys="FINANCE://SALARIES" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold">Salary #{{ $salary->id }} · {{ $salary->user->name }} · {{ $salary->status }}</h2>
    <p class="text-sm">Base {{ $salary->base_salary }} + bonus {{ $salary->bonus }} − deductions {{ $salary->deductions }} = <strong>net {{ $salary->net_salary }}</strong> · Period {{ $salary->period }}</p>
    @if($salary->status === 'pending')
    <form method="POST" action="{{ route('admin.salaries.approve', $salary->id) }}" class="mt-2">@csrf<button class="px-3 py-1 rounded bg-green-600 text-white text-sm">Approve</button></form>
    @endif
    @if($salary->status === 'approved')
    <form method="POST" action="{{ route('admin.salaries.pay', $salary->id) }}" class="mt-2 flex gap-2 text-sm">@csrf
        <input name="currency" value="GBP" maxlength="3" class="rounded border px-2 py-1 w-20 dark:bg-gray-800" />
        <input name="destination" placeholder="masked dest. ref" maxlength="100" class="rounded border px-2 py-1 dark:bg-gray-800" />
        <button class="px-3 py-1 rounded bg-blue-600 text-white">Initiate sandbox transfer</button>
    </form>
    @endif
    <h3 class="font-bold mt-4">Linked transfers</h3>
    @forelse($transfers as $t)<div class="text-sm border-t py-1">{{ $t->reference }} · {{ $t->status }} · ext {{ $t->external_reference ?? '—' }}</div>@empty<p class="text-sm text-gray-500">None.</p>@endforelse
    <a class="text-sm text-blue-600 underline" href="{{ route('admin.salaries.payslip', $salary->id) }}">Payslip</a>
</div>
@endsection
