@extends('layouts.app')
@section('page-title', 'Account & Phone Verification')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header title="Account Verification" subtitle="To ensure platform security and fulfill financial compliance, service orders require verified contact credentials." sys="CLIENT://VERIFY" num="14" />
    <div class="term-panel p-6">
        {{-- Status Grid --}}
        <div class="grid md:grid-cols-3 gap-4 mt-2">
            {{-- Email Status --}}
            <div class="term-panel-2 p-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="term-field-label !mb-0">Email</span>
                    @if($user->isEmailVerified())
                        <x-status-badge status="verified" label="Verified" />
                    @else
                        <x-status-badge status="pending" label="Pending" />
                    @endif
                </div>
                <div class="text-sm font-medium text-slate-900 dark:text-white mt-2 font-mono break-all">{{ $user->email }}</div>
                @if(!$user->isEmailVerified())
                <form action="{{ route('verification.send') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="term-link !text-[11px]">Resend email verification link</button>
                </form>
                @endif
            </div>

            {{-- Phone Status --}}
            <div class="term-panel-2 p-4">
                <div class="flex items-center justify-between gap-2">
                    <span class="term-field-label !mb-0">Phone</span>
                    @if($user->isPhoneVerified())
                        <x-status-badge status="verified" label="Verified" />
                    @else
                        <x-status-badge status="pending" label="{{ ucfirst(strtolower(str_replace('_', ' ', $phoneState ?? 'Pending'))) }}" />
                    @endif
                </div>
                <div class="text-sm font-medium text-slate-900 dark:text-white mt-2 font-mono">{{ $user->phone ?: 'No phone provided' }}</div>
                <div class="term-hint mt-1">State: {{ $phoneState ?? '—' }} (separate from email, identity &amp; 2FA).</div>
            </div>

            {{-- Overall Status --}}
            <div class="term-panel-2 p-4">
                <div class="term-field-label">Overall Standing</div>
                <div class="mt-2 flex items-center gap-2">
                    @if($user->isFullyVerified())
                        <span class="term-status-dot"></span>
                        <span class="font-bold text-sm"><span class="fin-tag fin-tag-income">Fully Verified</span></span>
                    @elseif($user->isSuspended() || $user->isBlocked())
                        <span class="term-status-dot term-status-dot-red"></span>
                        <span class="font-bold text-sm"><span class="fin-tag fin-tag-due">{{ ucfirst($user->verification_status) }}</span></span>
                    @else
                        <span class="term-status-dot term-status-dot-amber"></span>
                        <span class="font-bold text-sm"><span class="fin-tag fin-tag-expense">Pending Verification</span></span>
                    @endif
                </div>
                <div class="term-hint mt-1">
                    {{ $user->isFullyVerified() ? 'Authorized for confirmed orders & payments.' : 'Complete both email & phone steps to activate full ordering.' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Phone OTP Section --}}
    @if(!$user->isPhoneVerified())
    <div class="term-panel p-6">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Verify Phone Number via SMS OTP</h2>
        <p class="text-sm text-slate-600 dark:text-term-800 mt-1">Select your country, then enter your mobile number — the dial code is applied automatically. A full international number (e.g. +447123456789) also works.</p>

        @if(session('dev_otp_code'))
        <div class="term-alert term-alert-warn mt-4">
            <span class="term-alert-tag">DEV</span>
            <span class="font-mono text-xs">Generated OTP Code is <code class="font-bold">{{ session('dev_otp_code') }}</code></span>
        </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6 mt-6">
            {{-- Step 1: Send OTP --}}
            <div class="term-panel-2 p-4">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">1. Request Code</h3>
                <form action="{{ route('portal.verification.phone.send') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <x-phone-input :selected="old('country', $user->country_code ?? null)" :national="old('national_number', '')" :required="true"
                        label="Phone Number" hint="Choose the country first — no need to type the dial code yourself." />
                    <details class="text-xs text-slate-500 dark:text-term-700">
                        <summary class="cursor-pointer term-link !text-[11px]">Already have a full international number?</summary>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+447123456789" class="term-input mt-2 font-mono">
                    </details>
                    <button type="submit" class="term-btn term-btn-sm w-full">
                        Send Verification Code
                    </button>
                </form>
            </div>

            {{-- Step 2: Enter Code --}}
            <div class="term-panel-2 p-4">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">2. Enter Received OTP</h3>
                <form action="{{ route('portal.verification.phone.verify') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="term-field-label">6-Digit Verification Code</label>
                        <input type="text" name="code" maxlength="8" placeholder="123456" required
                               class="term-input text-center font-mono tracking-widest">
                        @error('code')
                        <p class="term-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="term-btn term-btn-sm w-full">
                        Verify &amp; Confirm Phone
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="term-panel p-6 text-center space-y-4">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Your Phone Number is Verified!</h2>
        <p class="text-sm text-slate-600 dark:text-term-800 max-w-md mx-auto">Thank you for securing your profile. You can now place confirmed service orders and process payments without restrictions.</p>
        <div class="pt-2">
            <a href="{{ route('portal.orders.index') }}" class="term-btn term-btn-sm">
                View My Orders →
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
