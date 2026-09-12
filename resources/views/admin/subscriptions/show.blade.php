@extends('layouts.app')
@section('page-title', 'Subscription ' . $subscription->subscription_number)
@section('content')
<div class="glass-card p-6 max-w-3xl">
    <h2 class="text-xl font-bold">{{ $subscription->name }} <span class="text-sm font-normal text-gray-500">{{ $subscription->subscription_number }} · {{ $subscription->status }}</span></h2>
    <p class="text-sm text-gray-500">Customer: {{ $subscription->customer?->name }} · {{ $subscription->currency }} {{ number_format($subscription->amount, 2) }} {{ $subscription->interval }} · Next billing: {{ $subscription->next_billing_at ?? '—' }}</p>
    <div class="flex gap-2 mt-4">
        <form method="POST" action="{{ route('admin.subscriptions.bill-now', $subscription) }}">@csrf<button class="btn-secondary btn-sm">Generate invoice now</button></form>
    </div>
    <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}" class="grid sm:grid-cols-3 gap-4 mt-6">
        @csrf @method('PUT')
        <div><label class="form-label">Status</label><select name="status" class="form-input w-full">@foreach(['trial','active','past_due','paused','cancelled'] as $s)<option value="{{ $s }}" @selected($subscription->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
        <div><label class="form-label">Amount</label><input name="amount" type="number" step="0.01" class="form-input w-full" value="{{ $subscription->amount }}"></div>
        <div><label class="form-label">Next billing</label><input name="next_billing_at" type="date" class="form-input w-full" value="{{ $subscription->next_billing_at }}"></div>
        <div class="sm:col-span-3"><button class="btn-primary btn-sm">Update Subscription</button></div>
    </form>
    <h3 class="font-bold mt-6 mb-2">Invoices ({{ $subscription->invoices->count() }})</h3>
    <div class="text-sm space-y-1">@forelse($subscription->invoices as $inv)<div><a class="text-primary-600" href="{{ route('admin.invoices.edit', $inv) }}">{{ $inv->invoice_number }} — {{ $inv->status }} — {{ $inv->total }}</a></div>@empty<p class="text-gray-500">No invoices generated yet.</p>@endforelse</div>
</div>
@endsection
