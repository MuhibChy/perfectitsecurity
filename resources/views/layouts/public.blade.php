<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $localeDirection ?? 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#050807">

    <title>@yield('title', 'PerfectITSecurity — Cybersecurity & IT Engineering') </title>
    <meta name="description" content="@yield('description', 'Managed IT support, cybersecurity, penetration testing, secure software development, and cloud/server management. Security operations active 24/7.')">
    <meta name="keywords" content="@yield('keywords', 'IT support, cybersecurity, penetration testing, managed IT, cloud services, secure software development')">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title', 'PerfectITSecurity — Cybersecurity & IT Engineering')">
    <meta property="og:description" content="@yield('description', 'Managed IT support, cybersecurity and secure engineering. Security operations active 24/7.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('favicon.ico') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'PerfectITSecurity — Cybersecurity & IT Engineering')">
    <meta name="twitter:description" content="@yield('description', 'Managed IT support, cybersecurity and secure engineering. Security operations active 24/7.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts: Sora (display) + Inter (body) + JetBrains Mono (technical) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page-specific head --}}
    @stack('head')

    {{-- Dark mode initialise (before render to avoid flash; persists across login) --}}
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const dark = saved ? saved === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="bg-black text-white min-h-screen flex flex-col relative overflow-x-hidden selection:bg-[#00FF00]/25 selection:text-[#00FF00] font-sans antialiased dark:bg-black dark:text-white"
      x-data="{ mobileOpen: false }"
      data-lights="{{ request()->routeIs('home') ? 'home' : 'public' }}"
      data-glove-mode="moving"
      data-3d="full">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <noscript><style>.reveal,.reveal-left,.reveal-right{opacity:1 !important;transform:none !important;}</style></noscript>

    {{-- Terminal environment (one fixed CSS layer; zero JS) --}}
    <x-terminal-background />

    {{-- Global 3D scene with moving glove --}}
    <x-global-3d-scene />

    {{-- Global fixed HUD frame: one command-center backdrop site-wide --}}
    <x-global-hud-frame />

    {{-- ═══════════════════════════════════════════════════════════
         NAVIGATION — Terminal Chrome (Solid Color Design)
         ═══════════════════════════════════════════════════════════ --}}
    <nav class="dark-island h-14 w-full bg-black border-b border-[#00FF00]/30 relative z-[1000] overflow-visible" id="main-nav"
         aria-label="Primary">

        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
            <div class="flex items-center justify-between h-14 gap-4">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group flex-shrink-0" aria-label="PerfectITSecurity — home">
                    <div class="w-8 h-8 border border-[#00FF00]/50 bg-[#00FF00]/10 flex items-center justify-center relative" aria-hidden="true">
                        <span class="absolute -top-px -left-px w-2 h-2 border-t border-l border-[#00FF00]"></span>
                        <span class="absolute -bottom-px -right-px w-2 h-2 border-b border-r border-[#00FF00]"></span>
                        <svg class="w-[18px] h-[18px] text-[#00FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.2 12l1.9 1.9L15 10"/>
                        </svg>
                    </div>
                    <div class="leading-none hidden sm:block">
                        <span class="block font-display font-extrabold text-[14px] tracking-tight text-white">
                            PERFECT<span class="text-[#00FF00]">IT</span>SECURITY
                        </span>
                        <span class="block font-mono text-[8px] tracking-[0.32em] text-gray-400 uppercase mt-1">
                            Secure // Build // Operate
                        </span>
                    </div>
                </a>

                {{-- Desktop Navigation (xl+: horizontal from 1280px so 1366/1920 show full links; below that the hamburger menu takes over) --}}
                <div class="hidden xl:flex items-center gap-0.5 2xl:gap-1 flex-nowrap whitespace-nowrap">

                    {{-- Services --}}
                    <div x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false" class="relative">
                        <button @click="open = !open" @keydown.escape.window="open = false"
                                :aria-expanded="open.toString()"
                                class="header-nav-item flex items-center gap-1 px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors"
                                aria-haspopup="true">
                            <span @if(request()->routeIs('services.*')) aria-current="page" @endif>Services</span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute top-full left-0 mt-2 w-64 bg-black border border-[#00FF00]/30 p-2 z-50"
                             style="display: none;">
                            <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-gray-400 px-2.5 pt-1.5 pb-2">01 / Services</div>
                            <a href="{{ route('services.index') }}" class="flex items-center justify-between px-2.5 py-2 text-sm font-semibold text-white hover:bg-[#00FF00]/10 hover:text-[#00FF00] transition-colors rounded-sm">
                                All Services
                                <svg class="w-3.5 h-3.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <div class="h-px bg-gray-800 my-1.5"></div>
                            @foreach(\App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->take(9)->get() as $category)
                            <a href="{{ route('services.index') }}?category={{ $category->slug }}"
                               class="flex items-center gap-2.5 px-2.5 py-1.5 text-sm text-gray-300 hover:bg-[#00FF00]/10 hover:text-white transition-colors rounded-sm">
                                <span class="w-1.5 h-1.5 bg-gray-600 flex-shrink-0" aria-hidden="true"></span>
                                <span class="truncate">{{ $category->name }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Solutions --}}
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" :aria-expanded="open.toString()" class="header-nav-item flex items-center gap-1 px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors" aria-haspopup="true">
                            <span @if(request()->routeIs('case-studies*','portfolio.*','pricing','get-quote','industries')) aria-current="page" @endif>Solutions</span>
                            <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute top-full left-0 mt-2 w-60 bg-black border border-[#00FF00]/30 p-2 z-50"
                             style="display: none;">
                            <div class="font-mono text-[10px] uppercase tracking-[0.2em] text-gray-400 px-2.5 pt-1.5 pb-2">02 / Solutions</div>
                            @foreach([
                                ['label' => 'Case Studies', 'url' => route('case-studies')],
                                ['label' => 'Portfolio', 'url' => route('portfolio.index')],
                                ['label' => 'Pricing', 'url' => route('pricing')],
                                ['label' => 'Get a Quote', 'url' => route('get-quote')],
                                ['label' => 'Industries', 'url' => route('industries')],
                            ] as $item)
                            <a href="{{ $item['url'] }}" class="block px-2.5 py-2 text-sm text-gray-300 hover:bg-[#00FF00]/10 hover:text-white transition-colors rounded-sm">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Security (home section anchor) --}}
                    <a href="{{ request()->routeIs('home') ? '#security' : route('home') . '#security' }}" class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">Security</a>

                    {{-- IT Support (home section anchor) --}}
                    <a href="{{ request()->routeIs('home') ? '#it-support' : route('home') . '#it-support' }}" class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">IT Support</a>

                    {{-- Portfolio --}}
                    <a href="{{ route('portfolio.index') }}" @if(request()->routeIs('portfolio.*')) aria-current="page" @endif class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">Portfolio</a>

                    {{-- Knowledge Base --}}
                    <a href="{{ route('kb.index') }}" @if(request()->routeIs('kb.*')) aria-current="page" @endif class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">Knowledge Base</a>

                    {{-- About --}}
                    <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">About</a>

                    {{-- Contact --}}
                    <a href="{{ route('contact') }}" @if(request()->routeIs('contact*')) aria-current="page" @endif class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">Contact</a>
                </div>

                {{-- Right Side Actions --}}
                <div class="hidden xl:flex items-center gap-1.5 2xl:gap-2 flex-shrink-0">

                    {{-- Language / Currency / Theme --}}
                    <x-language-switcher />
                    <x-currency-switcher />
                    <button id="theme-toggle"
                            onclick="window.Alpine && Alpine.store('theme').toggle()"
                            class="p-2 text-gray-400 hover:text-white hover:bg-[#00FF00]/10 transition-colors rounded-sm"
                            title="Toggle dark mode" aria-label="Toggle dark mode">
                        <svg class="w-[18px] h-[18px] dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>

                    <span class="w-px h-5 bg-gray-700" aria-hidden="true"></span>

                    {{-- Portal / Auth --}}
                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('portal.dashboard') }}" class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">My Portal</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">Operations</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="header-nav-item px-3 py-2 text-sm font-medium text-white hover:text-[#00FF00] transition-colors">Client Portal</a>
                    @endauth

                    {{-- Primary CTA --}}
                    <a href="{{ route('get-quote') }}" class="header-cta inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-black bg-[#00FF00] hover:bg-[#00CC00] transition-colors rounded-sm flex-shrink-0">
                        Get Started
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>

                {{-- Mobile controls --}}
                <div class="xl:hidden flex items-center gap-1">
                    <button id="mobile-theme-toggle"
                            onclick="window.Alpine && Alpine.store('theme').toggle()"
                            class="p-2 text-gray-400 hover:text-white hover:bg-[#00FF00]/10 transition-colors"
                            aria-label="Toggle dark mode">
                        <svg class="w-5 h-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <button @click="mobileOpen = !mobileOpen"
                            @keydown.escape.window="mobileOpen = false"
                            :aria-expanded="mobileOpen.toString()"
                            aria-controls="mobile-menu"
                            class="p-2 text-gray-400 hover:text-white hover:bg-[#00FF00]/10 transition-colors"
                            aria-label="Toggle menu">
                        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileOpen" id="mobile-menu"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="xl:hidden pb-6 border-t border-gray-700 mt-2 max-h-[calc(100dvh-3.5rem)] overflow-y-auto bg-black">
                <div class="pt-4 grid gap-0.5">
                    @php
                        $mobileLinks = [
                            ['label' => 'Services', 'url' => route('services.index'), 'active' => request()->routeIs('services.*')],
                            ['label' => 'Solutions', 'url' => route('case-studies'), 'active' => request()->routeIs('case-studies*')],
                            ['label' => 'Security', 'url' => request()->routeIs('home') ? '#security' : route('home') . '#security', 'active' => false],
                            ['label' => 'IT Support', 'url' => request()->routeIs('home') ? '#it-support' : route('home') . '#it-support', 'active' => false],
                            ['label' => 'Portfolio', 'url' => route('portfolio.index'), 'active' => request()->routeIs('portfolio.*')],
                            ['label' => 'Knowledge Base', 'url' => route('kb.index'), 'active' => request()->routeIs('kb.*')],
                            ['label' => 'About', 'url' => route('about'), 'active' => request()->routeIs('about')],
                            ['label' => 'Contact', 'url' => route('contact'), 'active' => request()->routeIs('contact*')],
                        ];
                    @endphp
                    @foreach($mobileLinks as $i => $link)
                    <a href="{{ $link['url'] }}" class="flex items-center justify-between px-4 py-3 border-b border-gray-800 text-sm font-medium {{ $link['active'] ? 'text-[#00FF00]' : 'text-white' }}">
                        <span>{{ $link['label'] }}</span>
                        <span class="font-mono text-xs text-gray-500">{{ str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    </a>
                    @endforeach

                    <div class="pt-4 space-y-2.5 px-4">
                        <x-language-switcher />
                        <x-currency-switcher />
                        @auth
                            @if(auth()->user()->isCustomer())
                                <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-black bg-[#00FF00] hover:bg-[#00CC00] transition-colors rounded-sm">My Portal</a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-black bg-[#00FF00] hover:bg-[#00CC00] transition-colors rounded-sm">Operations</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white border border-gray-700 hover:border-[#00FF00] hover:text-[#00FF00] transition-colors rounded-sm">Client Portal / Login</a>
                        @endauth
                        <a href="{{ route('get-quote') }}" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-black bg-[#00FF00] hover:bg-[#00CC00] transition-colors rounded-sm">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Nav spacer for non-hero pages --}}
    @if(!request()->routeIs('home'))
    <div class="h-14 bg-black/0"></div>
    @endif

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 mt-4">
        <div class="term-panel border-l-2 !border-l-accent px-5 py-3.5 flex items-center gap-3 text-sm text-slate-800 dark:text-slate-100 animate-slide-up" role="status">
            <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-accent-soft flex-shrink-0">SYS://OK</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 mt-4">
        <div class="term-panel border-l-2 px-5 py-3.5 flex items-center gap-3 text-sm text-slate-800 dark:text-slate-100 animate-slide-up" style="border-left-color: var(--term-red);" role="alert">
            <span class="font-mono text-[10px] uppercase tracking-[0.2em] flex-shrink-0" style="color: var(--term-red);">SYS://ERR</span>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    {{-- Main content --}}
    <div id="main-content" class="flex-1 relative z-10">@yield('content')</div>

    {{-- ═══════════════════════════════════════════════════════════
         FOOTER — Technical Terminal Footer
         ═══════════════════════════════════════════════════════════ --}}
    @php
        $footerCategories = \App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->take(6)->get();
        $footerEmail = \App\Models\Setting::get('company_email', 'info@perfectitsecurity.com');
        $footerPhone = \App\Models\Setting::get('company_phone', '');
    @endphp
    <footer class="dark-island relative bg-term-0 border-t border-term-300 z-10 overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-accent/50 to-transparent" aria-hidden="true"></div>
        <div class="absolute inset-0 bg-cyber-grid opacity-[0.05] pointer-events-none" aria-hidden="true"></div>

        <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-14 lg:pt-16 pb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 mb-12">

                {{-- Brand --}}
                <div class="lg:col-span-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 mb-5">
                        <div class="w-8 h-8 border border-accent/50 bg-accent/10 flex items-center justify-center relative" aria-hidden="true">
                            <span class="absolute -top-px -left-px w-2 h-2 border-t border-l border-accent"></span>
                            <span class="absolute -bottom-px -right-px w-2 h-2 border-b border-r border-accent"></span>
                            <svg class="w-[18px] h-[18px] text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.2 12l1.9 1.9L15 10"/>
                            </svg>
                        </div>
                        <span class="font-display font-extrabold text-[15px] tracking-tight text-white">
                            PERFECT<span class="text-accent">IT</span>SECURITY
                        </span>
                    </a>
                    <p class="font-mono text-[11px] leading-relaxed text-term-700 uppercase tracking-wider max-w-xs mb-6">
                        Cybersecurity // IT Operations<br>
                        Secure Engineering // Cloud
                    </p>
                    <p class="text-sm text-term-800 leading-relaxed max-w-xs mb-6">
                        Managed IT support, ethical security testing, and secure software engineering. We build, secure, and operate the technology your business depends on.
                    </p>
                    <div class="flex items-center gap-3">
                        @foreach([
                            ['name' => 'LinkedIn', 'url' => 'https://www.linkedin.com/', 'icon' => 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'],
                            ['name' => 'Twitter', 'url' => 'https://twitter.com/', 'icon' => 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
                            ['name' => 'GitHub', 'url' => 'https://github.com/', 'icon' => 'M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22'],
                        ] as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 border border-term-400 hover:border-accent/60 flex items-center justify-center transition-colors"
                           aria-label="{{ $social['name'] }}">
                            <svg class="w-4 h-4 text-term-700 hover:text-accent-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $social['icon'] }}"/>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Services --}}
                <div class="lg:col-span-2">
                    <h4 class="font-mono text-[10px] uppercase tracking-[0.24em] text-term-700 mb-4">// Services</h4>
                    <ul class="space-y-2.5 text-sm">
                        @if($footerCategories->isNotEmpty())
                            @foreach($footerCategories as $svc)
                            <li>
                                <a href="{{ route('services.index') }}?category={{ $svc->slug }}" class="text-term-800 hover:text-accent-soft transition-colors">{{ $svc->name }}</a>
                            </li>
                            @endforeach
                        @else
                            @foreach(['Cybersecurity', 'Managed IT Support', 'Web Development', 'Cloud & Servers', 'Microsoft 365', 'Digital Marketing'] as $svc)
                            <li><a href="{{ route('services.index') }}" class="text-term-800 hover:text-accent-soft transition-colors">{{ $svc }}</a></li>
                            @endforeach
                        @endif
                        <li><a href="{{ route('services.index') }}" class="text-accent-soft/80 hover:text-accent-soft transition-colors font-mono text-xs tracking-wider">ALL SERVICES →</a></li>
                    </ul>
                </div>

                {{-- Solutions --}}
                <div class="lg:col-span-2">
                    <h4 class="font-mono text-[10px] uppercase tracking-[0.24em] text-term-700 mb-4">// Solutions</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('case-studies') }}" class="text-term-800 hover:text-accent-soft transition-colors">Case Studies</a></li>
                        <li><a href="{{ route('portfolio.index') }}" class="text-term-800 hover:text-accent-soft transition-colors">Portfolio</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-term-800 hover:text-accent-soft transition-colors">Pricing</a></li>
                        <li><a href="{{ route('get-quote') }}" class="text-term-800 hover:text-accent-soft transition-colors">Get a Quote</a></li>
                        <li><a href="{{ route('industries') }}" class="text-term-800 hover:text-accent-soft transition-colors">Industries</a></li>
                        <li><a href="{{ route('careers') }}" class="text-term-800 hover:text-accent-soft transition-colors">Careers</a></li>
                    </ul>
                </div>

                {{-- Resources --}}
                <div class="lg:col-span-2">
                    <h4 class="font-mono text-[10px] uppercase tracking-[0.24em] text-term-700 mb-4">// Resources</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('kb.index') }}" class="text-term-800 hover:text-accent-soft transition-colors">Knowledge Base</a></li>
                        <li><a href="{{ route('faq') }}" class="text-term-800 hover:text-accent-soft transition-colors">FAQ</a></li>
                        <li><a href="{{ route('blog.index') }}" class="text-term-800 hover:text-accent-soft transition-colors">Blog</a></li>
                        <li><a href="{{ route('useful-links') }}" class="text-term-800 hover:text-accent-soft transition-colors">Useful Links</a></li>
                        <li><a href="{{ route('offices') }}" class="text-term-800 hover:text-accent-soft transition-colors">Offices</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div class="lg:col-span-2">
                    <h4 class="font-mono text-[10px] uppercase tracking-[0.24em] text-term-700 mb-4">// Contact</h4>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <span class="font-mono text-[10px] uppercase tracking-wider text-term-700 block mb-0.5">Email</span>
                            <a href="mailto:{{ $footerEmail }}" class="text-term-900 hover:text-accent-soft transition-colors break-all">{{ $footerEmail }}</a>
                        </li>
                        @if($footerPhone)
                        <li>
                            <span class="font-mono text-[10px] uppercase tracking-wider text-term-700 block mb-0.5">Phone</span>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $footerPhone) }}" class="text-term-900 hover:text-accent-soft transition-colors">{{ $footerPhone }}</a>
                        </li>
                        @endif
                        <li>
                            <span class="font-mono text-[10px] uppercase tracking-wider text-term-700 block mb-0.5">Support</span>
                            <span class="text-term-900">24/7 Available</span>
                        </li>
                    </ul>
                    <a href="{{ route('contact') }}" class="term-btn term-btn-sm w-full mt-5">
                        Send Request
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-term-300/70 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="font-mono text-[11px] text-term-700 tracking-wider">
                    &copy; {{ date('Y') }} PERFECTITSECURITY. ALL RIGHTS RESERVED.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 font-mono text-[11px] text-term-700">
                    <a href="{{ route('legal.privacy') }}" class="hover:text-term-900 transition-colors">PRIVACY</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-term-900 transition-colors">TERMS</a>
                    <a href="{{ route('legal.cookies') }}" class="hover:text-term-900 transition-colors">COOKIES</a>
                    <a href="{{ route('legal.refund') }}" class="hover:text-term-900 transition-colors">REFUNDS</a>
                    <a href="{{ route('legal.sla') }}" class="hover:text-term-900 transition-colors">SLA</a>
                    <a href="{{ route('legal.accessibility') }}" class="hover:text-term-900 transition-colors">ACCESSIBILITY</a>
                </div>
                <div class="term-status text-term-700">
                    <span class="term-status-dot" aria-hidden="true"></span>
                    <span>Status: Online</span>
                </div>
            </div>
        </div>
    </footer>

    <x-ai-chat-widget />
    <x-cookie-consent />

    @stack('scripts')

    {{-- Frontend Error Telemetry --}}
    <script>
        window.addEventListener('error', function(e) {
            try {
                fetch('{{ route("api.health.frontend-error") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ message: e.message || 'JS Error', url: e.filename || window.location.href, line: e.lineno || null, col: e.colno || null, stack: e.error?.stack || null })
                });
            } catch(err) {}
        });
    </script>
</body>
</html>
