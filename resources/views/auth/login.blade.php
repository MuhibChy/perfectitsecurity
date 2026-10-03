<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050807">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Access — PerfectITSecurity</title>
    <meta name="description" content="Access your PerfectITSecurity account.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
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
<body class="bg-black text-white min-h-screen flex items-center justify-center p-4 sm:p-6 relative dark:bg-black dark:text-white" data-lights="auth">
    <a href="#login-form" class="skip-link">Skip to login form</a>

    {{-- Terminal environment (one fixed CSS layer; zero JS) --}}
    <x-terminal-background />
    <x-global-3d-scene />
    <x-global-hud-frame />

    <div class="relative z-10 w-full max-w-md">
        {{-- Top breadcrumb --}}
        <div class="flex items-center justify-between mb-4 font-mono text-[11px] tracking-[0.18em] uppercase">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-emerald-700 dark:hover:text-[#00FF00] transition-colors">← SYSTEM://HOME</a>
            <span class="flex items-center gap-2">
                <span class="term-status"><span class="term-status-dot"></span>ONLINE</span>
                <button type="button" onclick="window.Alpine && Alpine.store('theme').toggle()" class="p-1.5 text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors" aria-label="Toggle color theme" title="Toggle color theme">
                    <svg class="w-4 h-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg class="w-4 h-4 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
            </span>
        </div>

        <div class="auth-term p-7 sm:p-9">
            <span class="auth-term-corner tl" aria-hidden="true"></span>
            <span class="auth-term-corner br" aria-hidden="true"></span>

            {{-- Brand --}}
            <div class="flex items-center gap-2.5 mb-7">
                <div class="w-9 h-9 border border-[#00FF00]/50 bg-[#00FF00]/10 flex items-center justify-center relative" aria-hidden="true">
                    <span class="absolute -top-px -left-px w-2 h-2 border-t border-l border-[#00FF00]"></span>
                    <span class="absolute -bottom-px -right-px w-2 h-2 border-b border-r border-[#00FF00]"></span>
                    <svg class="w-5 h-5 text-[#00FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.2 12l1.9 1.9L15 10"/>
                    </svg>
                </div>
                <div class="leading-none">
                    <span class="block font-display font-extrabold text-[14px] tracking-tight text-white">PERFECT<span class="text-[#00FF00]">IT</span>SECURITY</span>
                    <span class="block font-mono text-[8px] tracking-[0.32em] text-gray-500 uppercase mt-1">AUTH://IDENTITY</span>
                </div>
            </div>

            <h1 class="font-display font-extrabold text-2xl sm:text-3xl tracking-tight text-white">Secure Access</h1>
            <p class="text-sm text-term-800 mt-1.5 mb-7">Access your PerfectITSecurity account.</p>

            @if(session('status'))
            <div class="term-alert term-alert-ok mb-5" role="status">
                <span class="term-alert-tag">SYS://OK</span>
                <span class="text-term-950">{{ session('status') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="term-alert term-alert-err mb-5" role="alert">
                <span class="term-alert-tag">SYS://ERR</span>
                <span class="text-term-950">{{ $errors->first() }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="login-form" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="term-field-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="term-input" placeholder="you@company.com" aria-describedby="email-hint">
                    <p id="email-hint" class="term-hint">Use the email registered to your client profile.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-[0.45rem]">
                        <label for="password" class="term-field-label !mb-0">Password</label>
                        <a href="{{ route('password.request') }}" class="term-link font-mono text-[11px] tracking-wider">FORGOT?</a>
                    </div>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           class="term-input" placeholder="••••••••••••">
                </div>

                <div class="flex items-center gap-2.5">
                    <input type="checkbox" name="remember" id="remember"
                           class="h-4 w-4 border border-term-400 bg-transparent accent-[#00E67A]">
                    <label for="remember" class="text-xs text-term-800 cursor-pointer select-none">
                        Remember this workstation for 30 days
                    </label>
                </div>

                <button type="submit" class="term-btn w-full !py-3.5">
                    LOGIN →
                </button>
            </form>

            <div class="mt-7 pt-5 border-t border-term-300 text-center">
                <p class="font-mono text-[11px] tracking-[0.14em] text-gray-500 uppercase">
                    NO ACCOUNT? <a href="{{ route('register') }}" class="text-emerald-700 hover:text-emerald-900 dark:text-[#00FF00] dark:hover:text-white transition-colors">REGISTER →</a>
                </p>
            </div>
        </div>

        <p class="mt-4 text-center font-mono text-[10px] tracking-[0.2em] text-term-700 uppercase">SESSION ENCRYPTED // TLS 1.3</p>
    </div>
</body>
</html>
