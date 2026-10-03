@extends('layouts.public')

@section('title', '500 — System Incident | PerfectITSecurity')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 py-20 relative">
    <div class="term-panel max-w-xl w-full p-8 lg:p-12 text-center relative">
        <div class="font-mono text-[11px] tracking-[0.28em] uppercase mb-6" style="color: var(--term-red);">SYS://500 — INTERNAL_FAULT</div>

        <div class="err-code" aria-hidden="true">500<span class="cursor-blink"></span></div>

        <h1 class="font-display font-extrabold text-2xl sm:text-3xl tracking-tight text-navy-900 dark:text-white mt-4">System Incident</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-term-800 leading-relaxed mt-3 mb-8 max-w-md mx-auto">
            Something failed on our side. The incident has been logged — please try again shortly.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="term-btn w-full sm:w-auto">RETURN TO SYSTEM →</a>
            <a href="{{ route('contact') }}" class="term-btn term-btn-ghost w-full sm:w-auto">REPORT INCIDENT</a>
        </div>
    </div>
</div>
@endsection
