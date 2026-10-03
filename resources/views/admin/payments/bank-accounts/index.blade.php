@extends('layouts.app')
@section('title', 'Company Bank Accounts')
@section('page-title', 'Bank Accounts')

@section('content')
<x-page-header sys="PAYMENTS://BANK-ACCOUNTS" title="Company Bank Accounts" subtitle="Receiving accounts shown to customers at checkout. Numbers are encrypted and masked." :breadcrumbs="['Payments' => route('admin.payments.overview'), 'Bank accounts' => null]">
    <a href="{{ route('admin.bank-accounts.create') }}" class="term-btn term-btn-sm">Add account</a>
</x-page-header>

<div class="card p-0 overflow-hidden"><div class="overflow-x-auto term-table-wrap">
<table class="data-table term-table">
    <thead><tr><th>Label</th><th>Bank</th><th>Account name</th><th>Number</th><th>Currency</th><th>Country</th><th>Status</th><th></th></tr></thead>
    <tbody>
        @forelse($accounts as $a)
        <tr>
            <td data-label="Label"><strong>{{ $a->label }}</strong><div class="text-xs text-slate-500">{{ $a->purpose }}</div></td>
            <td data-label="Bank">{{ $a->bank_name }}@if($a->branch)<div class="text-xs text-slate-500">{{ $a->branch }}</div>@endif</td>
            <td data-label="Account name">{{ $a->account_name }}</td>
            <td class="font-mono" data-label="Number">{{ $a->maskedNumber() }}</td>
            <td data-label="Currency">{{ $a->currency }}</td>
            <td data-label="Country">{{ $a->country }}</td>
            <td data-label="Status"><span class="term-tag">{{ $a->is_active ? 'Active' : 'Inactive' }}</span></td>
            <td data-label="" class="whitespace-nowrap">
                <a href="{{ route('admin.bank-accounts.edit', $a) }}" class="link-arrow text-sm">Edit →</a>
                <form method="POST" action="{{ route('admin.bank-accounts.toggle', $a) }}" class="inline">@csrf<button class="link-arrow text-sm ml-2">{{ $a->is_active ? 'Deactivate' : 'Activate' }}</button></form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-slate-500 py-6">No bank accounts configured.</td></tr>
        @endforelse
    </tbody>
</table>
</div></div>
@endsection
