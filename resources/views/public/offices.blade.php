@extends('layouts.public')

@section('title', 'Global Offices — Our International Presence')
@section('description', 'TechSupport Solutions operates from strategic locations across the globe, providing 24/7 IT support, cybersecurity, and cloud services to enterprises worldwide.')

@section('content')

{{-- HERO --}}
<section class="relative w-full py-24 lg:py-32 bg-space-deep overflow-hidden z-10">
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="relative z-10 max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-6">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
            Global Presence
        </div>
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-tight mb-6">
            Operating Across<br>
            <span class="gradient-text-cyber">15+ Countries</span>
        </h1>
        <p class="text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto leading-relaxed">
            Our strategic global presence ensures we deliver 24/7/365 support with local expertise and international standards.
        </p>
    </div>
</section>

{{-- STATS --}}
<section class="relative w-full py-12 bg-[#040816]/90 backdrop-blur-2xl border-y border-white/10 z-20">
    <div class="max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            @foreach([
                ['value' => '15+', 'label' => 'Countries', 'color' => 'text-cyan-400'],
                ['value' => '24/7', 'label' => 'Global Coverage', 'color' => 'text-emerald-400'],
                ['value' => '5', 'label' => 'Continents', 'color' => 'text-violet-400'],
                ['value' => '500+', 'label' => 'Team Members', 'color' => 'text-blue-400'],
            ] as $stat)
            <div class="text-center">
                <div class="text-3xl lg:text-4xl font-black {{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div class="text-sm text-slate-400 mt-1">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- OFFICES --}}
<section class="relative w-full py-20 lg:py-28 bg-[#020617] border-b border-white/10 z-10">
    <div class="max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-4">Regional Headquarters</h2>
            <p class="text-base text-slate-400">Each office serves as a regional hub for operations, client engagement, and talent development.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['city' => 'London', 'country' => 'United Kingdom', 'flag' => '🇬🇧', 'region' => 'EMEA HQ', 'address' => '1 Canada Square, Canary Wharf, London E14 5AB', 'phone' => '+44 20 7946 0958', 'services' => ['Cybersecurity', 'Penetration Testing', 'IT Consulting'], 'timezone' => 'GMT/BST'],
                ['city' => 'New York', 'country' => 'United States', 'flag' => '🇺🇸', 'region' => 'Americas HQ', 'address' => '1 World Trade Center, New York, NY 10007', 'phone' => '+1 (212) 555-0100', 'services' => ['Cloud Architecture', 'Managed IT', 'Compliance'], 'timezone' => 'EST/CST'],
                ['city' => 'Dhaka', 'country' => 'Bangladesh', 'flag' => '🇧🇩', 'region' => 'Asia-Pacific Hub', 'address' => 'Gulshan Avenue, Dhaka 1212', 'phone' => '+880 2 5501 2345', 'services' => ['Web Development', 'Software Engineering', 'Remote IT Support'], 'timezone' => 'BST (GMT+6)'],
                ['city' => 'Singapore', 'country' => 'Singapore', 'flag' => '🇸🇬', 'region' => 'APAC Regional', 'address' => '1 Raffles Place, Singapore 048616', 'phone' => '+65 6100 0100', 'services' => ['Cloud Security', 'Network Infrastructure', 'AI Solutions'], 'timezone' => 'SGT (GMT+8)'],
                ['city' => 'Dubai', 'country' => 'United Arab Emirates', 'flag' => '🇦🇪', 'region' => 'MENA Regional', 'address' => 'DIFC, Dubai, UAE', 'phone' => '+971 4 555 0100', 'services' => ['IT Consulting', 'Digital Transformation', 'Managed Services'], 'timezone' => 'GST (GMT+4)'],
                ['city' => 'Sydney', 'country' => 'Australia', 'flag' => '🇦🇺', 'region' => 'Oceania', 'address' => '1 Macquarie Place, Sydney NSW 2000', 'phone' => '+61 2 5550 0100', 'services' => ['Cloud Migration', 'Disaster Recovery', 'IT Training'], 'timezone' => 'AEST (GMT+10)'],
            ] as $office)
            <div class="cosmic-card p-6 group">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-3xl">{{ $office['flag'] }}</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ $office['city'] }}</h3>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">{{ $office['region'] }}</span>
                    </div>
                </div>
                <p class="text-sm text-slate-400 mb-1">{{ $office['country'] }}</p>
                <p class="text-xs text-slate-500 mb-1">{{ $office['address'] }}</p>
                <p class="text-xs text-slate-500 mb-3">📞 {{ $office['phone'] }} · 🕐 {{ $office['timezone'] }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($office['services'] as $svc)
                    <span class="text-xs px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">{{ $svc }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- SERVICE REGIONS --}}
<section class="relative w-full py-20 lg:py-28 bg-[#030712] z-10">
    <div class="max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-4">Countries We Serve</h2>
            <p class="text-base text-slate-400">Our services are available across 15+ countries with localised pricing and support.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 max-w-5xl mx-auto">
            @php
            $servedCountries = App\Models\Country::where('is_active', true)->orderBy('name')->get();
            @endphp
            @forelse($servedCountries as $country)
            <div class="flex items-center gap-2.5 p-3 rounded-xl bg-white/[0.03] border border-white/5 hover:border-cyan-500/30 hover:bg-white/[0.06] transition-all">
                <span class="text-lg">{{ strtoupper($country->code) === 'US' ? '🇺🇸' : strtoupper($country->code) === 'GB' ? '🇬🇧' : strtoupper($country->code) === 'BD' ? '🇧🇩' : strtoupper($country->code) === 'SG' ? '🇸🇬' : strtoupper($country->code) === 'AE' ? '🇦🇪' : strtoupper($country->code) === 'AU' ? '🇦🇺' : strtoupper($country->code) === 'DE' ? '🇩🇪' : strtoupper($country->code) === 'FR' ? '🇫🇷' : strtoupper($country->code) === 'CA' ? '🇨🇦' : strtoupper($country->code) === 'IN' ? '🇮🇳' : '🌍' }}</span>
                <div>
                    <div class="text-sm font-medium text-white">{{ $country->name }}</div>
                    <div class="text-xs text-slate-500">{{ $country->currency_code }}</div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center text-slate-400 py-8">Country information available upon request.</div>
            @endforelse
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-24 bg-space-radial overflow-hidden z-10">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-cyan-500/10 blur-[120px]"></div>
    </div>
    <div class="relative z-10 max-w-[1000px] mx-auto px-6 text-center">
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-6">Ready to Work With Us?</h2>
        <p class="text-lg text-slate-300 mb-8 leading-relaxed">Contact the nearest office for a free consultation and discover how we can support your business globally.</p>
        <a href="{{ route('contact') }}" class="btn btn-lg text-white rounded-2xl px-10 py-5 font-bold" style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 12px 40px rgba(37,99,235,0.5);">
            Contact Us Today
        </a>
    </div>
</section>

@endsection
