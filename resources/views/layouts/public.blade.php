<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name') . ' — Enterprise IT Support & Cybersecurity')</title>
    <meta name="description" content="@yield('description', 'Enterprise-grade IT support, cybersecurity, cloud solutions, and managed services. Trusted by organisations worldwide.')">
    <meta name="keywords" content="@yield('keywords', 'IT support, cybersecurity, managed IT, cloud services, network security, IT consulting')">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title', config('app.name') . ' — Enterprise IT Support')">
    <meta property="og:description" content="@yield('description', 'Enterprise IT support, cybersecurity and managed services.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page-specific head --}}
    @stack('head')

    {{-- Dark mode initialise (before render to avoid flash) --}}
    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark) || true) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
@php
    // Per-page lighting composition (visual only).
    $pageLights = 'default';
    if (request()->routeIs('home')) $pageLights = 'home';
    elseif (request()->routeIs('about')) $pageLights = 'about';
    elseif (request()->routeIs('services.*')) $pageLights = 'services';
    elseif (request()->routeIs('kb.*')) $pageLights = 'kb';
    elseif (request()->routeIs('blog.*')) $pageLights = 'blog';
    elseif (request()->routeIs('case-studies*')) $pageLights = 'casestudies';
    elseif (request()->routeIs('portfolio.*')) $pageLights = 'portfolio';
    elseif (request()->routeIs('pricing')) $pageLights = 'pricing';
    elseif (request()->routeIs('contact') || request()->routeIs('get-quote')) $pageLights = 'contact';
    elseif (request()->routeIs('faq')) $pageLights = 'faq';
