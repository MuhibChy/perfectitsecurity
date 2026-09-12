@extends('layouts.public')

@section('title', 'Accessibility Statement — TechSupport Solutions')
@section('description', 'TechSupport Solutions accessibility commitment, compliance goals, and digital inclusion standards.')

@section('content')

{{-- Hero --}}
<section class="section bg-navy-900 dark:bg-navy-950 text-white overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-10 py-16 lg:py-24">
        <div class="max-w-3xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-px bg-brand-400"></div>
                <span class="label text-brand-400">Digital Inclusion</span>
            </div>
            <h1 class="heading-xl text-white mb-6">Accessibility Statement</h1>
            <p class="text-lg text-surface-400 leading-relaxed">
                TechSupport Solutions is committed to ensuring digital accessibility for people of all abilities. We continuously improve our user experience and apply relevant accessibility standards.
            </p>
            <div class="mt-6 flex items-center gap-4 text-xs text-surface-500">
                <span>Conforming with WCAG 2.1 Level AA Guidelines</span>
            </div>
        </div>
    </div>
</section>

{{-- Content Body --}}
<section class="section bg-white dark:bg-navy-900">
    <div class="max-w-[1000px] mx-auto px-6 lg:px-10 py-16 lg:py-20">
        <div class="prose prose-lg dark:prose-invert max-w-none space-y-12 text-navy-900 dark:text-surface-200">

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">1. Our Commitment</h2>
                <p class="text-surface-600 dark:text-surface-300 leading-relaxed">
                    We strive to adhere to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA standards. These guidelines outline best practices to ensure digital web content is Perceivable, Operable, Understandable, and Robust for all users, including individuals using screen readers, keyboard-only navigation, and high-contrast display settings.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-navy-900 dark:text-white mb-4">2. Key Accessibility Features Implemented</h2>
                <ul class="list-disc pl-6 space-y-2 text-surface-600 dark:text-surface-300">
                    <li><strong class="text-navy-900 dark:text-white">Keyboard Navigation:</strong> All key interactive buttons, dropdowns, forms, and dialogs are fully navigable via standard keyboard focus rings.</li>
                    <li><strong class="text-navy-900 dark:text-white">Semantic HTML:</strong> Valid heading hierarchies (`h1` through `h6`), ARIA landmark regions, and descriptive form label associations.</li>
                    <li><strong class="text-navy-900 dark:text-white">Contrast & Theming:</strong> Support for dark and light color modes calibrated to meet or exceed minimum contrast ratio thresholds.</li>
                    <li><strong class="text-navy-900 dark:text-white">Screen Reader Support:</strong> Informative alternative text attributes on iconography, graphics, and interactive widgets.</li>
                </ul>
            </div>

            <div class="p-6 rounded-xl border border-surface-200 dark:border-white/10 bg-surface-50 dark:bg-navy-800/40">
                <h3 class="text-lg font-bold text-navy-900 dark:text-white mb-2">Feedback & Assistance</h3>
                <p class="text-sm text-surface-600 dark:text-surface-300 mb-4">
                    If you experience difficulty accessing any part of our website or customer portal, please contact us with details about the issue and the assistive technology you are utilizing.
                </p>
                <a href="{{ route('contact') }}" class="inline-flex items-center text-sm font-semibold text-brand-500 hover:text-brand-400">
                    Report an Accessibility Barrier &rarr;
                </a>
            </div>

        </div>
    </div>
</section>

@endsection
