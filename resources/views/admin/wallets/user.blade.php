@extends('layouts.app')
@section('title', 'Customer Wallets')
@section('page-title', 'Wallets')

@section('content')
<x-page-header sys="FINANCE://WALLETS" :title="$user->name . ' — Wallets'" :subtitle="$user->email" :breadcrumbs="['Users' => route('admin.users.index'), 'Wallets' => null]">
    <x-role-badge :role="$user->role" />
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse($wallets as $w)
    <div class="card p-5">
        <div class="flex justify-between text-sm mb-1"><span class="font-mono">{{ $w->wallet_reference }}</span><span class="term-tag {{ $w->status === 'active' ? '' : '' }}">{{ ucfirst($w->status) }}</span></div>
        <div class="stat-value">{{ $w->currency }} {{ number_format($w->balance, 2) }}</div>
        <div class="stat-label mb-3">{{ $w->transactions_count }} ledger rows</div>
        <a href="{{ route('admin.wallets.show', $w) }}" class="term-btn term-btn-ghost term-btn-sm">Open ledger →</a>
    </div>
    @empty
    <p class="body-md">No wallets yet. One is created automatically on first wallet use.</p>
    @endforelse
</div>
@endsection
