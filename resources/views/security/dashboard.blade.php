@extends('layouts.app')
@section('page-title', 'Account Security')
@section('content')
<x-page-header title="Account Security" subtitle="Identity verification, device sessions and recent security events for your account." sys="SECURITY://ACCOUNT" num="01" />

<div class="term-panel p-5 sm:p-6 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <div class="term-sec-label mb-1">MEMBER://IDENTITY</div>
            <h2 class="font-display font-bold text-lg text-slate-900 dark:text-white tracking-tight">Account Security — <span class="font-mono text-base">{{ $user->member_number }}</span></h2>
        </div>
        <div class="term-status"><span class="term-status-dot"></span>MONITORED</div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        @foreach($summary as $k => $v)
            <div class="term-panel-2 p-4">
                <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-slate-500 dark:text-term-800">{{ str_replace('_', ' ', $k) }}</p>
                <p class="text-sm font-semibold font-mono text-slate-900 dark:text-term-950 mt-1">{{ $v }}</p>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2.5 mt-5">
        <a href="{{ route('mfa.setup') }}" class="term-btn term-btn-sm">2FA SETTINGS →</a>
        <a href="{{ route('identity.index') }}" class="term-btn term-btn-sm term-btn-ghost">IDENTITY VERIFICATION</a>
        <a href="{{ route('idcard.show') }}" class="term-btn term-btn-sm term-btn-ghost">MY DIGITAL ID</a>
    </div>
</div>

<div class="term-panel p-5 sm:p-6">
    <div class="term-sec-label mb-3">LOG://EVENTS</div>
    <h3 class="font-display font-bold text-base text-slate-900 dark:text-white mb-4">Recent security events</h3>
    <div class="font-mono text-xs">
        @forelse($logins as $log)
            <div class="border-t border-slate-200 dark:border-white/5 py-2.5 text-slate-600 dark:text-term-800 flex flex-wrap gap-x-3 gap-y-1">
                <span class="text-slate-400 dark:text-term-800">{{ optional($log->created_at)->format('Y-m-d H:i') }}</span>
                <span class="text-slate-800 dark:text-term-950">{{ $log->action }}</span>
                <span>{{ $log->description }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-term-800 font-sans">No security events recorded.</p>
        @endforelse
    </div>
    <p class="term-hint mt-4">Current session only is shown here. Full session inventory requires a database session driver (currently file-based).</p>
</div>
@endsection
