@extends('layouts.app')
@section('page-title', 'Subscriptions Management')
@section('content')
<div class="glass-card p-6">
    <div class="flex items-center justify-between mb-4">
        <div><h2 class="text-xl font-bold text-gray-900 dark:text-white">Subscriptions (recurring billing)</h2><p class="text-sm text-gray-500">Monthly/yearly services. Use “Bill now” to generate the next invoice.</p></div>
        <a href="{{ route('admin.subscriptions.create') }}" class="btn-primary btn-sm">New Subscription</a>
    </div>
    <div class="overflow-x-auto"><table class="min-w-full text-sm">
        <thead><tr class="text-left text-gray-500"><th class="py-2 pr-4">Subscription</th><th class="py-2 pr-4">Customer</th><th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Amount</th><th class="py-2">Next billing</th></tr></thead>
        <tbody>@forelse($subscriptions as $s)<tr class="border-t border-gray-100 dark:border-gray-800">
            <td class="py-2 pr-4"><a href="{{ route('admin.subscriptions.show', $s) }}" class="font-semibold text-primary-600">{{ $s->subscription_number }} — {{ $s->name }}</a><div class="text-xs text-gray-500">{{ $s->interval }} × {{ $s->interval_count }}</div></td>
            <td class="py-2 pr-4">{{ $s->customer?->name ?? '—' }}</td><td class="py-2 pr-4"><span class="badge">{{ $s->status }}</span></td>
            <td class="py-2 pr-4">{{ $s->currency }} {{ number_format($s->amount, 2) }}</td><td class="py-2 text-gray-500">{{ $s->next_billing_at }}</td>
        </tr>@empty<tr><td colspan="5" class="py-8 text-center text-gray-500">No subscriptions yet.</td></tr>@endforelse</tbody>
    </table></div>
    <div class="mt-4">{{ $subscriptions->links() }}</div>
</div>
@endsection
