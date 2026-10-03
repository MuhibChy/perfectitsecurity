<!DOCTYPE html>
@php
    // Authenticated background environment (visual only; no permission logic here).
    // Chain: User Role â†’ Authenticated Layout â†’ AuthenticatedBackgroundManager
    // â†’ Environment Configuration â†’ Unique Background Scene.
    $authBgUser = auth()->user();
    $authBgRole = $authBgUser->role ?? 'guest';
    try { $authBgRoute = request()->route()?->getName(); } catch (\Throwable $e) { $authBgRoute = null; }
    $authEnv = \App\Support\AuthenticatedBackgroundManager::resolve($authBgRole, $authBgRoute);
    $authVariant = \App\Support\AuthenticatedBackgroundManager::variant($authBgRoute);
    $roleBg = \App\Support\AuthenticatedBackgroundManager::legacyKey($authEnv);
    // Foreground glove preference (server-truth for the 3D scene + control).
    $glovePref = $authBgUser ? $authBgUser->glovePreference() : ['mode' => 'moving', 'x' => null, 'y' => null];
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $localeDirection ?? 'ltr' }}" class="scroll-smooth" x-data="{ dark: document.documentElement.classList.contains('dark') }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#020617">
    <script>
        (function() {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const dark = saved ? saved === 'dark' : true;
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) { document.documentElement.classList.add('dark'); }
        })();
    </script>
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Enterprise IT Support & Technology Services Platform')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        @media print {
            aside, header, nav, .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .print-panel, .term-panel { box-shadow: none !important; border: 1px solid #ccc !important; break-inside: avoid; }
            main, .flex-1 { margin: 0 !important; padding: 0 !important; }
            a { text-decoration: none !important; color: #000 !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 dark:bg-black dark:text-white font-sans antialiased relative min-h-screen" data-cosmic="false" data-rolebg="{{ $roleBg }}" data-auth-env="{{ $authEnv }}" data-auth-variant="{{ $authVariant }}" data-glove-mode="{{ $glovePref['mode'] }}" @if($glovePref['x'] !== null) data-glove-x="{{ $glovePref['x'] }}" data-glove-y="{{ $glovePref['y'] }}" @endif data-lights="app" data-3d="{{ request()->routeIs('admin.financials.*', 'admin.invoices.*', 'admin.expenses.*', 'admin.reports.*', 'portal.invoices.*', 'portal.orders.*') ? 'subtle' : 'full' }}">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <noscript><style>.reveal,.reveal-left,.reveal-right{opacity:1 !important;transform:none !important;}</style></noscript>

    {{-- Global solar-system universe (one instance; inherited by every page) --}}
    <x-global-space-background />

    {{-- Authenticated unique environment (Layer 3): canvas â†’ vignette â†’ overlay â†’ glass UI --}}
    <x-authenticated-background />

    {{-- Global 3D world (one fixed WebGL scene; scroll-aware, subtle on finance) --}}
    <x-global-3d-scene />

    {{-- Global fixed HUD frame: one command-center backdrop site-wide --}}
    <x-global-hud-frame />

    {{-- Workspace ambient lighting (complements the role 3D environment) --}}
    <div class="page-lights" aria-hidden="true"><div class="pl-blob pl-a"></div><div class="pl-blob pl-b"></div><div class="pl-blob pl-c"></div></div>

    <div x-data="sidebar()" class="flex min-h-screen relative z-10">

        {{-- Mobile Overlay --}}
        <div x-show="mobileOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:leave="transition-opacity ease-linear duration-300" @click="mobileOpen = false" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden" style="display: none;"></div>

        {{-- Sidebar --}}
        <aside :class="[collapsed ? 'w-20' : 'w-64', mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']" class="fixed left-0 top-0 z-40 h-screen bg-white dark:bg-black border-r border-gray-300 dark:border-[#00FF00]/30 transition-all duration-300 flex flex-col" aria-label="Primary">

            {{-- Logo --}}
            <div class="flex items-center gap-3 px-4 h-16 border-b border-gray-200 dark:border-white/10">
                <a href="{{ auth()->user() && auth()->user()->isCustomer() ? route('portal.dashboard') : route('admin.dashboard') }}" class="flex items-center gap-3" aria-label="Dashboard home">
                    <div class="w-9 h-9 border border-accent/50 bg-accent/10 flex items-center justify-center flex-shrink-0 relative" aria-hidden="true">
                        <span class="absolute -top-px -left-px w-2 h-2 border-t border-l border-accent"></span>
                        <span class="absolute -bottom-px -right-px w-2 h-2 border-b border-r border-accent"></span>
                        <svg class="w-5 h-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.2 12l1.9 1.9L15 10"/>
                        </svg>
                    </div>
                    <span x-show="!collapsed" x-transition class="leading-none whitespace-nowrap">
                        <span class="block font-display font-extrabold text-[13px] tracking-tight text-slate-900 dark:text-white">PERFECT<span class="text-emerald-700 dark:text-accent">IT</span>SECURITY</span>
                        <span class="shell-logo-sub block font-mono text-[8px] tracking-[0.28em] text-slate-500 dark:text-term-700 uppercase mt-1">OPS://CONSOLE</span>
                    </span>
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @php $user = auth()->user(); @endphp

                @if($user && $user->isAdmin())
                    <a href="{{ route('admin.manual') }}" target="_blank" class="shell-link">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span x-show="!collapsed" x-transition>User Manual</span>
                    </a>
                @endif

                @if($user && $user->isCustomer())
                    {{-- Customer navigation: rendered ONLY from CustomerNavigation
                         (role → capability → visible items). Route + policy
                         middleware remains the security boundary. --}}
                    <div x-show="!collapsed" class="shell-group-label px-2 pt-1 pb-2" aria-hidden="true">CLIENT://COMMAND</div>
                    @foreach(\App\Support\CustomerNavigation::for($user) as $navItem)
                    <a href="{{ route($navItem['route']) }}" @if(!empty($navItem['target'])) target="{{ $navItem['target'] }}" @endif class="shell-link {{ !empty($navItem['isActive']) ? 'is-active' : '' }} @if(!empty($navItem['highlight'])) !border-amber-500/40 !bg-amber-500/10 @endif">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $navItem['icon'] }}"/></svg>
                        <span x-show="!collapsed" x-transition @if(!empty($navItem['highlight'])) class="flex items-center justify-between w-full" @endif>
                            <span>{{ $navItem['label'] }}</span>
                            @if(!empty($navItem['highlight']))<span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>@endif
                        </span>
                    </a>
                    @endforeach
                @else
                    {{-- Staff navigation: Dashboard + 5 collapsible groups (single reusable component) --}}
                    <x-admin-sidebar-nav :user="$user" />

                @endif
            </nav>

            {{-- Sidebar Toggle --}}
            <div class="p-3 border-t border-gray-200 dark:border-white/10">
                <button @click="toggle()" class="shell-link w-full !justify-center" aria-label="Toggle sidebar" aria-expanded="false" aria-controls="sidebar-nav">
                    <svg x-show="!collapsed" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                    <svg x-show="collapsed" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                </button>
            </div>
        </aside>

        {{-- Main Content Workspace (Dynamically Expands when sidebar collapses) --}}
        <div :class="collapsed ? 'lg:ml-20' : 'lg:ml-64'" class="flex-1 w-full min-w-0 transition-all duration-300 flex flex-col">

            {{-- Header --}}
            <header class="sticky top-0 z-[1000] bg-white dark:bg-black border-b border-gray-300 dark:border-[#00FF00]/30">
                <div class="flex items-center justify-between h-14 px-4 lg:px-8 w-full">
                    <div class="flex items-center gap-3 min-w-0">
                        <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-gray-600 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sidebar-nav">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <span class="term-status hidden md:inline-flex flex-shrink-0" aria-hidden="true"><span class="term-status-dot"></span>OPS://ONLINE</span>
                        <span class="hidden md:block w-px h-4 bg-gray-300 dark:bg-gray-700" aria-hidden="true"></span>
                        <h1 class="text-base lg:text-lg font-bold font-display tracking-tight text-black dark:text-white truncate">@yield('page-title', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center gap-1.5">
                        {{-- Global search (role-scoped results server-side) --}}
                        @if(auth()->user()->isCustomer())
                        <form method="GET" action="{{ route('portal.search.index') }}" role="search" class="hidden md:block">
                            <input type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Search tickets, invoices, orders…" aria-label="Global search" class="term-input !w-52 !py-1.5 !text-xs">
                        </form>
                        @elseif(auth()->user()->isStaff())
                        <form method="GET" action="{{ route('admin.search.index') }}" role="search" class="hidden md:block">
                            <input type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Search customers, TK-, INV-, ORD-…" aria-label="Global search" class="term-input !w-52 !py-1.5 !text-xs">
                        </form>
                        @endif
                        {{-- Notification Bell --}}
                        @if(auth()->user()->isCustomer())
                        <a href="{{ route('portal.notifications.index') }}" class="relative p-2 text-gray-600 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors" aria-label="Notifications">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </a>
                        @endif

                        {{-- Dark Mode Toggle --}}
                        <button @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('theme', dark ? 'dark' : 'light'); window.dispatchEvent(new CustomEvent('theme-changed', {detail:{dark:dark}}))" class="p-2 text-gray-600 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors" aria-label="Toggle color theme">
                            <svg x-show="!dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <svg x-show="dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </button>

                        {{-- User Menu --}}
                        @auth
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 p-1 hover:bg-gray-100 dark:hover:bg-[#00FF00]/10">
                                <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 object-cover" alt="{{ auth()->user()->name }} profile avatar">
                                <span class="hidden sm:block text-sm font-medium text-black dark:text-white">{{ auth()->user()->name }}</span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-52 bg-white dark:bg-black border border-gray-300 dark:border-[#00FF00]/30 shadow-lg py-2" style="display: none;">
                                <div class="px-4 py-2 font-mono text-[10px] uppercase tracking-[0.2em] text-gray-600 dark:text-gray-500">SESSION://USER</div>
                                <a href="{{ auth()->user()->isCustomer() ? route('portal.profile.edit') : route('admin.my-profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-black dark:text-gray-300 dark:hover:bg-[#00FF00]/10 dark:hover:text-white {{ request()->routeIs('portal.profile.*', 'admin.my-profile.*') ? 'bg-gray-100 dark:bg-[#00FF00]/10 font-semibold' : '' }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Profile Settings
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-black dark:text-gray-300 dark:hover:bg-[#00FF00]/10 dark:hover:text-white">Settings</a>
                                <a href="{{ route('admin.health.index') }}" class="block px-4 py-2 text-sm text-green-700 hover:bg-green-50 dark:text-[#00FF00] dark:hover:bg-[#00FF00]/10">System Health</a>
                                @endif
                                @if(auth()->user()->isStaff())
                                <a href="{{ route('mfa.setup') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-black dark:text-gray-300 dark:hover:bg-[#00FF00]/10 dark:hover:text-white">Two-Factor Auth</a>
                                @endif
                                <hr class="my-1 border-gray-300 dark:border-gray-700">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-[#00FF00]/10 dark:hover:text-red-300">Logout →</button>
                                </form>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mx-4 lg:mx-8 mt-4">
                <div class="term-alert term-alert-ok" role="status">
                    <span class="term-alert-tag">SYS://OK</span>
                    <span class="text-slate-800 dark:text-slate-100">{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mx-4 lg:mx-8 mt-4">
                <div class="term-alert term-alert-err" role="alert">
                    <span class="term-alert-tag">SYS://ERR</span>
                    <span class="text-slate-800 dark:text-slate-100">{{ session('error') }}</span>
                </div>
            </div>
            @endif

            {{-- Page Content (Full available workspace) --}}
            <main id="main-content" class="flex-1 p-4 lg:p-8 w-full">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- AI Chat Widget (all authenticated users; backend enforces per-role access) --}}
    <x-ai-chat-widget />

    {{-- Foreground glove control (authenticated users only; guests stay moving) --}}
    <x-glove-control />

    @stack('scripts')

    {{-- Frontend Error Telemetry Script --}}
    <script>
        window.addEventListener('error', function (e) {
            try {
                fetch('{{ route("api.health.frontend-error") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: e.message || 'Frontend JS Error',
                        url: e.filename || window.location.href,
                        line: e.lineno || null,
                        col: e.colno || null,
                        stack: e.error ? e.error.stack : null
                    })
                });
            } catch (err) {}
        });
    </script>
</body>
</html>
