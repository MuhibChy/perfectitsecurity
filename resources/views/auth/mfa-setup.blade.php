<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set Up Two-Factor Authentication — TechSupport Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#020617] text-slate-100 min-h-screen flex items-center justify-center p-6 relative overflow-hidden" data-cosmic="false" data-rolebg="login">
<div class="role-bg-fallback" aria-hidden="true"></div>
<canvas id="role-bg-canvas" aria-hidden="true"></canvas>
<div class="role-bg-overlay" aria-hidden="true"></div>
<div class="w-full max-w-md relative z-10">
    <div class="cosmic-glass p-8 rounded-3xl border border-white/10">
        <h1 class="text-2xl font-black text-white mb-2">Two-Factor Setup</h1>
        <p class="text-sm text-slate-400 mb-6">Scan the QR code with Google Authenticator, Authy, or 1Password, then confirm with a code.</p>
        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif
        @if($enabled)
            <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">Two-factor authentication is currently <strong>enabled</strong> on your account.</div>
        @endif
        <div class="flex justify-center mb-4 bg-white p-4 rounded-2xl">
            <img src="{{ $qrUrl }}" alt="MFA QR code" class="w-48 h-48">
        </div>
        <p class="text-xs text-slate-500 text-center mb-6 font-mono break-all">Manual key: {{ $secret }}</p>
        <form method="POST" action="{{ route('mfa.enable') }}" class="space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-sm font-semibold text-slate-300 mb-2">Confirmation code</label>
                <input type="text" id="code" name="code" required inputmode="numeric" autocomplete="one-time-code" maxlength="8"
                       class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white text-center text-2xl tracking-[0.5em] focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30"
                       placeholder="••••••">
                @error('code') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full px-8 py-3 rounded-2xl text-white font-bold transition-all hover:scale-[1.02]" style="background: linear-gradient(135deg, #16A34A, #2563EB);">Enable Two-Factor</button>
        </form>
        @if($enabled)
        <form method="POST" action="{{ route('mfa.disable') }}" class="mt-4 space-y-4">
            @csrf
            <p class="text-xs text-slate-500">To disable, enter a current code from your authenticator app.</p>
            <input type="text" name="code" required inputmode="numeric" maxlength="8" placeholder="Current code to disable"
                   class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white text-center tracking-[0.3em] focus:outline-none focus:border-red-500/50">
            <button class="w-full px-8 py-3 rounded-2xl font-bold border border-red-500/40 text-red-400 hover:bg-red-500/10 transition-all">Disable Two-Factor</button>
        </form>
        @endif
        <div class="mt-4 text-center">
            <a href="{{ auth()->user() && auth()->user()->isCustomer() ? route('portal.dashboard') : route('admin.dashboard') }}" class="text-xs text-slate-500 hover:text-slate-300">Back to dashboard</a>
        </div>
    </div>
</div>
</body>
</html>
