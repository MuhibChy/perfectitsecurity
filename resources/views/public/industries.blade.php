@extends('layouts.public')

@section('title', 'Industries We Serve — Global IT Solutions')
@section('description', 'TechSupport Solutions serves enterprises across healthcare, finance, e-commerce, education, manufacturing, and government with tailored IT, cybersecurity, and cloud services.')

@section('content')

{{-- HERO --}}
<section class="relative w-full py-24 lg:py-32 bg-space-deep overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="relative z-10 max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-6">
            Industry Expertise
        </div>
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-tight mb-6">
            Tailored IT Solutions for<br>
            <span class="gradient-text-cyber">Every Industry</span>
        </h1>
        <p class="text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto leading-relaxed">
            We understand that every industry has unique compliance requirements, security challenges, and operational demands. Our solutions are engineered to address your sector-specific needs.
        </p>
    </div>
</section>

{{-- INDUSTRIES --}}
<section class="relative w-full py-20 lg:py-28 bg-[#020617] border-y border-white/10 z-10">
    <div class="max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                [
                    'name' => 'Healthcare & Life Sciences',
                    'desc' => 'HIPAA-compliant IT infrastructure, secure patient data management, telemedicine platforms, and clinical system support.',
                    'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                    'color' => 'text-rose-400',
                    'bg' => 'bg-rose-500/10 border-rose-500/20',
                    'tags' => ['HIPAA', 'EHR Systems', 'Telemedicine', 'Data Security'],
                ],
                [
                    'name' => 'Financial Services & FinTech',
                    'desc' => 'PCI-DSS compliance, real-time fraud detection, secure payment gateways, and regulatory reporting systems.',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'color' => 'text-emerald-400',
                    'bg' => 'bg-emerald-500/10 border-emerald-500/20',
                    'tags' => ['PCI-DSS', 'Fraud Detection', 'Payment Security', 'RegTech'],
                ],
                [
                    'name' => 'E-Commerce & Retail',
                    'desc' => 'Scalable cloud infrastructure, CDN optimization, inventory management systems, and omnichannel security.',
                    'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z',
                    'color' => 'text-amber-400',
                    'bg' => 'bg-amber-500/10 border-amber-500/20',
                    'tags' => ['Scalable Cloud', 'CDN', 'Inventory Systems', 'Security'],
                ],
                [
                    'name' => 'Education & EdTech',
                    'desc' => 'Learning management systems, student data protection, virtual classroom infrastructure, and campus network security.',
                    'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'color' => 'text-blue-400',
                    'bg' => 'bg-blue-500/10 border-blue-500/20',
                    'tags' => ['LMS', 'FERPA', 'Virtual Classrooms', 'Campus IT'],
                ],
                [
                    'name' => 'Manufacturing & Industrial',
                    'desc' => 'OT/IT convergence, IoT security, SCADA system protection, and supply chain digitisation.',
                    'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                    'color' => 'text-violet-400',
                    'bg' => 'bg-violet-500/10 border-violet-500/20',
                    'tags' => ['OT/IT', 'IoT Security', 'SCADA', 'Industry 4.0'],
                ],
                [
                    'name' => 'Government & Public Sector',
                    'desc' => 'FedRAMP compliance, classified network security, citizen data protection, and critical infrastructure defence.',
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'color' => 'text-cyan-400',
                    'bg' => 'bg-cyan-500/10 border-cyan-500/20',
                    'tags' => ['FedRAMP', 'Classified', 'Critical Infrastructure', 'Zero Trust'],
                ],
                [
                    'name' => 'Legal & Professional Services',
                    'desc' => 'Client privilege protection, secure document management, e-discovery support, and regulatory compliance.',
                    'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                    'color' => 'text-sky-400',
                    'bg' => 'bg-sky-500/10 border-sky-500/20',
                    'tags' => ['Client Privilege', 'Document Security', 'E-Discovery', 'Compliance'],
                ],
                [
                    'name' => 'Energy & Utilities',
                    'desc' => 'Critical infrastructure protection, SCADA security, smart grid defence, and environmental compliance monitoring.',
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'color' => 'text-yellow-400',
                    'bg' => 'bg-yellow-500/10 border-yellow-500/20',
                    'tags' => ['SCADA', 'Smart Grid', 'ICS Security', 'NERC CIP'],
                ],
                [
                    'name' => 'Media & Entertainment',
                    'desc' => 'Content protection, DRM systems, high-bandwidth streaming infrastructure, and intellectual property security.',
                    'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                    'color' => 'text-pink-400',
                    'bg' => 'bg-pink-500/10 border-pink-500/20',
                    'tags' => ['DRM', 'Content Protection', 'Streaming', 'IP Security'],
                ],
            ] as $industry)
            <div class="cosmic-card p-8 group">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 transition-all duration-300 group-hover:scale-110 {{ $industry['bg'] }} border">
                    <svg class="w-7 h-7 {{ $industry['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $industry['icon'] }}"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors">{{ $industry['name'] }}</h3>
                <p class="text-sm text-slate-400 leading-relaxed mb-5">{{ $industry['desc'] }}</p>
                <div class="flex flex-wrap gap-2 pt-4 border-t border-white/10">
                    @foreach($industry['tags'] as $tag)
                    <span class="text-xs px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-24 bg-space-radial overflow-hidden z-10">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-cyan-500/10 blur-[120px]"></div>
    </div>
    <div class="relative z-10 max-w-[1000px] mx-auto px-6 text-center">
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-6">
            Need Industry-Specific IT Solutions?
        </h2>
        <p class="text-lg text-slate-300 mb-8 leading-relaxed">
            Contact us for a free consultation on how our solutions can meet your industry's unique compliance, security, and operational requirements.
        </p>
        <a href="{{ route('contact') }}" class="btn btn-lg text-white rounded-2xl px-10 py-5 font-bold" style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 12px 40px rgba(37,99,235,0.5);">
            Schedule Industry Consultation
        </a>
    </div>
</section>

@endsection
