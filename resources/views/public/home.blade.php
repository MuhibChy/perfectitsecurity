@extends('layouts.public')

@section('title', 'PerfectITSecurity — Secure Your Digital World')
@section('description', 'Managed IT support, cybersecurity, penetration testing, secure software development, and cloud/server management. Security operations active.')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     HERO — SYS://PERFECTITSECURITY
     Subtle CSS-only technical environment (grid is global via
     terminal-background). No canvas, no WebGL, no heavy JS.
     ═══════════════════════════════════════════════════════════════ --}}
<section class="relative w-full overflow-hidden" aria-labelledby="hero-heading" data-3d-state="hero">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-accent/40 to-transparent" aria-hidden="true"></div>
    {{-- faint system coordinates --}}
    <div class="absolute top-20 right-6 lg:right-12 font-mono text-[10px] tracking-[0.2em] text-term-700/60 uppercase hidden md:block pointer-events-none select-none" aria-hidden="true">
        LAT 51.5072 // LON -0.1276<br>NODE: EDGE-01
    </div>
    <div class="absolute bottom-24 left-6 lg:left-12 font-mono text-[10px] tracking-[0.2em] text-term-700/60 uppercase hidden md:block pointer-events-none select-none" aria-hidden="true">
        SESSION: TLS-SECURED
    </div>

    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-36 pb-14 lg:pb-20 grid lg:grid-cols-2 gap-10 lg:gap-6 items-center">
        <div class="max-w-4xl">
            {{-- monospace status --}}
            <div class="flex flex-wrap items-center gap-2.5 mb-7 reveal">
                <span class="term-tag term-tag-accent">SYS://PERFECTITSECURITY</span>
                <span class="term-status text-emerald-700 dark:text-accent-soft"><span class="term-status-dot" aria-hidden="true"></span>Status: Online</span>
                <span class="term-tag">Security operations active</span>
            </div>

            <h1 id="hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance reveal">
                SECURE YOUR<br>
                DIGITAL WORLD<span class="term-cursor" aria-hidden="true"></span>
            </h1>

            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-surface-600 dark:text-term-800 max-w-2xl reveal delay-100">
                PerfectITSecurity provides managed IT support, cybersecurity and penetration
                testing, secure software development, cloud and server management, web
                development, and dependable digital solutions — built, secured, and operated
                by one engineering team.
            </p>

            <div class="mt-9 flex flex-col sm:flex-row flex-wrap gap-3 reveal delay-200">
                <a href="{{ route('get-quote') }}" class="term-btn term-btn-lg">
                    Get IT Support
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ auth()->check() ? route('portal.service-request.create') : route('get-quote') }}" class="term-btn term-btn-lg term-btn-ghost">
                    Request a Service
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('contact') }}" class="term-btn term-btn-lg term-btn-ghost">
                    Contact Us
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
            <p class="mt-5 font-mono text-[11px] tracking-[0.18em] uppercase text-slate-500 dark:text-term-700 reveal delay-300">
                Reliable IT Support <span class="text-accent mx-1" aria-hidden="true">•</span> Fast Response <span class="text-accent mx-1" aria-hidden="true">•</span> Professional Solutions
            </p>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-ai-chat'))"
               class="mt-5 inline-flex items-center gap-2 font-mono text-xs tracking-[0.14em] uppercase text-slate-600 hover:text-emerald-700 dark:text-term-700 dark:hover:text-accent-soft transition-colors reveal delay-300">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span class="underline underline-offset-4">Ask the AI assistant</span>
            </button>

            {{-- technical metadata strip (no statistics — capability markers only) --}}
            <dl class="mt-12 pt-6 border-t border-term-300 dark:border-term-400/60 grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 font-mono reveal delay-300">
                @foreach([
                    ['k' => 'SUPPORT', 'v' => '24/7 available'],
                    ['k' => 'APPROACH', 'v' => 'Security first'],
                    ['k' => 'DELIVERY', 'v' => 'Global remote service'],
                    ['k' => 'READY', 'v' => 'Enterprise workflows'],
                ] as $m)
                <div class="min-w-0">
                    <dt class="text-[10px] tracking-[0.24em] text-term-700 uppercase">{{ $m['k'] }}</dt>
                    <dd class="mt-1 text-[13px] tracking-wide text-navy-800 dark:text-term-950 truncate">{{ $m['v'] }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Spacer: the HUD visual now lives in the fixed global HUD frame
             (<x-global-hud-frame />); this column reserves its space so the
             headline never slides underneath it. --}}
        <div class="hidden lg:block" aria-hidden="true"></div>
    </div>
