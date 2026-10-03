@extends('layouts.public')

@section('title', '419 — Session Expired | PerfectITSecurity')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 py-20 relative">
    <div class="term-panel max-w-xl w-full p-8 lg:p-12 text-center relative">
        <div class="font-mono text-[11px] tracking-[0.28em] text-accent-soft uppercase mb-6">SYS://419 — SESSION_EXPIRED</div>

        <div class="err-code" aria-hidden="true">419<span class="cursor-blink"></span></div>

        <h1 class="font-display font-extrabold text-2xl sm:text-3xl tracking-tight text-navy-900 dark:text-white mt-4">Session Expired</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-term-800 leading-relaxed mt-3 mb-8 max-w-md mx-auto">
            Your secure session timed out. Refresh and try again — no data was lost.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="term-btn w-full sm:w-auto">RETRY REQUEST →</a>
            <a href="{{ route('login') }}" class="term-btn term-btn-ghost w-full sm:w-auto">RE-AUTHENTICATE</a>
        </div>
    </div>
</div>
@endsection
