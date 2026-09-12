@extends('layouts.public')

@section('title', 'Terms of Service — TechSupport Solutions')
@section('description', 'Terms and conditions governing use of TechSupport Solutions services, customer portal, and IT consulting agreements.')

@section('content')

{{-- Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-px bg-brand-400"></div>
                <span class="label text-brand-400">Legal Agreement</span>
            </div>
            <h1 class="heading-xl text-white mb-6">Terms of Service</h1>
            <p class="text-lg text-surface-400 leading-relaxed">
                These terms establish the rules, guidelines, and contractual obligations between your organization and TechSupport Solutions when accessing our portal and engaging our managed IT services.
            </p>
            <div class="mt-6 flex items-center gap-4 text-xs text-surface-500">
                <span>Effective Date: September 2026</span>
                <span>•</span>
                <span>Version 2.8</span>
            </div>
        </div>
    </div>
</section>

{{-- Content Body --}}
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1000px] mx-auto px-6 lg:px-10 py-16 lg:py-20">
        <div class="prose prose-lg dark:prose-invert max-w-none space-y-12 text-navy-900 dark:text-surface-200">

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">1. Acceptance of Terms</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed">
                    By registering an account, utilizing the TechSupport Solutions customer portal, or contracting for IT support, infrastructure engineering, or cybersecurity consulting, you agree to be legally bound by these Terms of Service and all incorporated service schedules.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">2. Provision of Managed Services</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    TechSupport Solutions agrees to deliver services in accordance with the specifications outlined in relevant Work Orders, Quotations, and Master Services Agreements (MSAs). Service availability, target resolution times, and priority classifications are governed by our Service Level Agreement (SLA).
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">3. Account Authentication & Security</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    Users are responsible for safeguarding portal credentials. Multi-factor authentication (email OTP or SMS OTP) is mandatory for authorized administrative actions. You must immediately notify our response team of any suspected credential compromises or security incidents affecting your account.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">4. Invoicing, Payments & Work Orders</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    All fees, quotes, and retainer amounts are defined in designated quotations and invoice summaries. Work order modifications or expedited response requests outside agreed SLAs may incur standard engineering rates upon prior written customer authorization.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">5. Intellectual Property & Confidentiality</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed mb-4">
                    Each party maintains full ownership of its pre-existing intellectual property. TechSupport Solutions maintains strict confidentiality regarding client network topologies, server credentials, codebases, and corporate data discovered during technical maintenance.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">6. Limitation of Liability</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed">
                    To the maximum extent permitted by applicable law, neither party shall be liable for indirect, incidental, punitive, or consequential damages resulting from downtime, force majeure events, or external cyber attacks beyond commercially reasonable security controls.
                </p>
            </div>

            <div class="p-6 rounded-xl border border-surface-200 dark:border-white/10 bg-surface-50 dark:bg-navy-800/40">
                <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2">Need a custom enterprise agreement?</h3>
                <p class="text-sm text-surface-600 dark:text-surface-300 mb-4">
                    For enterprise-grade Master Services Agreements (MSAs) or tailored SLA commitments, speak to our legal operations department.
                </p>
                <a href="{{ route('contact') }}" class="inline-flex items-center text-sm font-semibold text-brand-500 hover:text-brand-400">
                    Inquire About Enterprise Terms &rarr;
                </a>
            </div>

        </div>
    </div>
</section>

@endsection
