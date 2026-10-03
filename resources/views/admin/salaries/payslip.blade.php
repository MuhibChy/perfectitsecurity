@extends('layouts.app')
@section('page-title', 'Payslip')
@section('content')

    <x-page-header title="Payslip" sys="FINANCE://SALARIES" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold">Payslip — {{ $salary->user->name }} — {{ $salary->period }}</h2>
    <table class="data-table text-sm mt-2 term-table"><tbody>
        <tr><td class="p-1 pr-4">Base salary</td><td class="p-1 text-right">{{ $salary->base_salary }}</td></tr>
        <tr><td class="p-1 pr-4">Bonus</td><td class="p-1 text-right">{{ $salary->bonus }}</td></tr>
        <tr><td class="p-1 pr-4">Deductions</td><td class="p-1 text-right">{{ $salary->deductions }}</td></tr>
        <tr class="border-t font-bold"><td class="p-1 pr-4">Net</td><td class="p-1 text-right">{{ $salary->net_salary }}</td></tr>
    </tbody></table>
    <p class="text-xs text-gray-500 mt-2">Status: {{ $salary->status }} · Salary #{{ $salary->id }} · Authorized viewers only.</p>
</div>
@endsection
