<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — TechSupport Solutions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-50 dark:bg-[#020617] min-h-screen flex items-center justify-center px-6">
    {{-- Global solar-system universe (one instance) --}}
    <x-global-space-background />
    <div class="w-full max-w-sm relative z-10">
        <div class="flex items-center gap-3 mb-10">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 bg-navy-900 dark:bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white dark:text-navy-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-bold text-navy-900 dark:text-white">TechSupport</span>
            </a>
        </div>

        <h2 class="heading-md mb-2">New password</h2>
        <p class="body-sm mb-8">Enter your new password below.</p>

        @if($errors->any())
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg text-sm mb-6">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required class="w-full px-4 py-3 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors placeholder-surface-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors placeholder-surface-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full px-4 py-3 rounded-lg border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800/50 text-navy-900 dark:text-white text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors placeholder-surface-400">
            </div>
            <button type="submit" class="w-full btn-primary py-3 text-sm">Reset Password</button>
        </form>
    </div>
</body>
</html>
