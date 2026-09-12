@extends('layouts.public')

@section('title', config('app.name') . ' — Secure Your Business. Build Smarter Technology. Grow Without Limits.')
@section('description', 'Enterprise IT support, cybersecurity, cloud solutions, and managed services. Trusted by 250+ organisations worldwide. 24/7 monitoring, fast response, zero-compromise security.')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     HERO — Full-Width Cinematic 3D Cosmic Experience
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative min-h-screen w-full flex items-center overflow-hidden bg-[#030712]">

    {{-- Cinematic 3D scene (global reusable component; same implementation everywhere) --}}
    <x-hero-scene />

    {{-- Cosmic Atmospheric Overlays --}}
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse 80% 60% at 75% 45%, rgba(37,99,235,0.12) 0%, transparent 60%), radial-gradient(ellipse 60% 70% at 20% 60%, rgba(92,124,250,0.1) 0%, transparent 60%), radial-gradient(circle 500px at 50% 10%, rgba(168,85,247,0.08) 0%, transparent 70%);"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-[#030712]/90 via-[#030712]/40 to-transparent pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-[#030712] via-[#030712]/70 to-transparent pointer-events-none"></div>

    {{-- Scan line effect --}}
    <div class="scan-line opacity-25" aria-hidden="true"></div>

    {{-- Main Content Container (Full Width Expansive) --}}
    <div class="relative z-10 w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 xl:px-20 pt-28 pb-20 min-h-screen flex items-center">
        <div class="w-full grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">

            {{-- Left Column: High-Impact Typography & Action Suite --}}
            <div class="lg:col-span-7 xl:col-span-7 space-y-8">

                {{-- Eyebrow badge --}}
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-cyan-400/30 bg-cyan-950/40 backdrop-blur-xl animate-fade-in shadow-[0_0_20px_rgba(37,99,235,0.2)]">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan-500"></span>
                    </span>
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Next-Gen Cybersecurity & Cloud Infrastructure</span>
                </div>

                {{-- Main headline --}}
                <h1 class="text-4xl sm:text-6xl lg:text-7xl xl:text-[80px] font-black tracking-tight leading-[1.04] text-white animate-slide-up">
                    Secure Your Business.<br>
                    <span class="gradient-text-cyber drop-shadow-[0_0_35px_rgba(37,99,235,0.35)]">Build Smarter</span><br>
                    Technology.
                </h1>

                {{-- Sub-headline --}}
                <p class="text-lg sm:text-xl text-slate-300/90 leading-relaxed max-w-2xl font-normal animate-slide-up" style="animation-delay: 0.15s;">
                    Enterprise-grade IT support, proactive cyber defense, and high-performance cloud architecture. We engineer and protect mission-critical environments with 24/7/365 active monitoring and instant SLA response.
                </p>

                {{-- CTAs --}}
                <div class="flex flex-wrap items-center gap-4 pt-2 animate-slide-up" style="animation-delay: 0.25s;">
                    <a href="{{ route('contact') }}"
                       class="btn btn-lg text-white rounded-2xl text-base px-8 py-4 font-semibold transition-all duration-300 hover:scale-105"
                       style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 10px 35px rgba(37,99,235,0.45);">
                        Get Free Security Consultation
                        <svg class="w-5 h-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('services.index') }}"
                       class="btn btn-lg btn-glass rounded-2xl text-base px-8 py-4 font-semibold border-white/20 hover:border-cyan-400/50 hover:bg-white/10 transition-all duration-300">
                        Explore All Solutions
                    </a>
                </div>

                {{-- Trust indicators --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-white/10 animate-fade-in" style="animation-delay: 0.4s;">
                    @foreach([
                        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'ISO 27001 Aligned', 'sub' => 'Security Standard'],
                        ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => '24/7/365 SOC', 'sub' => 'Continuous Watch'],
                        ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'label' => '< 15min Response', 'sub' => 'Critical Incident SLA'],
                        ['icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'label' => 'Global Coverage', 'sub' => 'Multi-Region Support'],
                    ] as $trust)
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trust['icon'] }}"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-white tracking-tight">{{ $trust['label'] }}</div>
                            <div class="text-[11px] text-slate-400">{{ $trust['sub'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right Column: Interactive 3D Cyber HUD & Live Telemetry Panel --}}
            <div class="lg:col-span-5 xl:col-span-5 flex flex-col items-center lg:items-end justify-center space-y-4">
                
                {{-- Live Cyber Command Widget (floating over 3D planet & shield) --}}
                <div class="w-full max-w-md cosmic-glass p-6 rounded-3xl border border-white/10 backdrop-blur-2xl shadow-2xl relative overflow-hidden group hover:border-cyan-400/40 transition-all duration-500">
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    
                    {{-- Widget Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div class="flex items-center gap-2.5">
                            <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-xs font-bold uppercase tracking-widest text-white">Live Infrastructure Telemetry</span>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-1 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">SOC-1 ONLINE</span>
                    </div>

                    {{-- Metrics Grid --}}
                    <div class="grid grid-cols-2 gap-3.5 my-4">
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/5">
                            <div class="text-[11px] text-slate-400 uppercase tracking-wider mb-1">Global Threat Level</div>
                            <div class="text-lg font-bold text-emerald-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                DEFCON 5 (SECURE)
                            </div>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/5">
                            <div class="text-[11px] text-slate-400 uppercase tracking-wider mb-1">Uptime SLA</div>
                            <div class="text-lg font-bold text-cyan-300">99.998%</div>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/5">
                            <div class="text-[11px] text-slate-400 uppercase tracking-wider mb-1">Encrypted Endpoints</div>
                            <div class="text-lg font-bold text-violet-300">14,280+</div>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/5">
                            <div class="text-[11px] text-slate-400 uppercase tracking-wider mb-1">Mean Response</div>
                            <div class="text-lg font-bold text-amber-300">4.2 min</div>
                        </div>
                    </div>

                    {{-- Activity Stream --}}
                    <div class="space-y-2 pt-2 text-xs">
                        <div class="flex items-center justify-between text-slate-300 py-1 px-2.5 rounded-lg bg-black/30 font-mono text-[11px]">
                            <span class="flex items-center gap-1.5 text-cyan-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                                Cloud Firewall Sync
                            </span>
                            <span class="text-slate-500">2s ago</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300 py-1 px-2.5 rounded-lg bg-black/30 font-mono text-[11px]">
                            <span class="flex items-center gap-1.5 text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Zero-Trust Token Re-Auth
                            </span>
                            <span class="text-slate-500">8s ago</span>
                        </div>
                    </div>
                </div>

                {{-- Fast Security Scan Banner --}}
                <div class="w-full max-w-md p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 backdrop-blur-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-cyan-500/20 flex items-center justify-center text-cyan-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-white">Need Urgent IT Assistance?</div>
                            <div class="text-[11px] text-cyan-300/80">Direct dispatch line active 24/7</div>
                        </div>
                    </div>
                    <a href="{{ route('contact') }}" class="text-xs font-bold px-3.5 py-1.5 rounded-xl bg-cyan-500 text-black hover:bg-cyan-400 transition-colors shadow-lg shadow-cyan-500/30">
                        Call SOC
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce opacity-70 z-20 pointer-events-none" aria-hidden="true">
        <span class="text-[10px] font-mono uppercase tracking-[0.25em] text-cyan-400">Explore Platform</span>
        <div class="w-5 h-8 rounded-full border border-cyan-400/40 flex items-start justify-center pt-1.5 bg-black/40 backdrop-blur-sm">
            <div class="w-1 h-2 rounded-full bg-cyan-400 animate-bounce"></div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     STATS RIBBON — Full-Width Glassmorphism Metrics
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative w-full border-y border-white/10 bg-[#040816]/90 backdrop-blur-2xl py-12 z-20">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12">
            @foreach([
                ['value' => '500', 'suffix' => '+', 'label' => 'Enterprise Deployments', 'desc' => 'Projects successfully delivered globally', 'color' => 'text-cyan-400', 'border' => 'border-cyan-500/20'],
                ['value' => '99.99', 'suffix' => '%', 'label' => 'Guaranteed SLA Uptime', 'desc' => 'High-availability failover architecture', 'color' => 'text-emerald-400', 'border' => 'border-emerald-500/20'],
                ['value' => '250', 'suffix' => '+', 'label' => 'Retained Enterprise Clients', 'desc' => 'Across healthcare, finance & technology', 'color' => 'text-violet-400', 'border' => 'border-violet-500/20'],
                ['value' => '100', 'suffix' => '+', 'label' => 'Standardized IT Services', 'desc' => 'Fixed-scope and customizable catalog', 'color' => 'text-blue-400', 'border' => 'border-blue-500/20'],
            ] as $stat)
            <div class="p-6 rounded-2xl bg-white/[0.03] border {{ $stat['border'] }} hover:bg-white/[0.06] transition-all duration-300">
                <div class="text-3xl sm:text-4xl lg:text-5xl font-black {{ $stat['color'] }} tracking-tight mb-2">
                    {{ $stat['value'] }}<span class="text-2xl lg:text-3xl">{{ $stat['suffix'] }}</span>
                </div>
                <div class="text-sm font-bold text-white mb-1">{{ $stat['label'] }}</div>
                <div class="text-xs text-slate-400 leading-relaxed">{{ $stat['desc'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     SERVICES — Full-Width 3D Cosmic Service Cards
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative w-full py-24 lg:py-32 overflow-hidden bg-space-deep z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">

        {{-- Section Header --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8 mb-16">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 mb-4">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    <span class="text-xs font-semibold uppercase tracking-widest text-cyan-300">Comprehensive Solutions Matrix</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                    End-to-End Technology & Cyber Defense for Tomorrow's Enterprise.
                </h2>
                <p class="text-base sm:text-lg text-slate-400 mt-4 leading-relaxed">
                    Explore our integrated service catalog engineered for enterprise stability, rapid incident containment, and frictionless cloud scalability.
                </p>
            </div>
            <a href="{{ route('services.index') }}" class="btn btn-lg btn-glass rounded-2xl whitespace-nowrap self-start lg:self-end text-sm">
                View Complete 100+ Catalog
                <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        {{-- Service category cards --}}
        @php
            $serviceCategories = App\Models\ServiceCategory::where('is_active', true)->orderBy('sort_order')->take(6)->get();
            $categoryIcons = [
                'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z',
                'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
                'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z',
            ];
            $categoryGlows = [
                ['color' => '#22C55E', 'accent' => 'rgba(34, 197, 94, 0.15)', 'tag' => 'SOC 2 & ISO Ready'],
                ['color' => '#3B82F6', 'accent' => 'rgba(59, 130, 246, 0.15)', 'tag' => '24/7 Remote & Onsite'],
                ['color' => '#a855f7', 'accent' => 'rgba(168, 85, 247, 0.15)', 'tag' => 'AWS & Azure Native'],
                ['color' => '#10b981', 'accent' => 'rgba(16, 185, 129, 0.15)', 'tag' => 'Zero Downtime'],
                ['color' => '#f59e0b', 'accent' => 'rgba(245, 158, 11, 0.15)', 'tag' => 'Full-Stack Scalable'],
                ['color' => '#ef4444', 'accent' => 'rgba(239, 68, 68, 0.15)', 'tag' => 'Growth & Conversion'],
            ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @if($serviceCategories->isNotEmpty())
                @foreach($serviceCategories as $i => $cat)
                @php $glow = $categoryGlows[$i % count($categoryGlows)]; @endphp
                <div class="cosmic-card p-8 flex flex-col justify-between group h-full">
                    <div>
                        {{-- Card Header: Icon & Tag --}}
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 group-hover:scale-110"
                                 style="background: {{ $glow['accent'] }}; border: 1px solid {{ $cat->color ?? $glow['color'] }}40; box-shadow: 0 0 20px {{ $glow['accent'] }};">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $cat->color ?? $glow['color'] }}">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $categoryIcons[$i % count($categoryIcons)] }}"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-mono px-3 py-1 rounded-full border border-white/10 text-slate-300 bg-white/5">
                                {{ $glow['tag'] }}
                            </span>
                        </div>

                        {{-- Title & Description --}}
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors">
                            {{ $cat->name }}
                        </h3>
                        <p class="text-sm text-slate-400 leading-relaxed line-clamp-3 mb-6">
                            {{ $cat->description ?? 'Enterprise-grade ' . $cat->name . ' services tailored to ensure peak operational resilience, security compliance, and maximum cost efficiency.' }}
                        </p>
                    </div>

                    {{-- Actions Row --}}
                    <div class="pt-5 border-t border-white/10 flex items-center justify-between mt-auto">
                        <a href="{{ route('services.index') }}?category={{ $cat->slug }}"
                           class="text-sm font-semibold text-cyan-400 hover:text-white flex items-center gap-1.5 transition-colors">
                            View Category
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('contact') }}?service={{ $cat->slug }}"
                           class="text-xs font-bold px-3.5 py-1.5 rounded-xl border border-cyan-400/40 text-cyan-300 hover:bg-cyan-500 hover:text-black transition-all">
                            Consult Now
                        </a>
                    </div>
                </div>
                @endforeach
            @else
                {{-- Fallback services --}}
                @foreach([
                    ['name' => 'Enterprise Cybersecurity', 'desc' => 'Continuous threat detection, incident triage, penetration auditing, and proactive security architecture.', 'color' => '#16A34A', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['name' => 'Managed IT Infrastructure', 'desc' => 'High-reliability network administration, active hardware monitoring, and comprehensive 24/7 helpdesk.', 'color' => '#3B82F6', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['name' => 'Cloud Architecture & DevOps', 'desc' => 'Multi-cloud migration, automated Kubernetes orchestration, disaster recovery replication, and FinOps.', 'color' => '#a855f7', 'icon' => 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
                    ['name' => 'Network & Zero-Trust Defense', 'desc' => 'SD-WAN topology, next-gen hardware firewalls, micro-segmentation, and high-throughput VPN tunnels.', 'color' => '#10b981', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
                    ['name' => 'Enterprise Web Applications', 'desc' => 'Mission-critical portals, customer billing ecosystems, custom microservices, and high-load APIs.', 'color' => '#f59e0b', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                    ['name' => 'Data Protection & Compliance', 'desc' => 'Automated immutable backups, GDPR/HIPAA compliance readiness, and rapid ransomware rollback.', 'color' => '#ef4444', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z'],
                ] as $svc)
                <div class="cosmic-card p-8 flex flex-col justify-between group h-full">
                    <div>
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6"
                             style="background: {{ $svc['color'] }}15; border: 1px solid {{ $svc['color'] }}40;">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $svc['color'] }}">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $svc['icon'] }}"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors">{{ $svc['name'] }}</h3>
                        <p class="text-sm text-slate-400 leading-relaxed mb-6">{{ $svc['desc'] }}</p>
                    </div>
                    <div class="pt-5 border-t border-white/10 flex items-center justify-between mt-auto">
                        <a href="{{ route('services.index') }}" class="text-sm font-semibold text-cyan-400 hover:text-white flex items-center gap-1.5">
                            Learn More <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     WHY CHOOSE US — Full-Width Deep Space Showcase
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative w-full py-24 lg:py-32 bg-[#020617] border-y border-white/10 overflow-hidden z-10">
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="absolute top-1/3 -left-32 w-96 h-96 bg-cyan-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">

            {{-- Left: Enterprise Features --}}
            <div class="lg:col-span-7 space-y-8">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-4">
                        Enterprise Advantage
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                        Discipline, Precision, and <span class="gradient-text-cyber">Zero-Compromise Security.</span>
                    </h2>
                    <p class="text-base sm:text-lg text-slate-400 mt-4 leading-relaxed">
                        We don't provide generic ticketing. Every deployment is managed by certified cybersecurity architects, AWS/Azure specialists, and seasoned infrastructure engineers dedicated to your uptime.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach([
                        ['title' => 'Predictive Threat Hunting', 'desc' => 'AI-driven heuristic detection uncovers zero-day vectors before attackers strike.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'text-cyan-400'],
                        ['title' => 'Rapid Containment SLA', 'desc' => 'Critical security incidents isolated in under 15 minutes by live SOC engineers.', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'text-blue-400'],
                        ['title' => 'Predictable Fixed Scope', 'desc' => 'Zero surprise invoices. Transparent work orders with approved price milestones.', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-400'],
                        ['title' => 'Compliance Governance', 'desc' => 'Built-in audit trails, ISO 27001, GDPR, and HIPAA compliance verification.', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'text-violet-400'],
                    ] as $feat)
                    <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-cyan-500/30 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 {{ $feat['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feat['icon'] }}"/></svg>
                        </div>
                        <div class="font-bold text-white text-base mb-1">{{ $feat['title'] }}</div>
                        <div class="text-xs text-slate-400 leading-relaxed">{{ $feat['desc'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: Visual Architecture Topology --}}
            <div class="lg:col-span-5 flex justify-center">
                <div class="w-full max-w-lg cosmic-glass p-8 rounded-3xl border border-cyan-500/20 relative">
                    <div class="text-xs font-mono uppercase tracking-widest text-cyan-400 mb-4 pb-3 border-b border-white/10 flex items-center justify-between">
                        <span>Multi-Cloud Security Perimeter</span>
                        <span class="text-emerald-400">ENFORCED</span>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 rounded-xl bg-black/40 border border-cyan-500/30 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center text-cyan-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">Zero Trust Gateway</div>
                                    <div class="text-[11px] text-slate-400">Contextual MFA & Geo-Fencing</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-cyan-300">ACTIVE</span>
                        </div>

                        <div class="p-4 rounded-xl bg-black/40 border border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">Hybrid Cloud Mesh</div>
                                    <div class="text-[11px] text-slate-400">AWS / Azure / On-Premise Encrypted</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-blue-300">SYNCED</span>
                        </div>

                        <div class="p-4 rounded-xl bg-black/40 border border-white/10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-white">Immutable Backup Vault</div>
                                    <div class="text-[11px] text-slate-400">Air-Gapped Ransomware Safe</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-purple-300">LOCKED</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-5 border-t border-white/10 flex items-center justify-between">
                        <span class="text-xs text-slate-400">Telemetry Sampling Rate</span>
                        <span class="text-xs font-mono text-cyan-400">100ms real-time</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     12-STEP PROCESS — Full-Width Operational Sequence
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative w-full py-24 lg:py-32 bg-[#030712] z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-4">
                Structured Lifecycle
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                Our Proven 12-Step Delivery Framework.
            </h2>
            <p class="text-base sm:text-lg text-slate-400 mt-4 leading-relaxed">
                Every project and ticket follows strict enterprise lifecycle stages with transparent customer checkpoints, verified deliverables, and formal sign-offs.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach([
                ['step' => '01', 'title' => 'Consultation', 'desc' => 'Discovery & strategic objectives alignment.'],
                ['step' => '02', 'title' => 'Tech Audit', 'desc' => 'Vulnerability & architecture inspection.'],
                ['step' => '03', 'title' => 'Quotation', 'desc' => 'Transparent fixed price & defined milestones.'],
                ['step' => '04', 'title' => 'Agreement', 'desc' => 'Proposal approval & digital sign-off.'],
                ['step' => '05', 'title' => 'Escrow Auth', 'desc' => 'Secure deposit & receipt issuance.'],
                ['step' => '06', 'title' => 'IT Ticket', 'desc' => 'Automated ticket creation with SLA clock.'],
                ['step' => '07', 'title' => 'Lead Assign', 'desc' => 'Certified specialist dedicated to task.'],
                ['step' => '08', 'title' => 'Execution', 'desc' => 'Work carried out with live portal updates.'],
                ['step' => '09', 'title' => 'QA & Security', 'desc' => 'Penetration check & regression testing.'],
                ['step' => '10', 'title' => 'Client Signoff', 'desc' => 'Acceptance testing and formal handover.'],
                ['step' => '11', 'title' => 'Reconciliation', 'desc' => 'Final invoice, balance & financial receipt.'],
                ['step' => '12', 'title' => '24/7 Monitoring', 'desc' => 'Continuous telemetry & SOC supervision.'],
            ] as $step)
            <div class="p-5 rounded-2xl bg-white/[0.02] border border-white/5 hover:border-cyan-500/40 hover:bg-white/[0.05] transition-all flex flex-col justify-between group">
                <div>
                    <span class="text-xs font-mono font-bold text-cyan-400 block mb-2">STAGE {{ $step['step'] }}</span>
                    <h4 class="text-base font-bold text-white mb-1.5 group-hover:text-cyan-300 transition-colors">{{ $step['title'] }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-end">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400/40 group-hover:bg-cyan-400"></span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     TESTIMONIALS & TRUST — Full-Width Customer Verification
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative w-full py-24 lg:py-32 bg-[#020617] border-y border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-16">
            <div>
                <div class="text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-2">Verified Testimonials</div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">Trusted by Industry Leaders Worldwide.</h2>
            </div>
            <div class="flex items-center gap-2 text-amber-400 text-sm font-semibold">
                <span>★ 4.98 / 5.0 Enterprise Trust Score</span>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
                ['name' => 'Sarah Johnson', 'role' => 'CTO, Nexus Financial Group', 'text' => 'TechSupport Solutions completely fortified our transaction pipelines. Their team detected subtle zero-day vulnerabilities in our legacy core that multiple previous audits missed. Their 15-minute response SLA has saved us hours of potential downtime.', 'avatar' => 'SJ', 'tag' => 'FinTech'],
                ['name' => 'Dr. Marcus Chen', 'role' => 'Director of IT, Apex Healthcare Systems', 'text' => 'Zero security compromises in over two years of high-volume patient data management. Their proactive multi-layered cloud backup gave us complete ransomware peace of mind. Truly a tier-1 technology partner.', 'avatar' => 'MC', 'tag' => 'Healthcare'],
                ['name' => 'Emily Rodriguez', 'role' => 'VP of Engineering, CloudScale Commerce', 'text' => 'We scaled from 50,000 to over 2 million daily transactions with zero server hiccups. Their DevOps engineers designed an auto-healing Kubernetes cluster that reduced our cloud overhead by 34%.', 'avatar' => 'ER', 'tag' => 'E-Commerce'],
            ] as $testimonial)
            <div class="cosmic-glass p-8 rounded-3xl border border-white/10 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex text-amber-400 gap-1">
                            @for($s=0;$s<5;$s++)
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <span class="text-xs font-mono text-cyan-400 px-2.5 py-0.5 rounded-full bg-cyan-500/10 border border-cyan-500/20">
                            {{ $testimonial['tag'] }}
                        </span>
                    </div>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-6 font-normal">
                        "{{ $testimonial['text'] }}"
                    </p>
                </div>
                <div class="pt-5 border-t border-white/10 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center font-bold text-white text-sm">
                        {{ $testimonial['avatar'] }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white">{{ $testimonial['name'] }}</div>
                        <div class="text-xs text-slate-400">{{ $testimonial['role'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     FINAL SUPERNOVA CTA — Full-Width Planetary Glow Experience
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative w-full py-32 lg:py-44 overflow-hidden bg-space-radial z-10">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] rounded-full bg-cyan-500/15 blur-[120px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-blue-600/15 blur-[90px]"></div>
    </div>
    <div class="absolute inset-0 bg-cyber-grid opacity-20 pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-cyan-400/30 bg-cyan-950/50 backdrop-blur-xl mb-8">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs font-semibold uppercase tracking-widest text-cyan-300">Ready to Elevate Your Security Posture?</span>
        </div>

        <h2 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.08] mb-8">
            Protect What You Built.<br>
            <span class="gradient-text-cyber">Scale What You're Building.</span>
        </h2>

        <p class="text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed mb-12">
            Speak directly with a senior IT architect today. Receive an in-depth security posture assessment and customized infrastructure blueprint with zero obligation.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-5">
            <a href="{{ route('contact') }}"
               class="btn btn-lg text-white rounded-2xl text-base px-10 py-5 font-bold transition-all duration-300 hover:scale-105"
               style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 12px 40px rgba(37,99,235,0.5);">
                Schedule Architecture Consultation
                <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="{{ route('services.index') }}"
               class="btn btn-lg btn-glass rounded-2xl text-base px-8 py-5 font-semibold border-white/20 hover:border-cyan-400 hover:bg-white/10 transition-all">
                Explore Solutions
            </a>
        </div>
    </div>
</section>

@endsection
