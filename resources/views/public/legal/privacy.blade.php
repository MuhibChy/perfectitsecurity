@extends('layouts.public')

@section('title', 'Privacy Policy — PerfectITSecurity')
@section('description', 'Learn how PerfectITSecurity collects, uses, protects, and handles your personal information and corporate data.')

@section('content')

{{-- HERO — LEGAL://PRIVACY --}}
<section class="relative w-full overflow-hidden" aria-labelledby="privacy-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">LEGAL://PRIVACY</span>
                <span class="term-tag">Compliance + Trust</span>
            </div>
            <h1 id="privacy-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl text-navy-900 dark:text-white text-balance">PRIVACY POLICY</h1>
            <p class="mt-6 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
                At PerfectITSecurity, safeguarding your personal data and institutional assets is fundamental to our security practices. This policy details how we treat information gathered across our services.
            </p>
            <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-[11px] tracking-wider text-term-700">
                <span>LAST-REVISED:// SEPTEMBER 2026</span>
                <span aria-hidden="true">/</span>
                <span>VERSION:// 3.2</span>
            </div>
        </div>
    </div>
</section>

{{-- Content body --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Privacy policy content">
    <div class="w-full max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="term-panel p-6 sm:p-8 lg:p-10">
            <div class="prose dark:prose-invert max-w-none space-y-10 text-slate-600 dark:text-term-800">

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">1. Information We Collect</h2>
                    <p class="leading-relaxed mb-4">
                        We collect information to provide robust IT infrastructure, cybersecurity defense, and specialized customer support. The information collected depends on how you interact with our platform:
                    </p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong class="text-navy-900 dark:text-white">Account &amp; Contact Information:</strong> Name, professional email address, corporate phone number, company name, and physical business address.</li>
                        <li><strong class="text-navy-900 dark:text-white">Technical &amp; Telemetry Data:</strong> IP addresses, browser types, device identifiers, session timestamps, and network access records.</li>
                        <li><strong class="text-navy-900 dark:text-white">Support &amp; Service Artifacts:</strong> Diagnostics, support tickets, incident descriptions, and communication logs created during client support sessions.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">2. How We Utilize Your Information</h2>
                    <p class="leading-relaxed mb-4">
                        We process information strictly for legitimate operational, security, and contractual purposes:
                    </p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Provisioning, maintaining, and enhancing our IT management and ticketing platforms.</li>
                        <li>Executing two-factor and multi-factor authentication (e.g. Email &amp; SMS OTP verification).</li>
                        <li>Preventing malicious activities, unauthorized access, and cyber threats.</li>
                        <li>Fulfilling contractual service level agreements (SLAs) and invoicing requirements.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">3. Data Security &amp; Encryption Standards</h2>
                    <p class="leading-relaxed mb-4">
                        As an enterprise IT and cybersecurity organization, data protection is embedded in our engineering. We employ AES-256 encryption at rest, TLS 1.3 encryption in transit, strict role-based access control (RBAC), and regular vulnerability assessments. Sensitive authentication tokens and OTP hashes are stored using cryptographic collision-resistant algorithms.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">4. Third-Party Disclosures</h2>
                    <p class="leading-relaxed mb-4">
                        We do not sell, license, or monetize customer data. Information is only shared with vetted infrastructure subprocessors (such as payment gateways, cloud infrastructure providers, and transactional mail services) that adhere to stringent confidentiality and security obligations.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">5. Your Legal Rights &amp; Data Subject Access</h2>
                    <p class="leading-relaxed mb-4">
                        Depending on your jurisdiction (including GDPR, CCPA, and applicable local data privacy laws), you possess rights to inspect, rectify, or request erasure of your personal data. To initiate a data access or deletion request, please submit an inquiry through our client portal or contact our Data Protection Officer.
                    </p>
                </div>

                <div class="term-panel-2 p-6">
                    <h3 class="font-display text-lg font-bold text-navy-900 dark:text-white mb-2">Questions or concerns?</h3>
                    <p class="text-sm mb-4">
                        If you have questions regarding our privacy practices or wish to submit a privacy inquiry, reach out directly to our security compliance team.
                    </p>
                    <a href="{{ route('contact') }}" class="term-link text-sm">
                        Contact Security &amp; Compliance Team
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
