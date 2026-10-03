@extends('layouts.app')
@section('title', 'Payment Providers')
@section('page-title', 'Payment Providers')

@section('content')
<x-page-header sys="PAYMENTS://PROVIDERS" title="Payment Providers" subtitle="Providers are configuration, not code. Secrets are stored encrypted and never displayed." :breadcrumbs="['Payments' => route('admin.payments.overview'), 'Providers' => null]">
    <a href="{{ route('admin.payment-providers.create') }}" class="term-btn term-btn-sm">Add provider</a>
</x-page-header>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Provider</th><th>Country</th><th>Currency</th><th>Type</th><th>Mode</th><th>Status</th><th class="data-table-numeric">Priority</th><th>Webhook</th><th></th></tr></thead>
    <tbody>
        @foreach($providers as $p)
        <tr>
            <td data-label="Provider"><strong>{{ $p->name }}</strong><div class="text-xs text-slate-500 font-mono">{{ $p->key }}</div></td>
            <td data-label="Country">{{ $p->country ?? '—' }}</td>
            <td data-label="Currency">{{ $p->currencies ? implode(', ', $p->currencies) : 'All' }}</td>
            <td data-label="Type">{{ ucfirst(str_replace('_',' ',$p->type)) }}</td>
            <td data-label="Mode"><span class="term-tag">{{ $p->mode_label === 'LIVE' ? '🔴 LIVE' : ($p->mode_label === 'TEST' ? '🟡 TEST' : $p->mode_label) }}</span></td>
            <td data-label="Status"><span class="term-tag">{{ ucfirst($p->status) }}{{ $p->is_active ? '' : ' (off)' }}</span></td>
            <td class="data-table-numeric" data-label="Priority">{{ $p->priority }}</td>
            <td class="font-mono text-xs break-all" data-label="Webhook">{{ $p->webhook_endpoint }}</td>
            <td data-label="" class="whitespace-nowrap">
                <a href="{{ route('admin.payment-providers.edit', $p) }}" class="link-arrow text-sm">Edit →</a>
                <form method="POST" action="{{ route('admin.payment-providers.toggle', $p) }}" class="inline">@csrf<button class="link-arrow text-sm ml-2">{{ $p->is_active ? 'Disable' : 'Enable' }}</button></form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div></div>
@endsection
