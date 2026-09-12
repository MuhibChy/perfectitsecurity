@extends('layouts.public')

@section('title', 'Cookie Policy — TechSupport Solutions')
@section('description', 'Information regarding how TechSupport Solutions uses cookies, sessions, and telemetry on our website and customer portal.')

@section('content')

{{-- Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-px bg-brand-400"></div>
                <span class="label text-brand-400">Transparency</span>
            </div>
            <h1 class="heading-xl text-white mb-6">Cookie Policy</h1>
            <p class="text-lg text-surface-400 leading-relaxed">
                This document clarifies how we deploy cookies, local browser storage, and related tracking technologies to provide secure authentication, maintain session states, and monitor performance.
            </p>
            <div class="mt-6 flex items-center gap-4 text-xs text-surface-500">
                <span>Updated: September 2026</span>
            </div>
        </div>
    </div>
</section>

{{-- Content Body --}}
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1000px] mx-auto px-6 lg:px-10 py-16 lg:py-20">
        <div class="prose prose-lg dark:prose-invert max-w-none space-y-12 text-navy-900 dark:text-surface-200">

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">1. What are Cookies?</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed">
                    Cookies are small text records transferred to and stored in your browser by websites you visit. They enable platforms to remember login state, verify CSRF tokens, retain user preferences, and preserve security safeguards.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">2. Categories of Cookies We Use</h2>
                <div class="space-y-6 mt-4">
                    <div class="p-5 rounded-xl border border-surface-200 dark:border-white/10 bg-surface-50 dark:bg-navy-800/30">
                        <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2">Strictly Essential Cookies</h3>
                        <p class="text-sm text-surface-600 dark:text-surface-300">
                            Required for platform operation, secure authentication (`techsupport_session`), and Cross-Site Request Forgery mitigation (`XSRF-TOKEN`). The portal cannot function securely without these tokens.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl border border-surface-200 dark:border-white/10 bg-surface-50 dark:bg-navy-800/30">
                        <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2">Functional & Preference Cookies</h3>
                        <p class="text-sm text-surface-600 dark:text-surface-300">
                            Stores your interface preferences, such as selected dark/light mode and internationalization options (`NEXT_LOCALE`).
                        </p>
                    </div>

                    <div class="p-5 rounded-xl border border-surface-200 dark:border-white/10 bg-surface-50 dark:bg-navy-800/30">
                        <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2">Diagnostic & Telemetry Cookies</h3>
                        <p class="text-sm text-surface-600 dark:text-surface-300">
                            Anonymously captures application errors and performance bottlenecks to support real-time system health checks and high service availability.
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">3. Managing Your Cookie Preferences</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed">
                    You can configure your browser to reject cookies or notify you when a cookie is placed. However, disabling essential session and CSRF tokens will prevent you from authenticating or submitting requests through the customer portal.
                </p>
            </div>

        </div>
    </div>
</section>

@endsection
