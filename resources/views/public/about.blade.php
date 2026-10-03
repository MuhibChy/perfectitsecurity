@extends('layouts.public')

@section('title', 'About Us — PerfectITSecurity')
@section('description', 'Enterprise IT infrastructure, cybersecurity, and cloud engineering company. Our team delivers technology solutions for organizations worldwide.')

@section('content')

{{-- HERO — SYSTEM://ABOUT --}}
<section class="relative w-full overflow-hidden" aria-labelledby="about-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">SYSTEM://ABOUT</span>
                <span class="term-tag">PerfectITSecurity</span>
            </div>
            <h1 id="about-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                WE ENGINEER THE <span class="text-accent-soft">UNSHAKEABLE BACKBONE</span> OF MODERN BUSINESS.
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl">
                PerfectITSecurity delivers enterprise-grade IT infrastructure, predictive cybersecurity, and cloud engineering. We are trusted by organizations that demand continuous reliability, rigorous security compliance, and uncompromising operational uptime.
            </p>
        </div>
    </div>
</section>

{{-- Mission & Vision --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Mission and vision">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="01" label="SYSTEM://DIRECTIVE" title="Directive and long-term horizon." desc="What the team is mandated to deliver, every engagement." />
        <div class="mt-10 grid lg:grid-cols-2 gap-4 sm:gap-5">
            <div class="term-panel p-8 sm:p-10">
                <div class="term-sec-label mb-4">MANDATE://MISSION</div>
                <h2 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-navy-900 dark:text-white">Our Mission</h2>
                <p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
                    We believe technology should be an absolute competitive accelerator, never an operational bottleneck. Our mission is to engineer enterprise IT environments that are self-healing, bulletproof against ransomware, and meticulously aligned with your strategic business trajectory.
                </p>
            </div>
            <div class="term-panel p-8 sm:p-10">
                <div class="term-sec-label mb-4">HORIZON://VISION</div>
                <h2 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-navy-900 dark:text-white">Our Vision</h2>
                <p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
                    To be the foremost technology and cyber protection ally for forward-thinking enterprises. We establish lifelong corporate partnerships through uncompromising engineering transparency, rapid SLA adherence, and proactive zero-trust defense frameworks.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Infrastructure & Capabilities --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-labelledby="infra-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-6">
                <div class="term-panel p-0 overflow-hidden">
                    <div class="aspect-[4/3] overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=1200&q=80&auto=format&fit=crop" alt="Enterprise data center" class="w-full h-full object-cover">
                    </div>
                    <div class="px-5 py-4 border-t border-term-300 dark:border-white/5 flex items-center gap-3">
                        <span class="term-status-dot" aria-hidden="true"></span>
                        <div>
                            <div class="font-display text-base font-bold text-navy-900 dark:text-white">Tier-1 Response</div>
                            <div class="font-mono text-[11px] tracking-wider text-accent-soft">15-MINUTE CRITICAL SLA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <span class="term-tag">INFRASTRUCTURE://CALIBER</span>
                <h2 id="infra-heading" class="mt-4 font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-navy-900 dark:text-white text-balance">
                    Architected for resilience at every digital layer.
                </h2>
                <p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
                    Our Global Security Operations Center (SOC) operates 24/7/365 with active telemetry analysis, automated containment playbooks, and redundant cloud clusters across tier-IV certified data centers worldwide.
                </p>

                <ul class="mt-6 space-y-3">
                    @foreach([
                        'Tier IV certified data centers with N+2 power, cooling, and network redundancy',
                        'Real-time automated heuristic telemetry across all server clusters and cloud endpoints',
                        'ISO 27001, SOC 2 Type II, HIPAA, and GDPR aligned security governance',
                        'Automated micro-segmentation and instantaneous ransomware rollback mechanisms'
                    ] as $point)
                    <li class="term-panel-2 px-4 py-3 flex items-start gap-3">
                        <span class="w-1.5 h-1.5 bg-accent flex-shrink-0 mt-1.5" aria-hidden="true"></span>
                        <span class="text-sm text-slate-600 dark:text-term-800">{{ $point }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Core Values --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-labelledby="ethos-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="02" label="SYSTEM://ETHOS" title="Our engineering ethos." desc="Guiding principles behind every work order." />
        <span id="ethos-heading" class="sr-only">Core values</span>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            @foreach([
                ['title' => 'Integrity & Transparency', 'desc' => 'Every invoice, quotation milestone, and SLA report is open, verifiable, and free of hidden costs.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['title' => 'Technical Excellence', 'desc' => 'Certified architects across Microsoft Azure, AWS, Cisco, and offensive cybersecurity standards.', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['title' => 'Proactive Defense', 'desc' => 'We prevent threats before they materialize rather than simply reacting to downtime disruptions.', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['title' => 'Relentless Client Focus', 'desc' => 'Your business uptime is our primary KPI. We stand beside your team around the clock, every day.', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as $val)
            <div class="term-panel p-7 sm:p-8 group">
                <div class="font-mono text-[11px] tracking-[0.24em] text-accent-soft mb-5">{{ str_pad((string)($loop->iteration), 2, '0', STR_PAD_LEFT) }}</div>
                <svg class="w-6 h-6 text-term-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $val['icon'] }}"/></svg>
                <h3 class="font-display text-xl font-bold tracking-tight text-navy-900 dark:text-white">{{ $val['title'] }}</h3>
                <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-term-800">{{ $val['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-labelledby="about-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">SYSTEM://JOIN-FORCE</span>
        <h2 id="about-cta-heading" class="mt-4 font-display text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 dark:text-white text-balance">Work with certified infrastructure specialists</h2>
        <p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800 max-w-2xl mx-auto">Get in touch with our solutions engineering team to review your current architecture and security posture.</p>
        <a href="{{ route('contact') }}" class="term-btn term-btn-lg mt-8">
            Contact Our Engineering Team
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

@endsection
