@extends('layouts.app')

@section('title', 'Verify Email — PerfectITSecurity')

@section('page-title', 'Email Verification')

@section('content')
<main class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="auth-term max-w-md w-full p-8 text-center">
        <span class="auth-term-corner tl" aria-hidden="true"></span>
        <span class="auth-term-corner br" aria-hidden="true"></span>

        <div class="font-mono text-[10px] tracking-[0.28em] text-accent-soft uppercase mb-4">AUTH://VERIFY</div>

        <div class="w-14 h-14 mx-auto mb-5 border border-accent/40 bg-accent/10 flex items-center justify-center" aria-hidden="true">
            <svg class="w-7 h-7 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h1 class="font-display font-extrabold text-2xl tracking-tight text-slate-900 dark:text-white">Check Your Inbox</h1>
        <p class="text-sm text-slate-600 dark:text-term-800 leading-relaxed mt-2 mb-6">
            We dispatched an activation link to your registered email. Click the link to activate your portal.
        </p>

        @if (session('success'))
        <div class="term-alert term-alert-ok mb-5 text-left" role="status">
            <span class="term-alert-tag">SYS://OK</span>
            <span class="text-slate-800 dark:text-term-950">{{ session('success') }}</span>
        </div>
        @endif

        @if ($errors->any())
        <div class="term-alert term-alert-err mb-5 text-left" role="alert">
            <span class="term-alert-tag">SYS://ERR</span>
            <span class="text-slate-800 dark:text-term-950">{{ $errors->first() }}</span>
        </div>
        @endif

        <div class="space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="term-btn w-full !py-3" type="submit">RESEND LINK →</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="font-mono text-[11px] tracking-[0.14em] uppercase text-slate-500 dark:text-term-700 hover:text-slate-800 dark:hover:text-term-950 transition-colors py-2" type="submit">
                    Sign out &amp; switch account
                </button>
            </form>
        </div>
    </div>
</main>
@endsection
