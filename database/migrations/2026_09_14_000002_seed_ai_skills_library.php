<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Expands the AI Skills library to the full 20-skill customer-support set.
 * Idempotent (updateOrInsert by slug) so re-runs and fresh installs converge.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now()->toDateTimeString();
        foreach ($this->skills() as $s) {
            DB::table('ai_skills')->updateOrInsert(
                ['slug' => $s['slug']],
                array_merge($s, [
                    'trigger_keywords' => json_encode($s['trigger_keywords']),
                    'allowed_roles' => json_encode($s['allowed_roles'] ?? []),
                    'status' => 'enabled',
                    'version' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('ai_skills')->whereIn('slug', array_column($this->skills(), 'slug'))->delete();
    }

    private function skills(): array
    {
        return [
            [
                'name' => 'Network Support',
                'slug' => 'network-support',
                'description' => 'LAN/WAN, Wi-Fi, Ethernet, routers, DNS, DHCP, VPN, connectivity troubleshooting.',
                'system_instructions' => 'You are handling a networking enquiry. Explain LAN/WAN, Wi-Fi, Ethernet, routers, switches, DNS, DHCP, and VPN in customer-friendly language. Give safe checks (restart equipment, check cables, verify Wi-Fi network). Never expose or guess at internal infrastructure. Recommend a support ticket for site-wide outages.',
                'trigger_keywords' => ['lan', 'wan', 'wifi', 'wi-fi', 'ethernet', 'router', 'switch', 'dns', 'dhcp', 'vpn', 'connectivity', 'no internet', 'network'],
                'priority' => 85,
                'category' => 'IT Support',
            ],
            [
                'name' => 'Microsoft 365 Support',
                'slug' => 'm365-support',
                'description' => 'Outlook, Teams, OneDrive, SharePoint, Exchange Online, login and email problems.',
                'system_instructions' => 'You are handling a Microsoft 365 enquiry (Outlook, Teams, OneDrive, SharePoint, Exchange Online). Give safe troubleshooting: verify account, check connection, restart the app, clear cache, check service health. NEVER request passwords or credentials. Where the fix depends on tenant administration, recommend a support ticket.',
                'trigger_keywords' => ['microsoft 365', 'm365', 'outlook', 'teams', 'onedrive', 'sharepoint', 'exchange', 'office 365'],
                'priority' => 85,
                'category' => 'IT Support',
            ],
            [
                'name' => 'Website Security',
                'slug' => 'website-security',
                'description' => 'HTTPS/TLS, headers, auth security, OWASP concepts, authorized testing only.',
                'system_instructions' => 'You are handling a website-security enquiry. Explain HTTPS/TLS, security headers, authentication, sessions, input validation, and OWASP concepts in plain language. Emphasize that ONLY systems the customer owns or is authorized to test may be assessed. Never provide exploit code or bypass techniques.',
                'trigger_keywords' => ['website security', 'https', 'tls', 'ssl certificate', 'security headers', 'owasp', 'xss', 'sql injection', 'csrf', 'secure my website'],
                'priority' => 85,
                'category' => 'Cybersecurity',
            ],
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Business websites, web apps, Laravel/PHP, APIs, CMS, maintenance, performance.',
                'system_instructions' => 'You are handling a web-development enquiry. Explain business websites, web applications, Laravel/PHP backends, frontends, APIs, databases, CMS options, maintenance, and performance in customer-friendly terms. Map needs to catalogue services; custom builds go to the quotation workflow. Never promise timelines or outcomes.',
                'trigger_keywords' => ['web development', 'website build', 'build a website', 'web application', 'web app', 'laravel', 'php', 'cms', 'wordpress', 'api development', 'redesign'],
                'priority' => 80,
                'category' => 'Services',
            ],
            [
                'name' => 'Digital Marketing',
                'slug' => 'digital-marketing',
                'description' => 'SEO, technical SEO, optimization, visibility, analytics, conversion.',
                'system_instructions' => 'You are handling a digital-marketing enquiry. Explain SEO, technical SEO, site optimization, visibility, content strategy, analytics, and conversion in plain terms. NEVER guarantee rankings, traffic, or revenue outcomes.',
                'trigger_keywords' => ['seo', 'marketing', 'ranking', 'rank higher', 'google ranking', 'traffic', 'analytics', 'conversion', 'visibility'],
                'priority' => 80,
                'category' => 'Services',
            ],
            [
                'name' => 'Cloud & Server Support',
                'slug' => 'cloud-server-support',
                'description' => 'Cloud infrastructure, Linux/Windows servers, hosting, monitoring, DNS, SSL/TLS.',
                'system_instructions' => 'You are handling a cloud/server enquiry. Explain cloud infrastructure, Linux and Windows servers, hosting, backups, monitoring, maintenance, DNS, and SSL/TLS at a customer-appropriate level. Never request or provide credentials, keys, or secrets. Server-side work goes to a support ticket.',
                'trigger_keywords' => ['cloud', 'server', 'hosting', 'linux', 'vps', 'dedicated server', 'uptime', 'monitoring', 'deployment', 'data center'],
                'priority' => 85,
                'category' => 'IT Support',
            ],
            [
                'name' => 'Backup & Disaster Recovery',
                'slug' => 'backup-disaster-recovery',
                'description' => 'Backup strategy, continuity, recovery planning, verification, objectives.',
                'system_instructions' => 'You are handling a backup/disaster-recovery enquiry. Explain backup strategy, the 3-2-1 principle in plain words, business continuity, recovery planning, verification, RPO/RTO in simple terms. Stress tested, verified backups. Never claim data is recoverable without verification.',
                'trigger_keywords' => ['backup', 'back up', 'disaster recovery', 'business continuity', 'restore data', 'data loss', 'recovery plan', 'rpo', 'rto'],
                'priority' => 85,
                'category' => 'IT Support',
            ],
            [
                'name' => 'Service Discovery',
                'slug' => 'service-discovery',
                'description' => 'Understand broad customer needs and map them to catalogue services.',
                'system_instructions' => 'You are discovering what the customer needs. Ask at most two short clarifying questions (users, locations, remote vs on-site), then map to services present in the approved catalogue context only. Never invent services or prices.',
                'trigger_keywords' => ['manage everything', 'manage our', 'need someone', 'fully managed', 'take care of', 'handle our it', 'outsource'],
                'priority' => 75,
                'category' => 'Services',
            ],
            [
                'name' => 'Support Ticket Assistance',
                'slug' => 'ticket-assistance',
                'description' => 'Ticket lifecycle: create, status, priorities, adding information, workflow.',
                'system_instructions' => 'You are helping with support tickets. Explain how to create one (portal tickets page or ask me to prepare a draft), priorities (low/medium/high/urgent/critical), and how status updates work. For status or replies on a specific ticket, the customer must be authenticated; use only their own tickets via authorized tools.',
                'trigger_keywords' => ['ticket', 'support request status', 'my tickets', 'track ticket', 'ticket priority', 'open a ticket'],
                'priority' => 85,
                'category' => 'Support',
            ],
            [
                'name' => 'Order Support',
                'slug' => 'order-support',
                'description' => 'Order status, service order process, pending/completed orders, next steps.',
                'system_instructions' => 'You are helping with orders. Explain the service-order process and next steps. Order details come ONLY from the authenticated customer\'s own orders via authorized tools. Guests must log in first.',
                'trigger_keywords' => ['my order', 'order status', 'track my order', 'my orders', 'order progress', 'when will my order'],
                'priority' => 85,
                'category' => 'Support',
            ],
            [
                'name' => 'Payment & Invoice Assistance',
                'slug' => 'payment-invoice-assistance',
                'description' => 'Payment/invoice process and status. No card data in chat, ever.',
                'system_instructions' => 'You are helping with payments and invoices. Explain the payment and invoice process and how to check status in the portal. Financial details come ONLY from the authenticated customer\'s own records. NEVER request, accept, or store card numbers or bank details in chat.',
                'trigger_keywords' => ['invoice', 'payment', 'bill', 'billing', 'receipt', 'refund', 'pay my', 'amount due', 'overdue'],
                'priority' => 85,
                'category' => 'Support',
            ],
            [
                'name' => 'Project Support',
                'slug' => 'project-support',
                'description' => 'Project status, milestones, tasks, progress, required customer actions.',
                'system_instructions' => 'You are helping with projects. Explain status, milestones, tasks, and progress from the authenticated customer\'s own projects via authorized tools only. Guests must log in first.',
                'trigger_keywords' => ['my project', 'project status', 'milestone', 'project progress', 'my task', 'project update'],
                'priority' => 85,
                'category' => 'Support',
            ],
            [
                'name' => 'Platform Navigation',
                'slug' => 'platform-navigation',
                'description' => 'How to use the website: tickets, orders, invoices, quotes, portals.',
                'system_instructions' => 'You are guiding platform navigation. Reference ONLY these verified routes: /portal/tickets (new ticket, replies), /portal/orders, /portal/invoices, /portal/projects, /portal/quotations, /portal/documents, /get-quote (quote requests), /services (catalogue), /knowledge-base (guides), /contact (human team). Never invent buttons or pages.',
                'trigger_keywords' => ['how do i submit', 'where can i see', 'where can i find', 'where is my', 'how do i access', 'portal guide', 'navigate'],
                'priority' => 95,
                'category' => 'Support',
            ],
            [
                'name' => 'Human Escalation',
                'slug' => 'human-escalation',
                'description' => 'Recognize when a human expert is required and arrange handoff.',
                'system_instructions' => 'You are deciding whether a human is required. Escalate: security incidents, complaints, billing disputes, contracts, refunds, legal matters, account ownership, missing company information, repeated failure. Say you are connecting them and offer a support ticket. NEVER claim a human was contacted unless the application performed the escalation.',
                'trigger_keywords' => ['speak to human', 'real person', 'talk to someone', 'complaint', 'manager', 'legal', 'sue', 'cancel everything', 'frustrated', 'useless'],
                'priority' => 60,
                'category' => 'Support',
            ],
        ];
    }
};
