@extends('layouts.public')

@section('title', 'Global Offices — Our International Presence')
@section('description', 'PerfectITSecurity operates from strategic locations across the globe, providing 24/7 IT support, cybersecurity, and cloud services to enterprises worldwide.')

@section('content')

{{-- HERO — CONTACT://OFFICES --}}
<section class="relative w-full overflow-hidden" aria-labelledby="offices-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16 text-center">
        <div class="flex flex-wrap items-center justify-center gap-2.5 mb-7">
            <span class="term-tag term-tag-accent">CONTACT://OFFICES</span>
            <span class="term-tag">Global Presence</span>
        </div>
        <h1 id="offices-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
            OPERATING ACROSS <span class="text-accent-soft">15+ COUNTRIES</span>
        </h1>
        <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl mx-auto">
            Our strategic global presence ensures we deliver 24/7/365 support with local expertise and international standards.
        </p>
    </div>
</section>

{{-- Stats --}}
<section class="relative w-full py-10 border-t border-term-300 dark:border-white/5" aria-label="Global statistics">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            @foreach([
                ['value' => '15+', 'label' => 'Countries'],
                ['value' => '24/7', 'label' => 'Global Coverage'],
                ['value' => '5', 'label' => 'Continents'],
                ['value' => '500+', 'label' => 'Team Members'],
            ] as $stat)
            <div class="term-panel-2 px-5 py-5 text-center">
                <dd class="font-mono text-3xl lg:text-4xl font-bold text-navy-900 dark:text-white">{{ $stat['value'] }}</dd>
                <dt class="term-sec-label mt-2">{{ strtoupper($stat['label']) }}</dt>
            </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Offices --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-labelledby="hq-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="01" label="OFFICES://REGIONAL-HQ" title="Regional headquarters." desc="Each office serves as a regional hub for operations, client engagement, and talent development." />
        <span id="hq-heading" class="sr-only">Regional headquarters</span>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach([
                ['city' => 'London', 'country' => 'United Kingdom', 'code' => 'LON', 'region' => 'EMEA HQ', 'address' => '1 Canada Square, Canary Wharf, London E14 5AB', 'phone' => '+44 20 7946 0958', 'services' => ['Cybersecurity', 'Penetration Testing', 'IT Consulting'], 'timezone' => 'GMT/BST'],
                ['city' => 'New York', 'country' => 'United States', 'code' => 'NYC', 'region' => 'Americas HQ', 'address' => '1 World Trade Center, New York, NY 10007', 'phone' => '+1 (212) 555-0100', 'services' => ['Cloud Architecture', 'Managed IT', 'Compliance'], 'timezone' => 'EST/CST'],
                ['city' => 'Dhaka', 'country' => 'Bangladesh', 'code' => 'DAC', 'region' => 'Asia-Pacific Hub', 'address' => 'Gulshan Avenue, Dhaka 1212', 'phone' => '+880 2 5501 2345', 'services' => ['Web Development', 'Software Engineering', 'Remote IT Support'], 'timezone' => 'BST (GMT+6)'],
                ['city' => 'Singapore', 'country' => 'Singapore', 'code' => 'SIN', 'region' => 'APAC Regional', 'address' => '1 Raffles Place, Singapore 048616', 'phone' => '+65 6100 0100', 'services' => ['Cloud Security', 'Network Infrastructure', 'AI Solutions'], 'timezone' => 'SGT (GMT+8)'],
                ['city' => 'Dubai', 'country' => 'United Arab Emirates', 'code' => 'DXB', 'region' => 'MENA Regional', 'address' => 'DIFC, Dubai, UAE', 'phone' => '+971 4 555 0100', 'services' => ['IT Consulting', 'Digital Transformation', 'Managed Services'], 'timezone' => 'GST (GMT+4)'],
                ['city' => 'Sydney', 'country' => 'Australia', 'code' => 'SYD', 'region' => 'Oceania', 'address' => '1 Macquarie Place, Sydney NSW 2000', 'phone' => '+61 2 5550 0100', 'services' => ['Cloud Migration', 'Disaster Recovery', 'IT Training'], 'timezone' => 'AEST (GMT+10)'],
            ] as $office)
            <div class="term-panel p-6 group">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-11 h-11 border border-accent/40 bg-accent/5 flex items-center justify-center font-mono text-xs font-bold text-accent-soft flex-shrink-0" aria-hidden="true">{{ $office['code'] }}</span>
                    <div class="min-w-0">
                        <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white">{{ $office['city'] }}</h3>
                        <span class="term-tag mt-1">{{ strtoupper($office['region']) }}</span>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-term-800 mb-1">{{ $office['country'] }}</p>
                <p class="text-xs text-term-700 mb-1">{{ $office['address'] }}</p>
                <p class="font-mono text-[11px] tracking-wider text-term-700 mb-3">TEL:// {{ $office['phone'] }} // TZ:// {{ $office['timezone'] }}</p>
                <div class="flex flex-wrap gap-1.5 pt-3 border-t border-term-300 dark:border-white/5">
                    @foreach($office['services'] as $svc)
                    <span class="term-tag">{{ strtoupper($svc) }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Service regions --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-labelledby="served-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="02" label="OFFICES://COVERAGE" title="Countries we serve." desc="Our services are available across 15+ countries with localised pricing and support." />
        <span id="served-heading" class="sr-only">Countries served</span>

        <div class="mt-10 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 max-w-5xl mx-auto">
            @php
            $servedCountries = App\Models\Country::where('is_active', true)->orderBy('name')->get();
            $flagMap = ['US' => 'USA', 'GB' => 'GBR', 'BD' => 'BGD', 'SG' => 'SGP', 'AE' => 'ARE', 'AU' => 'AUS', 'DE' => 'DEU', 'FR' => 'FRA', 'CA' => 'CAN', 'IN' => 'IND'];
            @endphp
            @forelse($servedCountries as $country)
            <div class="term-panel-2 flex items-center gap-2.5 px-3 py-3">
                <span class="font-mono text-[10px] font-bold text-accent-soft flex-shrink-0" aria-hidden="true">{{ $flagMap[strtoupper($country->code)] ?? 'GLB' }}</span>
                <div class="min-w-0">
                    <div class="text-sm font-medium text-navy-900 dark:text-white truncate">{{ $country->name }}</div>
                    <div class="font-mono text-[10px] tracking-wider text-term-700">{{ $country->currency_code }}</div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center text-sm text-slate-600 dark:text-term-800 py-8">Country information available upon request.</div>
            @endforelse
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-labelledby="offices-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">OFFICES://ENGAGE</span>
        <h2 id="offices-cta-heading" class="mt-4 font-display text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 dark:text-white">Ready to work with us?</h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-term-800">Contact the nearest office for a free consultation and discover how we can support your business globally.</p>
        <a href="{{ route('contact') }}" class="term-btn term-btn-lg mt-8">
            Contact Us Today
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

@endsection
