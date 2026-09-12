@extends('layouts.public')

@section('title', 'About Us — TechSupport Solutions')
@section('description', 'Enterprise IT infrastructure, cybersecurity, and cloud engineering company. Our team delivers technology solutions for organizations worldwide.')

@section('content')

{{-- Hero --}}
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                About TechSupport Solutions
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                We Engineer the <span class="gradient-text-cyber">Unshakeable Backbone</span> of Modern Business.
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal">
                TechSupport Solutions delivers enterprise-grade IT infrastructure, predictive cybersecurity, and cloud engineering. We are trusted by organizations that demand continuous reliability, rigorous security compliance, and uncompromising operational uptime.
            </p>
        </div>
    </div>
</section>

{{-- Mission & Vision (Wide 2-Column Glass Panels) --}}
<section class="relative w-full py-24 bg-[#030712] border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
            <div class="cosmic-glass p-10 sm:p-12 rounded-3xl border border-white/10">
                <div class="inline-flex items-center gap-2 text-xs font-mono font-bold text-cyan-400 mb-4 uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    Operational Mandate
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white mb-6">Our Mission</h2>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                    We believe technology should be an absolute competitive accelerator, never an operational bottleneck. Our mission is to engineer enterprise IT environments that are self-healing, bulletproof against ransomware, and meticulously aligned with your strategic business trajectory.
                </p>
            </div>
            <div class="cosmic-glass p-10 sm:p-12 rounded-3xl border border-white/10">
                <div class="inline-flex items-center gap-2 text-xs font-mono font-bold text-violet-400 mb-4 uppercase tracking-widest">
                    <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                    Long-Term Horizon
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white mb-6">Our Vision</h2>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                    To be the foremost technology and cyber protection ally for forward-thinking enterprises. We establish lifelong corporate partnerships through uncompromising engineering transparency, rapid SLA adherence, and proactive zero-trust defense frameworks.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Infrastructure & Capabilities --}}
<section class="relative w-full py-24 lg:py-32 bg-space-deep border-b border-white/10 z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            
            <div class="lg:col-span-6 relative">
                <div class="rounded-3xl overflow-hidden aspect-[4/3] border border-white/10 relative shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=1200&q=80&auto=format&fit=crop" alt="Enterprise data center" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#030712] via-transparent to-transparent opacity-80"></div>
                </div>

                {{-- Floating Telemetry Card --}}
                <div class="absolute -bottom-6 -right-4 lg:right-6 cosmic-glass p-6 rounded-2xl border border-cyan-500/30 backdrop-blur-2xl shadow-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center text-cyan-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-white">Tier-1 Response</div>
                            <div class="text-xs text-cyan-400 font-mono">15-minute Critical SLA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400">
                    Infrastructure Caliber
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                    Architected for Resilience at Every Digital Layer.
                </h2>
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                    Our Global Security Operations Center (SOC) operates 24/7/365 with active telemetry analysis, automated containment playbooks, and redundant cloud clusters across tier-IV certified data centers worldwide.
                </p>

                <div class="space-y-4 pt-2">
                    @foreach([
                        'Tier IV certified data centers with N+2 power, cooling, and network redundancy',
                        'Real-time automated heuristic telemetry across all server clusters and cloud endpoints',
                        'ISO 27001, SOC 2 Type II, HIPAA, and GDPR aligned security governance',
                        'Automated micro-segmentation and instantaneous ransomware rollback mechanisms'
                    ] as $point)
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white/[0.02] border border-white/5">
                        <div class="w-5 h-5 rounded-full bg-cyan-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-medium text-slate-300">{{ $point }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Core Values (Wide 4-Column Grid) --}}
<section class="relative w-full py-24 bg-[#020617] z-10">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-3">
                Guiding Principles
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">Our Engineering Ethos.</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['title' => 'Integrity & Transparency', 'desc' => 'Every invoice, quotation milestone, and SLA report is open, verifiable, and free of hidden costs.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['title' => 'Technical Excellence', 'desc' => 'Certified architects across Microsoft Azure, AWS, Cisco, and offensive cybersecurity standards.', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['title' => 'Proactive Defense', 'desc' => 'We prevent threats before they materialize rather than simply reacting to downtime disruptions.', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['title' => 'Relentless Client Focus', 'desc' => 'Your business uptime is our primary KPI. We stand beside your team around the clock, every day.', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as $val)
            <div class="p-8 rounded-3xl bg-white/[0.03] border border-white/10 hover:border-cyan-500/40 transition-all flex flex-col justify-between">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $val['icon'] }}"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">{{ $val['title'] }}</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">{{ $val['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-24 bg-space-radial border-t border-white/10 z-10 text-center">
    <div class="w-full max-w-4xl mx-auto px-6">
        <h2 class="text-3xl sm:text-5xl font-black text-white mb-6">Work with Certified Infrastructure Specialists</h2>
        <p class="text-slate-300 text-lg mb-8 max-w-2xl mx-auto">Get in touch with our solutions engineering team to review your current architecture and security posture.</p>
        <a href="{{ route('contact') }}" class="btn btn-lg text-white font-bold px-8 py-4 rounded-2xl" style="background: linear-gradient(135deg, #16A34A, #2563EB);">
            Contact Our Engineering Team
        </a>
    </div>
</section>

@endsection
