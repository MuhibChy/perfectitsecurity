@extends('layouts.public')

@section('title', 'Service Level Agreement (SLA) — TechSupport Solutions')
@section('description', 'TechSupport Solutions service level agreement outlining response times, resolution targets, uptime guarantees, and escalation procedures.')

@section('content')

<section class="relative w-full py-24 lg:py-32 bg-space-deep overflow-hidden z-10">
    <div class="absolute inset-0 bg-cyber-grid opacity-15 pointer-events-none"></div>
    <div class="relative z-10 max-w-5xl mx-auto px-6 sm:px-10 lg:px-16">
        <div class="text-center mb-16">
            <h1 class="text-4xl sm:text-5xl font-black text-white tracking-tight mb-4">Service Level Agreement</h1>
            <p class="text-sm text-slate-400">Last updated: September 8, 2026</p>
        </div>

        {{-- SLA Tiers Overview --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-16">
            @foreach([
                ['tier' => 'Basic', 'response' => '4 hours', 'resolution' => '8 hours', 'color' => 'text-sky-400', 'border' => 'border-sky-500/20'],
                ['tier' => 'Standard', 'response' => '2 hours', 'resolution' => '4 hours', 'color' => 'text-emerald-400', 'border' => 'border-emerald-500/20'],
                ['tier' => 'Priority', 'response' => '1 hour', 'resolution' => '2 hours', 'color' => 'text-amber-400', 'border' => 'border-amber-500/20'],
                ['tier' => 'Critical', 'response' => '30 min', 'resolution' => '1 hour', 'color' => 'text-red-400', 'border' => 'border-red-500/20'],
            ] as $sla)
            <div class="cosmic-card p-6 text-center border {{ $sla['border'] }}">
                <h3 class="text-lg font-bold {{ $sla['color'] }} mb-2">{{ $sla['tier'] }}</h3>
                <div class="space-y-1 text-sm">
                    <div class="text-slate-300"><span class="text-slate-500">Response:</span> <strong class="text-white">{{ $sla['response'] }}</strong></div>
                    <div class="text-slate-300"><span class="text-slate-500">Resolution:</span> <strong class="text-white">{{ $sla['resolution'] }}</strong></div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="cosmic-card p-8 lg:p-12 space-y-8">

            <div>
                <h2 class="text-xl font-bold text-white mb-3">1. Purpose</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    This Service Level Agreement (SLA) defines the service standards, response times, resolution targets, and escalation procedures that TechSupport Solutions commits to when delivering IT support, managed services, and professional engagements to its clients.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">2. Scope</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    This SLA applies to all active service agreements between TechSupport Solutions and its clients. Specific SLA tiers may vary based on the client's service plan, contract terms, and priority level.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">3. Response Time Definitions</h2>
                <ul class="list-disc list-inside space-y-2 text-sm text-slate-300">
                    <li><strong class="text-white">Initial Response:</strong> The time between ticket submission and the first human acknowledgement from a support agent.</li>
                    <li><strong class="text-white">Resolution Time:</strong> The time between ticket submission and the point at which the issue is resolved or a permanent workaround is implemented.</li>
                    <li><strong class="text-white">Business Hours:</strong> Monday–Friday, 09:00–18:00 in the client's local timezone, unless 24/7 coverage is included in the service plan.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">4. Priority Classification</h2>
                <div class="overflow-x-auto">
                    <table class="data-table">
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
                                ['priority' => 'Critical', 'desc' => 'Complete system outage or security breach', 'examples' => 'Ransomware, production down, data breach', 'response' => '15 min', 'resolution' => '1 hour', 'color' => 'badge-danger'],
                                ['priority' => 'Urgent', 'desc' => 'Major functionality impaired', 'examples' => 'Email down, VPN failure, database issues', 'response' => '30 min', 'resolution' => '2 hours', 'color' => 'badge-danger'],
                                ['priority' => 'High', 'desc' => 'Significant impact on operations', 'examples' => 'Slow performance, partial outage', 'response' => '1 hour', 'resolution' => '4 hours', 'color' => 'badge-warning'],
                                ['priority' => 'Medium', 'desc' => 'Moderate impact, workaround available', 'examples' => 'Non-critical feature issues', 'response' => '2 hours', 'resolution' => '8 hours', 'color' => 'badge-info'],
                                ['priority' => 'Low', 'desc' => 'Minor impact, cosmetic or informational', 'examples' => 'UI issues, documentation requests', 'response' => '4 hours', 'resolution' => '24 hours', 'color' => 'badge-info'],
                            ] as $p)
                            <tr>
                                <td><span class="badge {{ $p['color'] }}">{{ $p['priority'] }}</span></td>
                                <td class="text-sm">{{ $p['desc'] }}</td>
                                <td class="text-xs text-slate-400">{{ $p['examples'] }}</td>
                                <td class="text-sm font-semibold text-white">{{ $p['response'] }}</td>
                                <td class="text-sm font-semibold text-white">{{ $p['resolution'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">5. Escalation Procedures</h2>
                <ul class="list-disc list-inside space-y-2 text-sm text-slate-300">
                    <li><strong class="text-white">Level 1 (T+30 min):</strong> Automatic escalation to support team lead if no initial response within SLA.</li>
                    <li><strong class="text-white">Level 2 (T+60 min):</strong> Escalation to support manager with management notification.</li>
                    <li><strong class="text-white">Level 3 (T+2 hours):</strong> Escalation to CTO/Director with client executive notification.</li>
                    <li><strong class="text-white">Emergency Bridge:</strong> For critical incidents, a dedicated bridge call is established within 15 minutes with all stakeholders.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">6. Uptime Guarantee</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    For managed services clients, TechSupport Solutions guarantees a minimum of <strong class="text-white">99.9% uptime</strong> for supported systems during each calendar month. Uptime is measured excluding scheduled maintenance windows (communicated 48 hours in advance) and force majeure events.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">7. SLA Credits</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    If SLA targets are not met, clients may be eligible for service credits:
                </p>
                <ul class="list-disc list-inside space-y-2 text-sm text-slate-300 mt-2">
                    <li>99.0%–99.9% uptime: 5% credit on monthly service fee</li>
                    <li>95.0%–99.0% uptime: 10% credit on monthly service fee</li>
                    <li>Below 95.0% uptime: 25% credit on monthly service fee</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">8. Exclusions</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    This SLA does not cover: client-caused outages, third-party service provider failures, natural disasters, internet service provider outages, scheduled maintenance (with advance notice), or issues outside the agreed scope of support.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">9. Reporting</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    TechSupport Solutions provides monthly SLA compliance reports to all managed services clients. Reports include response times, resolution times, uptime metrics, ticket volumes, and SLA breach analysis.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-white mb-3">10. Contact</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    For SLA-related inquiries, please contact your account manager or email <strong class="text-white">sla@techsupport.com</strong>.
                </p>
            </div>

        </div>
    </div>
</section>

@endsection
