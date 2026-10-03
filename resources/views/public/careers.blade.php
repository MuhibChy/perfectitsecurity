@extends('layouts.public')

@section('title', 'Careers — Join Our Global IT Team')
@section('description', 'Join PerfectITSecurity — a world-class IT services company. We are hiring cybersecurity experts, cloud engineers, developers, and IT support specialists across multiple countries.')

@section('content')

{{-- HERO — CAREERS://OPEN-ROLES --}}
<section class="relative w-full overflow-hidden" aria-labelledby="careers-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16">
        <div class="max-w-2xl">
            <div class="flex flex-wrap items-center gap-2.5 mb-7">
                <span class="term-tag term-tag-accent">CAREERS://OPEN-ROLES</span>
                <span class="term-status text-accent-soft"><span class="term-status-dot" aria-hidden="true"></span>Hiring</span>
            </div>
            <h1 id="careers-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
                BUILD THE FUTURE OF <span class="text-accent-soft">CYBERSECURITY + CLOUD</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800">
                Join a global team of elite IT professionals protecting enterprises worldwide. We offer competitive compensation, remote flexibility, continuous learning, and the chance to work on cutting-edge infrastructure.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#positions" class="term-btn term-btn-lg">
                    View Open Positions
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="#culture" class="term-btn term-btn-lg term-btn-ghost">
                    Our Culture
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Why join us --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" id="culture" aria-labelledby="culture-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="01" label="CAREERS://CULTURE" title="Why top engineers choose us." desc="Life at PerfectITSecurity — distributed, technical, and growth-driven." />
        <span id="culture-heading" class="sr-only">Why join us</span>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach([
                ['title' => 'Global Remote-First', 'desc' => 'Work from anywhere in the world. Our distributed team spans 15+ countries with flexible hours that respect your timezone.', 'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064'],
                ['title' => 'Cutting-Edge Tech', 'desc' => 'Work with AWS, Azure, Kubernetes, AI/ML security tools, and zero-trust architectures on enterprise-scale deployments.', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                ['title' => 'Continuous Learning', 'desc' => 'Annual certification budgets, internal tech talks, conference sponsorships, and a dedicated learning management system.', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['title' => 'Competitive Pay', 'desc' => 'Above-market salaries, performance bonuses, equity options for senior roles, and transparent compensation bands.', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['title' => 'Health & Wellness', 'desc' => 'Comprehensive health insurance, mental health support, gym stipends, and generous paid time off across all regions.', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                ['title' => 'Career Growth', 'desc' => 'Clear promotion tracks, mentorship programmes, cross-team rotations, and leadership development for high performers.', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ] as $benefit)
            <div class="term-panel p-7 sm:p-8 group">
                <div class="font-mono text-[11px] tracking-[0.24em] text-accent-soft mb-4">{{ str_pad((string)($loop->iteration), 2, '0', STR_PAD_LEFT) }}</div>
                <svg class="w-6 h-6 text-term-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $benefit['icon'] }}"/></svg>
                <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white">{{ $benefit['title'] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-term-800">{{ $benefit['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Open positions --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" id="positions" aria-labelledby="positions-heading">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <x-section-head num="02" label="CAREERS://POSITIONS" title="Find your role." desc="We are always looking for exceptional talent to join our global team." />
        <span id="positions-heading" class="sr-only">Open positions</span>

        <div class="mt-10 space-y-3 sm:space-y-4 max-w-4xl mx-auto">
            @foreach([
                ['title' => 'Senior Cybersecurity Analyst', 'dept' => 'Security Operations', 'location' => 'Remote / London / New York', 'type' => 'Full-time', 'salary' => '$90K – $140K', 'tags' => ['SOC', 'Incident Response', 'SIEM']],
                ['title' => 'Cloud Infrastructure Engineer', 'dept' => 'Cloud Architecture', 'location' => 'Remote / Singapore / Dubai', 'type' => 'Full-time', 'salary' => '$100K – $160K', 'tags' => ['AWS', 'Azure', 'Kubernetes']],
                ['title' => 'Full-Stack Developer', 'dept' => 'Engineering', 'location' => 'Remote / Dhaka / London', 'type' => 'Full-time', 'salary' => '$70K – $120K', 'tags' => ['Laravel', 'React', 'DevOps']],
                ['title' => 'IT Support Specialist', 'dept' => 'Technical Support', 'location' => 'Remote / Dhaka', 'type' => 'Full-time', 'salary' => '$30K – $55K', 'tags' => ['M365', 'Networking', 'Helpdesk']],
                ['title' => 'Penetration Tester', 'dept' => 'Security Assessment', 'location' => 'Remote / London', 'type' => 'Full-time', 'salary' => '$95K – $150K', 'tags' => ['Web App', 'Network', 'OSCP']],
                ['title' => 'Project Manager', 'dept' => 'Delivery', 'location' => 'Remote / London / New York', 'type' => 'Full-time', 'salary' => '$80K – $130K', 'tags' => ['Agile', 'PMP', 'Client Relations']],
            ] as $job)
            <div class="term-panel p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5 mb-2">
                        <h3 class="font-display text-lg font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $job['title'] }}</h3>
                        <span class="term-tag">{{ strtoupper($job['type']) }}</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-term-800 mb-2">{{ $job['dept'] }} // {{ $job['location'] }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($job['tags'] as $tag)
                        <span class="term-tag">{{ strtoupper($tag) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-4 flex-shrink-0">
                    <span class="font-mono text-sm font-bold text-accent-soft">{{ $job['salary'] }}</span>
                    <a href="{{ route('contact') }}" class="term-btn term-btn-sm">
                        Apply Now
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-labelledby="careers-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">CAREERS://OPEN-APPLICATION</span>
        <h2 id="careers-cta-heading" class="mt-4 font-display text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 dark:text-white">
            Don't see your role?
        </h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-term-800">
            We are always interested in hearing from exceptional people. Send us your CV and tell us how you can contribute to our mission.
        </p>
        <a href="{{ route('contact') }}" class="term-btn term-btn-lg mt-8">
            Send Open Application
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

@endsection
