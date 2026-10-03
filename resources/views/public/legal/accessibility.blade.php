@extends('layouts.public')

@section('title', 'Accessibility Statement — PerfectITSecurity')
@section('description', 'PerfectITSecurity accessibility commitment, compliance goals, and digital inclusion standards.')

@section('content')

{{-- HERO — LEGAL://ACCESSIBILITY --}}
<section class="relative w-full overflow-hidden" aria-labelledby="a11y-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">LEGAL://ACCESSIBILITY</span>
                <span class="term-tag">Digital Inclusion</span>
            </div>
            <h1 id="a11y-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl text-navy-900 dark:text-white text-balance">ACCESSIBILITY STATEMENT</h1>
            <p class="mt-6 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-term-800">
                PerfectITSecurity is committed to ensuring digital accessibility for people of all abilities. We continuously improve our user experience and apply relevant accessibility standards.
            </p>
            <div class="mt-6 font-mono text-[11px] tracking-wider text-term-700">
                <span>STANDARD:// WCAG 2.1 LEVEL AA</span>
            </div>
        </div>
    </div>
</section>

{{-- Content body --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-label="Accessibility statement content">
    <div class="w-full max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="term-panel p-6 sm:p-8 lg:p-10">
            <div class="prose dark:prose-invert max-w-none space-y-10 text-slate-600 dark:text-term-800">

                <div>
                    <div class="term-sec-label mb-2">SECTION://01</div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">Our Commitment</h2>
                    <p class="leading-relaxed">
                        We strive to adhere to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA standards. These guidelines outline best practices to ensure digital web content is Perceivable, Operable, Understandable, and Robust for all users, including individuals using screen readers, keyboard-only navigation, and high-contrast display settings.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://02</div>
                    <h2 class="font-display text-2xl font-bold text-navy-900 dark:text-white mb-4">Key Accessibility Features Implemented</h2>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong class="text-navy-900 dark:text-white">Keyboard Navigation:</strong> All key interactive buttons, dropdowns, forms, and dialogs are fully navigable via standard keyboard focus rings.</li>
                        <li><strong class="text-navy-900 dark:text-white">Semantic HTML:</strong> Valid heading hierarchies (`h1` through `h6`), ARIA landmark regions, and descriptive form label associations.</li>
                        <li><strong class="text-navy-900 dark:text-white">Contrast &amp; Theming:</strong> Support for dark and light color modes calibrated to meet or exceed minimum contrast ratio thresholds.</li>
                        <li><strong class="text-navy-900 dark:text-white">Screen Reader Support:</strong> Informative alternative text attributes on iconography, graphics, and interactive widgets.</li>
                    </ul>
                </div>

                <div class="term-panel-2 p-6">
                    <h3 class="font-display text-lg font-bold text-navy-900 dark:text-white mb-2">Feedback &amp; assistance</h3>
                    <p class="text-sm mb-4">
                        If you experience difficulty accessing any part of our website or customer portal, please contact us with details about the issue and the assistive technology you are utilizing.
                    </p>
                    <a href="{{ route('contact') }}" class="term-link text-sm">
                        Report an Accessibility Barrier
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
