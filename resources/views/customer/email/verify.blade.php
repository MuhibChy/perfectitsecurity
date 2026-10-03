@extends('layouts.app')

@section('title', 'Verify Your Email Address — Customer Portal')

@section('content')
<div class="max-w-2xl mx-auto py-8 space-y-6">
    <x-page-header
        title="Email Verification"
        subtitle="Verify your corporate email address to unlock service orders, quotation approvals, and invoice billing."
        sys="CLIENT://VERIFY"
        :breadcrumbs="['Customer Portal' => route('portal.dashboard'), 'Email Verification' => null]"
    />

    <div class="term-panel p-8 lg:p-10 relative overflow-hidden">
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Enter Verification Code</h2>
            <p class="text-sm text-slate-600 dark:text-term-800">
                A 6-digit security OTP was dispatched to <strong class="text-slate-900 dark:text-white font-mono">{{ auth()->user()->email }}</strong>.
            </p>
        </div>

        @if (session('status'))
        <div class="term-alert term-alert-ok mb-6">
            <span class="term-alert-tag">OK</span>
            <span>{{ session('status') }}</span>
        </div>
        @endif

        @if (session('dev_email_otp'))
        <div class="term-alert term-alert-warn mb-6">
            <span class="term-alert-tag">DEV</span>
            <span class="font-mono text-xs">OTP: {{ session('dev_email_otp') }}</span>
        </div>
        @endif

        @if ($errors->any())
        <div class="term-alert term-alert-err mb-6">
            <span class="term-alert-tag">ERR</span>
            <ul class="list-disc list-inside space-y-1 text-sm">
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
                <label for="code" class="term-field-label text-center">
                    6-Digit Verification Code
                </label>
                <div class="max-w-xs mx-auto">
                    <input id="code" name="code" type="text" maxlength="6" pattern="[0-9]{6}" required autofocus
                           placeholder="••••••"
                           class="term-input text-center font-mono tracking-widest">
                </div>
            </div>

            <div class="max-w-xs mx-auto">
                <button type="submit" class="term-btn w-full">
                    Verify Email Address
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-white/10 text-center">
            <p class="term-hint mb-3">Didn't receive the code or expired?</p>
            <form method="POST" action="{{ route('portal.verification.email.send') }}" class="inline-block">
                @csrf
                <button type="submit" class="term-btn term-btn-ghost term-btn-sm">
                    Send New OTP Code
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