@endphp
<body class="bg-[#030712] text-slate-100 min-h-screen flex flex-col relative overflow-x-hidden selection:bg-cyan-500/25 selection:text-cyan-300"
      x-data="{ mobileOpen: false, servicesOpen: false, industriesOpen: false, kbOpen: false }"
      data-lights="{{ $pageLights }}">

    {{-- Global solar-system universe (one instance; inherited by every page) --}}
    <x-global-space-background />

    {{-- Global ambient cosmic canvas for non-hero pages --}}
    <canvas id="cosmic-canvas" class="fixed inset-0 pointer-events-none z-0" aria-hidden="true"></canvas>

    {{-- Page-wide intelligent lighting (three ambient sources, per-page composition) --}}
    <div class="page-lights" aria-hidden="true"><div class="pl-blob pl-a"></div><div class="pl-blob pl-b"></div><div class="pl-blob pl-c"></div></div>

    {{-- ═══════════════════════════════════════════════════════════
         NAVIGATION — Premium Glassmorphism
         ═══════════════════════════════════════════════════════════ --}}
    <nav class="nav-premium" id="main-nav"
         x-data="{ scrolled: window.scrollY > 20 }"
         @scroll.window="scrolled = window.scrollY > 20"
         :class="{ 'nav-scrolled shadow-2xl': scrolled }">

        <div class="w-full max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-12">
            <div class="flex items-center justify-between h-[70px]">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group flex-shrink-0">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-105 group-hover:shadow-lg"
                         style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="font-bold text-[15px] text-navy-900 dark:text-white tracking-tight">TechSupport</span>
                        <span class="text-[9px] uppercase tracking-[0.25em] text-cyber-500 dark:text-cyber-400 font-semibold">Solutions</span>
                    </div>
                </a>

                {{-- Desktop Navigation --}}
                <div class="hidden xl:flex items-center gap-0.5">

                    {{-- Home --}}
                    <a href="{{ route('home') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('home') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        Home
                    </a>

                    {{-- Services Dropdown --}}
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open"
                                class="px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-1.5 transition-all duration-200 {{ request()->routeIs('services.*') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                            <span>Services</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                             class="absolute top-full left-0 mt-2 w-72 bg-white dark:bg-navy-800 rounded-2xl shadow-2xl border border-surface-200 dark:border-white/8 p-2 z-50"
                             style="display: none;">
                            <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-navy-800 dark:text-white hover:bg-brand-50 dark:hover:bg-cyber-500/10 hover:text-brand-600 dark:hover:text-cyber-400 transition-colors mb-1">
                                <div class="w-8 h-8 rounded-lg bg-brand-500/10 dark:bg-cyber-500/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-brand-600 dark:text-cyber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                </div>
                                All Services
                            </a>
                            <div class="h-px bg-surface-100 dark:bg-white/5 my-1"></div>
                            @foreach(App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->take(8)->get() as $category)
                            <a href="{{ route('services.index') }}?category={{ $category->slug }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-surface-600 dark:text-surface-300 hover:text-navy-900 dark:hover:text-white hover:bg-surface-50 dark:hover:bg-white/5 transition-colors">
                                @if($category->color)
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $category->color }}"></span>
                                @endif
                                <span>{{ $category->name }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Industries Dropdown --}}
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open"
                                class="px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-1.5 transition-all duration-200 text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5">
                            <span>Industries</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                             class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-navy-800 rounded-2xl shadow-2xl border border-surface-200 dark:border-white/8 p-2 z-50"
                             style="display: none;">
                            @foreach([
                                ['name' => 'Healthcare', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                                ['name' => 'Financial Services', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                ['name' => 'E-Commerce', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                                ['name' => 'Education', 'icon' => 'M12 14l9-5-9-5-9 5 9 5z'],
                                ['name' => 'Manufacturing', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                                ['name' => 'Government', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            ] as $industry)
                            <a href="{{ route('industries') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-surface-600 dark:text-surface-300 hover:text-navy-900 dark:hover:text-white hover:bg-surface-50 dark:hover:bg-white/5 transition-colors">
                                <svg class="w-4 h-4 flex-shrink-0 text-brand-500 dark:text-cyber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $industry['icon'] }}"/></svg>
                                {{ $industry['name'] }}
                            </a>
                            @endforeach
                            <div class="h-px bg-surface-100 dark:bg-white/5 my-1"></div>
                            <a href="{{ route('industries') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-brand-600 dark:text-cyber-400 hover:bg-brand-50 dark:hover:bg-cyber-500/10 transition-colors">
                                View All Industries
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Knowledge Base --}}
                    <a href="{{ route('kb.index') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('kb.*') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        Knowledge Base
                    </a>

                    {{-- About --}}
                    <a href="{{ route('about') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('about') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        About
                    </a>

                    {{-- Case Studies --}}
                    <a href="{{ route('case-studies') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('case-studies') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        Case Studies
                    </a>

                    {{-- Careers --}}
                    <a href="{{ route('careers') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('careers') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        Careers
                    </a>

                    {{-- Portfolio --}}
                    <a href="{{ route('portfolio.index') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('portfolio.*') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        Portfolio
                    </a>

                    {{-- FAQ --}}
                    <a href="{{ route('faq') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('faq') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        FAQ
                    </a>

                    {{-- Contact --}}
                    <a href="{{ route('contact') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('contact*') ? 'text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10' : 'text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5' }}">
                        Contact
                    </a>
                </div>

                {{-- Right Side Actions --}}
                <div class="hidden xl:flex items-center gap-2">

                    {{-- Language Switcher --}}
                    <x-language-switcher />

                    {{-- Currency Switcher --}}
                    <x-currency-switcher />

                    {{-- Dark mode toggle --}}
                    <button id="theme-toggle"
                            onclick="window.Alpine && Alpine.store('theme').toggle()"
                            class="p-2 rounded-lg text-surface-500 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5 transition-all duration-200"
                            title="Toggle dark mode" aria-label="Toggle dark mode">
                        <svg class="w-5 h-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>

                    @auth
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('portal.dashboard') }}" class="btn btn-secondary px-4 py-2 text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                My Portal
                            </a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary px-4 py-2 text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Admin
                            </a>
                        @endif
                    @else
                        {{-- Customer Login --}}
                        <a href="{{ route('login') }}"
                           class="px-3.5 py-2 rounded-lg text-sm font-medium text-surface-600 dark:text-surface-400 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5 transition-colors">
                            Customer Login
                        </a>

                        {{-- Free Consultation CTA --}}
                        <a href="{{ route('contact') }}"
                           class="btn text-sm px-5 py-2.5 text-white rounded-xl"
                           style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 4px 20px rgba(37,99,235,0.35);">
                            Free Consultation
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @endauth
                </div>

                {{-- Mobile toggle --}}
                <div class="xl:hidden flex items-center gap-2">
                    <button id="mobile-theme-toggle"
                            onclick="window.Alpine && Alpine.store('theme').toggle()"
                            class="p-2 rounded-lg text-surface-500 hover:bg-surface-100 dark:hover:bg-white/5 transition-colors">
                        <svg class="w-5 h-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <button @click="mobileOpen = !mobileOpen" class="p-2 rounded-lg text-surface-500 hover:text-navy-900 dark:hover:text-white hover:bg-surface-100 dark:hover:bg-white/5 transition-colors" aria-label="Open menu">
                        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="xl:hidden pb-5 border-t border-surface-200 dark:border-white/5 mt-1"
                 style="display:none;">
                <div class="pt-3 space-y-0.5">
                    <a href="{{ route('home') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">Home</a>
                    <a href="{{ route('services.index') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">Services</a>
                    <a href="{{ route('kb.index') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">Knowledge Base</a>
                    <a href="{{ route('about') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">About</a>
                    <a href="{{ route('pricing') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">Pricing</a>
                    <a href="{{ route('blog.index') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">Blog</a>
                    <a href="{{ route('faq') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">FAQ</a>
                    <a href="{{ route('faq') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">FAQ</a>
                    <a href="{{ route('contact') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">Contact</a>

                    <div class="pt-3 mt-2 border-t border-surface-200 dark:border-white/5 space-y-2">
                        @auth
                            @if(auth()->user()->isCustomer())
                                <a href="{{ route('portal.dashboard') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10">My Portal</a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-brand-600 dark:text-cyber-400 bg-brand-50 dark:bg-cyber-500/10">Admin Dashboard</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-white/5">Customer Login</a>
                            <a href="{{ route('get-quote') }}" class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-white text-center" style="background: linear-gradient(135deg, #16A34A, #2563EB);">Get a Free Quote</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Nav spacer for non-hero pages --}}
    @if(!request()->routeIs('home'))
    <div class="h-[70px]"></div>
    @endif

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="w-full max-w-[1720px] mx-auto px-6 lg:px-12 mt-4">
        <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-5 py-3.5 rounded-xl flex items-center gap-3 text-sm animate-slide-up">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="w-full max-w-[1720px] mx-auto px-6 lg:px-12 mt-4">
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 px-5 py-3.5 rounded-xl flex items-center gap-3 text-sm animate-slide-up">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    {{-- Main content --}}
    <div class="flex-1 relative z-10">@yield('content')</div>

    {{-- ═══════════════════════════════════════════════════════════
         FOOTER — Premium Multi-Column
         ═══════════════════════════════════════════════════════════ --}}
    <footer class="bg-[#040816] text-white relative overflow-hidden border-t border-white/10 z-10 footer-light">
        {{-- Decorative background --}}
        <div class="absolute inset-0 bg-cyber-grid opacity-20 pointer-events-none"></div>
        <div class="absolute top-0 left-0 right-0 h-px" style="background: linear-gradient(90deg, transparent, rgba(37,99,235,0.4), rgba(92,124,250,0.4), transparent);"></div>

        {{-- Main footer --}}
        <div class="relative w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 pt-16 lg:pt-20 pb-10">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 mb-12">

                {{-- Brand --}}
                <div class="lg:col-span-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 mb-5 group">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                             style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="font-bold text-[15px] text-white tracking-tight">TechSupport</span>
                            <span class="text-[9px] uppercase tracking-[0.25em] text-cyber-400 font-semibold">Solutions</span>
                        </div>
                    </a>
                    <p class="text-sm text-surface-400 leading-relaxed max-w-xs mb-6">
                        Enterprise IT support, cybersecurity, and managed services. Delivering technology solutions for organisations that demand reliability, security, and performance.
                    </p>
                    {{-- Social Links --}}
                    <div class="flex items-center gap-3">
                        @foreach([
                            ['name' => 'LinkedIn', 'url' => '#', 'icon' => 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'],
                            ['name' => 'Twitter', 'url' => '#', 'icon' => 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
                            ['name' => 'GitHub', 'url' => '#', 'icon' => 'M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22'],
                        ] as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 rounded-lg bg-white/5 hover:bg-cyber-500/20 border border-white/10 hover:border-cyber-500/30 flex items-center justify-center transition-all duration-200 group"
                           aria-label="{{ $social['name'] }}">
                            <svg class="w-4 h-4 text-surface-400 group-hover:text-cyber-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $social['icon'] }}"/>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Services --}}
                <div class="lg:col-span-2 lg:col-start-6">
                    <h4 class="text-xs font-semibold uppercase tracking-widest text-surface-500 mb-4">Services</h4>
                    <ul class="space-y-2.5 text-sm">
                        @foreach(['IT Support', 'Cybersecurity', 'Cloud Solutions', 'Network Engineering', 'Web Development', 'Digital Marketing'] as $svc)
                        <li>
                            <a href="{{ route('services.index') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">{{ $svc }}</a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Company --}}
                <div class="lg:col-span-2">
                    <h4 class="text-xs font-semibold uppercase tracking-widest text-surface-500 mb-4">Company</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('about') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">About Us</a></li>
                        <li><a href="{{ route('industries') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Industries</a></li>
                        <li><a href="{{ route('offices') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Global Offices</a></li>
                        <li><a href="{{ route('case-studies') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Case Studies</a></li>
<li><a href="{{ route('portfolio.index') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Portfolio</a></li>
                        <li><a href="{{ route('careers') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Careers</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Pricing</a></li>
                        <li><a href="{{ route('blog.index') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Blog</a></li>
                        <li><a href="{{ route('kb.index') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Knowledge Base</a></li>
                        <li><a href="{{ route('faq') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">FAQ</a></li>
                        <li><a href="{{ route('get-quote') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Get a Quote</a></li>
                        <li><a href="{{ route('contact') }}" class="text-surface-400 hover:text-white transition-colors cyber-underline">Contact</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div class="lg:col-span-3">
                    <h4 class="text-xs font-semibold uppercase tracking-widest text-surface-500 mb-4">Get in Touch</h4>
                    <ul class="space-y-3.5 text-sm">
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-cyber-500/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-cyber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] text-surface-500 uppercase tracking-wide mb-0.5">Email</div>
                                <a href="mailto:info@techsupport.com" class="text-surface-300 hover:text-cyber-400 transition-colors">info@techsupport.com</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-cyber-500/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-cyber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] text-surface-500 uppercase tracking-wide mb-0.5">Phone</div>
                                <a href="tel:+15551234567" class="text-surface-300 hover:text-cyber-400 transition-colors">+1 (555) 123-4567</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-cyber-500/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-cyber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-[11px] text-surface-500 uppercase tracking-wide mb-0.5">Support</div>
                                <span class="text-surface-300">24/7 Available</span>
                            </div>
                        </li>
                    </ul>

                    {{-- CTA --}}
                    <div class="mt-6">
                        <a href="{{ route('contact') }}" class="btn text-sm px-5 py-2.5 w-full justify-center text-white rounded-xl"
                           style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                            Get a Free Consultation
                        </a>
                    </div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-surface-600">
                    &copy; {{ date('Y') }} TechSupport Solutions. All rights reserved.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-surface-600">
                    <a href="{{ route('legal.privacy') }}" class="hover:text-surface-400 transition-colors">Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-surface-400 transition-colors">Terms of Service</a>
                    <a href="{{ route('legal.cookies') }}" class="hover:text-surface-400 transition-colors">Cookie Policy</a>
                    <a href="{{ route('legal.refund') }}" class="hover:text-surface-400 transition-colors">Refund Policy</a>
                    <a href="{{ route('legal.sla') }}" class="hover:text-surface-400 transition-colors">SLA</a>
                    <a href="{{ route('legal.accessibility') }}" class="hover:text-surface-400 transition-colors">Accessibility</a>
                </div>
                <div class="flex items-center gap-2 text-xs text-surface-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                    All systems operational
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
