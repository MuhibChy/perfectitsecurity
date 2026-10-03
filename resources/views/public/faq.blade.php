@extends('layouts.public')

@section('title', 'Frequently Asked Questions — PerfectITSecurity')
@section('description', 'Find answers to common questions about our IT support services, cybersecurity solutions, pricing, and more.')

@section('content')

{{-- HERO — FAQ://INDEX --}}
<section class="relative w-full overflow-hidden" aria-labelledby="faq-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-4xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">FAQ://INDEX</span>
                <span class="term-tag">Help Centre</span>
            </div>
            <h1 id="faq-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                FREQUENTLY ASKED <span class="text-accent-soft">QUESTIONS</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl">
                Find answers to the most common questions about our IT services, cybersecurity solutions, support processes, and pricing.
            </p>
        </div>
    </div>
</section>

{{-- FAQ Search --}}
<section class="relative w-full py-10 border-t border-term-300 dark:border-white/5" aria-label="Search answers">
    <div class="w-full max-w-3xl mx-auto px-4 sm:px-6">
        <div x-data="{ search: '' }" class="relative">
            <label for="faq-search" class="sr-only">Search for answers</label>
            <input type="text" id="faq-search" x-model="search" placeholder="search answers: e.g. pricing, SLA, onboarding…"
                   class="term-input font-mono text-sm !pl-4">
        </div>
    </div>
</section>

{{-- FAQ Categories --}}
<section class="relative w-full py-12 lg:py-16 border-t border-term-300 dark:border-white/5" x-data="{ activeCategory: 'general', search: '' }" aria-label="Questions by category">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

        {{-- Category Tabs --}}
        <div class="flex flex-wrap gap-1.5 mb-10 justify-center" role="tablist" aria-label="FAQ categories">
            @foreach([
                'general' => 'General',
                'services' => 'Services',
                'security' => 'Cybersecurity',
                'pricing' => 'Pricing & Billing',
                'support' => 'Technical Support',
                'account' => 'Account & Portal',
            ] as $key => $label)
            <button @click="activeCategory = '{{ $key }}'"
                    :class="activeCategory === '{{ $key }}' ? 'term-tag-accent' : ''"
                    class="term-tag" role="tab">
                {{ strtoupper($label) }}
            </button>
            @endforeach
        </div>

        {{-- General --}}
        <div x-show="activeCategory === 'general'" class="max-w-4xl mx-auto space-y-3">
            @php
            $faqs = [
                ['q' => 'What IT services does PerfectITSecurity provide?', 'a' => 'We provide comprehensive IT services including cybersecurity (penetration testing, vulnerability assessments, security audits), managed IT support, cloud infrastructure, web and software development, IT consulting, and digital transformation services.'],
                ['q' => 'Which industries do you serve?', 'a' => 'We serve a wide range of industries including healthcare, finance and banking, legal, education, e-commerce, manufacturing, and government organisations. Our solutions are tailored to each industry\'s specific compliance and operational requirements.'],
                ['q' => 'Do you provide international support?', 'a' => 'Yes. We support clients across the United Kingdom, United States, Bangladesh, Europe, and other regions. Our team operates across multiple time zones and can provide remote and on-site support as needed.'],
                ['q' => 'What are your support hours?', 'a' => 'Our standard support operates Monday to Friday, 9 AM to 6 PM local time. Premium and enterprise customers have access to 24/7/365 emergency support with guaranteed response times based on their SLA tier.'],
                ['q' => 'How do I get started with PerfectITSecurity?', 'a' => 'Simply visit our Contact page or request a free consultation. Our solutions team will assess your requirements, recommend the right services, and provide a tailored proposal with transparent pricing.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="border border-term-300 dark:border-white/10 overflow-hidden transition-colors hover:border-accent/40" :class="open ? '!border-accent/50' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left" :aria-expanded="open.toString()">
                    <span class="font-mono text-[10px] tracking-[0.2em] text-accent-soft flex-shrink-0">FAQ://{{ str_pad((string)($i + 1), 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex-1 text-sm sm:text-base font-semibold text-navy-900 dark:text-white">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-term-700 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-term-800 pl-0 sm:pl-[72px]">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Services --}}
        <div x-show="activeCategory === 'services'" class="max-w-4xl mx-auto space-y-3" style="display: none;">
            @php
            $faqs = [
                ['q' => 'What cybersecurity services do you offer?', 'a' => 'Our cybersecurity services include penetration testing, vulnerability assessments, web application security testing, network security audits, security compliance audits (ISO 27001, SOC 2, HIPAA, GDPR), incident response, and managed security services.'],
                ['q' => 'Do you offer managed IT support?', 'a' => 'Yes. Our managed IT support includes 24/7 monitoring, remote help desk, server management, network infrastructure management, Microsoft 365 support, backup and disaster recovery, and proactive maintenance.'],
                ['q' => 'Can you help with cloud migration?', 'a' => 'Absolutely. We provide end-to-end cloud migration services including assessment, planning, migration execution, and post-migration optimisation. We work with AWS, Microsoft Azure, and Google Cloud Platform.'],
                ['q' => 'What development services are available?', 'a' => 'We offer web development, mobile app development, CRM/ERP development, e-commerce development, API development, custom software development, and UI/UX design services.'],
                ['q' => 'Do you provide IT training?', 'a' => 'Yes. We offer customised IT training programmes for organisations, including cybersecurity awareness training, cloud platform training, Microsoft 365 training, and technical skills development.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="border border-term-300 dark:border-white/10 overflow-hidden transition-colors hover:border-accent/40" :class="open ? '!border-accent/50' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left" :aria-expanded="open.toString()">
                    <span class="font-mono text-[10px] tracking-[0.2em] text-accent-soft flex-shrink-0">FAQ://{{ str_pad((string)($i + 101), 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex-1 text-sm sm:text-base font-semibold text-navy-900 dark:text-white">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-term-700 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-term-800 pl-0 sm:pl-[72px]">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Cybersecurity --}}
        <div x-show="activeCategory === 'security'" class="max-w-4xl mx-auto space-y-3" style="display: none;">
            @php
            $faqs = [
                ['q' => 'How often should we conduct a penetration test?', 'a' => 'We recommend at least annually, with additional tests after major infrastructure changes, new application deployments, or following a security incident. Regulated industries may require more frequent testing.'],
                ['q' => 'What compliance frameworks do you support?', 'a' => 'We support ISO 27001, SOC 2 Type II, HIPAA, GDPR, PCI DSS, Cyber Essentials, and NIST frameworks. Our compliance experts can guide you through the entire certification process.'],
                ['q' => 'Do you offer incident response services?', 'a' => 'Yes. Our incident response team provides 24/7 emergency support for security breaches, ransomware attacks, data breaches, and other cyber incidents. We follow industry-standard IR playbooks for rapid containment and recovery.'],
                ['q' => 'What is your approach to security audits?', 'a' => 'We conduct comprehensive security audits covering network security, application security, access controls, data protection, physical security, and policy compliance. Our audits include detailed findings reports with actionable remediation recommendations.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="border border-term-300 dark:border-white/10 overflow-hidden transition-colors hover:border-accent/40" :class="open ? '!border-accent/50' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left" :aria-expanded="open.toString()">
                    <span class="font-mono text-[10px] tracking-[0.2em] text-accent-soft flex-shrink-0">FAQ://{{ str_pad((string)($i + 201), 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex-1 text-sm sm:text-base font-semibold text-navy-900 dark:text-white">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-term-700 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-term-800 pl-0 sm:pl-[72px]">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pricing --}}
        <div x-show="activeCategory === 'pricing'" class="max-w-4xl mx-auto space-y-3" style="display: none;">
            @php
            $faqs = [
                ['q' => 'How is pricing determined?', 'a' => 'Our pricing depends on the service type, complexity, scope, and your location. We offer fixed-price projects, hourly rates, monthly retainer plans, and custom enterprise pricing. Visit our Pricing page for detailed plans.'],
                ['q' => 'Do you offer free consultations?', 'a' => 'Yes. We provide a free initial consultation to understand your requirements and recommend the best solution. There is no obligation to proceed after the consultation.'],
                ['q' => 'What payment methods do you accept?', 'a' => 'We accept bank transfers, credit/debit cards (via Stripe), PayPal, and other regional payment methods. Enterprise clients can also arrange NET-30 or NET-60 payment terms.'],
                ['q' => 'Is there a refund policy?', 'a' => 'Yes. We have a transparent refund policy. If you are not satisfied with our services within the specified period, you may request a refund. Please review our Refund Policy page for full details.'],
                ['q' => 'Do prices vary by country?', 'a' => 'Yes. Our pricing is adjusted for different markets to reflect local economic conditions. You can see country-specific pricing on our Services page by selecting your region.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="border border-term-300 dark:border-white/10 overflow-hidden transition-colors hover:border-accent/40" :class="open ? '!border-accent/50' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left" :aria-expanded="open.toString()">
                    <span class="font-mono text-[10px] tracking-[0.2em] text-accent-soft flex-shrink-0">FAQ://{{ str_pad((string)($i + 301), 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex-1 text-sm sm:text-base font-semibold text-navy-900 dark:text-white">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-term-700 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-term-800 pl-0 sm:pl-[72px]">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Support --}}
        <div x-show="activeCategory === 'support'" class="max-w-4xl mx-auto space-y-3" style="display: none;">
            @php
            $faqs = [
                ['q' => 'How do I submit a support ticket?', 'a' => 'Registered customers can submit support tickets directly from the Customer Portal under "My Tickets". You can also call our support line or email support@techsupport.com for urgent issues.'],
                ['q' => 'What are your SLA response times?', 'a' => 'Response times depend on your support tier: Basic (4hr response / 8hr resolution), Standard (2hr / 4hr), Priority (1hr / 2hr), Critical (30min / 1hr), and Emergency (15min / 30min). See our SLA page for details.'],
                ['q' => 'Can I track my support ticket status?', 'a' => 'Yes. Log in to your Customer Portal to view all your tickets, their current status, SLA deadlines, and conversation history with our support team.'],
                ['q' => 'Do you provide on-site support?', 'a' => 'Yes. We offer on-site support for customers in our service areas. On-site support can be arranged as part of your support plan or as a one-time visit. Contact us for availability and pricing.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="border border-term-300 dark:border-white/10 overflow-hidden transition-colors hover:border-accent/40" :class="open ? '!border-accent/50' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left" :aria-expanded="open.toString()">
                    <span class="font-mono text-[10px] tracking-[0.2em] text-accent-soft flex-shrink-0">FAQ://{{ str_pad((string)($i + 401), 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex-1 text-sm sm:text-base font-semibold text-navy-900 dark:text-white">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-term-700 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-term-800 pl-0 sm:pl-[72px]">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Account --}}
        <div x-show="activeCategory === 'account'" class="max-w-4xl mx-auto space-y-3" style="display: none;">
            @php
            $faqs = [
                ['q' => 'How do I create an account?', 'a' => 'Click "Register" in the top navigation. Fill in your details, verify your email address via the OTP sent to your inbox, and then verify your phone number. Once both are verified, you will have full access to the Customer Portal.'],
                ['q' => 'How do I reset my password?', 'a' => 'Click "Forgot Password" on the login page, enter your email address, and follow the instructions in the reset email. For security, reset links expire after 60 minutes.'],
                ['q' => 'Can I update my company information?', 'a' => 'Yes. Log in to the Customer Portal and navigate to "My Profile" to update your personal and company information. For company-level changes, contact our support team.'],
                ['q' => 'How do I verify my account?', 'a' => 'After registration, you will receive an email OTP and a phone OTP. Enter both in the Customer Portal verification section. Verified accounts have full access to all portal features.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="border border-term-300 dark:border-white/10 overflow-hidden transition-colors hover:border-accent/40" :class="open ? '!border-accent/50' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left" :aria-expanded="open.toString()">
                    <span class="font-mono text-[10px] tracking-[0.2em] text-accent-soft flex-shrink-0">FAQ://{{ str_pad((string)($i + 501), 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex-1 text-sm sm:text-base font-semibold text-navy-900 dark:text-white">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-term-700 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-5 sm:px-6 pb-5 sm:pb-6">
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-term-800 pl-0 sm:pl-[72px]">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-labelledby="faq-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">FAQ://ESCALATE</span>
        <h2 id="faq-cta-heading" class="mt-4 font-display text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 dark:text-white">Still have questions?</h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-term-800 max-w-2xl mx-auto">Our team is ready to help you find the right IT solution for your business.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('contact') }}" class="term-btn term-btn-lg">
                Contact Us
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="{{ route('services.index') }}" class="term-btn term-btn-lg term-btn-ghost">
                Browse Services
            </a>
        </div>
    </div>
</section>

@endsection
