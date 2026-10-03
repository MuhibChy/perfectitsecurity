<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#050807">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password — PerfectITSecurity</title>
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
<body class="bg-term-0 text-term-950 min-h-screen flex items-center justify-center p-4 sm:p-6 relative" data-lights="auth">
    <a href="#email" class="skip-link">Skip to email field</a>
    <x-terminal-background />
    <x-global-3d-scene />
    <x-global-hud-frame />

    <div class="w-full max-w-md relative z-10">
        <div class="flex items-center justify-between mb-4 font-mono text-[11px] tracking-[0.18em] uppercase">
            <a href="{{ route('home') }}" class="text-term-700 hover:text-accent-soft transition-colors">← SYSTEM://HOME</a>
            <span class="term-status"><span class="term-status-dot"></span>ONLINE</span>
        </div>

        <div class="auth-term p-7 sm:p-9">
            <span class="auth-term-corner tl" aria-hidden="true"></span>
            <span class="auth-term-corner br" aria-hidden="true"></span>

            <div class="font-mono text-[10px] tracking-[0.28em] text-accent-soft uppercase mb-4">AUTH://RECOVERY</div>
            <h1 class="font-display font-extrabold text-2xl tracking-tight text-white">Reset Password</h1>
            <p class="text-sm text-term-800 mt-1.5 mb-6">Enter your email and we will send a reset link.</p>

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

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="term-field-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="term-input" placeholder="you@company.com">
                </div>
                <button type="submit" class="term-btn w-full !py-3.5">SEND RESET LINK →</button>
            </form>

            <p class="mt-6 text-center">
                <a href="{{ route('login') }}" class="term-link font-mono text-[11px] tracking-[0.14em] uppercase">← Back to sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
