@extends('layouts.app')

@section('title', 'Verify Your Email Address — Customer Portal')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <x-page-header
        title="Email Verification"
        subtitle="Verify your corporate email address to unlock service orders, quotation approvals, and invoice billing."
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Email Verification' => null]"
    />

    <div class="glass-card p-8 lg:p-10 rounded-2xl shadow-2xl border border-white/10 relative overflow-hidden">
        {{-- Shield Icon --}}
        <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-brand-50 dark:bg-brand-950/40 border border-brand-500/30 flex items-center justify-center shadow-lg shadow-brand-500/10">
            <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Enter Verification Code</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                A 6-digit security OTP was dispatched to <strong class="text-gray-900 dark:text-white font-mono">{{ auth()->user()->email }}</strong>.
            </p>
        </div>

        @if (session('status'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm mb-6 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('status') }}</span>
        </div>
        @endif

        @if (session('dev_email_otp'))
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs mb-6 font-mono">
            <strong>DEV MODE OTP:</strong> {{ session('dev_email_otp') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-sm mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- OTP Verification Form --}}
        <form method="POST" action="{{ route('portal.verification.email.verify') }}" class="space-y-6">
            @csrf
            <div>
                <label for="code" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2 text-center">
                    6-Digit Verification Code
                </label>
                <div class="max-w-xs mx-auto">
                    <input id="code" name="code" type="text" maxlength="6" pattern="[0-9]{6}" required autofocus
                           placeholder="••••••"
                           class="w-full text-center tracking-[0.5em] font-mono text-2xl font-bold py-3.5 px-4 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all">
                </div>
            </div>

            <div class="max-w-xs mx-auto">
                <button type="submit" class="btn-primary w-full py-3 text-sm">
                    Verify Email Address
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/5 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Didn't receive the code or expired?</p>
            <form method="POST" action="{{ route('portal.verification.email.send') }}" class="inline-block">
                @csrf
                <button type="submit" class="btn-secondary btn-sm">
                    Send New OTP Code
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
