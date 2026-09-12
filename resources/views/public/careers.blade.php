@extends('layouts.public')

@section('title', 'Careers — Join Our Global IT Team')
@section('description', 'Join TechSupport Solutions — a world-class IT services company. We are hiring cybersecurity experts, cloud engineers, developers, and IT support specialists across multiple countries.')

@section('content')

{{-- HERO --}}
<section class="relative w-full py-24 lg:py-32 bg-space-deep overflow-hidden z-10">
    {{-- Global 3D hero scene (same implementation as Home) --}}
    <x-hero-scene />
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="max-w-2xl text-left">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-6">
            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
            We're Hiring
        </div>
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-tight mb-6">
            Build the Future of<br>
            <span class="gradient-text-cyber">Cybersecurity & Cloud</span>
        </h1>
        <p class="text-lg sm:text-xl text-slate-300 leading-relaxed mb-10">
            Join a global team of elite IT professionals protecting enterprises worldwide. We offer competitive compensation, remote flexibility, continuous learning, and the chance to work on cutting-edge infrastructure.
        </p>
        <div class="flex flex-wrap items-center justify-start gap-4">
            <a href="#positions" class="btn btn-lg text-white rounded-2xl px-8 py-4 font-semibold" style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 10px 35px rgba(37,99,235,0.45);">
                View Open Positions
            </a>
            <a href="#culture" class="btn btn-lg btn-glass rounded-2xl px-8 py-4 font-semibold border-white/20 hover:border-cyan-400/50 hover:bg-white/10 transition-all">
                Our Culture
            </a>
        </div>
        </div>
    </div>
</section>

{{-- WHY JOIN US --}}
<section class="relative w-full py-20 lg:py-28 bg-[#020617] border-y border-white/10 z-10" id="culture">
    <div class="max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-4">
                Life at TechSupport
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                Why Top Engineers Choose Us.
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @foreach([
                ['title' => 'Global Remote-First', 'desc' => 'Work from anywhere in the world. Our distributed team spans 15+ countries with flexible hours that respect your timezone.', 'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'color' => 'text-cyan-400'],
                ['title' => 'Cutting-Edge Tech', 'desc' => 'Work with AWS, Azure, Kubernetes, AI/ML security tools, and zero-trust architectures on enterprise-scale deployments.', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', 'color' => 'text-blue-400'],
                ['title' => 'Continuous Learning', 'desc' => 'Annual certification budgets, internal tech talks, conference sponsorships, and a dedicated learning management system.', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'text-violet-400'],
                ['title' => 'Competitive Pay', 'desc' => 'Above-market salaries, performance bonuses, equity options for senior roles, and transparent compensation bands.', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-400'],
                ['title' => 'Health & Wellness', 'desc' => 'Comprehensive health insurance, mental health support, gym stipends, and generous paid time off across all regions.', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'color' => 'text-rose-400'],
                ['title' => 'Career Growth', 'desc' => 'Clear promotion tracks, mentorship programmes, cross-team rotations, and leadership development for high performers.', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color' => 'text-amber-400'],
            ] as $benefit)
            <div class="cosmic-card p-8 group">
                <div class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 {{ $benefit['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $benefit['icon'] }}"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">{{ $benefit['title'] }}</h3>
                <p class="text-sm text-slate-400 leading-relaxed">{{ $benefit['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- OPEN POSITIONS --}}
<section class="relative w-full py-20 lg:py-28 bg-[#030712] z-10" id="positions">
    <div class="max-w-[1720px] mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-xs font-semibold uppercase tracking-widest text-cyan-400 mb-4">
                Open Positions
            </div>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                Find Your Role.
            </h2>
            <p class="text-base text-slate-400 mt-4">We are always looking for exceptional talent to join our global team.</p>
        </div>

        <div class="space-y-4 max-w-4xl mx-auto">
            @foreach([
                ['title' => 'Senior Cybersecurity Analyst', 'dept' => 'Security Operations', 'location' => 'Remote / London / New York', 'type' => 'Full-time', 'salary' => '$90K – $140K', 'tags' => ['SOC', 'Incident Response', 'SIEM']],
                ['title' => 'Cloud Infrastructure Engineer', 'dept' => 'Cloud Architecture', 'location' => 'Remote / Singapore / Dubai', 'type' => 'Full-time', 'salary' => '$100K – $160K', 'tags' => ['AWS', 'Azure', 'Kubernetes']],
                ['title' => 'Full-Stack Developer', 'dept' => 'Engineering', 'location' => 'Remote / Dhaka / London', 'type' => 'Full-time', 'salary' => '$70K – $120K', 'tags' => ['Laravel', 'React', 'DevOps']],
                ['title' => 'IT Support Specialist', 'dept' => 'Technical Support', 'location' => 'Remote / Dhaka', 'type' => 'Full-time', 'salary' => '$30K – $55K', 'tags' => ['M365', 'Networking', 'Helpdesk']],
                ['title' => 'Penetration Tester', 'dept' => 'Security Assessment', 'location' => 'Remote / London', 'type' => 'Full-time', 'salary' => '$95K – $150K', 'tags' => ['Web App', 'Network', 'OSCP']],
                ['title' => 'Project Manager', 'dept' => 'Delivery', 'location' => 'Remote / London / New York', 'type' => 'Full-time', 'salary' => '$80K – $130K', 'tags' => ['Agile', 'PMP', 'Client Relations']],
            ] as $job)
            <div class="cosmic-card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $job['title'] }}</h3>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">{{ $job['type'] }}</span>
                    </div>
                    <p class="text-sm text-slate-400 mb-2">{{ $job['dept'] }} · {{ $job['location'] }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($job['tags'] as $tag)
                        <span class="text-xs px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-4 flex-shrink-0">
                    <span class="text-sm font-bold text-emerald-400">{{ $job['salary'] }}</span>
                    <a href="{{ route('contact') }}" class="btn btn-sm rounded-xl bg-cyan-500 text-black font-semibold hover:bg-cyan-400 transition-colors">
                        Apply Now
                    </a>
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
            Don't See Your Role?
        </h2>
        <p class="text-lg text-slate-300 mb-8 leading-relaxed">
            We are always interested in hearing from exceptional people. Send us your CV and tell us how you can contribute to our mission.
        </p>
        <a href="{{ route('contact') }}" class="btn btn-lg text-white rounded-2xl px-10 py-5 font-bold" style="background: linear-gradient(135deg, #16A34A, #2563EB); box-shadow: 0 12px 40px rgba(37,99,235,0.5);">
            Send Open Application
        </a>
    </div>
</section>

@endsection
