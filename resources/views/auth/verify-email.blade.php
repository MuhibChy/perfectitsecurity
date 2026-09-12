@extends('layouts.app')

@section('title', 'Verify Your Email — TechSupport Solutions')

@section('content')
<main class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="glass-card max-w-md w-full p-8 lg:p-10 rounded-2xl shadow-2xl border border-white/10 text-center relative overflow-hidden">
        {{-- Glow background --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-brand-50 dark:bg-brand-950/40 border border-brand-500/30 flex items-center justify-center shadow-lg shadow-brand-500/10">
            <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Check Your Inbox</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
            We have dispatched an activation link to your registered email address. Click the link in the message to activate your customer portal.
        </p>

        @if (session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs mb-6">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs mb-6">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn-primary w-full py-2.5 text-sm" type="submit">
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full py-2 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors" type="submit">
                    Sign Out & Switch Account
                </button>
            </form>
        </div>
    </div>
</main>
@endsection
