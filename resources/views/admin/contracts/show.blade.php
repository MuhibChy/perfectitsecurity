@extends('layouts.app')
@section('page-title', 'Contract ' . $contract->contract_number)
@section('content')
<div class="glass-card p-6 max-w-3xl">
    <h2 class="text-xl font-bold">{{ $contract->title }} <span class="text-sm font-normal text-gray-500">{{ $contract->contract_number }} · {{ $contract->status }}</span></h2>
    <p class="text-sm text-gray-500">Customer: {{ $contract->customer?->name }} · Value: {{ $contract->currency }} {{ number_format($contract->value, 2) }} · {{ $contract->start_date }} → {{ $contract->end_date }}</p>
    <div class="mt-4 text-sm whitespace-pre-line">{{ $contract->body }}</div>
    <form method="POST" action="{{ route('admin.contracts.update', $contract) }}" class="grid sm:grid-cols-2 gap-4 mt-6">
        @csrf @method('PUT')
        <div><label class="form-label">Status</label><select name="status" class="form-input w-full">@foreach(['draft','sent','active','expired','terminated'] as $s)<option value="{{ $s }}" @selected($contract->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div><label class="form-label">Value</label><input name="value" type="number" step="0.01" class="form-input w-full" value="{{ $contract->value }}"></div>
        <div class="sm:col-span-2"><button class="btn-primary btn-sm">Update Contract</button></div>
    </form>
</div>
@endsection
