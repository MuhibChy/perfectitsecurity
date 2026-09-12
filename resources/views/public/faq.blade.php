@extends('layouts.public')

@section('title', 'Frequently Asked Questions — TechSupport Solutions')
@section('description', 'Find answers to common questions about our IT support services, cybersecurity solutions, pricing, and more.')

@section('content')

{{-- Hero --}}
<section class="relative w-full py-28 lg:py-36 bg-space-radial border-b border-white/10 overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 relative z-10">
        <div class="max-w-4xl">
            <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Help Centre
            </div>
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.05] mb-8">
                Frequently Asked <span class="gradient-text-cyber">Questions</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-300 leading-relaxed font-normal">
                Find answers to the most common questions about our IT services, cybersecurity solutions, support processes, and pricing.
            </p>
        </div>
    </div>
</section>

{{-- FAQ Search --}}
<section class="relative w-full py-12 bg-[#030712] border-b border-white/5 z-10">
    <div class="w-full max-w-3xl mx-auto px-6">
        <div x-data="{ search: '' }" class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" placeholder="Search for answers..."
                   class="w-full pl-12 pr-4 py-4 bg-white/[0.03] border border-white/10 rounded-2xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/30 transition-all">
        </div>
    </div>
</section>

{{-- FAQ Categories --}}
<section class="relative w-full py-16 lg:py-24 bg-[#030712] z-10" x-data="{ activeCategory: 'general', search: '' }">
    <div class="w-full max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">

        {{-- Category Tabs --}}
        <div class="flex flex-wrap gap-2 mb-12 justify-center">
            @foreach([
                'general' => 'General',
                'services' => 'Services',
                'security' => 'Cybersecurity',
                'pricing' => 'Pricing & Billing',
                'support' => 'Technical Support',
                'account' => 'Account & Portal',
            ] as $key => $label)
            <button @click="activeCategory = '{{ $key }}'"
                    :class="activeCategory === '{{ $key }}' ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-300' : 'bg-white/[0.02] border-white/10 text-slate-400 hover:text-white hover:border-white/20'"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold border transition-all duration-200">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- General --}}
        <div x-show="activeCategory === 'general'" class="max-w-4xl mx-auto space-y-4">
            @php
            $faqs = [
                ['q' => 'What IT services does TechSupport Solutions provide?', 'a' => 'We provide comprehensive IT services including cybersecurity (penetration testing, vulnerability assessments, security audits), managed IT support, cloud infrastructure, web and software development, IT consulting, and digital transformation services.'],
                ['q' => 'Which industries do you serve?', 'a' => 'We serve a wide range of industries including healthcare, finance and banking, legal, education, e-commerce, manufacturing, and government organisations. Our solutions are tailored to each industry\'s specific compliance and operational requirements.'],
                ['q' => 'Do you provide international support?', 'a' => 'Yes. We support clients across the United Kingdom, United States, Bangladesh, Europe, and other regions. Our team operates across multiple time zones and can provide remote and on-site support as needed.'],
                ['q' => 'What are your support hours?', 'a' => 'Our standard support operates Monday to Friday, 9 AM to 6 PM local time. Premium and enterprise customers have access to 24/7/365 emergency support with guaranteed response times based on their SLA tier.'],
                ['q' => 'How do I get started with TechSupport Solutions?', 'a' => 'Simply visit our Contact page or request a free consultation. Our solutions team will assess your requirements, recommend the right services, and provide a tailored proposal with transparent pricing.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="cosmic-glass rounded-2xl border border-white/10 overflow-hidden transition-all duration-200" :class="open ? 'border-cyan-500/30' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="text-base font-semibold text-white pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <p class="text-slate-400 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Services --}}
        <div x-show="activeCategory === 'services'" class="max-w-4xl mx-auto space-y-4" style="display: none;">
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
            <div x-data="{ open: false }" class="cosmic-glass rounded-2xl border border-white/10 overflow-hidden transition-all duration-200" :class="open ? 'border-cyan-500/30' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="text-base font-semibold text-white pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <p class="text-slate-400 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Cybersecurity --}}
        <div x-show="activeCategory === 'security'" class="max-w-4xl mx-auto space-y-4" style="display: none;">
            @php
            $faqs = [
                ['q' => 'How often should we conduct a penetration test?', 'a' => 'We recommend at least annually, with additional tests after major infrastructure changes, new application deployments, or following a security incident. Regulated industries may require more frequent testing.'],
                ['q' => 'What compliance frameworks do you support?', 'a' => 'We support ISO 27001, SOC 2 Type II, HIPAA, GDPR, PCI DSS, Cyber Essentials, and NIST frameworks. Our compliance experts can guide you through the entire certification process.'],
                ['q' => 'Do you offer incident response services?', 'a' => 'Yes. Our incident response team provides 24/7 emergency support for security breaches, ransomware attacks, data breaches, and other cyber incidents. We follow industry-standard IR playbooks for rapid containment and recovery.'],
                ['q' => 'What is your approach to security audits?', 'a' => 'We conduct comprehensive security audits covering network security, application security, access controls, data protection, physical security, and policy compliance. Our audits include detailed findings reports with actionable remediation recommendations.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="cosmic-glass rounded-2xl border border-white/10 overflow-hidden transition-all duration-200" :class="open ? 'border-cyan-500/30' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="text-base font-semibold text-white pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <p class="text-slate-400 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pricing --}}
        <div x-show="activeCategory === 'pricing'" class="max-w-4xl mx-auto space-y-4" style="display: none;">
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
            <div x-data="{ open: false }" class="cosmic-glass rounded-2xl border border-white/10 overflow-hidden transition-all duration-200" :class="open ? 'border-cyan-500/30' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="text-base font-semibold text-white pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <p class="text-slate-400 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Support --}}
        <div x-show="activeCategory === 'support'" class="max-w-4xl mx-auto space-y-4" style="display: none;">
            @php
            $faqs = [
                ['q' => 'How do I submit a support ticket?', 'a' => 'Registered customers can submit support tickets directly from the Customer Portal under "My Tickets". You can also call our support line or email support@techsupport.com for urgent issues.'],
                ['q' => 'What are your SLA response times?', 'a' => 'Response times depend on your support tier: Basic (4hr response / 8hr resolution), Standard (2hr / 4hr), Priority (1hr / 2hr), Critical (30min / 1hr), and Emergency (15min / 30min). See our SLA page for details.'],
                ['q' => 'Can I track my support ticket status?', 'a' => 'Yes. Log in to your Customer Portal to view all your tickets, their current status, SLA deadlines, and conversation history with our support team.'],
                ['q' => 'Do you provide on-site support?', 'a' => 'Yes. We offer on-site support for customers in our service areas. On-site support can be arranged as part of your support plan or as a one-time visit. Contact us for availability and pricing.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="cosmic-glass rounded-2xl border border-white/10 overflow-hidden transition-all duration-200" :class="open ? 'border-cyan-500/30' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="text-base font-semibold text-white pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <p class="text-slate-400 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Account --}}
        <div x-show="activeCategory === 'account'" class="max-w-4xl mx-auto space-y-4" style="display: none;">
            @php
            $faqs = [
                ['q' => 'How do I create an account?', 'a' => 'Click "Register" in the top navigation. Fill in your details, verify your email address via the OTP sent to your inbox, and then verify your phone number. Once both are verified, you will have full access to the Customer Portal.'],
                ['q' => 'How do I reset my password?', 'a' => 'Click "Forgot Password" on the login page, enter your email address, and follow the instructions in the reset email. For security, reset links expire after 60 minutes.'],
                ['q' => 'Can I update my company information?', 'a' => 'Yes. Log in to the Customer Portal and navigate to "My Profile" to update your personal and company information. For company-level changes, contact our support team.'],
                ['q' => 'How do I verify my account?', 'a' => 'After registration, you will receive an email OTP and a phone OTP. Enter both in the Customer Portal verification section. Verified accounts have full access to all portal features.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div x-data="{ open: false }" class="cosmic-glass rounded-2xl border border-white/10 overflow-hidden transition-all duration-200" :class="open ? 'border-cyan-500/30' : ''">
                <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="text-base font-semibold text-white pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <p class="text-slate-400 leading-relaxed">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-24 bg-space-radial border-t border-white/10 z-10 text-center">
    <div class="w-full max-w-4xl mx-auto px-6">
        <h2 class="text-3xl sm:text-5xl font-black text-white mb-6">Still Have Questions?</h2>
        <p class="text-slate-300 text-lg mb-8 max-w-2xl mx-auto">Our team is ready to help you find the right IT solution for your business.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('contact') }}" class="btn btn-lg text-white font-bold px-8 py-4 rounded-2xl" style="background: linear-gradient(135deg, #16A34A, #2563EB);">
                Contact Us
            </a>
            <a href="{{ route('services.index') }}" class="btn btn-lg btn-glass font-bold px-8 py-4 rounded-2xl border-white/20 hover:border-cyan-400/50">
                Browse Services
            </a>
        </div>
    </div>
</section>

@endsection
