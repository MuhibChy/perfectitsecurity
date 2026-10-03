@extends('layouts.app')
@section('page-title', 'My Account')
@section('content')
<div class="space-y-6">
    <x-page-header title="My Account" subtitle="Lifetime financial account, verification state and profile health. Figures come from your invoices, payments and wallet — a single source of truth." sys="CLIENT://ACCOUNT" num="09" />

    {{-- Lifetime value (§6) --}}
    <div class="term-panel p-6">
        <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-4">Customer lifetime value</h2>
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-profit">Total spent</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($finance['total_spent'], 2) }}</dd>
            </div>
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-income">Invoice paid</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($finance['invoice_paid'], 2) }}</dd>
            </div>
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-due">Outstanding</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($finance['outstanding'], 2) }}</dd>
            </div>
            <div class="term-panel-2 p-4">
                <dt class="fin-tag fin-tag-expense">Wallet balance</dt>
                <dd class="fin-value mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($finance['wallet_balance'], 2) }}</dd>
            </div>
        </dl>
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600 dark:text-term-800">
            <span>Orders active: <strong class="text-slate-900 dark:text-white">{{ $finance['orders_active'] }}</strong></span>
            <span>Orders completed: <strong class="text-slate-900 dark:text-white">{{ $finance['orders_completed'] }}</strong></span>
            <span>Refunded: <strong class="text-slate-900 dark:text-white">{{ number_format($finance['total_refunded'], 2) }}</strong></span>
        </div>
        <div class="mt-4 flex flex-wrap gap-2.5">
            <a href="{{ route('portal.history.index') }}" class="term-btn term-btn-sm term-btn-ghost">Service history</a>
            <a href="{{ route('portal.wallet.index') }}" class="term-btn term-btn-sm term-btn-ghost">Wallet</a>
            <a href="{{ route('portal.account.comms') }}" class="term-btn term-btn-sm term-btn-ghost">Communications</a>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Verification (§20) --}}
        <div class="term-panel p-6">
            <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700 mb-4">Verification state</h2>
            <ul class="space-y-2.5 text-sm">
                @foreach($verification as $k => $v)
                <li class="flex items-center justify-between gap-3 border-b border-slate-100 dark:border-white/5 pb-2">
                    <span class="text-slate-600 dark:text-term-800 capitalize">{{ str_replace('_', ' ', $k) }}</span>
                    <x-status-badge :status="in_array($v, ['active', 'verified', 'enabled'], true) ? 'verified' : $v" :label="$v" />
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Completion (§2) --}}
        <div class="term-panel p-6">
            <div class="flex items-center justify-between gap-3 mb-2">
                <h2 class="font-mono text-[11px] tracking-[0.2em] uppercase text-slate-500 dark:text-term-700">Profile completion</h2>
                <span class="font-mono text-sm font-bold text-emerald-700 dark:text-accent-soft">{{ $completion['percent'] }}%</span>
            </div>
            <div class="h-2 rounded bg-slate-200 dark:bg-white/10 overflow-hidden" role="progressbar" aria-valuenow="{{ $completion['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion">
                <div class="h-full bg-emerald-600 dark:bg-accent" style="width: {{ $completion['percent'] }}%"></div>
            </div>
            @if(!empty($completion['missing']))
            <p class="mt-2 text-xs text-slate-600 dark:text-term-800">Missing: {{ implode(' · ', $completion['missing']) }}</p>
            @endif
            <a href="{{ route('portal.profile.edit') }}" class="term-btn term-btn-sm term-btn-ghost mt-4">Complete profile</a>
        </div>
    </div>
</div>
@endsection
