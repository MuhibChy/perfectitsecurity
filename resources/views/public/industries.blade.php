@extends('layouts.public')

@section('title', 'Industries We Serve — Global IT Solutions')
@section('description', 'PerfectITSecurity serves enterprises across healthcare, finance, e-commerce, education, manufacturing, and government with tailored IT, cybersecurity, and cloud services.')

@section('content')

{{-- HERO — INDUSTRIES://SECTORS --}}
<section class="relative w-full overflow-hidden" aria-labelledby="industries-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16 text-center">
        <div class="flex flex-wrap items-center justify-center gap-2.5 mb-7">
            <span class="term-tag term-tag-accent">INDUSTRIES://SECTORS</span>
        </div>
        <h1 id="industries-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-6xl lg:text-7xl text-navy-900 dark:text-white text-balance">
            TAILORED IT SOLUTIONS FOR <span class="text-accent-soft">EVERY INDUSTRY</span>
        </h1>
        <p class="mt-6 text-base sm:text-lg lg:text-xl leading-relaxed text-slate-600 dark:text-term-800 max-w-3xl mx-auto">
            We understand that every industry has unique compliance requirements, security challenges, and operational demands. Our solutions are engineered to address your sector-specific needs.
        </p>
    </div>
</section>

{{-- Industries grid --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 border-t border-term-300 dark:border-white/5" aria-label="Industries">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach([
                [
                    'name' => 'Healthcare & Life Sciences',
                    'desc' => 'HIPAA-compliant IT infrastructure, secure patient data management, telemedicine platforms, and clinical system support.',
                    'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                    'tags' => ['HIPAA', 'EHR Systems', 'Telemedicine', 'Data Security'],
                    'challenges' => ['Patient-data protection and access control', 'Clinical workstation downtime', 'Appointment-system reliability', 'Backup and recovery assurance'],
                    'solutions' => ['Endpoint support and patch planning', 'Access reviews and MFA guidance', 'Backup verification and restore testing', 'Documented incident escalation'],
                ],
                [
                    'name' => 'Financial Services & FinTech',
                    'desc' => 'PCI-DSS compliance, real-time fraud detection, secure payment gateways, and regulatory reporting systems.',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'tags' => ['PCI-DSS', 'Fraud Detection', 'Payment Security', 'RegTech'],
                    'challenges' => ['Account takeover and phishing risk', 'Email and payment-workflow faults', 'Audit-trail gaps', 'Secure remote access'],
                    'solutions' => ['Security hardening and MFA rollout', 'Email-delivery troubleshooting', 'Centralised ticket and change records', 'VPN and device support'],
                ],
                [
                    'name' => 'E-Commerce & Retail',
                    'desc' => 'Scalable cloud infrastructure, CDN optimization, inventory management systems, and omnichannel security.',
                    'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z',
                    'tags' => ['Scalable Cloud', 'CDN', 'Inventory Systems', 'Security'],
                    'challenges' => ['Point-of-sale and workstation problems', 'Network or Wi-Fi interruptions', 'Website availability and performance', 'Account security and access management', 'Data backup and recovery planning'],
                    'solutions' => ['Remote and onsite IT support', 'Network troubleshooting', 'Website and application maintenance', 'Security hardening', 'Backup and restoration testing'],
                ],
                [
                    'name' => 'Education & EdTech',
                    'desc' => 'Learning management systems, student data protection, virtual classroom infrastructure, and campus network security.',
                    'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'tags' => ['LMS', 'FERPA', 'Virtual Classrooms', 'Campus IT'],
                    'challenges' => ['Classroom device faults at term start', 'Student-account provisioning load', 'Campus Wi-Fi dead zones', 'Safeguarding access controls'],
                    'solutions' => ['Bulk device provisioning checklists', 'Identity and mailbox support', 'Wi-Fi survey and monitoring', 'Joiner/leaver access workflows'],
                ],
                [
                    'name' => 'Manufacturing & Industrial',
                    'desc' => 'OT/IT convergence, IoT security, SCADA system protection, and supply chain digitisation.',
                    'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                    'tags' => ['OT/IT', 'IoT Security', 'SCADA', 'Industry 4.0'],
                    'challenges' => ['Shop-floor PC and printer downtime', 'Untracked operational devices', 'Patch windows without stopping production', 'Backup doubts for control PCs'],
                    'solutions' => ['Preventive maintenance scheduling', 'Asset register and lifecycle tracking', 'Planned patch cadence', 'Restore-test documentation'],
                ],
                [
                    'name' => 'Government & Public Sector',
                    'desc' => 'FedRAMP compliance, classified network security, citizen data protection, and critical infrastructure defence.',
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'tags' => ['FedRAMP', 'Classified', 'Critical Infrastructure', 'Zero Trust'],
                    'challenges' => ['Strict access-approval processes', 'Legacy system maintenance', 'Audit-ready documentation demands', 'Multi-site support consistency'],
                    'solutions' => ['Standardised request workflows', 'Knowledge-base and SLA reporting', 'Asset and configuration records', 'Scheduled onsite coordination'],
                ],
                [
                    'name' => 'Legal & Professional Services',
                    'desc' => 'Client privilege protection, secure document management, e-discovery support, and regulatory compliance.',
                    'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
                    'tags' => ['Client Privilege', 'Document Security', 'E-Discovery', 'Compliance'],
                    'challenges' => ['Confidential document-access control', 'Email filing and search faults', 'Time-billing application downtime', 'Secure file sharing with clients'],
                    'solutions' => ['Permission audits and MFA', 'Microsoft 365 mailbox support', 'Application troubleshooting', 'Backup and retention guidance'],
                ],
                [
                    'name' => 'Energy & Utilities',
                    'desc' => 'Critical infrastructure protection, SCADA security, smart grid defence, and environmental compliance monitoring.',
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'tags' => ['SCADA', 'Smart Grid', 'ICS Security', 'NERC CIP'],
                    'challenges' => ['Remote-site connectivity gaps', 'Monitoring alert noise', 'Field-device lifecycle tracking', 'Incident-record completeness'],
                    'solutions' => ['Connectivity assessment and failover', 'Monitoring configuration reviews', 'Asset inventory programmes', 'Structured incident workflows'],
                ],
                [
                    'name' => 'Media & Entertainment',
                    'desc' => 'Content protection, DRM systems, high-bandwidth streaming infrastructure, and intellectual property security.',
                    'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                    'tags' => ['DRM', 'Content Protection', 'Streaming', 'IP Security'],
                    'challenges' => ['Large-file storage and transfer faults', 'Editing-workstation performance', 'Streaming-platform uptime', 'Freelancer access sprawl'],
                    'solutions' => ['Workstation optimisation plans', 'Storage and backup reviews', 'Website performance checks', 'Time-bound access provisioning'],
                ],
                [
                    'name' => 'Logistics, Transport & Distribution',
                    'desc' => 'Depot connectivity, fleet-device support, warehouse systems and shipment-tracking uptime for operators moving goods around the clock.',
                    'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                    'tags' => ['Depot Networks', 'Fleet Devices', 'Tracking Systems', 'Uptime'],
                    'challenges' => ['Depot Wi-Fi and scanner dropouts', 'Fleet tablet and GPS faults', 'Warehouse system downtime', 'Multi-site support coordination'],
                    'solutions' => ['Remote and onsite depot support', 'Network troubleshooting', 'Device lifecycle management', 'Prioritised incident response'],
                ],
                [
                    'name' => 'Hospitality & Accommodation',
                    'desc' => 'Guest Wi-Fi, front-desk systems, booking platforms and POS support for hotels, restaurants and venues.',
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'tags' => ['Guest Wi-Fi', 'Booking Systems', 'POS Support', 'Front-Desk IT'],
                    'challenges' => ['Guest Wi-Fi complaints at peak hours', 'Booking and POS interruptions', 'Seasonal device onboarding', 'After-hours faults'],
                    'solutions' => ['Wi-Fi assessment and monitoring', 'POS and workstation support', 'Scheduled maintenance visits', 'Documented escalation path'],
                ],
                [
                    'name' => 'Construction & Property Management',
                    'desc' => 'Site-office connectivity, project devices, document platforms and maintenance coordination for builders and agents.',
                    'icon' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4',
                    'tags' => ['Site Connectivity', 'Project Devices', 'Document Platforms', 'Maintenance'],
                    'challenges' => ['Temporary site-office networks', 'Rugged device damage and loss', 'Drawing and document version chaos', 'Subcontractor access control'],
                    'solutions' => ['Rapid site-network setup', 'Endpoint provisioning and tracking', 'Secure file-sharing configuration', 'Access reviews for leavers'],
                ],
                [
                    'name' => 'Nonprofits & Membership Organisations',
                    'desc' => 'Cost-effective IT, donor-data protection, volunteer onboarding and trustee-ready reporting for mission-driven teams.',
                    'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                    'tags' => ['Donor Data', 'Volunteer IT', 'Cost Control', 'Reporting'],
                    'challenges' => ['Tight budgets with big expectations', 'Volunteer turnover and access sprawl', 'Donor-database protection', 'Trustee reporting demands'],
                    'solutions' => ['Standardised low-cost device builds', 'Joiner/leaver access workflows', 'Backup verification', 'Monthly operations summaries'],
                ],
                [
                    'name' => 'Automotive, Fleet & Vehicle Services',
                    'desc' => 'Workshop systems, diagnostic-device support, customer-data handling and multi-branch connectivity for dealers and fleets.',
                    'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0zM5 17h14M5 17l1.5-6h11L19 17M7 11h10',
                    'tags' => ['Workshop Systems', 'Diagnostics', 'Multi-Branch', 'Customer Data'],
                    'challenges' => ['Workshop management-system faults', 'Diagnostic tablet connectivity', 'Customer record protection', 'Branch network inconsistency'],
                    'solutions' => ['Workshop application support', 'Standardised branch networks', 'Endpoint maintenance plans', 'Backup and recovery testing'],
                ],
                [
                    'name' => 'Agriculture & Food Processing',
                    'desc' => 'Rural-site connectivity, cold-chain monitoring support, traceability systems and office IT for producers and processors.',
                    'icon' => 'M12 3v18m0 0c-4 0-7-2-7-6 4 0 7 2 7 6zm0 0c4 0 7-2 7-6-4 0-7 2-7 6z',
                    'tags' => ['Rural Connectivity', 'Monitoring', 'Traceability', 'Office IT'],
                    'challenges' => ['Poor rural broadband reliability', 'Sensor and monitoring gaps', 'Traceability record keeping', 'Seasonal workforce onboarding'],
                    'solutions' => ['Connectivity assessment and failover', 'Monitoring alert configuration', 'Business application support', 'Documented seasonal onboarding'],
                ],
                [
                    'name' => 'Telecommunications & Internet Providers',
                    'desc' => 'NOC-adjacent support tooling, customer-premises troubleshooting workflows and billing-platform maintenance for local ISPs.',
                    'icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a10 10 0 0114.142 0M1.394 8.394a15 15 0 0121.213 0',
                    'tags' => ['Support Tooling', 'CPE Troubleshooting', 'Billing Platforms', 'Uptime'],
                    'challenges' => ['High ticket volumes at outages', 'CPE fault diagnosis consistency', 'Billing-platform maintenance risk', 'Status communication gaps'],
                    'solutions' => ['Triage playbooks and knowledge base', 'Structured escalation workflows', 'Planned maintenance windows', 'Service-status reporting'],
                ],
                [
                    'name' => 'Recruitment & Staffing Agencies',
                    'desc' => 'Candidate-data protection, CRM/ATS support, video-interview reliability and fast consultant onboarding for agencies.',
                    'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-8 0 4 4 0 008 0zm6-5a4 4 0 01-3 3.87',
                    'tags' => ['Candidate Data', 'ATS/CRM', 'Video Interviews', 'Onboarding'],
                    'challenges' => ['Candidate-database access control', 'ATS slowdowns at peak', 'Video-interview failures', 'Rapid consultant churn'],
                    'solutions' => ['Permission reviews and MFA', 'Application performance checks', 'Meeting-room and headset standards', 'Same-day joiner/leaver process'],
                ],
                [
                    'name' => 'Sports, Leisure & Fitness Clubs',
                    'desc' => 'Membership systems, class-booking platforms, access control and front-of-house IT for clubs and leisure centres.',
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'tags' => ['Membership Systems', 'Bookings', 'Access Control', 'Front-Desk IT'],
                    'challenges' => ['Membership-system outages at peak', 'Class-booking errors', 'Door-access faults', 'Music and display systems'],
                    'solutions' => ['Front-of-house support plans', 'Booking-platform troubleshooting', 'Access-system maintenance', 'Scheduled off-peak upkeep'],
                ],
                [
                    'name' => 'Home Services & Field Trades',
                    'desc' => 'Job-management apps, quoting tools, mobile-device support and customer-record protection for plumbers, electricians and contractors.',
                    'icon' => 'M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4L14 13l-3-3 3.7-3.7z',
                    'tags' => ['Job Apps', 'Mobile Devices', 'Quoting Tools', 'Customer Records'],
                    'challenges' => ['Phone and tablet damage on site', 'Job-app sync failures', 'Quote and invoice data loss', 'No office IT presence'],
                    'solutions' => ['Mobile-device provisioning', 'App troubleshooting and backups', 'Cloud file and photo protection', 'Simple monthly support plan'],
                ],
            ] as $industry)
            <div class="term-panel p-7 sm:p-8 group">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <svg class="w-7 h-7 text-term-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $industry['icon'] }}"/></svg>
                    <span class="font-mono text-[10px] tracking-[0.2em] text-term-700">SEC_{{ str_pad((string)($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h3 class="font-display text-xl font-bold tracking-tight text-navy-900 dark:text-white group-hover:text-accent-soft transition-colors">{{ $industry['name'] }}</h3>
                <p class="mt-2.5 text-sm leading-relaxed text-slate-600 dark:text-term-800">{{ $industry['desc'] }}</p>
                @if(!empty($industry['challenges']))
                <div class="mt-4 text-left">
                    <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-term-700 mb-1.5">Typical challenges</p>
                    <ul class="space-y-1 text-[13px] text-slate-600 dark:text-term-800">
                        @foreach($industry['challenges'] as $challenge)<li class="flex gap-2"><span aria-hidden="true">–</span><span>{{ $challenge }}</span></li>@endforeach
                    </ul>
                </div>
                @endif
                @if(!empty($industry['solutions']))
                <div class="mt-3 text-left">
                    <p class="font-mono text-[10px] uppercase tracking-[0.2em] text-term-700 mb-1.5">How we help</p>
                    <ul class="space-y-1 text-[13px] text-slate-600 dark:text-term-800">
                        @foreach($industry['solutions'] as $solution)<li class="flex gap-2"><span class="text-accent-soft" aria-hidden="true">✓</span><span>{{ $solution }}</span></li>@endforeach
                    </ul>
                    <a href="{{ route('contact') }}" class="term-link mt-2.5 text-xs">Discuss your sector
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @endif
                <div class="flex flex-wrap gap-1.5 pt-4 mt-4 border-t border-term-300 dark:border-white/5">
                    @foreach($industry['tags'] as $tag)
                    <span class="term-tag">{{ strtoupper($tag) }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative w-full py-16 sm:py-20 border-t border-term-300 dark:border-white/5" aria-labelledby="industries-cta-heading">
    <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <span class="term-tag term-tag-accent">INDUSTRIES://CONSULT</span>
        <h2 id="industries-cta-heading" class="mt-4 font-display text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 dark:text-white">
            Need industry-specific IT solutions?
        </h2>
        <p class="mt-4 text-base sm:text-lg text-slate-600 dark:text-term-800">
            Contact us for a free consultation on how our solutions can meet your industry's unique compliance, security, and operational requirements.
        </p>
        <a href="{{ route('contact') }}" class="term-btn term-btn-lg mt-8">
            Schedule Industry Consultation
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

@endsection
