@extends('layouts.app')
@section('page-title', 'Account & Phone Verification')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="glass-card p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Account Verification Status</h1>
        <p class="text-sm text-gray-500 mt-1">To ensure platform security and fulfill financial compliance, service orders require verified contact credentials.</p>

        {{-- Status Grid --}}
        <div class="grid md:grid-cols-3 gap-4 mt-6">
            {{-- Email Status --}}
            <div class="p-4 rounded-xl border {{ $user->isEmailVerified() ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50 dark:border-amber-800/40 dark:bg-amber-950/20' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Email Verification</span>
                    @if($user->isEmailVerified())
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 rounded-full">✓ Verified</span>
                    @else
                        <span class="text-xs font-bold text-amber-600 bg-amber-100 dark:bg-amber-900/60 px-2 py-0.5 rounded-full">Pending</span>
                    @endif
                </div>
                <div class="text-sm font-medium text-gray-900 dark:text-white mt-2">{{ $user->email }}</div>
                @if(!$user->isEmailVerified())
                <form action="{{ route('verification.send') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="text-xs text-primary-600 hover:text-primary-700 font-semibold underline">Resend email verification link</button>
                </form>
                @endif
            </div>

            {{-- Phone Status --}}
            <div class="p-4 rounded-xl border {{ $user->isPhoneVerified() ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800/40 dark:bg-emerald-950/20' : 'border-amber-200 bg-amber-50 dark:border-amber-800/40 dark:bg-amber-950/20' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Phone Verification</span>
                    @if($user->isPhoneVerified())
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 rounded-full">✓ Verified</span>
                    @else
                        <span class="text-xs font-bold text-amber-600 bg-amber-100 dark:bg-amber-900/60 px-2 py-0.5 rounded-full">Pending</span>
                    @endif
                </div>
                <div class="text-sm font-medium text-gray-900 dark:text-white mt-2">{{ $user->phone ?: 'No phone provided' }}</div>
            </div>

            {{-- Overall Status --}}
            <div class="p-4 rounded-xl border {{ $user->isFullyVerified() ? 'border-emerald-300 bg-emerald-100/50 dark:border-emerald-700 dark:bg-emerald-900/30' : 'border-blue-200 bg-blue-50 dark:border-blue-800/40 dark:bg-blue-950/20' }}">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Overall Standing</div>
                <div class="mt-2 flex items-center gap-2">
                    @if($user->isFullyVerified())
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-400">Fully Verified</span>
                    @elseif($user->isSuspended() || $user->isBlocked())
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="font-bold text-red-700 dark:text-red-400">{{ ucfirst($user->verification_status) }}</span>
                    @else
                        <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                        <span class="font-bold text-amber-700 dark:text-amber-400">Pending Verification</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $user->isFullyVerified() ? 'Authorized for confirmed orders & payments.' : 'Complete both email & phone steps to activate full ordering.' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Phone OTP Section --}}
    @if(!$user->isPhoneVerified())
    <div class="glass-card p-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Verify Phone Number via SMS OTP</h2>
        <p class="text-sm text-gray-500 mt-1">Enter your mobile phone number including international country code (e.g. +447123456789 or +12025550123).</p>

        @if(session('dev_otp_code'))
        <div class="mt-4 p-3 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs text-indigo-700 dark:text-indigo-300 font-mono">
            <strong>Dev Environment Notice:</strong> Generated OTP Code is <code class="bg-white dark:bg-gray-800 px-2 py-0.5 rounded font-bold">{{ session('dev_otp_code') }}</code>
        </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6 mt-6">
            {{-- Step 1: Send OTP --}}
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">1. Request Code</h3>
                <form action="{{ route('portal.verification.phone.send') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Phone Number (with + country code)</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+447123456789" required
                               class="mt-1 block w-full px-3 py-2 border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm focus:ring-primary-500">
                        @error('phone')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium text-sm rounded-xl transition-colors">
                        Send Verification Code
                    </button>
                </form>
            </div>

            {{-- Step 2: Enter Code --}}
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">2. Enter Received OTP</h3>
                <form action="{{ route('portal.verification.phone.verify') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">6-Digit Verification Code</label>
                        <input type="text" name="code" maxlength="8" placeholder="123456" required
                               class="mt-1 block w-full px-3 py-2 text-center tracking-widest font-mono text-lg border rounded-xl bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:ring-primary-500">
                        @error('code')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-xl transition-colors">
                        Verify & Confirm Phone
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="glass-card p-6 text-center space-y-4">
        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-xl font-bold">✓</div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Your Phone Number is Verified!</h2>
        <p class="text-sm text-gray-500 max-w-md mx-auto">Thank you for securing your profile. You can now place confirmed service orders and process payments without restrictions.</p>
        <div class="pt-2">
            <a href="{{ route('portal.orders.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700">
                View My Orders →
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
