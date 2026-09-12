@extends('layouts.public')

@section('title', 'Privacy Policy — TechSupport Solutions')
@section('description', 'Learn how TechSupport Solutions collects, uses, protects, and handles your personal information and corporate data.')

@section('content')

{{-- Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-px bg-brand-400"></div>
                <span class="label text-brand-400">Compliance & Trust</span>
            </div>
            <h1 class="heading-xl text-white mb-6">Privacy Policy</h1>
            <p class="text-lg text-surface-400 leading-relaxed">
                At TechSupport Solutions, safeguarding your personal data and institutional assets is fundamental to our security practices. This policy details how we treat information gathered across our services.
            </p>
            <div class="mt-6 flex items-center gap-4 text-xs text-surface-500">
                <span>Last revised: September 2026</span>
                <span>•</span>
                <span>Version 3.2</span>
            </div>
        </div>
    </div>
</section>

{{-- Content Body --}}
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1000px] mx-auto px-6 lg:px-10 py-16 lg:py-20">
        <div class="prose prose-lg dark:prose-invert max-w-none space-y-12 text-navy-900 dark:text-surface-200">

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">1. Information We Collect</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    We collect information to provide robust IT infrastructure, cybersecurity defense, and specialized customer support. The information collected depends on how you interact with our platform:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-surface-600 dark:text-surface-300">
                    <li><strong class="text-navy-900 dark:text-white">Account & Contact Information:</strong> Name, professional email address, corporate phone number, company name, and physical business address.</li>
                    <li><strong class="text-navy-900 dark:text-white">Technical & Telemetry Data:</strong> IP addresses, browser types, device identifiers, session timestamps, and network access records.</li>
                    <li><strong class="text-navy-900 dark:text-white">Support & Service Artifacts:</strong> Diagnostics, support tickets, incident descriptions, and communication logs created during client support sessions.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">2. How We Utilize Your Information</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    We process information strictly for legitimate operational, security, and contractual purposes:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-surface-600 dark:text-surface-300">
                    <li>Provisioning, maintaining, and enhancing our IT management and ticketing platforms.</li>
                    <li>Executing two-factor and multi-factor authentication (e.g. Email & SMS OTP verification).</li>
                    <li>Preventing malicious activities, unauthorized access, and cyber threats.</li>
                    <li>Fulfilling contractual service level agreements (SLAs) and invoicing requirements.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">3. Data Security & Encryption Standards</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    As an enterprise IT and cybersecurity organization, data protection is embedded in our engineering. We employ AES-256 encryption at rest, TLS 1.3 encryption in transit, strict role-based access control (RBAC), and regular vulnerability assessments. Sensitive authentication tokens and OTP hashes are stored using cryptographic collision-resistant algorithms.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">4. Third-Party Disclosures</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    We do not sell, license, or monetize customer data. Information is only shared with vetted infrastructure subprocessors (such as payment gateways, cloud infrastructure providers, and transactional mail services) that adhere to stringent confidentiality and security obligations.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">5. Your Legal Rights & Data Subject Access</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    Depending on your jurisdiction (including GDPR, CCPA, and applicable local data privacy laws), you possess rights to inspect, rectify, or request erasure of your personal data. To initiate a data access or deletion request, please submit an inquiry through our client portal or contact our Data Protection Officer.
                </p>
            </div>

            <div class="p-6 rounded-xl border border-surface-200 dark:border-white/10 bg-surface-50 dark:bg-navy-800/40">
                <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2">Questions or Concerns?</h3>
                <p class="text-sm text-surface-600 dark:text-surface-300 mb-4">
                    If you have questions regarding our privacy practices or wish to submit a privacy inquiry, reach out directly to our security compliance team.
                </p>
                <a href="{{ route('contact') }}" class="inline-flex items-center text-sm font-semibold text-brand-500 hover:text-brand-400">
                    Contact Security & Compliance Team &rarr;
                </a>
            </div>

        </div>
    </div>
</section>

@endsection
