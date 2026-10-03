@extends('layouts.app')
@section('page-title', 'Transfer detail')
@section('content')

    <x-page-header title="Transfer detail" sys="FINANCE://TRANSFERS" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold">{{ $transfer->reference }} · {{ $transfer->status }}</h2>
    <p class="text-sm">{{ $transfer->currency }} {{ $transfer->amount }} → {{ $transfer->beneficiary->name }} ({{ $transfer->purpose }}, {{ $transfer->provider }})</p>
    <p class="text-xs text-gray-500">Destination: {{ \App\Models\BankTransfer::mask($transfer->masked_destination) ?? '—' }} · External ref: {{ $transfer->external_reference ?? '— (not confirmed)' }}</p>
    <div class="flex flex-wrap gap-2 mt-3 text-sm">
        @if($transfer->status === 'pending_approval')<form method="POST" action="{{ route('admin.transfers.approve', $transfer->id) }}">@csrf<button class="px-3 py-1 rounded bg-green-600 text-white">Approve</button></form>@endif
        @if($transfer->status === 'approved')<form method="POST" action="{{ route('admin.transfers.process', $transfer->id) }}">@csrf<button class="term-btn term-btn-sm term-btn-ghost">Send for processing</button></form>@endif
        @if(in_array($transfer->status, ['approved', 'processing']))
        <form method="POST" action="{{ route('admin.transfers.complete', $transfer->id) }}" class="flex gap-1">@csrf<input name="external_reference" required placeholder="provider/bank ref" class="term-input" /><button class="term-btn term-btn-sm">Complete</button></form>
        <form method="POST" action="{{ route('admin.transfers.fail', $transfer->id) }}" class="flex gap-1">@csrf<input name="reason" required placeholder="failure reason" class="term-input" /><button class="btn btn-destructive term-btn-sm">Fail</button></form>
        @endif
        @if(in_array($transfer->status, ['draft', 'pending_approval', 'approved']))<form method="POST" action="{{ route('admin.transfers.cancel', $transfer->id) }}">@csrf<button class="px-3 py-1 rounded bg-gray-600 text-white">Cancel</button></form>@endif
    </div>
</div>
@endsection
