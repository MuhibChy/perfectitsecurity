@extends('layouts.public')

@section('title', 'Terms of Service — PerfectITSecurity')
@section('description', 'Terms and conditions governing use of PerfectITSecurity services, customer portal, and IT consulting agreements.')

@section('content')

{{-- HERO — LEGAL://TERMS --}}
<section class="relative w-full overflow-hidden" aria-labelledby="terms-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">LEGAL://TERMS</span>
                <span class="term-tag">Legal Agreement</span>
            </div>
            <h1 id="terms-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl text-navy-900 dark:text-white text-balance">TERMS OF SERVICE</h1>
            <p class="mt-6 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
                These terms establish the rules, guidelines, and contractual obligations between your organization and PerfectITSecurity when accessing our portal and engaging our managed IT services.
            </p>
            <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-[11px] tracking-wider text-term-700">
                <span>EFFECTIVE:// SEPTEMBER 2026</span>
                <span aria-hidden="true">/</span>
                <span>VERSION:// 2.8</span>
            </div>
        </div>
    </div>
</section>

{{-- Content body --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Terms of service content">
    <div class="w-full max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="term-panel p-6 sm:p-8 lg:p-10">
            <div class="prose dark:prose-invert max-w-none space-y-10 text-slate-600 dark:text-term-800">

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">1. Acceptance of Terms</h2>
                    <p class="leading-relaxed">
                        By registering an account, utilizing the PerfectITSecurity customer portal, or contracting for IT support, infrastructure engineering, or cybersecurity consulting, you agree to be legally bound by these Terms of Service and all incorporated service schedules.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">2. Provision of Managed Services</h2>
                    <p class="leading-relaxed mb-4">
                        PerfectITSecurity agrees to deliver services in accordance with the specifications outlined in relevant Work Orders, Quotations, and Master Services Agreements (MSAs). Service availability, target resolution times, and priority classifications are governed by our Service Level Agreement (SLA).
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">3. Account Authentication &amp; Security</h2>
                    <p class="leading-relaxed mb-4">
                        Users are responsible for safeguarding portal credentials. Multi-factor authentication (email OTP or SMS OTP) is mandatory for authorized administrative actions. You must immediately notify our response team of any suspected credential compromises or security incidents affecting your account.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">4. Invoicing, Payments &amp; Work Orders</h2>
                    <p class="leading-relaxed mb-4">
                        All fees, quotes, and retainer amounts are defined in designated quotations and invoice summaries. Work order modifications or expedited response requests outside agreed SLAs may incur standard engineering rates upon prior written customer authorization.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">5. Intellectual Property &amp; Confidentiality</h2>
                    <p class="leading-relaxed mb-4">
                        Each party maintains full ownership of its pre-existing intellectual property. PerfectITSecurity maintains strict confidentiality regarding client network topologies, server credentials, codebases, and corporate data discovered during technical maintenance.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">6. Limitation of Liability</h2>
                    <p class="leading-relaxed">
                        To the maximum extent permitted by applicable law, neither party shall be liable for indirect, incidental, punitive, or consequential damages resulting from downtime, force majeure events, or external cyber attacks beyond commercially reasonable security controls.
                    </p>
                </div>

                <div class="term-panel-2 p-6">
                    <h3 class="font-display text-lg font-bold text-navy-900 dark:text-white mb-2">Need a custom enterprise agreement?</h3>
                    <p class="text-sm mb-4">
                        For enterprise-grade Master Services Agreements (MSAs) or tailored SLA commitments, speak to our legal operations department.
                    </p>
                    <a href="{{ route('contact') }}" class="term-link text-sm">
                        Inquire About Enterprise Terms
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
