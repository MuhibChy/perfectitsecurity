<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050807">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Check — PerfectITSecurity</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const dark = saved ? saved === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) { document.documentElement.classList.add('dark'); }
        })();
    </script>
</head>
<body class="bg-term-0 text-term-950 min-h-screen flex items-center justify-center p-4 sm:p-6 relative" data-lights="auth">
    <a href="#code" class="skip-link">Skip to verification code</a>
    <x-terminal-background />
    <x-global-3d-scene />
    <x-global-hud-frame />

    <div class="w-full max-w-md relative z-10">
        <div class="auth-term p-7 sm:p-9">
            <span class="auth-term-corner tl" aria-hidden="true"></span>
            <span class="auth-term-corner br" aria-hidden="true"></span>

            <div class="font-mono text-[10px] tracking-[0.28em] text-accent-soft uppercase mb-4">AUTH://2FA-CHALLENGE</div>
            <h1 class="font-display font-extrabold text-2xl tracking-tight text-white">Two-Factor Check</h1>
            <p class="text-sm text-term-800 mt-1.5 mb-6">Enter the 6-digit code from your authenticator app — or a recovery code (XXXX-XXXX).</p>

            @if(session('success'))
            <div class="term-alert term-alert-ok mb-5" role="status">
                <span class="term-alert-tag">SYS://OK</span>
                <span class="text-term-950">{{ session('success') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('mfa.verify') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="code" class="term-field-label">Authentication code</label>
                    <input type="text" id="code" name="code" required autofocus autocomplete="one-time-code" maxlength="12"
                           class="term-input auth-code-input" placeholder="••••••">
                    @error('code') <p class="term-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="term-btn w-full !py-3.5">VERIFY →</button>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="mt-5 text-center">
                @csrf
                <button class="font-mono text-[11px] tracking-[0.14em] text-term-700 hover:text-term-950 uppercase transition-colors">Sign out instead</button>
            </form>
        </div>
    </div>
</body>
</html>
