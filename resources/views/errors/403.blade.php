@extends('layouts.public')

@section('title', '403 — Access Forbidden | PerfectITSecurity')
@section('description', 'Access to this resource is restricted by role-based access controls.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 py-20 relative">
    <div class="term-panel max-w-xl w-full p-8 lg:p-12 text-center relative">
        <div class="font-mono text-[11px] tracking-[0.28em] text-amber-400 uppercase mb-6">SEC://403 — ACCESS_DENIED</div>

        <div class="err-code" aria-hidden="true">403<span class="cursor-blink"></span></div>

        <h1 class="font-display font-extrabold text-2xl sm:text-3xl tracking-tight text-navy-900 dark:text-white mt-4">Access Forbidden</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-term-800 leading-relaxed mt-3 mb-8 max-w-md mx-auto">
            Your credentials are valid, but this zone requires a higher clearance level. Contact your administrator if you need access.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="term-btn w-full sm:w-auto">RETURN TO SYSTEM →</a>
            <a href="{{ route('login') }}" class="term-btn term-btn-ghost w-full sm:w-auto">SWITCH ACCOUNT</a>
        </div>
    </div>
</div>
@endsection
