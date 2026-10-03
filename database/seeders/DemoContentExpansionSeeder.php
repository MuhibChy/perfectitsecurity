<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;

/**
 * DemoContentExpansionSeeder — 10 sample team profiles + 10 illustrative
 * case studies for the public website.
 *
 * Safety (matches house demo policy):
 * - Every record is_demo=true, is_published=true (removable via demo:cleanup).
 * - Fictional names/organisations only; profiles carry a visible demo banner
 *   in the page; no real employees, clients, certifications or measured
 *   results are claimed. Numeric outcomes are labelled illustrative.
 * - Idempotent: matched by title via firstOrCreate — re-runs create nothing.
 */
class DemoContentExpansionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->profiles() as $p) {
            PortfolioItem::firstOrCreate(
                ['title' => $p['title']],
                $p + ['is_demo' => true, 'is_published' => true, 'published_at' => now()]
            );
        }
        foreach ($this->cases() as $c) {
            CaseStudy::firstOrCreate(
                ['title' => $c['title']],
                $c + ['is_demo' => true, 'is_published' => true, 'published_at' => now()]
            );
        }
        $this->command?->info('Demo expansion ready: 10 sample profiles + 10 illustrative case studies (idempotent).');
    }

    private function demoNote(): string
    {
        return 'Demo/sample portfolio profile — fictional individual with a stated illustrative 12 years of experience. Not an actual PerfectITSecurity employee; shown until replaced by verified team members.';
    }

    private function profiles(): array
    {
        $common = "\n\n" . $this->demoNote();
        return [
            [
                'title' => 'Daniel Whitfield — Senior IT Support Engineer',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Windows desktop and laptop troubleshooting, service-desk operations and incident resolution. Stated demo experience: 12 years.',
                'description' => "Senior IT support engineer focused on end-user computing: Windows 10/11 diagnostics, hardware fault isolation, business application support and service-desk queue management.\n\nCore skills: Windows troubleshooting, hardware diagnostics, user onboarding, ticket triage, remote assistance.\n\nExample responsibilities: resolving escalated desktop incidents, maintaining support documentation, coaching first-line analysts.\n\nIllustrative achievements: helped design a sample triage checklist that reduced repeat visits in a fictional scenario.\n\nTools: Windows, Microsoft 365 admin basics, ticketing systems, remote support tools." . $common,
                'sort_order' => 101,
            ],
            [
                'title' => 'Priya Natarajan — Network Infrastructure Engineer',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'LAN/WAN, switching, routing, Wi-Fi, VPNs and network performance optimisation. Stated demo experience: 12 years.',
                'description' => "Network engineer covering small-to-mid business infrastructure: VLAN segmentation, managed switches, firewall rules, site-to-site VPNs and Wi-Fi surveys.\n\nCore skills: TCP/IP, DHCP/DNS, VLANs, VPN, wireless troubleshooting, network documentation.\n\nExample responsibilities: investigating intermittent connectivity, reviewing switch configurations, planning capacity upgrades.\n\nIllustrative achievements: produced a sample network baseline template used in fictional assessments.\n\nTools: managed switches, firewalls, Wi-Fi analysers, network monitors." . $common,
                'sort_order' => 102,
            ],
            [
                'title' => 'Thomas Aldridge — Windows Server & Microsoft 365 Specialist',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Windows Server, Active Directory, Microsoft 365, Exchange mail flow and identity administration. Stated demo experience: 12 years.',
                'description' => "Microsoft-platform specialist: Active Directory user/computer lifecycle, Group Policy, Exchange Online mail-flow troubleshooting, Teams/SharePoint support and licence administration.\n\nCore skills: Active Directory, Group Policy, Exchange Online, mail-flow diagnostics, MFA and conditional access basics.\n\nExample responsibilities: fixing email delivery faults, onboarding leavers/joiners securely, reviewing mailbox permissions.\n\nIllustrative achievements: authored a sample mailbox-migration runbook for fictional tenants.\n\nTools: Windows Server, Microsoft 365 admin centres, PowerShell basics. No vendor partnership claimed." . $common,
                'sort_order' => 103,
            ],
            [
                'title' => 'Sara Lindqvist — Linux Server & Cloud Engineer',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Linux administration, web hosting, cloud infrastructure and system monitoring. Stated demo experience: 12 years.',
                'description' => "Linux and cloud engineer: LAMP/LEMP stacks, virtual servers, storage, scheduled maintenance and uptime monitoring for small business workloads.\n\nCore skills: Linux CLI, web server configuration, TLS certificates, backups, log analysis, monitoring alerts.\n\nExample responsibilities: patching schedules, investigating slow sites, documenting recovery steps.\n\nIllustrative achievements: built a sample server-hardening checklist applied in fictional reviews.\n\nTools: Linux distributions, web servers, cloud consoles, monitoring platforms." . $common,
                'sort_order' => 104,
            ],
            [
                'title' => 'Marcus Bell — Cybersecurity & Vulnerability Management Specialist',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Security assessments, vulnerability management, hardening and remediation planning (authorised work only). Stated demo experience: 12 years.',
                'description' => "Security specialist focused on defensive improvement: authorised vulnerability scans, patch-gap analysis, baseline hardening and readable remediation plans. General support engagements are separated from formally scoped testing.\n\nCore skills: vulnerability scanning, CVSS prioritisation, hardening baselines, incident triage support.\n\nExample responsibilities: tracking remediation progress, verifying fixes, reporting risk in plain language.\n\nIllustrative achievements: drafted a sample remediation tracker adopted in fictional exercises. No certifications or client engagements claimed." . $common,
                'sort_order' => 105,
            ],
            [
                'title' => 'Elena Petrova — Web Application Security Specialist',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Authorised web security reviews, secure configuration and remediation verification. Stated demo experience: 12 years.',
                'description' => "Application-security reviewer: defined-scope website assessments, authentication and session checks, secure header/configuration review and fix verification. All testing is explicitly authorised in writing before it begins.\n\nCore skills: OWASP Top 10 awareness, secure configuration review, report writing, retest verification.\n\nExample responsibilities: scoping reviews, documenting risk-rated findings, confirming remediation.\n\nIllustrative achievements: created a sample finding template improving fix turnaround in fictional scenarios. No certifications claimed." . $common,
                'sort_order' => 106,
            ],
            [
                'title' => 'James Okafor — Backup & Disaster Recovery Specialist',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Backup verification, restoration planning, recovery testing and continuity procedures. Stated demo experience: 12 years.',
                'description' => "Recovery specialist: backup coverage reviews, restore-test scheduling, recovery documentation and continuity walkthroughs for small businesses.\n\nCore skills: backup verification, restore testing, RTO/RPO planning, documentation.\n\nExample responsibilities: proving restores actually work, closing backup gaps, maintaining runbooks.\n\nIllustrative achievements: designed a sample quarterly restore-test calendar used in fictional plans.\n\nTools: backup suites, cloud storage, checklist-driven test plans." . $common,
                'sort_order' => 107,
            ],
            [
                'title' => 'Hannah Schneider — IT Asset & Endpoint Management Specialist',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Asset inventory, endpoint configuration, patch planning, lifecycle tracking and software inventory. Stated demo experience: 12 years.',
                'description' => "Asset and endpoint specialist: hardware registers, ownership records, warranty tracking, standard builds and refresh planning.\n\nCore skills: asset registers, endpoint baselines, licence reconciliation, lifecycle reporting.\n\nExample responsibilities: auditing device estates, reconciling software licences, planning replacements.\n\nIllustrative achievements: built a sample asset-register template reused across fictional audits." . $common,
                'sort_order' => 108,
            ],
            [
                'title' => 'Arif Chowdhury — Web Development & Database Support Engineer',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Website troubleshooting, application maintenance, database diagnostics and performance optimisation. Stated demo experience: 12 years.',
                'description' => "Web and data support engineer: CMS troubleshooting, slow-page diagnostics, database query review and safe, tested fixes for business sites.\n\nCore skills: PHP/Laravel basics, MySQL diagnostics, frontend performance, error-log analysis.\n\nExample responsibilities: resolving site errors, optimising slow queries, regression-checking updates.\n\nIllustrative achievements: documented a sample performance checklist cutting fictional debug time." . $common,
                'sort_order' => 109,
            ],
            [
                'title' => 'Oliver Grant — IT Service Management & Automation Consultant',
                'category' => 'Sample Team Profile',
                'client_name' => 'Illustrative demo profile (fictional)',
                'summary' => 'Incident/request workflows, service catalogues, documentation, monitoring, reporting and automation. Stated demo experience: 12 years.',
                'description' => "ITSM consultant: ticket workflow design, service catalogue structure, knowledge articles, SLA-friendly queues and light automation for repetitive tasks.\n\nCore skills: ITIL-aligned workflows, SLA design, knowledge management, reporting, scripting basics.\n\nExample responsibilities: standardising intake, building dashboards, reducing manual handoffs.\n\nIllustrative achievements: mapped a sample request catalogue adopted in fictional rollouts." . $common,
                'sort_order' => 110,
            ],
        ];
    }

    private function cases(): array
    {
        $label = 'Illustrative demo scenario — fictional organisation; outcomes are illustrative examples, not measured results.';
        return [
            [
                'title' => 'Multi-Office IT Support Modernisation',
                'client_name' => 'Northbridge Consulting Group (fictional)',
                'industry' => 'Professional Services',
                'country' => 'UK',
                'summary' => 'Centralised requests, standard troubleshooting and escalation across four fictional offices. ' . $label,
                'challenge' => 'A fictional firm handled IT informally per office: no shared request channel, inconsistent troubleshooting steps, no asset records and unclear escalation. Symptoms: repeat issues, slow handoffs, no visibility for managers.',
                'solution' => 'Introduced a central request queue, standard triage checklist, shared asset register and a two-tier escalation path with documented response targets.',
                'results' => 'Illustrative outcomes: fewer repeat visits, clearer ownership of every request, and consistent documentation. Before: ad-hoc emails and calls. After: one queue, tracked SLAs, monthly service summary.',
                'sort_order' => 201,
            ],
            [
                'title' => 'Network Reliability Improvement',
                'client_name' => 'Harborview Hotel Group (fictional)',
                'industry' => 'Hospitality',
                'country' => 'UK',
                'summary' => 'Assessment, configuration review and monitoring for intermittent guest Wi-Fi faults. ' . $label,
                'challenge' => 'A fictional hotel group suffered intermittent guest Wi-Fi dropouts and front-desk outages. Symptoms: guest complaints at peak hours, POS terminal disconnects, no monitoring.',
                'solution' => 'Performed a wired/wireless assessment, reviewed access-point placement and switch configuration, separated guest and operations traffic, and added basic availability monitoring.',
                'results' => 'Illustrative outcomes: stable peak-hour connectivity and faster fault isolation. Before: blind troubleshooting. After: monitored network with documented configuration.',
                'sort_order' => 202,
            ],
            [
                'title' => 'Microsoft 365 and Business Email Support',
                'client_name' => 'Cedarfield Accountancy (fictional)',
                'industry' => 'Financial Services',
                'country' => 'UK',
                'summary' => 'Mail-flow troubleshooting, identity checks and user guidance for a fictional practice. ' . $label,
                'challenge' => 'A fictional accountancy had delayed inbound mail, shared-mailbox permission confusion and lockouts after a password policy change.',
                'solution' => 'Traced mail flow, corrected connector and DNS-adjacent settings, audited mailbox permissions, re-enabled accounts securely and published short user guides.',
                'results' => 'Illustrative outcomes: predictable mail delivery and fewer access tickets. Before: scattered fixes. After: documented identity and mail-flow baseline.',
                'sort_order' => 203,
            ],
            [
                'title' => 'Server Performance and Maintenance',
                'client_name' => 'Brightline Retail Ltd (fictional)',
                'industry' => 'Retail',
                'country' => 'US',
                'summary' => 'Diagnostics, resource analysis and preventive maintenance planning for slow servers. ' . $label,
                'challenge' => 'A fictional retailer reported slow server responses during trading hours: stock lookups lagged and back-office apps timed out.',
                'solution' => 'Ran performance diagnostics, identified disk and memory pressure, rescheduled heavy jobs, cleaned stale services and set a preventive maintenance calendar.',
                'results' => 'Illustrative outcomes: steadier trading-hours response and planned maintenance windows. Before: reactive reboots. After: monitored capacity with scheduled upkeep.',
                'sort_order' => 204,
            ],
            [
                'title' => 'Website Security Assessment',
                'client_name' => 'Aldermore Education Trust (fictional)',
                'industry' => 'Education',
                'country' => 'UK',
                'summary' => 'Authorised, scoped website review with risk-based findings and remediation steps. ' . $label,
                'challenge' => 'A fictional trust wanted assurance before a parent-portal launch: login hardening, form handling and third-party plugin risk were unverified.',
                'solution' => 'Agreed a written scope, ran controlled checks against staging, risk-rated each finding and verified fixes on retest with plain-language guidance.',
                'results' => 'Illustrative outcomes: launch with documented assurance and a repeatable review checklist. No live-system disruption; testing confined to scope.',
                'sort_order' => 205,
            ],
            [
                'title' => 'Backup and Recovery Improvement',
                'client_name' => 'Fenland Manufacturing (fictional)',
                'industry' => 'Manufacturing',
                'country' => 'UK',
                'summary' => 'Backup review, restoration testing plan and recovery documentation. ' . $label,
                'challenge' => 'A fictional manufacturer backed up nightly but had never tested a restore; coverage of two file shares was uncertain and responsibilities were unclear.',
                'solution' => 'Audited backup coverage, scheduled quarterly restore tests, wrote step-by-step recovery documentation and assigned clear owners.',
                'results' => 'Illustrative outcomes: proven restores and a rehearsed recovery path. Before: assumed safety. After: tested recovery with named owners.',
                'sort_order' => 206,
            ],
            [
                'title' => 'IT Asset Inventory and Lifecycle Management',
                'client_name' => 'Parkside Logistics (fictional)',
                'industry' => 'Logistics',
                'country' => 'US',
                'summary' => 'Asset register, ownership records and lifecycle tracking for a fictional fleet. ' . $label,
                'challenge' => 'A fictional logistics firm could not account for laptops, scanners and spares across depots; warranties lapsed unnoticed and leavers kept devices.',
                'solution' => 'Built a central asset register with ownership, location and warranty fields, tagged equipment, and added joiner/leaver device steps plus refresh planning.',
                'results' => 'Illustrative outcomes: accounted devices, planned replacements and cleaner audits. Before: spreadsheets and guesswork. After: one register with lifecycle states.',
                'sort_order' => 207,
            ],
            [
                'title' => 'Website and Application Performance',
                'client_name' => 'Loopway E-commerce (fictional)',
                'industry' => 'E-commerce',
                'country' => 'US',
                'summary' => 'Diagnostics, database review and frontend optimisation for slow pages. ' . $label,
                'challenge' => 'A fictional store suffered slow category pages and checkout errors at peak times; image weight and unindexed queries were suspected.',
                'solution' => 'Profiled slow pages, added missing indexes, compressed media, fixed error-prone checkout steps and regression-tested releases.',
                'results' => 'Illustrative outcomes: faster page loads in the fictional test environment and fewer checkout errors. Before: guesswork fixes. After: measured, tested changes.',
                'sort_order' => 208,
            ],
            [
                'title' => 'Remote and Onsite Support Coordination',
                'client_name' => 'Meadowvale Clinics Group (fictional)',
                'industry' => 'Healthcare',
                'country' => 'BD',
                'summary' => 'Ticket assignment, appointment tracking and completion records across sites. ' . $label,
                'challenge' => 'A fictional clinic group mixed remote fixes and site visits with no shared diary: double-bookings, lost notes and unconfirmed completions.',
                'solution' => 'Routed all requests through tickets, scheduled visits with technician notes, required completion records and customer confirmation.',
                'results' => 'Illustrative outcomes: predictable visit diary and confirmed completions. Before: phone-only coordination. After: tracked appointments with sign-off.',
                'sort_order' => 209,
            ],
            [
                'title' => 'IT Service Desk and Workflow Automation',
                'client_name' => 'Cornerstone Nonprofit Alliance (fictional)',
                'industry' => 'Nonprofit',
                'country' => 'US',
                'summary' => 'Structured ticket workflows, knowledge articles and operational reporting. ' . $label,
                'challenge' => 'A fictional alliance relied on manual inbox triage: inconsistent categorisation, missed follow-ups and no reporting for trustees.',
                'solution' => 'Introduced structured intake forms, categorised workflows, a starter knowledge base, controlled notifications and a monthly operations report.',
                'results' => 'Illustrative outcomes: consistent handling and trustee-ready reporting. Before: inbox chaos. After: defined workflows with visible metrics.',
                'sort_order' => 210,
            ],
        ];
    }
}
