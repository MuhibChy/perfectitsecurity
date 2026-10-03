@extends('layouts.public')

@section('title', 'Service Level Agreement (SLA) — PerfectITSecurity')
@section('description', 'PerfectITSecurity service level agreement outlining response times, resolution targets, uptime guarantees, and escalation procedures.')

@section('content')

{{-- HERO — LEGAL://SLA --}}
<section class="relative w-full overflow-hidden" aria-labelledby="sla-hero-heading">
    <div class="absolute inset-0 bg-cyber-grid opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="relative w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-10 pt-24 sm:pt-28 lg:pt-32 pb-12 lg:pb-16 text-center">
        <div class="flex flex-wrap items-center justify-center gap-2.5 mb-7">
            <span class="term-tag term-tag-accent">LEGAL://SLA</span>
        </div>
        <h1 id="sla-hero-heading" class="font-display font-extrabold tracking-tight leading-[1.02] text-4xl sm:text-5xl text-navy-900 dark:text-white text-balance">SERVICE LEVEL AGREEMENT</h1>
        <p class="mt-4 font-mono text-[11px] tracking-wider text-term-700">LAST-UPDATED:// SEPTEMBER 8, 2026</p>
    </div>
</section>

<section class="relative w-full pb-16 sm:pb-20 lg:pb-24" aria-label="SLA content">
    <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-10">
        {{-- SLA Tiers Overview --}}
        <dl class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-10">
            @foreach([
                ['tier' => 'Basic', 'response' => '4 hours', 'resolution' => '8 hours'],
                ['tier' => 'Standard', 'response' => '2 hours', 'resolution' => '4 hours'],
                ['tier' => 'Priority', 'response' => '1 hour', 'resolution' => '2 hours'],
                ['tier' => 'Critical', 'response' => '30 min', 'resolution' => '1 hour'],
            ] as $sla)
            <div class="term-panel px-5 py-6 text-center">
                <dt class="font-mono text-xs tracking-[0.2em] text-accent-soft">TIER://{{ strtoupper($sla['tier']) }}</dt>
                <dd class="mt-3 space-y-1.5 font-mono text-sm">
                    <div class="text-slate-600 dark:text-term-800"><span class="text-term-700 text-[11px]">RESPONSE://</span> <strong class="text-navy-900 dark:text-white">{{ $sla['response'] }}</strong></div>
                    <div class="text-slate-600 dark:text-term-800"><span class="text-term-700 text-[11px]">RESOLVE://</span> <strong class="text-navy-900 dark:text-white">{{ $sla['resolution'] }}</strong></div>
                </dd>
            </div>
            @endforeach
        </dl>

        <div class="term-panel p-6 sm:p-8 lg:p-12">
            <div class="prose dark:prose-invert max-w-none space-y-8 text-slate-600 dark:text-term-800">

                <div>
                    <div class="term-sec-label mb-2">SECTION://01</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Purpose</h2>
                    <p class="text-sm leading-relaxed">
                        This Service Level Agreement (SLA) defines the service standards, response times, resolution targets, and escalation procedures that PerfectITSecurity commits to when delivering IT support, managed services, and professional engagements to its clients.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://02</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Scope</h2>
                    <p class="text-sm leading-relaxed">
                        This SLA applies to all active service agreements between PerfectITSecurity and its clients. Specific SLA tiers may vary based on the client's service plan, contract terms, and priority level.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://03</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Response Time Definitions</h2>
                    <ul class="list-disc list-inside space-y-2 text-sm">
                        <li><strong class="text-navy-900 dark:text-white">Initial Response:</strong> The time between ticket submission and the first human acknowledgement from a support agent.</li>
                        <li><strong class="text-navy-900 dark:text-white">Resolution Time:</strong> The time between ticket submission and the point at which the issue is resolved or a permanent workaround is implemented.</li>
                        <li><strong class="text-navy-900 dark:text-white">Business Hours:</strong> Monday–Friday, 09:00–18:00 in the client's local timezone, unless 24/7 coverage is included in the service plan.</li>
                    </ul>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://04</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Priority Classification</h2>
                    <div class="term-table-wrap">
                        <table class="data-table term-table term-table-cards">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Description</th>
                                    <th>Examples</th>
                                    <th>Response</th>
                                    <th>Resolution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    ['priority' => 'Critical', 'desc' => 'Complete system outage or security breach', 'examples' => 'Ransomware, production down, data breach', 'response' => '15 min', 'resolution' => '1 hour'],
                                    ['priority' => 'Urgent', 'desc' => 'Major functionality impaired', 'examples' => 'Email down, VPN failure, database issues', 'response' => '30 min', 'resolution' => '2 hours'],
                                    ['priority' => 'High', 'desc' => 'Significant impact on operations', 'examples' => 'Slow performance, partial outage', 'response' => '1 hour', 'resolution' => '4 hours'],
                                    ['priority' => 'Medium', 'desc' => 'Moderate impact, workaround available', 'examples' => 'Non-critical feature issues', 'response' => '2 hours', 'resolution' => '8 hours'],
                                    ['priority' => 'Low', 'desc' => 'Minor impact, cosmetic or informational', 'examples' => 'UI issues, documentation requests', 'response' => '4 hours', 'resolution' => '24 hours'],
                                ] as $p)
                                <tr>
                                    <td data-label="Priority"><span class="term-tag">{{ strtoupper($p['priority']) }}</span></td>
                                    <td data-label="Description" class="text-sm">{{ $p['desc'] }}</td>
                                    <td data-label="Examples" class="text-xs text-term-700">{{ $p['examples'] }}</td>
                                    <td data-label="Response" class="text-sm font-semibold font-mono text-navy-900 dark:text-white">{{ $p['response'] }}</td>
                                    <td data-label="Resolution" class="text-sm font-semibold font-mono text-navy-900 dark:text-white">{{ $p['resolution'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://05</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Escalation Procedures</h2>
                    <ul class="list-disc list-inside space-y-2 text-sm">
                        <li><strong class="text-navy-900 dark:text-white">Level 1 (T+30 min):</strong> Automatic escalation to support team lead if no initial response within SLA.</li>
                        <li><strong class="text-navy-900 dark:text-white">Level 2 (T+60 min):</strong> Escalation to support manager with management notification.</li>
                        <li><strong class="text-navy-900 dark:text-white">Level 3 (T+2 hours):</strong> Escalation to CTO/Director with client executive notification.</li>
                        <li><strong class="text-navy-900 dark:text-white">Emergency Bridge:</strong> For critical incidents, a dedicated bridge call is established within 15 minutes with all stakeholders.</li>
                    </ul>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://06</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Uptime Guarantee</h2>
                    <p class="text-sm leading-relaxed">
                        For managed services clients, PerfectITSecurity guarantees a minimum of <strong class="text-navy-900 dark:text-white">99.9% uptime</strong> for supported systems during each calendar month. Uptime is measured excluding scheduled maintenance windows (communicated 48 hours in advance) and force majeure events.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://07</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">SLA Credits</h2>
                    <p class="text-sm leading-relaxed">
                        If SLA targets are not met, clients may be eligible for service credits:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-sm mt-2">
                        <li>99.0%–99.9% uptime: 5% credit on monthly service fee</li>
                        <li>95.0%–99.0% uptime: 10% credit on monthly service fee</li>
                        <li>Below 95.0% uptime: 25% credit on monthly service fee</li>
                    </ul>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://08</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Exclusions</h2>
                    <p class="text-sm leading-relaxed">
                        This SLA does not cover: client-caused outages, third-party service provider failures, natural disasters, internet service provider outages, scheduled maintenance (with advance notice), or issues outside the agreed scope of support.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://09</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Reporting</h2>
                    <p class="text-sm leading-relaxed">
                        PerfectITSecurity provides monthly SLA compliance reports to all managed services clients. Reports include response times, resolution times, uptime metrics, ticket volumes, and SLA breach analysis.
                    </p>
                </div>

                <div>
                    <div class="term-sec-label mb-2">SECTION://10</div>
                    <h2 class="font-display text-xl font-bold text-navy-900 dark:text-white mb-3">Contact</h2>
                    <p class="text-sm leading-relaxed">
                        For SLA-related inquiries, please contact your account manager or email <strong class="text-navy-900 dark:text-white">sla@techsupport.com</strong>.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
