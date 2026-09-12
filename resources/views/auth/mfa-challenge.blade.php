<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Challenge — TechSupport Solutions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#020617] text-slate-100 min-h-screen flex items-center justify-center p-6 relative overflow-hidden" data-cosmic="false" data-rolebg="login">
<div class="role-bg-fallback" aria-hidden="true"></div>
<canvas id="role-bg-canvas" aria-hidden="true"></canvas>
<div class="role-bg-overlay" aria-hidden="true"></div>
<div class="w-full max-w-md relative z-10">
    <div class="cosmic-glass p-8 rounded-3xl border border-white/10">
        <h1 class="text-2xl font-black text-white mb-2">Two-Factor Authentication</h1>
        <p class="text-sm text-slate-400 mb-6">Enter the 6-digit code from your authenticator app to continue.</p>
        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('mfa.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-sm font-semibold text-slate-300 mb-2">Authentication code</label>
                <input type="text" id="code" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code" maxlength="8"
                       class="w-full px-4 py-3 bg-white/[0.03] border border-white/10 rounded-xl text-white text-center text-2xl tracking-[0.5em] focus:outline-none focus:border-brand-500/60 focus:ring-1 focus:ring-brand-500/30"
                       placeholder="••••••">
                @error('code') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full px-8 py-3 rounded-2xl text-white font-bold transition-all hover:scale-[1.02]" style="background: linear-gradient(135deg, #16A34A, #2563EB);">Verify &amp; Continue</button>
        </form>
        <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
            @csrf
            <button class="text-xs text-slate-500 hover:text-slate-300">Sign out instead</button>
        </form>
    </div>
</div>
</body>
</html>
