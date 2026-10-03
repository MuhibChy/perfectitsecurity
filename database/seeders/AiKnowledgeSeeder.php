<?php

namespace Database\Seeders;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Approved AI Knowledge library: categories + customer-friendly FAQ and
 * troubleshooting articles. Idempotent (firstOrCreate by slug) and free of
 * fabricated company facts — no invented certifications, awards, prices,
 * SLAs, or customer stories. Anything scope-dependent points at the quote
 * or ticket workflow.
 */
class AiKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::whereIn('role', ['super_admin', 'admin'])->first();
        if (! $author) {
            $this->command?->warn('AiKnowledgeSeeder: no admin user found, skipping.');

            return;
        }

        foreach ($this->categories() as $slug => $cat) {
            KbCategory::firstOrCreate(['slug' => $slug], $cat + ['slug' => $slug, 'is_active' => true]);
        }

        $count = 0;
        foreach ($this->articles() as $a) {
            $cat = KbCategory::where('slug', $a['category'])->firstOrFail();
            KbArticle::firstOrCreate(
                ['slug' => $a['slug']],
                [
                    'category_id' => $cat->id,
                    'author_id' => $author->id,
                    'title' => $a['title'],
                    'slug' => $a['slug'],
                    'content' => $a['content'],
                    'excerpt' => $a['excerpt'],
                    'keywords' => $a['keywords'],
                    'visibility' => 'public',
                    'language' => 'en',
                    'difficulty' => $a['difficulty'] ?? 'beginner',
                    'is_published' => true,
                    'is_featured' => $a['featured'] ?? false,
                    'ai_readable' => true,
                ]
            );
            $count++;
        }
        $this->command?->info("AiKnowledgeSeeder: {$count} articles ensured.");
    }

    private function categories(): array
    {
        return [
            'about-company' => ['name' => 'About PerfectITSecurity', 'description' => 'Who we are and how to work with us.', 'icon' => 'building', 'sort_order' => 10],
            'it-support' => ['name' => 'IT Support', 'description' => 'Everyday IT help for businesses.', 'icon' => 'desktop', 'sort_order' => 11],
            'microsoft-365' => ['name' => 'Microsoft 365', 'description' => 'Outlook, Teams, OneDrive, accounts.', 'icon' => 'mail', 'sort_order' => 12],
            'networking' => ['name' => 'Networking', 'description' => 'Wi-Fi, Ethernet, DNS, VPN, connectivity.', 'icon' => 'wifi', 'sort_order' => 13],
            'website-security' => ['name' => 'Website Security', 'description' => 'HTTPS, headers, secure development.', 'icon' => 'globe', 'sort_order' => 14],
            'web-development' => ['name' => 'Web Development', 'description' => 'Websites, web apps, APIs, maintenance.', 'icon' => 'code', 'sort_order' => 15],
            'cloud-server' => ['name' => 'Cloud & Server', 'description' => 'Cloud, servers, hosting, monitoring.', 'icon' => 'cloud', 'sort_order' => 16],
            'backup-dr' => ['name' => 'Backup & Disaster Recovery', 'description' => 'Backups, continuity, recovery.', 'icon' => 'database', 'sort_order' => 17],
            'digital-marketing' => ['name' => 'Digital Marketing', 'description' => 'SEO, visibility, analytics.', 'icon' => 'chart', 'sort_order' => 18],
            'managed-it' => ['name' => 'Managed IT Services', 'description' => 'Fully managed IT for businesses.', 'icon' => 'briefcase', 'sort_order' => 19],
            'pricing-quotes' => ['name' => 'Pricing & Quotes', 'description' => 'How quoting and pricing work.', 'icon' => 'tag', 'sort_order' => 20],
            'orders-billing' => ['name' => 'Orders & Billing', 'description' => 'Orders, invoices, payments.', 'icon' => 'receipt', 'sort_order' => 21],
            'projects' => ['name' => 'Projects', 'description' => 'Project tracking and milestones.', 'icon' => 'kanban', 'sort_order' => 22],
            'support-tickets' => ['name' => 'Support Tickets', 'description' => 'Tickets, priorities, tracking.', 'icon' => 'ticket', 'sort_order' => 23],
            'customer-portal' => ['name' => 'Customer Portal', 'description' => 'Account, orders, documents.', 'icon' => 'user', 'sort_order' => 24],
            'ai-assistant' => ['name' => 'AI Assistant', 'description' => 'Using the AI support assistant.', 'icon' => 'sparkles', 'sort_order' => 25],
            'faq' => ['name' => 'Frequently Asked Questions', 'description' => 'Quick answers to common questions.', 'icon' => 'question', 'sort_order' => 26],
            'security-awareness' => ['name' => 'Security Awareness', 'description' => 'Stay safe online.', 'icon' => 'shield', 'sort_order' => 27],
            'troubleshooting' => ['name' => 'Troubleshooting Guides', 'description' => 'Safe first checks before contacting support.', 'icon' => 'wrench', 'sort_order' => 28],
            'policies' => ['name' => 'Policies', 'description' => 'How we handle requests and data.', 'icon' => 'document', 'sort_order' => 29],
            'contact-escalation' => ['name' => 'Contact & Escalation', 'description' => 'Reach a human expert.', 'icon' => 'phone', 'sort_order' => 30],
        ];
    }

    private function articles(): array
    {
        $A = [];
        $add = function (string $category, string $slug, string $title, string $excerpt, string $keywords, string $content, string $difficulty = 'beginner', bool $featured = false) use (&$A) {
            $A[] = compact('category', 'slug', 'title', 'excerpt', 'keywords', 'content', 'difficulty', 'featured');
        };

        // About
        $add('about-company', 'what-does-perfectitsecurity-do', 'What does PerfectITSecurity do?', 'IT support, cybersecurity, and technology services for businesses.', 'company services about',
            "PerfectITSecurity provides IT support, cybersecurity, and technology services for businesses.\n\n- **Managed IT support** — day-to-day technology help and maintenance\n- **Cybersecurity** — assessments, hardening, and awareness\n- **Websites and software** — development, maintenance, and security\n- **Cloud and servers** — hosting, monitoring, and backups\n\nUse /services to browse the catalogue or /get-quote to request a quotation.");
        $add('about-company', 'how-to-become-customer', 'How do I become a customer?', 'Register, verify, request a quote or service.', 'register customer account signup',
            "Becoming a customer takes three steps:\n\n1. **Register** an account and verify your email and phone.\n2. **Request a quote** at /get-quote or browse /services.\n3. **Accept the quotation** in your portal and work begins.\n\nFor help choosing, ask the AI Assistant or contact the team via /contact.");
        $add('about-company', 'do-you-provide-remote-support', 'Do you provide remote support?', 'Yes — remote-first support with on-site options by arrangement.', 'remote support onsite',
            "Yes. Most support is delivered remotely: troubleshooting, configuration, monitoring, and guidance.\n\nOn-site visits can be arranged where the work requires physical presence. Describe your situation in a service request and the team will propose the right approach.");

        // IT support / managed
        $add('it-support', 'what-is-managed-it-support', 'What is managed IT support?', 'One team managing your company technology.', 'managed it support what is',
            "Managed IT support means one accountable team looks after your company technology: user support, updates, monitoring, backups, and security basics.\n\nInstead of reacting to breakdowns, managed support aims to prevent them. Tell us your user count and locations through /get-quote and we will scope a suitable plan.", true);
        $add('managed-it', 'managed-it-for-50-employees', 'Managed IT for a growing company', 'How managed support scales with your team.', 'managed it employees offices scale',
            "Managed support scales with users, devices, and offices.\n\nTo scope a plan we usually ask: number of users, number of locations, remote vs on-site needs, and current pain points. Submit these via /get-quote and our team prepares a transparent quotation. No outcome is ever guaranteed — scope defines everything.");
        $add('it-support', 'remote-it-support-included', 'What does remote IT support include?', 'Troubleshooting, configuration, guidance, escalation.', 'remote support included troubleshooting',
            "Remote IT support typically includes:\n\n- Diagnosing software, email, and connectivity problems\n- Guiding configuration changes step by step\n- Monitoring and maintenance where contracted\n- Escalation to specialists or on-site visits\n\nAnything needing physical access or administrator credentials is handled through a verified support ticket, never through chat.");

        // M365
        $add('microsoft-365', 'outlook-not-opening', 'Outlook is not opening — safe first checks', 'Restart, update, profile repair, then ticket.', 'outlook not opening crash m365',
            "Safe checks you can try:\n\n1. Restart Outlook and your computer.\n2. Install pending Microsoft 365 updates.\n3. Start Outlook in safe mode to rule out add-ins.\n4. Check your internet connection.\n\n**Needs a professional:** repeated crashes, profile corruption across devices, or anything asking for administrator credentials. Create a support ticket instead of sharing passwords.");
        $add('microsoft-365', 'email-sync-problems', 'Email is not synchronizing', 'Connection, storage, filters, then support.', 'email sync not receiving send outlook',
            "Safe checks:\n\n1. Confirm you are online and can browse.\n2. Check mailbox storage is not full.\n3. Look in Junk/Other folders and check inbox rules.\n4. Remove and re-add the account only if you know the settings.\n\n**Needs a professional:** tenant-level mail flow, shared mailboxes, or compliance holds — open a ticket.");
        $add('microsoft-365', 'm365-login-problems', 'Microsoft 365 login problems', 'Credentials, MFA, and account status checks.', 'microsoft 365 login sign in mfa password',
            "Safe checks:\n\n1. Confirm the exact email address and tenant.\n2. Use the official password-reset link — never share your password in chat.\n3. Approve the MFA prompt on your second device.\n4. Try a private browser window to rule out cached sessions.\n\n**Needs a professional:** locked accounts, MFA resets, or admin consent — these require verified identity through a ticket.");

        // Networking
        $add('networking', 'no-internet-quick-checks', 'No internet — quick checks', 'Power, cables, router, ISP status.', 'no internet offline wifi ethernet down',
            "Safe checks:\n\n1. Check whether all devices or just one are affected.\n2. Restart the router/modem and wait two minutes.\n3. Try a wired connection to isolate Wi-Fi.\n4. Check your provider's status page from mobile data.\n\n**Needs a professional:** site-wide outages, router configuration, or DNS/DHCP changes — open a ticket.");
        $add('networking', 'what-is-dns-simple', 'What is DNS, simply?', 'DNS helps devices find the right server.', 'dns what is domain name',
            "DNS (Domain Name System) helps your device find the correct server for a website or service — like a phone book for the internet.\n\nIf DNS is not working correctly, websites or applications may appear unreachable even though your connection is fine. Switching to a known public resolver temporarily can confirm it; permanent changes belong to your IT administrator.");
        $add('networking', 'wifi-slow-or-dropping', 'Wi-Fi is slow or keeps dropping', 'Placement, congestion, device count.', 'wifi slow dropping disconnect wireless',
            "Safe checks:\n\n1. Move closer to the access point and retest.\n2. Restart the access point.\n3. Reduce congestion: too many devices on one radio slows everyone.\n4. Test with a cable to confirm Wi-Fi is the cause.\n\n**Needs a professional:** office-wide redesign, new access points, or controller configuration — request a quotation.");

        // Website security
        $add('website-security', 'what-is-https-tls', 'What are HTTPS and TLS?', 'Encrypted website connections explained.', 'https tls ssl certificate secure',
            "HTTPS means traffic between the browser and your website is encrypted using TLS. Visitors see a padlock; without it browsers show warnings.\n\nA valid certificate, automatic renewal, and redirecting all HTTP traffic to HTTPS are the basics. Certificate and server setup can be handled through a service request.");
        $add('website-security', 'what-is-vulnerability-assessment', 'What is a vulnerability assessment?', 'Authorized scanning plus prioritized fixes.', 'vulnerability assessment security testing scan',
            "A vulnerability assessment systematically checks your website or systems for known weaknesses, then prioritizes fixes by risk.\n\nImportant: only systems you own or are explicitly authorized to test may be assessed. Request a quotation describing your website and hosting, and the team will scope an authorized assessment. We never test third-party systems.");
        $add('website-security', 'secure-passwords-mfa', 'Secure passwords and MFA basics', 'Unique passwords plus a second factor.', 'password mfa 2fa secure account',
            "Two habits prevent most account takeovers:\n\n1. **Unique passwords** — a different long password per service, kept in a reputable password manager.\n2. **Multi-factor authentication (MFA)** — approve logins on a second device or use an authenticator app.\n\nNever share passwords in chat, email, or tickets. Support will never ask for yours.");

        // Web dev
        $add('web-development', 'business-website-service', 'Can you build a business website?', 'Yes — scope through the quote process.', 'build website business web design',
            "Yes. Business websites are scoped through /get-quote: pages, content, design needs, integrations, and maintenance.\n\nTimelines and cost depend entirely on scope, so the quotation defines them transparently. Ask the AI Assistant to prepare a quote request draft.");
        $add('web-development', 'web-app-vs-website', 'Website vs web application — which do I need?', 'Content sites vs interactive software.', 'web app vs website difference',
            "A **website** presents content (pages, blog, contact). A **web application** does interactive work (accounts, dashboards, bookings, payments).\n\nIf users log in and data changes, you likely need a web application. Describe your workflows in a quote request and the team will recommend the right approach.");
        $add('web-development', 'website-maintenance', 'Do you provide website maintenance?', 'Updates, backups, monitoring, small changes.', 'website maintenance updates care plan',
            "Maintenance typically covers updates, backups, uptime monitoring, security checks, and small content changes.\n\nCoverage and response expectations are defined in the service agreement — request a quotation for your site and hosting setup.");

        // Cloud/server
        $add('cloud-server', 'cloud-vs-own-servers', 'Cloud vs own servers — how to choose', 'Flexibility vs control trade-offs.', 'cloud vs server on premise hosting',
            "Cloud suits variable demand and minimal hardware fuss; own servers suit steady workloads, data-residency needs, or existing investments.\n\nMost small businesses start in the cloud. Describe your applications and data in a service request for a tailored recommendation.");
        $add('cloud-server', 'server-down-first-steps', 'Server unreachable — first steps', 'Confirm scope, provider status, then escalate.', 'server down unreachable offline',
            "Safe checks:\n\n1. Confirm whether one service or the whole server is affected.\n2. Check your hosting provider's status page.\n3. Note any recent changes (updates, deployments, DNS edits).\n4. Do NOT repeatedly reboot production without knowing the cause.\n\nThen open an urgent ticket with these details.");

        // Backup
        $add('backup-dr', 'backup-strategy-basics', 'Backup strategy basics (3-2-1)', 'Three copies, two media, one off-site.', 'backup strategy 3-2-1 data protection',
            "A sound backup strategy keeps **three copies** of important data, on **two different media**, with **one copy off-site**.\n\nEqually important: test restores regularly. An untested backup is only a hope. Ask for a backup review through a service request.");
        $add('backup-dr', 'what-are-rpo-rto', 'What are RPO and RTO?', 'How much data and time you can afford to lose.', 'rpo rto recovery objectives explained',
            "RPO (Recovery Point Objective) is how much data loss is tolerable — e.g. one day means daily backups suffice. RTO (Recovery Time Objective) is how fast systems must return.\n\nDefine both before buying backup services; they determine cost and design. We can help scope this via quotation.");

        // Marketing
        $add('digital-marketing', 'what-is-seo', 'What is SEO?', 'Visibility work — never guaranteed rankings.', 'seo what is search optimization',
            "SEO (Search Engine Optimization) improves how search engines understand and rank your site: fast pages, clear structure, useful content, and reputable links.\n\nNo honest provider guarantees rankings. Be wary of anyone who does. Technical SEO audits are available on quotation.");

        // Pricing/quotes
        $add('pricing-quotes', 'how-to-request-quote', 'How do I request a quote?', 'Submit requirements, receive transparent quotation.', 'how request quote quotation process',
            "Requesting a quote:\n\n1. Go to /get-quote and describe your requirements.\n2. Include users, locations, timeline, and current setup where relevant.\n3. Our team reviews and prepares a transparent quotation.\n4. Review, accept, or discuss it in your portal.\n\nThe AI Assistant can collect your requirements and prepare the request draft.", true);
        $add('pricing-quotes', 'why-pricing-varies', 'Why does pricing vary by project?', 'Scope defines cost.', 'pricing vary cost why custom',
            "Every environment differs — users, devices, locations, integrations, and timelines all change the work involved.\n\nThat is why fixed public prices would be misleading. Quotations are scoped per request so you pay for defined work, not guesses.");

        // Orders/billing
        $add('orders-billing', 'where-are-my-orders', 'Where are my orders?', 'Portal orders section walkthrough.', 'where orders track order portal',
            "Find your orders at /portal/orders after logging in. Each order shows its service, status, amounts paid and due, and next steps.\n\nGuests cannot view orders — log in to the account that placed them.");
        $add('orders-billing', 'where-are-my-invoices', 'Where are my invoices?', 'Portal invoices, PDF downloads.', 'where invoices download invoice portal billing',
            "Find invoices at /portal/invoices. Open any invoice to see line items, amounts paid and due, and download the PDF.\n\nFor billing disputes or refunds, open a support ticket — decisions need human review, never chat promises.");
        $add('orders-billing', 'how-do-payments-work', 'How do payments work?', 'Pay per invoice through offered methods.', 'how pay payment methods pay invoice',
            "Payments are made against invoices through the methods offered at checkout. Partial payments reduce the amount due; the invoice shows the remaining balance.\n\nNever share card numbers or bank details in chat — payments happen only on secure payment pages.");

        // Projects/tickets/portal
        $add('projects', 'track-project-progress', 'How do I track my project?', 'Portal projects, milestones, tasks.', 'track project progress milestone portal',
            "Track progress at /portal/projects: status, milestones, and tasks update as work proceeds.\n\nIf a milestone needs your input (content, approvals, access), respond promptly to keep the schedule.");
        $add('support-tickets', 'how-to-open-ticket', 'How do I open a support ticket?', 'Portal tickets page or AI draft.', 'how open ticket create support ticket',
            "Two ways:\n\n1. Log in, open /portal/tickets, choose New Ticket, describe the issue with steps to reproduce.\n2. Ask the AI Assistant — it prepares a draft for your confirmation before submitting.\n\nPriorities: low, medium, high, urgent, critical. Choose honestly; critical is for outages.", true);
        $add('support-tickets', 'check-ticket-status', 'How do I check my ticket?', 'Portal list, status meanings.', 'check ticket status track my ticket',
            "Open /portal/tickets after logging in. Each ticket shows its number, subject, status, and priority.\n\nOpen a ticket to read replies and add information. Only your own tickets are visible to you.");
        $add('customer-portal', 'portal-overview', 'Customer portal overview', 'Dashboard, tickets, orders, documents.', 'portal overview dashboard account guide',
            "Your portal centralizes everything:\n\n- **Dashboard** — overview and recent activity\n- **Tickets / Orders / Projects** — status and history\n- **Invoices / Quotations** — billing and approvals\n- **Documents** — shared files\n\nVerify your email and phone to unlock order confirmation features.");
        $add('ai-assistant', 'what-can-ai-assistant-do', 'What can the AI Assistant do?', 'Answers, drafts, status, escalation.', 'ai assistant what can do help',
            "The AI Assistant can:\n\n- Answer questions from approved company knowledge\n- Explain services and the quote process\n- Prepare ticket, service, and quote request drafts for your confirmation\n- Check your own tickets, orders, and projects when logged in\n- Connect you to human support\n\nIt cannot process payments, change contracts, or access other customers' data.", true);
        $add('faq', 'remote-support-available', 'Do you support businesses remotely?', 'Yes, remote-first with on-site options.', 'remote support businesses locations',
            "Yes — support is delivered remotely as standard, covering troubleshooting, configuration, and monitoring.\n\nMulti-location businesses are supported through planned coverage. Describe your setup via /get-quote.");

        // Awareness/incident
        $add('security-awareness', 'phishing-what-to-do', 'Suspected phishing — what to do', 'Stop, verify, report, never share secrets.', 'phishing suspicious email what to do',
            "If an email looks suspicious:\n\n1. **Stop** — do not click links or open attachments.\n2. **Verify** the sender through a known channel, never by replying.\n3. **Report** it to your IT team and mark it as phishing.\n4. **Never** share passwords, codes, or payment details.\n\nIf credentials may be compromised, say so in a support ticket immediately.");
        $add('security-awareness', 'suspected-compromise', 'Suspected account compromise', 'Contain, report urgently, no password sharing.', 'hacked account compromised unauthorized login',
            "Act quickly:\n\n1. Disconnect affected sessions where safe to do so.\n2. Do NOT delete evidence (messages, logs).\n3. Open an urgent support ticket describing what happened and when.\n4. Never share passwords — the team verifies identity through proper channels.\n\nThis is always escalated to human specialists.");

        // Policies/contact
        $add('policies', 'how-refunds-decided', 'How are refunds decided?', 'Human review per case, via ticket.', 'refund policy money back',
            "Refund decisions are reviewed individually by the responsible team — the assistant cannot approve or promise refunds.\n\nOpen a support ticket describing the order and reason. Include your order or invoice number for faster handling.");
        $add('policies', 'data-privacy-basics', 'How is my data handled?', 'Own-account access, role controls, no chat secrets.', 'privacy data handled gdpr personal',
            "Your data is visible only to your own account and authorized staff under role controls.\n\nSupport chats never ask for passwords, card numbers, or secrets. Customer data is strictly isolated between accounts.");
        $add('contact-escalation', 'talk-to-human', 'How do I reach a human expert?', 'Ticket, contact page, escalation.', 'talk human real person contact support phone',
            "Three routes:\n\n1. **Support ticket** — /portal/tickets, tracked and prioritized.\n2. **Contact page** — /contact for general enquiries and quotes.\n3. **In chat** — ask for a human and the assistant escalates the conversation.\n\nUrgent outages should always go through a ticket marked urgent or critical.");

        // Troubleshooting extras
        $add('troubleshooting', 'computer-slow-first-checks', 'Computer running slowly — first checks', 'Restart, updates, disk space, startup apps.', 'computer slow performance windows fix',
            "Safe checks:\n\n1. Restart the computer (not just sleep).\n2. Install pending system updates.\n3. Free disk space (aim for 15%+ free).\n4. Disable unneeded startup programs.\n5. Run a malware scan with your installed protection.\n\n**Needs a professional:** overheating, failing drives (clicking/grinding), or business-wide slowness.");
        $add('troubleshooting', 'printer-not-responding', 'Printer is not responding', 'Power, queue, drivers, network.', 'printer not working printing offline',
            "Safe checks:\n\n1. Power-cycle the printer and check for error lights/paper jams.\n2. Clear stuck jobs from the print queue and retry.\n3. Confirm the printer shows online; re-add it if not.\n4. For network printers, confirm it has an IP address.\n\n**Needs a professional:** shared office printers, print servers, or driver deployment.");

        return $A;
    }
}
