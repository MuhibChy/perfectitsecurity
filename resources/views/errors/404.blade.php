@extends('layouts.public')

@section('title', '404 — Resource Not Found | PerfectITSecurity')
@section('description', 'The requested endpoint could not be located.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 py-20 relative">
    <div class="term-panel max-w-xl w-full p-8 lg:p-12 text-center relative">
        <div class="font-mono text-[11px] tracking-[0.28em] text-accent-soft uppercase mb-6">SYS://404 — ROUTE_UNRESOLVED</div>

        <div class="err-code" aria-hidden="true">404<span class="cursor-blink"></span></div>

        <h1 class="font-display font-extrabold text-2xl sm:text-3xl tracking-tight text-navy-900 dark:text-white mt-4">Resource Not Found</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-term-800 leading-relaxed mt-3 mb-8 max-w-md mx-auto">
            The requested endpoint could not be located. It may have moved, or access is restricted under security policy.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="term-btn w-full sm:w-auto">RETURN TO SYSTEM →</a>
            <a href="{{ route('contact') }}" class="term-btn term-btn-ghost w-full sm:w-auto">CONTACT SUPPORT</a>
        </div>

        <p class="mt-8 font-mono text-[10px] tracking-[0.2em] text-term-700 uppercase">ENDPOINT: {{ request()->path() }}</p>
    </div>
</div>
@endsection
