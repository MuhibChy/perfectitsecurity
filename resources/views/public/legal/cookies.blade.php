@extends('layouts.public')

@section('title', 'Cookie Policy — PerfectITSecurity')
@section('description', 'Information regarding how PerfectITSecurity uses cookies, sessions, and telemetry on our website and customer portal.')

@section('content')

{{-- HERO — LEGAL://COOKIES --}}
<section class="relative w-full overflow-hidden" aria-labelledby="cookies-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">LEGAL://COOKIES</span>
                <span class="term-tag">Transparency</span>
            </div>
            <h1 id="cookies-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl text-navy-900 dark:text-white text-balance">COOKIE POLICY</h1>
            <p class="mt-6 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
                This document clarifies how we deploy cookies, local browser storage, and related tracking technologies to provide secure authentication, maintain session states, and monitor performance.
            </p>
            <div class="mt-6 font-mono text-[11px] tracking-wider text-term-700">
                <span>UPDATED:// SEPTEMBER 2026</span>
            </div>
        </div>
    </div>
</section>

{{-- Content body --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Cookie policy content">
    <div class="w-full max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="term-panel p-6 sm:p-8 lg:p-10">
            <div class="prose dark:prose-invert max-w-none space-y-10 text-slate-600 dark:text-term-800">

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">1. What are Cookies?</h2>
                    <p class="leading-relaxed">
                        Cookies are small text records transferred to and stored in your browser by websites you visit. They enable platforms to remember login state, verify CSRF tokens, retain user preferences, and preserve security safeguards.
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">2. Categories of Cookies We Use</h2>
                    <div class="space-y-4 mt-4">
                        <div class="term-panel-2 p-5">
                            <div class="term-sec-label mb-2">COOKIE://ESSENTIAL</div>
                            <p class="text-sm">
                                Required for platform operation, secure authentication (`techsupport_session`), and Cross-Site Request Forgery mitigation (`XSRF-TOKEN`). The portal cannot function securely without these tokens.
                            </p>
                        </div>

                        <div class="term-panel-2 p-5">
                            <div class="term-sec-label mb-2">COOKIE://FUNCTIONAL</div>
                            <p class="text-sm">
                                Stores your interface preferences, such as selected dark/light mode and internationalization options (`NEXT_LOCALE`).
                            </p>
                        </div>

                        <div class="term-panel-2 p-5">
                            <div class="term-sec-label mb-2">COOKIE://TELEMETRY</div>
                            <p class="text-sm">
                                Anonymously captures application errors and performance bottlenecks to support real-time system health checks and high service availability.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">3. Managing Your Cookie Preferences</h2>
                    <p class="leading-relaxed">
                        You can configure your browser to reject cookies or notify you when a cookie is placed. However, disabling essential session and CSRF tokens will prevent you from authenticating or submitting requests through the customer portal.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
