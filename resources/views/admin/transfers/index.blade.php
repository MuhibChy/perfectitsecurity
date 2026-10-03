@extends('layouts.app')
@section('page-title', 'Bank transfers')
@section('content')

    <x-page-header title="Bank transfers" sys="FINANCE://TRANSFERS" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold mb-4">Bank transfers</h2>
    <table class="data-table min-w-full text-sm term-table">
        <thead><tr><th class="text-left p-2">Ref</th><th class="text-left p-2">Beneficiary</th><th class="text-right p-2">Amount</th><th class="text-left p-2">Purpose</th><th class="text-left p-2">Status</th><th class="text-left p-2">Provider</th></tr></thead>
        <tbody>@foreach($transfers as $t)<tr class="border-t"><td class="p-2" data-label="Ref"><a class="underline" href="{{ route('admin.transfers.show', $t->id) }}">{{ $t->reference }}</a></td><td class="p-2" data-label="Beneficiary">{{ $t->beneficiary->name }}</td><td class="p-2 text-right" data-label="Amount">{{ $t->currency }} {{ $t->amount }}</td><td class="p-2" data-label="Purpose">{{ $t->purpose }}</td><td class="p-2" data-label="Status">{{ $t->status }}</td><td class="p-2" data-label="Provider">{{ $t->provider }}</td></tr>@endforeach</tbody>
    </table>
    <div class="mt-4">{{ $transfers->links() }}</div>
</div>
@endsection