</section>

{{-- ═══════════ 01 / SERVICES://CORE ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28" aria-labelledby="services-heading" id="services" data-3d-state="services">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="01" label="SERVICES://CORE"
            title="Four disciplines. One accountable team."
            desc="Every engagement is delivered through the same ticketed, tracked, and documented workflow — request a service, follow its progress, and keep the full record." />
        <span id="services-heading" class="sr-only">Core services</span>

        <div class="mt-10 lg:mt-14 grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-2 xl:grid-cols-4 overflow-hidden">
            @forelse($serviceCategories as $i => $cat)
            <article class="term-panel min-w-0 p-5 sm:p-6 group reveal card-3d tilt-3d" aria-labelledby="svc-{{ $cat->id }}">
                <span class="card-3d-shine" aria-hidden="true"></span>
                <div class="flex items-start justify-between gap-4 mb-5">
                    <span class="font-mono text-[11px] tracking-[0.24em] text-accent-soft">{{ str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) }} / {{ strtoupper($cat->name) }}</span>
                    <span class="term-tag">{{ $cat->services->count() }} module{{ $cat->services->count() === 1 ? '' : 's' }}</span>
                </div>
                <h3 id="svc-{{ $cat->id }}" class="font-display text-lg sm:text-xl font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">
                    {{ $cat->name }}
                </h3>
                <p class="mt-2.5 text-sm leading-relaxed text-surface-600 dark:text-term-800">
                    {{ $cat->description ?? 'Scoped, priced, and delivered through the client portal with full service history.' }}
                </p>
                @if($cat->services->isNotEmpty())
                <ul class="mt-5 flex flex-wrap gap-1.5" aria-label="Services in {{ $cat->name }}">
                    @foreach($cat->services->take(8) as $svc)
                    <li><a href="{{ route('services.show', $svc->slug) }}" class="term-tag hover:border-accent/50 hover:text-accent-soft transition-colors">{{ $svc->name }}</a></li>
                    @endforeach
                </ul>
                @endif
                <div class="mt-6 pt-5 border-t border-term-300 dark:border-white/5 flex flex-wrap items-center gap-x-6 gap-y-3">
                    <a href="{{ route('services.index') }}?category={{ $cat->slug }}" class="term-link">Learn More
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ auth()->check() ? route('portal.service-request.create') : route('get-quote') }}" class="term-link">Request Service
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </article>
            @empty
            <div class="term-panel p-8 text-sm text-term-800 md:col-span-2 xl:col-span-4">
                The service catalogue is managed by administrators.
                <a href="{{ route('services.index') }}" class="term-link ml-2">Browse services →</a>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ═══════════ 02 / WHY://OPERATE ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="why-heading" data-3d-state="features">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="02" label="WHY://OPERATE"
            title="Complexity is the risk. Discipline is the answer."
            desc="Digital systems become increasingly complex. We counter that with one operating model across IT, security, and engineering." />
        <span id="why-heading" class="sr-only">Why PerfectITSecurity</span>

        <div class="mt-10 lg:mt-14 grid gap-4 sm:gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['n' => '01', 't' => 'THE PROBLEM', 'd' => 'Websites, servers, cloud accounts, devices, and vendors multiply. Nobody owns the full picture, and small gaps go unnoticed.'],
                ['n' => '02', 't' => 'THE RISK', 'd' => 'Security gaps, downtime, poor infrastructure, and uncontrolled access turn into data loss, outages, and unexpected cost.'],
                ['n' => '03', 't' => 'OUR APPROACH', 'd' => 'We combine IT operations, cybersecurity, and software engineering in one ticketed workflow with documented scope and sign-off.'],
                ['n' => '04', 't' => 'THE RESULT', 'd' => 'A more secure, maintainable, and reliable digital environment — with a complete record you can audit at any time.'],
            ] as $c)
            <article class="term-panel p-6 sm:p-7 reveal">
                <div class="font-mono text-[11px] tracking-[0.24em] text-accent-soft mb-4">{{ $c['n'] }}</div>
                <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white">{{ $c['t'] }}</h3>
                <p class="mt-2.5 text-sm leading-relaxed text-surface-600 dark:text-term-800">{{ $c['d'] }}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 03 / SECURITY://OPERATIONS ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="security-heading" id="security">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="03" label="SECURITY://OPERATIONS"
            title="Authorized testing. Documented findings. Verified fixes."
            desc="All security work is performed exclusively on systems you own or are explicitly authorized to test, under agreed scope, with written findings and remediation guidance." />
        <span id="security-heading" class="sr-only">Security operations</span>

        <div class="mt-10 lg:mt-14 grid lg:grid-cols-12 gap-4 sm:gap-5">
            <div class="lg:col-span-7 term-panel p-6 sm:p-8 reveal">
                <div class="font-mono text-[10px] tracking-[0.24em] text-term-700 uppercase mb-5">Assessment scope // authorized systems only</div>
                <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-3.5 text-sm">
                    @foreach(['Penetration Testing','Vulnerability Assessment','Web Application Security','API Security','Network Security','Cloud Security','Security Hardening','Security Monitoring','Security Consulting','Security Documentation'] as $s)
                    <li class="flex items-center gap-2.5 text-navy-800 dark:text-term-900">
                        <span class="w-1.5 h-1.5 bg-accent flex-shrink-0" aria-hidden="true"></span>{{ $s }}
                    </li>
                    @endforeach
                </ul>
                <div class="mt-7 pt-5 border-t border-term-300 dark:border-white/5 flex flex-wrap gap-x-6 gap-y-3">
                    <a href="{{ route('get-quote') }}" class="term-link">Request Assessment
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
                    <a href="{{ route('contact') }}" class="term-link">Discuss Scope
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                </div>
            </div>
            <div class="lg:col-span-5 flex flex-col gap-4 sm:gap-5">
                @foreach([
                    ['k' => 'TARGET', 'v' => 'YOUR WEB APPLICATION'],
                    ['k' => 'STATUS', 'v' => 'ASSESSMENT READY'],
                    ['k' => 'SCOPE', 'v' => 'AUTHORIZED SYSTEM'],
                ] as $row)
                <div class="term-panel-2 px-5 py-4 flex items-center justify-between gap-4 reveal">
                    <span class="font-mono text-[10px] tracking-[0.24em] text-term-700">{{ $row['k'] }}</span>
                    <span class="font-mono text-xs tracking-[0.12em] text-accent-soft text-right">{{ $row['v'] }}</span>
                </div>
                @endforeach
                <div class="term-panel-2 p-5 sm:p-6 reveal">
                    <p class="font-mono text-[11px] leading-relaxed tracking-wider text-term-800">
                        $ scope --verify-authorization<br>
                        <span class="text-accent-soft">✓ ownership confirmed → assessment unlocked</span>
                    </p>
                    <p class="mt-3 text-xs leading-relaxed text-term-700">No testing begins without written authorization and a defined scope. You receive findings, severity ratings, and remediation steps.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 04 / IT_SUPPORT://OPERATIONS ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="itsupport-heading" id="it-support">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="04" label="IT_SUPPORT://OPERATIONS"
            title="Support tickets you can open, track, and audit."
            desc="One queue for every request — hardware, software, accounts, email, network, or server — with status tracking and a full communication record in your portal." />
        <span id="itsupport-heading" class="sr-only">IT support operations</span>

        <div class="mt-10 lg:mt-14 grid lg:grid-cols-12 gap-4 sm:gap-5">
            <div class="lg:col-span-8 term-panel p-6 sm:p-8 reveal">
                <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-3.5 text-sm">
                    @foreach(['Ticket Management','Remote Support','Hardware Support','Software Support','Microsoft 365','Windows','Linux','Network','Server','User Accounts','Email','Endpoints','IT Asset Management'] as $s)
                    <li class="flex items-center gap-2.5 text-navy-800 dark:text-term-900">
                        <span class="w-1.5 h-1.5 flex-shrink-0" style="background:#4DA3FF" aria-hidden="true"></span>{{ $s }}
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="lg:col-span-4 term-panel p-6 sm:p-8 flex flex-col justify-between gap-6 reveal delay-100">
                <div>
                    <div class="term-status text-accent-soft mb-3"><span class="term-status-dot" aria-hidden="true"></span>Queue: monitored</div>
                    <p class="text-sm leading-relaxed text-surface-600 dark:text-term-800">Open a request in minutes. Track assignment, replies, and resolution — every step timestamped.</p>
                </div>
                <div class="flex flex-col gap-2.5">
                    <a href="{{ auth()->check() ? route('portal.tickets.create') : route('login') }}" class="term-btn w-full">Open Support Request
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
                    <a href="{{ route('login') }}" class="term-btn term-btn-ghost w-full">Client Portal →</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 05 / PORTAL://ACCESS ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="portal-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 grid lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        <div class="lg:col-span-7">
            <x-section-head num="05" label="PORTAL://ACCESS"
                title="Your IT. Your security. Your record."
                desc="Customers work independently in the portal using the platform's existing authentication — no second login, no parallel system. Your tickets, services, finances, and documents stay in one auditable place." />
            <span id="portal-heading" class="sr-only">Customer portal</span>
            <div class="mt-8 flex flex-col sm:flex-row gap-3 reveal">
                <a href="{{ route('login') }}" class="term-btn">Access Client Portal
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
                <a href="{{ route('register') }}" class="term-btn term-btn-ghost">Create Account →</a>
            </div>
        </div>
        <div class="lg:col-span-5 term-panel p-6 sm:p-7 reveal delay-100" aria-label="What the portal contains">
            <div class="font-mono text-[10px] tracking-[0.24em] text-term-700 uppercase mb-4">$ portal --list-records</div>
            <ul class="space-y-2.5 text-sm text-navy-800 dark:text-term-900">
                @foreach(['Support tickets','Service history','Invoices & payments','Documents & reports','Security assessments','Task progress','Communication history'] as $r)
                <li class="flex items-center justify-between gap-3 border-b border-term-300/60 dark:border-white/5 pb-2.5">
                    <span>{{ $r }}</span><span class="font-mono text-[10px] text-accent-soft">TRACKED</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- ═══════════ 06 / OPS://PLATFORM ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="ops-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="06" label="OPS://PLATFORM"
            title="An internal service-management platform behind every order."
            desc="Staff operate from the same system you see — tasks, tickets, customer history, time tracking, assets, reporting, finance, and documentation stay connected. No private employee data is exposed publicly." />
        <span id="ops-heading" class="sr-only">Operations platform</span>
        <div class="mt-10 grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach([
                ['t' => 'TASK MANAGEMENT', 'd' => 'Scoped work with assignment and approval.'],
                ['t' => 'TICKET MANAGEMENT', 'd' => 'SLA-tracked queue with full thread history.'],
                ['t' => 'CUSTOMER HISTORY', 'd' => 'Every order, invoice, and message in one view.'],
                ['t' => 'SERVICE TRACKING', 'd' => 'Live progress with customer-visible updates.'],
                ['t' => 'TIME TRACKING', 'd' => 'Recorded effort attached to tickets and tasks.'],
                ['t' => 'REPORTING & FINANCE', 'd' => 'Invoices, payments, and reports from one ledger.'],
            ] as $c)
            <div class="term-panel-2 p-5 sm:p-6 reveal">
                <h3 class="font-mono text-xs tracking-[0.18em] text-navy-900 dark:text-white">{{ $c['t'] }}</h3>
                <p class="mt-2 text-[13px] leading-relaxed text-term-700 dark:text-term-800">{{ $c['d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 07 / SECURITY://PROCESS ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="process-heading" data-3d-state="process">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="07" label="SECURITY://PROCESS"
            title="From discovery to continuous monitoring."
            desc="A fixed six-stage workflow keeps every assessment scoped, documented, and verified — nothing informal, nothing undocumented." />
        <span id="process-heading" class="sr-only">Security process</span>
        <ol class="mt-10 lg:mt-14 grid gap-4 sm:gap-5 sm:grid-cols-2 lg:grid-cols-6 lg:gap-0 lg:divide-x lg:divide-term-300 lg:dark:divide-white/5">
            @foreach([
                ['n' => '01', 't' => 'DISCOVER', 'd' => 'Map assets and agree the authorized scope in writing.'],
                ['n' => '02', 't' => 'ASSESS', 'd' => 'Baseline vulnerabilities and configuration weaknesses.'],
                ['n' => '03', 't' => 'TEST', 'd' => 'Controlled testing strictly inside the agreed scope.'],
                ['n' => '04', 't' => 'REMEDIATE', 'd' => 'Prioritized fixes with clear severity and guidance.'],
                ['n' => '05', 't' => 'VERIFY', 'd' => 'Retest fixes and confirm closure with evidence.'],
                ['n' => '06', 't' => 'MONITOR', 'd' => 'Ongoing watch with documented follow-up.'],
            ] as $s)
            <li class="term-panel lg:border-0 lg:bg-transparent lg:rounded-none p-5 sm:p-6 lg:px-6 reveal">
                <div class="font-mono text-[11px] tracking-[0.24em] text-accent-soft">{{ $s['n'] }}</div>
                <h3 class="mt-2 font-display text-base font-bold tracking-tight text-navy-900 dark:text-white">{{ $s['t'] }}</h3>
                <p class="mt-2 text-[13px] leading-relaxed text-surface-600 dark:text-term-800">{{ $s['d'] }}</p>
            </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ═══════════ 08 / STACK://CAPABILITIES ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="stack-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="08" label="STACK://CAPABILITIES"
            title="Capability areas we actively deliver."
            desc="Named only where the team genuinely delivers client work today — no invented partnerships or certifications." />
        <span id="stack-heading" class="sr-only">Technology capabilities</span>
        <div class="mt-10 grid gap-4 sm:gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['t' => 'SECURITY', 'items' => 'Testing · Hardening · Monitoring · Documentation'],
                ['t' => 'DEVELOPMENT', 'items' => 'Websites · Portals · Business automation · Secure builds'],
                ['t' => 'INFRASTRUCTURE', 'items' => 'Servers · Networks · Endpoints · Microsoft 365'],
                ['t' => 'CLOUD & DATA', 'items' => 'Hosting · Backups · Monitoring · Reporting'],
            ] as $c)
            <div class="term-panel p-6 reveal">
                <h3 class="font-mono text-xs tracking-[0.2em] text-accent-soft">{{ $c['t'] }}</h3>
                <p class="mt-3 text-sm leading-relaxed text-surface-600 dark:text-term-800">{{ $c['items'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ 09 / COMPLIANCE://FRAMEWORKS ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="compliance-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="09" label="COMPLIANCE://FRAMEWORKS"
            title="Security practices aligned with recognized frameworks."
            desc="We do not claim certifications we do not hold. Our practices are aligned with widely used frameworks, and we support your compliance preparation with documentation and evidence." />
        <span id="compliance-heading" class="sr-only">Compliance frameworks</span>
        <ul class="mt-10 flex flex-wrap gap-2.5 reveal" aria-label="Reference frameworks">
            @foreach(['ISO 27001','SOC 2','GDPR','OWASP','NIST','CIS Controls'] as $f)
            <li class="term-tag">Aligned: {{ $f }}</li>
            @endforeach
        </ul>
        <p class="mt-5 text-xs leading-relaxed text-term-700 max-w-2xl reveal">Wording is deliberate: “aligned with” and “support for compliance preparation” — never a claim of certification unless verified and published by the company.</p>
    </div>
</section>

{{-- ═══════════ 10 / ASSESSMENT://READINESS ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="assess-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 grid lg:grid-cols-12 gap-8">
        <div class="lg:col-span-5">
            <x-section-head num="10" label="ASSESSMENT://READINESS"
                title="Check your security readiness in 60 seconds."
                desc="Seven quick questions produce an informational readiness signal. This is guidance only — not a penetration test, audit, or certification." />
            <span id="assess-heading" class="sr-only">Security readiness assessment</span>
        </div>
        <div class="lg:col-span-7">
            <form id="readiness-quiz" class="term-panel p-6 sm:p-8 reveal" aria-describedby="quiz-note">
                <div class="font-mono text-[10px] tracking-[0.24em] text-term-700 uppercase mb-5">$ readiness --interactive</div>
                <ol class="space-y-5">
                    @foreach([
                        'Do you have a website or business application?',
                        'Do you have an internal IT team or provider?',
                        'Have you had a security assessment in the last 12 months?',
                        'Do you enforce multi-factor authentication (MFA)?',
                        'Do you maintain tested, off-site backups?',
                        'Do you have an incident response plan?',
                        'Do you manage company devices centrally?',
                    ] as $i => $q)
                    <li>
                        <fieldset>
                            <legend class="text-sm font-medium text-navy-900 dark:text-term-950">{{ str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) }}. {{ $q }}</legend>
                            <div class="mt-2 flex gap-2">
                                <label class="term-tag cursor-pointer has-[:checked]:border-accent/60 has-[:checked]:text-accent-soft"><input type="radio" name="q{{ $i }}" value="1" class="sr-only" required> Yes</label>
                                <label class="term-tag cursor-pointer has-[:checked]:border-accent/60 has-[:checked]:text-accent-soft"><input type="radio" name="q{{ $i }}" value="0" class="sr-only"> No</label>
                            </div>
                        </fieldset>
                    </li>
                    @endforeach
                </ol>
                <div class="mt-7 flex flex-col sm:flex-row sm:items-center gap-3">
                    <button type="submit" class="term-btn">Generate Result →</button>
                    <button type="reset" class="term-btn term-btn-ghost" id="quiz-reset">Reset</button>
                </div>
                <div id="quiz-result" class="mt-6 hidden" role="status" aria-live="polite"></div>
                <p id="quiz-note" class="mt-5 text-xs leading-relaxed text-term-700">Informational self-check only. No personal data is collected or transmitted — scoring runs entirely in your browser. For a formal review, <a href="{{ route('get-quote') }}" class="underline underline-offset-2 hover:text-accent-soft">request an assessment</a>.</p>
            </form>
        </div>
    </div>
</section>

{{-- ═══════════ 11 / WORK://CASEFILES ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="work-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <x-section-head num="11" label="WORK://CASEFILES"
                title="Selected work from the live catalogue."
                desc="Pulled directly from the database — administrators manage these records, and the public list always matches." />
            <a href="{{ route('portfolio.index') }}" class="term-link flex-shrink-0 reveal">All casefiles
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
        </div>
        <span id="work-heading" class="sr-only">Portfolio case files</span>
        <div class="mt-10 grid gap-4 sm:gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($portfolioItems as $i => $item)
            <article class="term-panel p-6 sm:p-7 group reveal">
                <div class="font-mono text-[11px] tracking-[0.2em] text-term-700 mb-3">FILE_{{ str_pad((string)($i + 1), 3, '0', STR_PAD_LEFT) }} // {{ strtoupper($item->category ?? 'GENERAL') }}</div>
                <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $item->title }}</h3>
                @if($item->summary)<p class="mt-2 text-sm leading-relaxed text-surface-600 dark:text-term-800 line-clamp-3">{{ $item->summary }}</p>@endif
                <dl class="mt-4 space-y-1.5 font-mono text-[11px] tracking-wider">
                    @if($item->category)<div class="flex gap-2"><dt class="text-term-700">CATEGORY:</dt><dd class="text-term-900 dark:text-term-950">{{ $item->category }}</dd></div>@endif
                    @if($item->technologies)<div class="flex gap-2"><dt class="text-term-700">TECH:</dt><dd class="text-term-900 dark:text-term-950 line-clamp-1">{{ is_array($item->technologies) ? implode(', ', $item->technologies) : $item->technologies }}</dd></div>@endif
                </dl>
                <a href="{{ route('portfolio.show', $item->slug) }}" class="term-link mt-5">Open casefile
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
            </article>
            @empty
            <div class="term-panel p-8 text-sm text-term-800 md:col-span-2 xl:col-span-3">
                Case files are being curated.
                <a href="{{ route('portfolio.index') }}" class="term-link ml-2">View portfolio →</a>
            </div>
            @endforelse
        </div>

        @if($caseStudies->isNotEmpty())
        <div class="mt-8 grid gap-4 sm:gap-5 md:grid-cols-3">
            @foreach($caseStudies as $cs)
            <a href="{{ route('case-studies.show', $cs->slug) }}" class="term-panel-2 p-5 sm:p-6 group block reveal">
                <div class="font-mono text-[10px] tracking-[0.2em] text-term-700 mb-2">CASE STUDY // {{ strtoupper($cs->industry ?? 'GENERAL') }}</div>
                <h3 class="font-display text-base font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $cs->title }}</h3>
                @if($cs->summary)<p class="mt-2 text-[13px] leading-relaxed text-term-700 line-clamp-2">{{ $cs->summary }}</p>@endif
            </a>
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- ═══════════ 12 / KNOWLEDGE://BASE ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="kb-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <x-section-head num="12" label="KNOWLEDGE://BASE"
                title="Documentation-grade guides, searchable."
                desc="Live articles from the knowledge base — security guides, IT procedures, troubleshooting, and FAQs maintained by the team." />
            <a href="{{ route('kb.index') }}" class="term-link flex-shrink-0 reveal">Open knowledge base
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
        </div>
        <span id="kb-heading" class="sr-only">Knowledge base</span>

        <form action="{{ route('kb.index') }}" method="GET" role="search" class="mt-8 flex flex-col sm:flex-row gap-2.5 max-w-2xl reveal">
            <label for="home-kb-search" class="sr-only">Search the knowledge base</label>
            <input id="home-kb-search" name="search" type="search" placeholder="search guides: e.g. MFA, backups, VPN…" class="form-input flex-1 font-mono text-sm" autocomplete="off">
            <button type="submit" class="term-btn flex-shrink-0">Search →</button>
        </form>

        @if($kbCategories->isNotEmpty())
        <ul class="mt-5 flex flex-wrap gap-1.5 reveal" aria-label="Knowledge base categories">
            @foreach($kbCategories as $kc)
            <li><a href="{{ route('kb.index') }}?category={{ $kc->slug }}" class="term-tag hover:border-accent/50 hover:text-accent-soft transition-colors">{{ $kc->name }}</a></li>
            @endforeach
        </ul>
        @endif

        <div class="mt-8 grid gap-4 sm:gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($kbArticles as $a)
            <a href="{{ route('kb.show', $a->slug) }}" class="term-panel p-5 sm:p-6 group block reveal">
                <div class="font-mono text-[10px] tracking-[0.2em] text-term-700 mb-2">{{ strtoupper($a->category->name ?? 'GUIDE') }} // {{ strtoupper($a->difficulty ?? 'ALL LEVELS') }}</div>
                <h3 class="font-display text-base font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors line-clamp-2">{{ $a->title }}</h3>
                @if($a->excerpt)<p class="mt-2 text-[13px] leading-relaxed text-term-700 line-clamp-2">{{ $a->excerpt }}</p>@endif
            </a>
            @empty
            <div class="term-panel p-8 text-sm text-term-800 md:col-span-2 xl:col-span-3">
                Guides are being published.
                <a href="{{ route('kb.index') }}" class="term-link ml-2">Browse all →</a>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ═══════════ 13 / AI://ASSISTANT ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="ai-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 grid lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-7">
            <x-section-head num="13" label="AI://ASSISTANT"
                title="A support console, not a chatbot toy."
                desc="The assistant answers from the company knowledge base, helps you discover services, guides support requests, and escalates to a human when needed." />
            <span id="ai-heading" class="sr-only">AI customer assistant</span>
            <div class="mt-8 flex flex-col sm:flex-row gap-3 reveal">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-ai-chat'))" class="term-btn">Launch Assistant →</button>
                <a href="{{ route('faq') }}" class="term-btn term-btn-ghost">Read FAQ</a>
            </div>
        </div>
        <div class="lg:col-span-5 term-panel p-0 overflow-hidden reveal delay-100" aria-hidden="true">
            <div class="flex items-center justify-between px-5 py-3 border-b border-term-300 dark:border-white/5">
                <span class="font-mono text-[10px] tracking-[0.24em] text-term-700">AI://CONSOLE</span>
                <span class="term-status text-accent-soft"><span class="term-status-dot"></span>Ready</span>
            </div>
            <div class="p-5 font-mono text-xs leading-relaxed space-y-3">
                <p class="text-term-700">visitor&gt; <span class="text-term-950 dark:text-term-950">how do I reset MFA on my account?</span></p>
                <p class="text-term-800">assistant&gt; <span class="text-navy-800 dark:text-term-900">Here is the documented procedure from the knowledge base… [escalation to human available]</span></p>
                <p class="text-term-700">system&gt; <span class="text-accent-soft">knowledge-base linked · no credentials requested</span></p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ 14 / CONTACT://SECURE_CHANNEL ═══════════ --}}
<section class="relative w-full py-16 sm:py-20 lg:py-28 border-t border-term-300 dark:border-white/5" aria-labelledby="contact-heading" data-3d-state="contact">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 text-center">
        <div class="term-sec-head justify-center" aria-hidden="true">
            <span class="term-sec-num">14</span>
            <span class="term-sec-label">CONTACT://SECURE_CHANNEL</span>
        </div>
        <h2 id="contact-heading" class="term-sec-title text-3xl sm:text-4xl lg:text-6xl text-balance reveal">LET'S SECURE YOUR<br>NEXT PROJECT.</h2>
        <p class="term-sec-desc mt-5 max-w-xl mx-auto reveal delay-100">Tell us what you need — support, security, development, or cloud. Requests use the existing contact workflow and land in the same tracked queue.</p>
        <div class="mt-9 flex flex-col sm:flex-row justify-center gap-3 reveal delay-200">
            <a href="{{ route('contact') }}" class="term-btn term-btn-lg">Send Request
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></a>
            <a href="{{ route('get-quote') }}" class="term-btn term-btn-lg term-btn-ghost">Get a Quote →</a>
        </div>
        @if($latestPosts->isNotEmpty())
        <div class="mt-16 text-left">
            <div class="font-mono text-[10px] tracking-[0.24em] text-term-700 uppercase mb-5 reveal">LATEST://TRANSMISSIONS</div>
            <div class="grid gap-4 sm:gap-5 md:grid-cols-3">
                @foreach($latestPosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="term-panel p-5 sm:p-6 group block reveal">
                    <h3 class="font-display text-base font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors line-clamp-2">{{ $post->title }}</h3>
                    @if($post->excerpt)<p class="mt-2 text-[13px] leading-relaxed text-term-700 line-clamp-2">{{ $post->excerpt }}</p>@endif
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('readiness-quiz');
    if (!form) return;
    var result = document.getElementById('quiz-result');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var score = 0, answered = 0;
        for (var i = 0; i < 7; i++) {
            var checked = form.querySelector('input[name="q' + i + '"]:checked');
            if (checked) { answered++; score += parseInt(checked.value, 10); }
        }
        if (answered < 7) {
            result.classList.remove('hidden');
            result.innerHTML = '<p class="font-mono text-xs tracking-wider text-amber-400">Please answer all 7 questions to generate your signal.</p>';
            return;
        }
        var level, detail, cls;
        if (score >= 6) { level = 'READINESS: STRONG'; detail = 'Core hygiene looks covered. A periodic professional assessment keeps it that way.'; cls = 'text-accent-soft border-accent/40'; }
        else if (score >= 4) { level = 'READINESS: DEVELOPING'; detail = 'Foundations exist with clear gaps. Prioritize MFA, backups, and an incident plan.'; cls = 'text-[#4DA3FF] border-[#4DA3FF]/40'; }
        else { level = 'READINESS: AT RISK'; detail = 'Several fundamentals are missing. Start with backups + MFA, then book a scoped review.'; cls = 'text-[#FFB454] border-[#FFB454]/40'; }
        result.classList.remove('hidden');
        // Text is fully static (no user input reflected) — no XSS surface.
        result.innerHTML = '<div class="border px-5 py-4 ' + cls + '">'
            + '<p class="font-mono text-xs tracking-[0.18em]">' + level + ' — ' + score + '/7</p>'
            + '<p class="mt-2 text-sm leading-relaxed text-term-800">' + detail + '</p>'
            + '<p class="mt-2 font-mono text-[11px] text-term-700">signal informational only · not a penetration test</p></div>';
        result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
    document.getElementById('quiz-reset').addEventListener('click', function () {
        result.classList.add('hidden');
        result.innerHTML = '';
    });
})();
</script>
@endpush

@endsection
