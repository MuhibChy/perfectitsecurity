@extends('layouts.app')
@section('page-title', 'Proposal ' . $proposal->proposal_number)
@section('content')
<div class="glass-card p-6 max-w-3xl">
    <h2 class="text-xl font-bold">{{ $proposal->title }} <span class="text-sm font-normal text-gray-500">{{ $proposal->proposal_number }} · v{{ $proposal->version }} · {{ $proposal->status }}</span></h2>
    <p class="text-sm text-gray-500">Customer: {{ $proposal->customer?->name ?? '—' }} · Lead: {{ $proposal->lead?->lead_number ?? '—' }} · Total: {{ $proposal->currency }} {{ number_format($proposal->total, 2) }}</p>
    @if($proposal->scope_of_work)<h3 class="font-bold mt-4">Scope</h3><div class="text-sm whitespace-pre-line">{{ $proposal->scope_of_work }}</div>@endif
    @if($proposal->deliverables)<h3 class="font-bold mt-4">Deliverables</h3><div class="text-sm whitespace-pre-line">{{ $proposal->deliverables }}</div>@endif
    @if($proposal->timeline)<h3 class="font-bold mt-4">Timeline</h3><div class="text-sm whitespace-pre-line">{{ $proposal->timeline }}</div>@endif
    @if($proposal->terms)<h3 class="font-bold mt-4">Terms</h3><div class="text-sm whitespace-pre-line">{{ $proposal->terms }}</div>@endif
    <div class="flex gap-2 mt-6">
        <form method="POST" action="{{ route('admin.proposals.send', $proposal) }}">@csrf<button class="btn-secondary btn-sm">Mark as Sent</button></form>
    </div>
    <form method="POST" action="{{ route('admin.proposals.update', $proposal) }}" class="grid sm:grid-cols-2 gap-4 mt-6">
        @csrf @method('PUT')
        <div><label class="form-label">Status</label><select name="status" class="form-input w-full">@foreach(['draft','sent','viewed','accepted','rejected','revised'] as $s)<option value="{{ $s }}" @selected($proposal->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div><label class="form-label">Subtotal (revision bumps version)</label><input name="subtotal" type="number" step="0.01" class="form-input w-full" value="{{ $proposal->subtotal }}"></div>
        <div class="sm:col-span-2"><button class="btn-primary btn-sm">Save revision</button></div>
    </form>
    <h3 class="font-bold mt-6 mb-2">Version history ({{ $proposal->versions->count() }})</h3>
    <div class="text-sm space-y-1">@foreach($proposal->versions as $v)<div class="text-gray-500">v{{ $v->version }} — {{ $v->created_at }}</div>@endforeach</div>
</div>
@endsection
