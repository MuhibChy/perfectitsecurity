@extends('layouts.public')

@section('title', '503 — Maintenance | PerfectITSecurity')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 py-20 relative">
    <div class="term-panel max-w-xl w-full p-8 lg:p-12 text-center relative">
        <div class="font-mono text-[11px] tracking-[0.28em] text-amber-400 uppercase mb-6">SYS://503 — OFFLINE_MAINTENANCE</div>

        <div class="err-code" aria-hidden="true">503<span class="cursor-blink"></span></div>

        <h1 class="font-display font-extrabold text-2xl sm:text-3xl tracking-tight text-navy-900 dark:text-white mt-4">Scheduled Maintenance</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-term-800 leading-relaxed mt-3 mb-8 max-w-md mx-auto">
            The platform is briefly offline for maintenance. Services will resume shortly.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="term-btn w-full sm:w-auto">RETRY CONNECTION →</a>
            <a href="{{ route('contact') }}" class="term-btn term-btn-ghost w-full sm:w-auto">CONTACT SUPPORT</a>
        </div>

        <div class="mt-8 term-status justify-center">
            <span class="term-status-dot-amber term-status-dot"></span>
            <span>Status: Maintenance</span>
        </div>
    </div>
</div>
@endsection
