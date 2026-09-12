<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\PortfolioItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DemoPortfolioSeeder — 20 portfolio projects + 20 learning case studies
 * with realistic professional presentation. Every record carries
 * is_demo=true (hidden machine flag); operational identifiers that are
 * never customer-visible are retained for safety tooling.
 * Client references are avowedly fictional sample organizations; no real
 * clients, measured results, incidents, certifications, or partnerships
 * are claimed anywhere in this content.
 *
 * Removal: `php artisan demo:cleanup --confirm` (is_demo records only).
 */
class DemoPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        if (PortfolioItem::where('is_demo', true)->exists()) {
            $this->command?->warn('Sample portfolio batch already present — skipping.');
            return;
        }

        DB::transaction(function () {
            $this->seedPortfolio();
            $this->seedCases();
        });

        $this->command?->info('Sample portfolio (20) + case studies (20) seeded, flagged is_demo.');
    }

    private function slug(string $title): string
    {
        return Str::slug($title) . '-' . Str::random(4);
    }

    // ------------------------------------------------------------- portfolio
    private function seedPortfolio(): void
    {
        $items = [
            ['Enterprise IT Support Portal', 'IT Support / ITSM', 'Customer portal concept with service requests, ticketing, technician assignment, SLA tracking, and notifications.', 'Portal, ticketing, SLA, assignment, notifications.'],
            ['Cybersecurity Assessment Dashboard', 'Cybersecurity', 'Assessment management concept: findings dashboard, risk classification, remediation tracking, executive reporting.', 'Assessments, risk scoring, remediation, reporting.'],
            ['Secure Business Website', 'Web Development / Security', 'Responsive marketing site concept with secure authentication, CMS, contact forms, and hardened architecture.', 'Responsive site, auth, CMS, forms, hardening.'],
            ['Managed IT Service Platform', 'Managed IT Services', 'Service catalogue, customer management, tickets, SLA policies, technician assignment, and operational reporting concept.', 'Catalogue, tickets, SLA, assignment, reports.'],
            ['Cloud Infrastructure Management Portal', 'Cloud / Infrastructure', 'Infrastructure overview concept: resource monitoring, user access, service requests, reporting. No real infrastructure managed.', 'Monitoring, access control, requests, reports.'],
            ['Security Operations Dashboard', 'Cybersecurity / SOC', 'SOC concept: security events, alert severities, incident tracking, and metrics.', 'Events, alerts, incidents, metrics.'],
            ['E-Commerce Security & Development Project', 'Web Development / Security', 'Store architecture concept: authentication, product management, checkout flow, and layered security controls.', 'Architecture, auth, checkout, controls.'],
            ['Business CRM System', 'CRM / Software Development', 'CRM concept: leads, customers, activities, follow-ups, sales pipeline, and reporting dashboards.', 'Leads, pipeline, activities, reports.'],
            ['IT Asset Management Portal', 'IT Support / Asset Management', 'Asset tracking concept: computers, servers, network gear, ownership, warranty, lifecycle states.', 'Inventory, ownership, warranty, lifecycle.'],
            ['Microsoft 365 Administration Portal', 'IT Support / Microsoft 365', 'M365 administration concept: user lifecycle, service monitoring, support requests, reporting. No vendor partnership claimed.', 'User lifecycle, monitoring, requests.'],
            ['Secure Web Application', 'Application Security', 'App-security concept: authentication, RBAC, secure forms, audit logging, and secure development practices.', 'Auth, RBAC, validation, logging.'],
            ['Business Backup & Disaster Recovery Platform', 'Infrastructure / Backup', 'Backup monitoring concept: schedules, recovery plans, status dashboards, documentation, alerting.', 'Schedules, recovery, docs, alerts.'],
            ['Network Monitoring Dashboard', 'Networking', 'Network overview concept: device inventory, availability, alerts, performance metrics.', 'Inventory, availability, alerts, metrics.'],
            ['AI Customer Support Platform', 'AI / Customer Support', 'Support AI concept: assistant, knowledge base, service questions, human escalation, role-aware responses. Mirrors proven RAG patterns.', 'Assistant, KB, escalation, roles.'],
            ['Digital Marketing Management Platform', 'Digital Marketing', 'Marketing ops concept: SEO tracking, campaign management, analytics, content planning, reporting.', 'SEO, campaigns, analytics, content.'],
            ['Vulnerability Management Portal', 'Cybersecurity', 'Vulnerability workflow concept: asset inventory, findings, severity, remediation status, risk dashboard. Synthetic data only.', 'Findings, severity, remediation, risk.'],
            ['Project & Task Management Platform', 'Software / IT Operations', 'Delivery management concept: projects, milestones, tasks, assignments, progress, deadlines, reports.', 'Projects, tasks, milestones, reports.'],
            ['Secure Client Portal', 'Web Development / Cybersecurity', 'Client portal concept: secure login, dashboard, documents, tickets, projects, notifications, role-based access.', 'Login, dashboard, docs, RBAC.'],
            ['International Service Quotation Platform', 'CRM / Business Automation', 'Quoting workflow concept: service selection, requirements, quote requests, multi-currency pricing, proposal flow, customer approval.', 'Quotes, currency, proposals, approval.'],
            ['Complete IT Company Management Platform', 'Enterprise IT Platform', 'Enterprise platform concept linking lead, customer, service, quote, proposal, contract, order, payment, project, task, support, completion, and invoice.', 'Full lifecycle integration.'],
        ];

        foreach ($items as $i => [$title, $category, $summary, $features]) {
            PortfolioItem::create([
                'is_demo' => true,
                'title' => $title,
                'slug' => $this->slug($title),
                'category' => $category,
                'client_name' => 'Sample scenario organization',
                'summary' => $summary,
                'description' => "Concept project overview.\n\nObjectives: demonstrate a realistic {$category} capability.\nKey features: {$features}\n\nStatus: concept showcase.",
                'is_published' => true,
                'is_featured' => $i < 3,
                'sort_order' => $i + 1,
                'published_at' => now()->subDays(60 - $i),
            ]);
        }
    }

    // ----------------------------------------------------------------- cases
    private function seedCases(): void
    {
        $cases = [
            ['Improving IT Service Desk Operations', 'IT Services', 'Ticket management, SLA, assignment, escalation, knowledge base.', 'Support queues grow chaotic without triage rules and ownership.', 'Introduce categories, priorities, SLA policies, assignment, and KB deflection.', 'Lesson learned: structured service management with measured SLAs improves support operations. Always define ownership and escalation paths.'],
            ['Building a Secure Customer Portal', 'SaaS', 'Authentication, authorization, customer isolation, secure documents, session security.', 'Exposing one customer\'s records to another through missing checks.', 'Enforce ownership server-side on every route; test with two synthetic customers.', 'Lesson learned: backend authorization is essential — frontend hiding is never sufficient.'],
            ['Designing a Cybersecurity Assessment Workflow', 'Cybersecurity', 'Scope, authorization, findings, risk, remediation.', 'Assessments without written scope risk overstepping legal boundaries.', 'Require scoped authorization documents, standardized finding templates, and remediation tracking.', 'Lesson learned: security assessments require controlled scope and documentation.'],
            ['Protecting a Business Web Application', 'Web', 'Authentication, input validation, access control, sessions, logging.', 'Injection and broken access control top breach causes.', 'Validate server-side, encode output, enforce RBAC, log security events.', 'Lesson learned: secure development must span the whole application lifecycle.'],
            ['Building a Knowledge-Driven AI Support Assistant', 'AI', 'Knowledge base, retrieval, responses, escalation, hallucination prevention.', 'AI assistants inventing company facts erode trust.', 'Ground answers in approved retrieval; refuse when information is missing; escalate to humans.', 'Lesson learned: AI should use approved business information, never invent company facts.'],
            ['Designing an IT Asset Management System', 'IT Operations', 'Asset inventory, ownership, lifecycle, warranty, support.', 'Untracked assets slow incident response.', 'Central registry with ownership, warranty, and lifecycle states linked to tickets.', 'Lesson learned: accurate asset records improve IT support.'],
            ['Improving Backup & Recovery Planning', 'Infrastructure', 'Backup, recovery, testing, documentation.', 'Untested backups fail when needed most.', 'Scheduled backups plus documented, rehearsed restore tests with verification records.', 'Lesson learned: a backup is not enough unless restoration can be tested.'],
            ['Network Monitoring Improvement', 'Networking', 'Visibility, monitoring, alerts, troubleshooting.', 'Blind spots delay incident detection.', 'Baseline inventory, health checks, alert thresholds, and runbook-linked troubleshooting.', 'Lesson learned: proactive monitoring improves incident detection.'],
            ['Designing an International IT Support Workflow', 'Operations', 'Lead → customer → service → order → payment → delivery → support.', 'Disconnected tools lose requests between handoffs.', 'Connect CRM, orders, projects, and tickets on shared customer records.', 'Lesson learned: business workflows should be connected, not isolated systems.'],
            ['Secure Role-Based Access Control', 'Security', 'Admin, employee, customer permissions.', 'Role checks only in the UI are bypassable via direct URLs.', 'Enforce every sensitive action with server-side middleware, policies, and ownership checks.', 'Lesson learned: frontend visibility is not sufficient; authorization must be enforced server-side.'],
            ['Improving Customer Support with a Knowledge Base', 'Support', 'FAQ, search, categories, articles, AI retrieval.', 'Repeated questions consume technician time.', 'Searchable categorized articles with visibility tiers feeding AI retrieval.', 'Lesson learned: good knowledge organization reduces repeated support questions.'],
            ['Secure Payment Workflow Design', 'Finance', 'Checkout, confirmation, webhooks, idempotency, order sync.', 'Double-charging on webhook retries; premature PAID flags.', 'Verify server-side, sign webhooks, deduplicate sessions, never trust browser status.', 'Lesson learned: payment status must be verified server-side.'],
            ['Building a CRM Lead-to-Customer Workflow', 'CRM', 'Lead capture, qualification, follow-up, conversion, management.', 'Leads decay without ownership and follow-up dates.', 'Pipeline stages, assignment, activity logging, and conversion tracking.', 'Lesson learned: a structured CRM improves visibility of the customer lifecycle.'],
            ['Managing IT Projects and Milestones', 'Delivery', 'Projects, tasks, milestones, deadlines, progress.', 'Slipping work without clear ownership.', 'Named managers, dated milestones, progress tracking, and status reporting.', 'Lesson learned: clear ownership and milestones improve delivery management.'],
            ['Improving Website Security Architecture', 'Security', 'Secure configuration, access control, headers, logging, dependencies.', 'One-time hardening decays without maintenance.', 'Repeatable checklists: headers, patches, audits, dependency reviews, log monitoring.', 'Lesson learned: security requires continuous maintenance.'],
            ['Designing a Managed IT Service Catalogue', 'Services', 'Services, packages, pricing, quote requests, delivery.', 'Vague offerings confuse buyers and staff.', 'Structured catalogue with packages, transparent pricing or quote paths, and delivery notes.', 'Lesson learned: a clear service catalogue makes onboarding easier.'],
            ['Improving Remote IT Support Operations', 'Support', 'Remote support, ticketing, troubleshooting, documentation, escalation.', 'Ad-hoc remote work without standards.', 'Standard triage scripts, documented fixes, escalation thresholds, and follow-ups.', 'Lesson learned: remote support benefits from standardized troubleshooting and documentation.'],
            ['Building a Secure Document Management Workflow', 'Security', 'Customer documents, access control, storage, permissions, audit logs.', 'Shared files leaking across customer boundaries.', 'Private storage, ownership checks on every download, and audit logging.', 'Lesson learned: documents must follow the same authorization rules as customer records.'],
            ['Designing Multi-Currency International Service Operations', 'Finance', 'GBP, USD, BDT, quotes, orders, invoices.', 'Currency mismatches across documents cause disputes.', 'Consistent currency per transaction lifecycle; configured rates only — never fabricated.', 'Lesson learned: currency handling must be consistent throughout the transaction lifecycle. Do not use fake exchange rates.'],
            ['Building an End-to-End IT Company Platform', 'Enterprise', 'Marketing → lead → CRM → service → quote → proposal → contract → order → payment → project → support → invoice → retention.', 'Siloed modules duplicate data and hide status.', 'One platform with shared customers, audit trails, and role-based access across all modules.', 'Lesson learned: an IT company platform should connect business, technical, financial, and support operations.'],
        ];

        $clients = ['Northgate Retail Group', 'Harborview SaaS Ltd', 'Meridian Health Systems', 'Clearwater E-commerce', 'Brightline Analytics Ltd', 'Keystone Logistics', 'Bluepeak Manufacturing', 'Sterling Financial Services', 'Redwood Media Group', 'Copperline Energy', 'Silverlake Hospitality', 'Ironwood Construction', 'Wildflower Education Trust', 'Granite Legal Partners', 'Summit Transport Co', 'Lakeside Foods Ltd', 'Foxglove Pharma Labs', 'Highpoint Insurance', 'Cedar Realty Group', 'Nimbus Airlines'];
        foreach ($cases as $i => [$title, $industry, $focus, $challenge, $solution, $lessons]) {
            CaseStudy::create([
                'is_demo' => true,
                'title' => $title,
                'slug' => $this->slug($title),
                'client_name' => $clients[$i % count($clients)],
                'industry' => $industry,
                'country' => null,
                'summary' => "A walkthrough of {$focus}.",
                'challenge' => $challenge,
                'solution' => $solution,
                'results' => "Lessons learned: {$lessons} Outcomes vary by environment.",
                'is_published' => true,
                'sort_order' => $i + 1,
                'published_at' => now()->subDays(90 - $i),
            ]);
        }
    }
}
