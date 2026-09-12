<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Enterprise Account — TechSupport Solutions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#020617] text-slate-100 min-h-screen flex items-center justify-center p-6 relative overflow-hidden" data-cosmic="false" data-rolebg="login" data-lights="auth">

    {{-- Global solar-system universe (one instance) --}}
    <x-global-space-background />

    {{-- Role-based 3D environment: canvas → black overlay → glass UI --}}
    <div class="role-bg-fallback" aria-hidden="true"></div>
    <canvas id="role-bg-canvas" aria-hidden="true"></canvas>
    <div class="role-bg-overlay" aria-hidden="true"></div>

    {{-- Auth ambient lighting --}}
    <div class="page-lights" aria-hidden="true"><div class="pl-blob pl-a"></div><div class="pl-blob pl-b"></div><div class="pl-blob pl-c"></div></div>

    {{-- Atmospheric Glow Blobs --}}
    <div class="absolute inset-0 bg-cyber-grid opacity-20 pointer-events-none"></div>
    <div class="absolute top-1/4 right-1/3 w-96 h-96 bg-brand-600/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 left-1/3 w-96 h-96 bg-brand-900/25 rounded-full blur-[120px] pointer-events-none"></div>

    {{-- Centered Glassmorphism Register Container --}}
    <div class="relative z-10 w-full max-w-lg cosmic-glass p-8 sm:p-10 rounded-3xl border border-white/10 shadow-2xl">
        
        {{-- Logo & Brand Header --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg transition-transform group-hover:scale-105"
                     style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="text-xl font-black text-white tracking-tight">TechSupport <span class="text-brand-400">Portal</span></span>
            </a>
            <h1 class="text-2xl font-black text-white tracking-tight">Create Client Profile</h1>
            <p class="text-xs text-slate-400 mt-1">Get immediate access to quotations, tickets, and cloud resources</p>
        </div>

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl text-xs font-semibold mb-6">
            @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $error }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-1.5">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full px-4 py-3 rounded-xl border border-white/10 bg-black/50 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 transition-all"
                       placeholder="Jane Doe">
            </div>

            <div>
                <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-1.5">Corporate Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-3 rounded-xl border border-white/10 bg-black/50 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 transition-all"
                       placeholder="you@company.com">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-1.5">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl border border-white/10 bg-black/50 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 transition-all"
                           placeholder="••••••••••••">
                </div>
                <div>
                    <label class="block text-xs font-mono uppercase tracking-widest text-slate-300 mb-1.5">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-3 rounded-xl border border-white/10 bg-black/50 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 transition-all"
                           placeholder="••••••••••••">
                </div>
            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed pt-2">
                By registering, you agree to our 
                <a href="{{ route('legal.terms') }}" class="text-brand-400 hover:underline">Terms of Service</a> and 
                <a href="{{ route('legal.privacy') }}" class="text-brand-400 hover:underline">Privacy Policy</a>.
            </p>

            <button type="submit"
                    class="btn w-full py-4 text-center rounded-xl text-sm font-bold text-white transition-all shadow-lg shadow-brand-600/40 hover:scale-[1.02] mt-4"
                    style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                Create Enterprise Profile
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-white/10 text-center">
            <p class="text-xs text-slate-400">
                Already registered?
                <a href="{{ route('login') }}" class="text-brand-400 font-bold hover:underline ml-1">Sign In</a>
            </p>
        </div>
    </div>

</body>
</html>
