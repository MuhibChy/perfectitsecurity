@extends('layouts.app')
@section('page-title', 'Contract ' . $contract->contract_number)
@section('content')

    <x-page-header title="Contracts" sys="OPS://CONTRACTS" />
<div class="term-panel p-6 max-w-3xl">
    <h2 class="text-xl font-bold">{{ $contract->title }} <span class="text-sm font-normal text-gray-500">{{ $contract->contract_number }} · {{ $contract->status }}</span></h2>
    <p class="text-sm text-gray-500">Customer: {{ $contract->customer?->name }} · Value: {{ $contract->currency }} {{ number_format($contract->value, 2) }} · {{ $contract->start_date }} → {{ $contract->end_date }}</p>
    <div class="mt-4 text-sm whitespace-pre-line">{{ $contract->body }}</div>
    <form method="POST" action="{{ route('admin.contracts.update', $contract) }}" class="grid sm:grid-cols-2 gap-4 mt-6">
        @csrf @method('PUT')
        <div><label class="term-field-label">Status</label><select name="status" class="term-input">@foreach(['draft','sent','active','expired','terminated'] as $s)<option value="{{ $s }}" @selected($contract->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div><label class="term-field-label">Value</label><input name="value" type="number" step="0.01" class="term-input" value="{{ $contract->value }}"></div>
        <div class="sm:col-span-2"><button class="term-btn term-btn-sm">Update Contract</button></div>
    </form>
    <form method="POST" action="{{ route('admin.contracts.renew', $contract) }}" class="flex flex-wrap items-end gap-2 mt-4">
        @csrf
        <div><label class="term-field-label">New end date</label><input name="end_date" type="date" required min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ $contract->end_date?->copy()->addYear()->format('Y-m-d') }}" class="term-input"></div>
        <button class="term-btn term-btn-ghost term-btn-sm">Renew Contract</button>
    </form>
</div>
@endsection
