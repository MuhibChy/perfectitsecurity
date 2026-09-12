<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Project;
use App\Models\Task;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\FinancialTransaction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\CommissionPayout;
use App\Models\CommissionPayoutItem;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\KbCategory;
use App\Models\KbArticle;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Setting;
use App\Models\UsefulLink;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\ProjectMilestone;
use App\Models\ProjectComment;
use App\Models\TaskComment;
use App\Models\BlogComment;
use App\Models\BlogTag;
use App\Models\KbTag;
use App\Models\SupportTeam;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Company
        $company = Company::create([
            'name' => 'TechSupport Corp',
            'slug' => 'techsupport-corp',
            'email' => 'info@techsupport.com',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Tech Avenue',
            'city' => 'San Francisco',
            'state' => 'CA',
            'country' => 'US',
            'website' => 'https://techsupport.com',
        ]);

        // Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $financeMgr = User::create([
            'name' => 'Finance Manager',
            'email' => 'finance@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'finance_manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $supportMgr = User::create([
            'name' => 'Support Manager',
            'email' => 'support@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'support_manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $agent1 = User::create([
            'name' => 'John Agent',
            'email' => 'agent1@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'support_agent',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $pm = User::create([
            'name' => 'Sarah PM',
            'email' => 'pm@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'project_manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $freelancer = User::create([
            'name' => 'Mike Freelancer',
            'email' => 'freelancer@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'freelancer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $customer1 = User::create([
            'name' => 'Alice Customer',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'company_name' => 'Acme Corp',
            'phone' => '+1 555 111 2222',
            'is_active' => true,
            'email_verified_at' => now(),
            'company_id' => $company->id,
        ]);

        $customer2 = User::create([
            'name' => 'Bob Customer',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'company_name' => 'TechStart Inc',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $agent2 = User::create([
            'name' => 'Jane Agent',
            'email' => 'agent2@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'support_agent',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $agent3 = User::create([
            'name' => 'Dave Agent',
            'email' => 'agent3@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'support_agent',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $customer3 = User::create([
            'name' => 'Carol Customer',
            'email' => 'carol@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'company_name' => 'Innovate Labs',
            'phone' => '+1 555 333 4444',
            'is_active' => true,
            'email_verified_at' => now(),
            'company_id' => $company->id,
        ]);

        $customer4 = User::create([
            'name' => 'David Customer',
            'email' => 'david@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'company_name' => 'GreenLeaf Systems',
            'phone' => '+1 555 555 6666',
            'is_active' => true,
            'email_verified_at' => now(),
            'company_id' => $company->id,
        ]);

        $customer5 = User::create([
            'name' => 'Eva Customer',
            'email' => 'eva@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'company_name' => 'Nexus Digital',
            'phone' => '+1 555 777 8888',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $freelancer2 = User::create([
            'name' => 'Lisa Freelancer',
            'email' => 'freelancer2@techsupport.com',
            'password' => Hash::make('password'),
            'role' => 'freelancer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Service Categories and Services are seeded via ServiceCatalogueSeeder (~100 services with multi-country pricing)

        // SLA Policies
        $slaLow = SlaPolicy::create(['name' => 'Basic', 'response_time_minutes' => 240, 'resolution_time_minutes' => 480, 'priority' => 'low']);
        $slaMed = SlaPolicy::create(['name' => 'Standard', 'response_time_minutes' => 120, 'resolution_time_minutes' => 240, 'priority' => 'medium']);
        $slaHigh = SlaPolicy::create(['name' => 'Priority', 'response_time_minutes' => 60, 'resolution_time_minutes' => 120, 'priority' => 'high']);
        $slaUrgent = SlaPolicy::create(['name' => 'Critical', 'response_time_minutes' => 30, 'resolution_time_minutes' => 60, 'priority' => 'urgent']);
        $slaCrit = SlaPolicy::create(['name' => 'Emergency', 'response_time_minutes' => 15, 'resolution_time_minutes' => 30, 'priority' => 'critical']);

        // Ticket Categories
        $tcHardware = TicketCategory::create(['name' => 'Hardware Issues', 'slug' => 'hardware', 'sla_policy_id' => $slaMed->id]);
        $tcSoftware = TicketCategory::create(['name' => 'Software Issues', 'slug' => 'software', 'sla_policy_id' => $slaMed->id]);
        $tcNetwork = TicketCategory::create(['name' => 'Network Issues', 'slug' => 'network', 'sla_policy_id' => $slaHigh->id]);
        $tcSecurity = TicketCategory::create(['name' => 'Security', 'slug' => 'security', 'sla_policy_id' => $slaUrgent->id]);
        $tcGeneral = TicketCategory::create(['name' => 'General Support', 'slug' => 'general', 'sla_policy_id' => $slaMed->id]);

        TicketSubcategory::create(['category_id' => $tcHardware->id, 'name' => 'Desktop', 'slug' => 'desktop']);
        TicketSubcategory::create(['category_id' => $tcHardware->id, 'name' => 'Laptop', 'slug' => 'laptop']);
        TicketSubcategory::create(['category_id' => $tcSoftware->id, 'name' => 'Windows', 'slug' => 'windows']);
        TicketSubcategory::create(['category_id' => $tcSoftware->id, 'name' => 'Microsoft 365', 'slug' => 'm365']);

        // Expense Categories
        ExpenseCategory::create(['name' => 'Software', 'slug' => 'software']);
        ExpenseCategory::create(['name' => 'Hosting', 'slug' => 'hosting']);
        ExpenseCategory::create(['name' => 'Marketing', 'slug' => 'marketing']);
        ExpenseCategory::create(['name' => 'Hardware', 'slug' => 'hardware']);
        ExpenseCategory::create(['name' => 'Office', 'slug' => 'office']);
        ExpenseCategory::create(['name' => 'Contractor', 'slug' => 'contractor']);

        // Commission Rules
        CommissionRule::create(['name' => 'Standard Commission', 'type' => 'percentage', 'rate' => 10, 'is_active' => true]);
        CommissionRule::create(['name' => 'Premium Commission', 'type' => 'percentage', 'rate' => 15, 'is_active' => true]);

        // Support Team
        $team1 = SupportTeam::create(['name' => 'Network Team', 'description' => 'Handles all networking issues']);
        $team2 = SupportTeam::create(['name' => 'Security Team', 'description' => 'Handles security incidents and assessments']);

        // Tickets
        $t1 = Ticket::create([
            'ticket_number' => 'TK-ABC12345',
            'customer_id' => $customer1->id,
            'subject' => 'Cannot connect to WiFi',
            'description' => 'Unable to connect to the office WiFi network from my laptop. The network shows as available but connection fails.',
            'category_id' => $tcNetwork->id,
            'priority' => 'high',
            'status' => 'in_progress',
            'assigned_to' => $agent1->id,
            'sla_response_deadline' => now()->addHours(2),
            'sla_resolution_deadline' => now()->addHours(4),
            'first_response_at' => now()->subHours(3),
            'tags' => ['wifi', 'office', 'laptop'],
        ]);

        $t2 = Ticket::create([
            'ticket_number' => 'TK-DEF67890',
            'customer_id' => $customer2->id,
            'subject' => 'Microsoft 365 email not syncing',
            'description' => 'Emails are not syncing across devices. Some emails from the last 24 hours are missing.',
            'category_id' => $tcSoftware->id,
            'priority' => 'medium',
            'status' => 'new',
            'tags' => ['email', 'm365', 'sync'],
        ]);

        $t3 = Ticket::create([
            'ticket_number' => 'TK-GHI11223',
            'customer_id' => $customer3->id,
            'subject' => 'Suspicious login activity detected',
            'description' => 'I noticed several failed login attempts on my admin account from unknown IP addresses. This could be a brute-force attack.',
            'category_id' => $tcSecurity->id,
            'priority' => 'urgent',
            'status' => 'in_progress',
            'assigned_to' => $agent2->id,
            'sla_response_deadline' => now()->addMinutes(30),
            'sla_resolution_deadline' => now()->addHours(1),
            'first_response_at' => now()->subMinutes(45),
            'tags' => ['security', 'brute-force', 'urgent'],
        ]);

        $t4 = Ticket::create([
            'ticket_number' => 'TK-JKL44556',
            'customer_id' => $customer4->id,
            'subject' => 'Printer not working in the main office',
            'description' => 'The HP LaserJet Pro on the 3rd floor is showing a paper jam error but there is no visible jam. I have restarted the printer multiple times.',
            'category_id' => $tcHardware->id,
            'priority' => 'low',
            'status' => 'new',
            'tags' => ['hardware', 'printer'],
        ]);

        $t5 = Ticket::create([
            'ticket_number' => 'TK-MNO77889',
            'customer_id' => $customer5->id,
            'subject' => 'VPN connection drops frequently',
            'description' => 'The VPN disconnects every 10-15 minutes when I am working from home. I am using the latest version of the VPN client on Windows 11.',
            'category_id' => $tcNetwork->id,
            'priority' => 'high',
            'status' => 'in_progress',
            'assigned_to' => $agent1->id,
            'first_response_at' => now()->subHours(1),
            'tags' => ['vpn', 'remote', 'connectivity'],
        ]);

        $t6 = Ticket::create([
            'ticket_number' => 'TK-PQR12345',
            'customer_id' => $customer1->id,
            'subject' => 'Windows update causing blue screen',
            'description' => 'After installing the latest Windows update, my computer keeps crashing with a BSOD. The error code is IRQL_NOT_LESS_OR_EQUAL.',
            'category_id' => $tcSoftware->id,
            'priority' => 'high',
            'status' => 'resolved',
            'assigned_to' => $agent3->id,
            'resolved_at' => now()->subDays(1),
            'resolution_details' => 'Rolled back the problematic Windows update and applied a patched driver. System is now stable.',
            'satisfaction_rating' => 5,
            'tags' => ['windows', 'bsod', 'update'],
        ]);

        $t7 = Ticket::create([
            'ticket_number' => 'TK-STU67890',
            'customer_id' => $customer2->id,
            'subject' => 'Need help setting up a new workstation',
            'description' => 'We just got 5 new laptops and need them configured with our standard software image, domain join, and email setup.',
            'category_id' => $tcGeneral->id,
            'priority' => 'medium',
            'status' => 'in_progress',
            'assigned_to' => $agent1->id,
            'tags' => ['setup', 'hardware', 'onboarding'],
        ]);

        $t8 = Ticket::create([
            'ticket_number' => 'TK-VWX12345',
            'customer_id' => $customer3->id,
            'subject' => 'Database server running slow',
            'description' => 'Our MySQL database server is responding very slowly. Queries that usually take 100ms are now taking 5+ seconds. This is affecting our production application.',
            'category_id' => $tcSoftware->id,
            'priority' => 'critical',
            'status' => 'in_progress',
            'assigned_to' => $agent2->id,
            'sla_response_deadline' => now()->addMinutes(15),
            'sla_resolution_deadline' => now()->addHours(1),
            'first_response_at' => now()->subMinutes(10),
            'tags' => ['database', 'performance', 'production'],
        ]);

        $t9 = Ticket::create([
            'ticket_number' => 'TK-YZA34567',
            'customer_id' => $customer4->id,
            'subject' => 'Email server certificate expiring',
            'description' => 'Our SSL certificate for the email server is expiring in 3 days. We need it renewed immediately to avoid service disruption.',
            'category_id' => $tcSecurity->id,
            'priority' => 'urgent',
            'status' => 'new',
            'tags' => ['ssl', 'certificate', 'email'],
        ]);

        $t10 = Ticket::create([
            'ticket_number' => 'TK-BCD89012',
            'customer_id' => $customer5->id,
            'subject' => 'Cannot access shared drive',
            'description' => 'Getting an access denied error when trying to connect to the shared network drive. I had access yesterday.',
            'category_id' => $tcNetwork->id,
            'priority' => 'medium',
            'status' => 'closed',
            'assigned_to' => $agent3->id,
            'resolved_at' => now()->subDays(2),
            'closed_at' => now()->subDays(1),
            'resolution_details' => 'User permissions were reset after a domain policy update. Re-granted access.',
            'satisfaction_rating' => 4,
            'tags' => ['permissions', 'network-drive'],
        ]);

        $t11 = Ticket::create([
            'ticket_number' => 'TK-EFG45678',
            'customer_id' => $customer1->id,
            'subject' => 'Antivirus software blocking legitimate application',
            'description' => 'Windows Defender is flagging our internal time-tracking application as malware and quarantining it. We need an exception added.',
            'category_id' => $tcSoftware->id,
            'priority' => 'medium',
            'status' => 'new',
            'tags' => ['antivirus', 'false-positive'],
        ]);

        $t12 = Ticket::create([
            'ticket_number' => 'TK-HIJ90123',
            'customer_id' => $customer3->id,
            'subject' => 'Need DNS records updated',
            'description' => 'We migrated our website to a new hosting provider and need the DNS A record and MX records updated to point to the new server.',
            'category_id' => $tcNetwork->id,
            'priority' => 'high',
            'status' => 'resolved',
            'assigned_to' => $agent1->id,
            'resolved_at' => now()->subDays(3),
            'resolution_details' => 'DNS records updated. TTL propagation complete across all major DNS providers.',
            'satisfaction_rating' => 5,
            'tags' => ['dns', 'hosting', 'migration'],
        ]);

        // Ticket Messages
        TicketMessage::create(['ticket_id' => $t1->id, 'user_id' => $agent1->id, 'message' => 'Hi Alice, I am looking into your WiFi issue. Can you try restarting your router?']);
        TicketMessage::create(['ticket_id' => $t1->id, 'user_id' => $customer1->id, 'message' => 'I tried restarting but still having the same issue.']);
        TicketMessage::create(['ticket_id' => $t1->id, 'user_id' => $agent1->id, 'message' => 'Thank you for confirming. Can you also check if other devices can connect to the same network? This will help us determine if it is device-specific.']);

        TicketMessage::create(['ticket_id' => $t3->id, 'user_id' => $agent2->id, 'message' => 'Hi Carol, I have reviewed the login logs. I can see 47 failed attempts from IP 192.168.1.105 over the past 2 hours. I have temporarily locked the account and will set up 2FA enforcement.']);
        TicketMessage::create(['ticket_id' => $t3->id, 'user_id' => $customer3->id, 'message' => 'Thank you for the quick response! Yes, that IP address is definitely not ours. Please lock it down.']);

        TicketMessage::create(['ticket_id' => $t5->id, 'user_id' => $agent1->id, 'message' => 'Hi Eva, I have checked our VPN server logs. I see connection resets happening at regular intervals. This could be a MTU issue. Can you try setting your MTU to 1400?']);
        TicketMessage::create(['ticket_id' => $t5->id, 'user_id' => $customer5->id, 'message' => 'I changed the MTU setting and the connection seems more stable now. It has been up for 30 minutes without dropping.']);
        TicketMessage::create(['ticket_id' => $t5->id, 'user_id' => $agent1->id, 'message' => 'Great! Let us monitor it for the rest of the day. If it stays stable, we can consider this resolved.']);

        TicketMessage::create(['ticket_id' => $t8->id, 'user_id' => $agent2->id, 'message' => 'I am investigating the database performance issue. Running a slow query log analysis now.']);
        TicketMessage::create(['ticket_id' => $t8->id, 'user_id' => $agent2->id, 'message' => 'Found the issue — a missing index on the orders table. Adding it now. This should improve query performance significantly.']);

        TicketMessage::create(['ticket_id' => $t7->id, 'user_id' => $agent1->id, 'message' => 'Hi Bob, I have started configuring the first laptop. Standard software image is being deployed. Estimated time for all 5 laptops is 2 business days.']);

        // Projects
        $proj1 = Project::create([
            'project_number' => 'PRJ-XYZ12345',
            'name' => 'Website Redesign',
            'slug' => 'website-redesign',
            'description' => 'Complete redesign and development of the company website with modern UI/UX, responsive design, and CMS integration.',
            'customer_id' => $customer1->id,
            'project_manager_id' => $pm->id,
            'service_id' => Service::where('name', 'Website Development')->first()->id ?? null,
            'budget' => 15000,
            'estimated_cost' => 8000,
            'actual_cost' => 3500,
            'start_date' => now()->subMonth(),
            'deadline' => now()->addMonths(2),
            'progress' => 35,
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $proj2 = Project::create([
            'project_number' => 'PRJ-MNO54321',
            'name' => 'Cloud Infrastructure Migration',
            'slug' => 'cloud-infrastructure-migration',
            'description' => 'Migrate on-premises servers to AWS cloud infrastructure. Includes EC2, RDS, S3, and CloudFront setup with auto-scaling.',
            'customer_id' => $customer3->id,
            'project_manager_id' => $pm->id,
            'service_id' => Service::where('name', 'Cloud Migration')->first()->id ?? null,
            'budget' => 45000,
            'estimated_cost' => 28000,
            'actual_cost' => 12000,
            'start_date' => now()->subMonths(2),
            'deadline' => now()->addMonth(),
            'progress' => 55,
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $proj3 = Project::create([
            'project_number' => 'PRJ-QRS98765',
            'name' => 'Security Audit & Hardening',
            'slug' => 'security-audit-hardening',
            'description' => 'Comprehensive security audit of all systems including penetration testing, vulnerability assessment, and remediation plan.',
            'customer_id' => $customer4->id,
            'project_manager_id' => $pm->id,
            'service_id' => Service::where('name', 'Penetration Testing')->first()->id ?? null,
            'budget' => 20000,
            'estimated_cost' => 12000,
            'actual_cost' => 10000,
            'start_date' => now()->subMonths(3),
            'deadline' => now()->subWeek(),
            'progress' => 90,
            'status' => 'review',
            'priority' => 'medium',
        ]);

        $proj4 = Project::create([
            'project_number' => 'PRJ-TUV34567',
            'name' => 'CRM Web Application',
            'slug' => 'crm-web-application',
            'description' => 'Custom CRM web application development with lead management, pipeline tracking, and reporting dashboard.',
            'customer_id' => $customer5->id,
            'project_manager_id' => $pm->id,
            'service_id' => Service::where('name', 'Web Application Development')->first()->id ?? null,
            'budget' => 35000,
            'estimated_cost' => 20000,
            'actual_cost' => 0,
            'start_date' => now()->addWeek(),
            'deadline' => now()->addMonths(4),
            'progress' => 0,
            'status' => 'planning',
            'priority' => 'high',
        ]);

        $proj5 = Project::create([
            'project_number' => 'PRJ-WXY11223',
            'name' => 'Office Network Overhaul',
            'slug' => 'office-network-overhaul',
            'description' => 'Complete office network redesign with new switches, access points, VLAN segmentation, and firewall configuration.',
            'customer_id' => $customer2->id,
            'project_manager_id' => $pm->id,
            'service_id' => Service::where('name', 'Network Setup')->first()->id ?? null,
            'budget' => 12000,
            'estimated_cost' => 7500,
            'actual_cost' => 7500,
            'start_date' => now()->subMonths(4),
            'deadline' => now()->subMonths(1),
            'completed_at' => now()->subMonths(1),
            'progress' => 100,
            'status' => 'completed',
            'priority' => 'medium',
        ]);

        // Project Milestones
        ProjectMilestone::create(['project_id' => $proj1->id, 'name' => 'Discovery & Planning', 'description' => 'Requirements gathering and project planning', 'due_date' => now()->subWeeks(3), 'is_completed' => true, 'completed_at' => now()->subWeeks(3), 'sort_order' => 1]);
        ProjectMilestone::create(['project_id' => $proj1->id, 'name' => 'UI/UX Design', 'description' => 'Design mockups and prototypes', 'due_date' => now()->subWeeks(1), 'is_completed' => true, 'completed_at' => now()->subWeeks(1), 'sort_order' => 2]);
        ProjectMilestone::create(['project_id' => $proj1->id, 'name' => 'Frontend Development', 'description' => 'Build responsive frontend components', 'due_date' => now()->addMonth(), 'sort_order' => 3]);
        ProjectMilestone::create(['project_id' => $proj1->id, 'name' => 'Backend Integration', 'description' => 'API development and CMS integration', 'due_date' => now()->addMonths(2), 'sort_order' => 4]);

        ProjectMilestone::create(['project_id' => $proj2->id, 'name' => 'Assessment & Planning', 'description' => 'Infrastructure assessment and migration plan', 'due_date' => now()->subMonths(1), 'is_completed' => true, 'completed_at' => now()->subMonths(1), 'sort_order' => 1]);
        ProjectMilestone::create(['project_id' => $proj2->id, 'name' => 'AWS Setup', 'description' => 'VPC, subnets, and core services configuration', 'due_date' => now()->subWeeks(2), 'is_completed' => true, 'completed_at' => now()->subWeeks(2), 'sort_order' => 2]);
        ProjectMilestone::create(['project_id' => $proj2->id, 'name' => 'Data Migration', 'description' => 'Migrate databases and file storage', 'due_date' => now()->addWeek(), 'sort_order' => 3]);
        ProjectMilestone::create(['project_id' => $proj2->id, 'name' => 'Cutover & Testing', 'description' => 'Final cutover and performance testing', 'due_date' => now()->addMonth(), 'sort_order' => 4]);

        // Tasks
        // Project 1 tasks
        Task::create(['title' => 'Design wireframes', 'project_id' => $proj1->id, 'assigned_to' => $freelancer->id, 'created_by' => $pm->id, 'status' => 'completed', 'priority' => 'high', 'type' => 'assigned', 'reward_amount' => 500]);
        Task::create(['title' => 'Develop frontend', 'project_id' => $proj1->id, 'assigned_to' => $freelancer->id, 'created_by' => $pm->id, 'status' => 'in_progress', 'priority' => 'high', 'type' => 'assigned', 'reward_amount' => 1500]);
        Task::create(['title' => 'Setup backend API', 'project_id' => $proj1->id, 'created_by' => $pm->id, 'status' => 'pending', 'priority' => 'medium', 'type' => 'open']);
        Task::create(['title' => 'Content migration', 'project_id' => $proj1->id, 'assigned_to' => $freelancer2->id, 'created_by' => $pm->id, 'status' => 'pending', 'priority' => 'medium', 'type' => 'assigned', 'reward_amount' => 800]);

        // Project 2 tasks
        Task::create(['title' => 'Set up AWS VPC and subnets', 'project_id' => $proj2->id, 'assigned_to' => $freelancer->id, 'created_by' => $pm->id, 'status' => 'completed', 'priority' => 'high', 'type' => 'assigned', 'reward_amount' => 1200]);
        Task::create(['title' => 'Configure RDS instances', 'project_id' => $proj2->id, 'assigned_to' => $freelancer->id, 'created_by' => $pm->id, 'status' => 'completed', 'priority' => 'high', 'type' => 'assigned', 'reward_amount' => 1000]);
        Task::create(['title' => 'Migrate MySQL databases', 'project_id' => $proj2->id, 'assigned_to' => $freelancer2->id, 'created_by' => $pm->id, 'status' => 'in_progress', 'priority' => 'urgent', 'type' => 'assigned', 'reward_amount' => 2000]);
        Task::create(['title' => 'Setup CloudFront CDN', 'project_id' => $proj2->id, 'created_by' => $pm->id, 'status' => 'pending', 'priority' => 'medium', 'type' => 'open']);
        Task::create(['title' => 'Performance testing', 'project_id' => $proj2->id, 'created_by' => $pm->id, 'status' => 'pending', 'priority' => 'high', 'type' => 'open']);

        // Project 3 tasks
        Task::create(['title' => 'Network vulnerability scan', 'project_id' => $proj3->id, 'assigned_to' => $freelancer->id, 'created_by' => $pm->id, 'status' => 'completed', 'priority' => 'high', 'type' => 'assigned', 'reward_amount' => 1500]);
        Task::create(['title' => 'Web application penetration test', 'project_id' => $proj3->id, 'assigned_to' => $freelancer2->id, 'created_by' => $pm->id, 'status' => 'completed', 'priority' => 'urgent', 'type' => 'assigned', 'reward_amount' => 3000]);
        Task::create(['title' => 'Write remediation report', 'project_id' => $proj3->id, 'assigned_to' => $freelancer->id, 'created_by' => $pm->id, 'status' => 'in_progress', 'priority' => 'high', 'type' => 'assigned', 'reward_amount' => 1000]);

        // Project comments
        ProjectComment::create(['project_id' => $proj1->id, 'user_id' => $pm->id, 'comment' => 'Design mockups are approved by the client. Moving to frontend development phase.']);
        ProjectComment::create(['project_id' => $proj1->id, 'user_id' => $freelancer->id, 'comment' => 'Started on the responsive layout. Should have the homepage done by Friday.']);
        ProjectComment::create(['project_id' => $proj2->id, 'user_id' => $pm->id, 'comment' => 'AWS setup is complete. All VPC peering and security groups are configured.']);
        ProjectComment::create(['project_id' => $proj2->id, 'user_id' => $freelancer->id, 'comment' => 'Database migration script is tested and ready to run. Scheduling the migration window for this weekend.']);

        // Task comments
        TaskComment::create(['task_id' => Task::where('title', 'Develop frontend')->first()->id, 'user_id' => $freelancer->id, 'comment' => 'Navigation and hero section are complete. Working on the services grid next.']);
        TaskComment::create(['task_id' => Task::where('title', 'Migrate MySQL databases')->first()->id, 'user_id' => $freelancer2->id, 'comment' => 'Schema export is done. Currently migrating the 50GB production database.']);

        // Commissions for Mike Freelancer (completed tasks)
        $ruleStandard = CommissionRule::where('name', 'Standard Commission')->first();
        $rulePremium = CommissionRule::where('name', 'Premium Commission')->first();

        $comm1 = Commission::create([
            'worker_id' => $freelancer->id,
            'rule_id' => $ruleStandard->id,
            'task_id' => Task::where('title', 'Design wireframes')->first()->id,
            'customer_id' => $customer1->id,
            'project_id' => $proj1->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 500,
            'commission_rate' => 10,
            'commission_amount' => 50,
            'status' => 'approved',
            'approved_by' => $pm->id,
            'approved_at' => now()->subWeeks(3),
            'payment_status' => 'paid',
            'paid_at' => now()->subWeeks(2),
            'notes' => 'Commission for completing wireframe design task',
        ]);

        $comm2 = Commission::create([
            'worker_id' => $freelancer->id,
            'rule_id' => $rulePremium->id,
            'task_id' => Task::where('title', 'Set up AWS VPC and subnets')->first()->id,
            'customer_id' => $customer3->id,
            'project_id' => $proj2->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 1200,
            'commission_rate' => 15,
            'commission_amount' => 180,
            'status' => 'approved',
            'approved_by' => $pm->id,
            'approved_at' => now()->subWeeks(2),
            'payment_status' => 'paid',
            'paid_at' => now()->subWeeks(1),
            'notes' => 'Commission for AWS VPC setup - premium task',
        ]);

        $comm3 = Commission::create([
            'worker_id' => $freelancer->id,
            'rule_id' => $ruleStandard->id,
            'task_id' => Task::where('title', 'Configure RDS instances')->first()->id,
            'customer_id' => $customer3->id,
            'project_id' => $proj2->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 1000,
            'commission_rate' => 10,
            'commission_amount' => 100,
            'status' => 'approved',
            'approved_by' => $pm->id,
            'approved_at' => now()->subWeek(),
            'payment_status' => 'unpaid',
            'notes' => 'Commission for RDS configuration task',
        ]);

        // Commissions for Mike Freelancer (in-progress tasks)
        $comm4 = Commission::create([
            'worker_id' => $freelancer->id,
            'rule_id' => $rulePremium->id,
            'task_id' => Task::where('title', 'Develop frontend')->first()->id,
            'customer_id' => $customer1->id,
            'project_id' => $proj1->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 1500,
            'commission_rate' => 15,
            'commission_amount' => 225,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => 'Commission pending - frontend development in progress',
        ]);

        // Commissions for Mike Freelancer (completed security tasks)
        $comm5 = Commission::create([
            'worker_id' => $freelancer->id,
            'rule_id' => $rulePremium->id,
            'task_id' => Task::where('title', 'Network vulnerability scan')->first()->id,
            'customer_id' => $customer4->id,
            'project_id' => $proj3->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 1500,
            'commission_rate' => 15,
            'commission_amount' => 225,
            'status' => 'approved',
            'approved_by' => $pm->id,
            'approved_at' => now()->subWeeks(2),
            'payment_status' => 'paid',
            'paid_at' => now()->subWeek(),
            'notes' => 'Commission for network vulnerability scan',
        ]);

        $comm6 = Commission::create([
            'worker_id' => $freelancer->id,
            'rule_id' => $ruleStandard->id,
            'task_id' => Task::where('title', 'Write remediation report')->first()->id,
            'customer_id' => $customer4->id,
            'project_id' => $proj3->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 1000,
            'commission_rate' => 10,
            'commission_amount' => 100,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => 'Commission pending - remediation report in progress',
        ]);

        // Commissions for Lisa Freelancer
        $comm7 = Commission::create([
            'worker_id' => $freelancer2->id,
            'rule_id' => $rulePremium->id,
            'task_id' => Task::where('title', 'Web application penetration test')->first()->id,
            'customer_id' => $customer4->id,
            'project_id' => $proj3->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 3000,
            'commission_rate' => 15,
            'commission_amount' => 450,
            'status' => 'approved',
            'approved_by' => $pm->id,
            'approved_at' => now()->subWeeks(2),
            'payment_status' => 'paid',
            'paid_at' => now()->subWeeks(1),
            'notes' => 'Commission for web application penetration test - high value task',
        ]);

        $comm8 = Commission::create([
            'worker_id' => $freelancer2->id,
            'rule_id' => $ruleStandard->id,
            'task_id' => Task::where('title', 'Migrate MySQL databases')->first()->id,
            'customer_id' => $customer3->id,
            'project_id' => $proj2->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 2000,
            'commission_rate' => 10,
            'commission_amount' => 200,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => 'Commission pending - database migration in progress',
        ]);

        $comm9 = Commission::create([
            'worker_id' => $freelancer2->id,
            'rule_id' => $ruleStandard->id,
            'task_id' => Task::where('title', 'Content migration')->first()->id,
            'customer_id' => $customer1->id,
            'project_id' => $proj1->id,
            'commission_type' => 'task_reward',
            'revenue_amount' => 800,
            'commission_rate' => 10,
            'commission_amount' => 80,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => 'Commission pending - content migration task',
        ]);

        // Commission Payouts
        $payout1 = CommissionPayout::create([
            'worker_id' => $freelancer->id,
            'amount' => 230,
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'TXN-BT-2024-001',
            'notes' => 'Payout for commissions COM-001 and COM-002 (wireframes + AWS VPC)',
            'status' => 'completed',
            'paid_at' => now()->subWeeks(1),
            'processed_by' => $financeMgr->id,
        ]);
        CommissionPayoutItem::create(['payout_id' => $payout1->id, 'commission_id' => $comm1->id, 'amount' => 50]);
        CommissionPayoutItem::create(['payout_id' => $payout1->id, 'commission_id' => $comm2->id, 'amount' => 180]);

        $payout2 = CommissionPayout::create([
            'worker_id' => $freelancer->id,
            'amount' => 225,
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'TXN-BT-2024-002',
            'notes' => 'Payout for commission COM-005 (network vulnerability scan)',
            'status' => 'completed',
            'paid_at' => now()->subWeek(),
            'processed_by' => $financeMgr->id,
        ]);
        CommissionPayoutItem::create(['payout_id' => $payout2->id, 'commission_id' => $comm5->id, 'amount' => 225]);

        $payout3 = CommissionPayout::create([
            'worker_id' => $freelancer2->id,
            'amount' => 450,
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'TXN-BT-2024-003',
            'notes' => 'Payout for commission COM-007 (web application penetration test)',
            'status' => 'completed',
            'paid_at' => now()->subWeeks(1),
            'processed_by' => $financeMgr->id,
        ]);
        CommissionPayoutItem::create(['payout_id' => $payout3->id, 'commission_id' => $comm7->id, 'amount' => 450]);

        $payout4 = CommissionPayout::create([
            'worker_id' => $freelancer->id,
            'amount' => 100,
            'payment_method' => 'paypal',
            'transaction_reference' => 'TXN-PP-2024-001',
            'notes' => 'Payout for commission COM-003 (RDS configuration)',
            'status' => 'completed',
            'paid_at' => now()->subDays(3),
            'processed_by' => $financeMgr->id,
        ]);
        CommissionPayoutItem::create(['payout_id' => $payout4->id, 'commission_id' => $comm3->id, 'amount' => 100]);

        // Pending payout (batch processing)
        $payout5 = CommissionPayout::create([
            'worker_id' => $freelancer2->id,
            'amount' => 280,
            'payment_method' => 'bank_transfer',
            'transaction_reference' => null,
            'notes' => 'Pending payout for commissions COM-008 and COM-009 (database migration + content migration)',
            'status' => 'pending',
            'paid_at' => null,
            'processed_by' => $financeMgr->id,
        ]);
        CommissionPayoutItem::create(['payout_id' => $payout5->id, 'commission_id' => $comm8->id, 'amount' => 200]);
        CommissionPayoutItem::create(['payout_id' => $payout5->id, 'commission_id' => $comm9->id, 'amount' => 80]);

        $payout6 = CommissionPayout::create([
            'worker_id' => $freelancer->id,
            'amount' => 225,
            'payment_method' => 'bank_transfer',
            'transaction_reference' => null,
            'notes' => 'Pending payout for commission COM-004 (frontend development)',
            'status' => 'pending',
            'paid_at' => null,
            'processed_by' => $financeMgr->id,
        ]);
        CommissionPayoutItem::create(['payout_id' => $payout6->id, 'commission_id' => $comm4->id, 'amount' => 225]);

        // Invoices
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-20240101-AB12',
            'customer_id' => $customer1->id,
            'subtotal' => 1299,
            'tax_rate' => 10,
            'tax_amount' => 129.90,
            'total' => 1428.90,
            'amount_paid' => 1428.90,
            'amount_due' => 0,
            'status' => 'paid',
            'due_date' => now()->addDays(30),
            'paid_at' => now()->subDays(5),
        ]);

        InvoiceItem::create(['invoice_id' => $inv1->id, 'description' => 'Remote IT Support - Monthly', 'quantity' => 1, 'unit_price' => 299, 'total' => 299]);
        InvoiceItem::create(['invoice_id' => $inv1->id, 'description' => 'Managed IT Services', 'quantity' => 1, 'unit_price' => 999, 'total' => 999]);
        InvoiceItem::create(['invoice_id' => $inv1->id, 'description' => 'Network Consultation', 'quantity' => 1, 'unit_price' => 1, 'total' => 1]);

        $inv2 = Invoice::create([
            'invoice_number' => 'INV-20240101-CD34',
            'customer_id' => $customer2->id,
            'subtotal' => 5000,
            'total' => 5000,
            'amount_due' => 5000,
            'status' => 'sent',
            'due_date' => now()->addDays(15),
        ]);

        InvoiceItem::create(['invoice_id' => $inv2->id, 'description' => 'Penetration Testing', 'quantity' => 1, 'unit_price' => 5000, 'total' => 5000]);

        $inv3 = Invoice::create([
            'invoice_number' => 'INV-20240215-EF56',
            'customer_id' => $customer3->id,
            'project_id' => $proj2->id,
            'subtotal' => 12000,
            'tax_rate' => 10,
            'tax_amount' => 1200,
            'total' => 13200,
            'amount_paid' => 13200,
            'amount_due' => 0,
            'status' => 'paid',
            'due_date' => now()->subMonths(1),
            'paid_at' => now()->subMonths(1)->addDays(5),
        ]);
        InvoiceItem::create(['invoice_id' => $inv3->id, 'description' => 'Cloud Migration - Phase 1 (Assessment & Planning)', 'quantity' => 1, 'unit_price' => 4000, 'total' => 4000]);
        InvoiceItem::create(['invoice_id' => $inv3->id, 'description' => 'AWS Infrastructure Setup', 'quantity' => 40, 'unit_price' => 100, 'total' => 4000]);
        InvoiceItem::create(['invoice_id' => $inv3->id, 'description' => 'Project Management', 'quantity' => 40, 'unit_price' => 100, 'total' => 4000]);

        $inv4 = Invoice::create([
            'invoice_number' => 'INV-20240301-GH78',
            'customer_id' => $customer4->id,
            'project_id' => $proj3->id,
            'subtotal' => 10000,
            'tax_rate' => 10,
            'tax_amount' => 1000,
            'total' => 11000,
            'amount_paid' => 5000,
            'amount_due' => 6000,
            'status' => 'partially_paid',
            'due_date' => now()->addDays(10),
        ]);
        InvoiceItem::create(['invoice_id' => $inv4->id, 'description' => 'Network Vulnerability Scan', 'quantity' => 1, 'unit_price' => 3000, 'total' => 3000]);
        InvoiceItem::create(['invoice_id' => $inv4->id, 'description' => 'Web Application Penetration Test', 'quantity' => 1, 'unit_price' => 5000, 'total' => 5000]);
        InvoiceItem::create(['invoice_id' => $inv4->id, 'description' => 'Security Report & Remediation Plan', 'quantity' => 1, 'unit_price' => 2000, 'total' => 2000]);

        $inv5 = Invoice::create([
            'invoice_number' => 'INV-20240315-IJ90',
            'customer_id' => $customer5->id,
            'subtotal' => 500,
            'tax_rate' => 10,
            'tax_amount' => 50,
            'total' => 550,
            'amount_paid' => 0,
            'amount_due' => 550,
            'status' => 'overdue',
            'due_date' => now()->subWeek(),
        ]);
        InvoiceItem::create(['invoice_id' => $inv5->id, 'description' => 'Remote IT Support - February', 'quantity' => 1, 'unit_price' => 299, 'total' => 299]);
        InvoiceItem::create(['invoice_id' => $inv5->id, 'description' => 'After-hours support (2 hours)', 'quantity' => 2, 'unit_price' => 100, 'total' => 200]);

        $inv6 = Invoice::create([
            'invoice_number' => 'INV-20240401-KL12',
            'customer_id' => $customer1->id,
            'subtotal' => 8000,
            'tax_rate' => 10,
            'tax_amount' => 800,
            'total' => 8800,
            'amount_paid' => 0,
            'amount_due' => 8800,
            'status' => 'draft',
            'due_date' => now()->addDays(45),
        ]);
        InvoiceItem::create(['invoice_id' => $inv6->id, 'description' => 'Website Redesign - Frontend Development', 'quantity' => 1, 'unit_price' => 5000, 'total' => 5000]);
        InvoiceItem::create(['invoice_id' => $inv6->id, 'description' => 'UI/UX Design - Additional Pages', 'quantity' => 5, 'unit_price' => 600, 'total' => 3000]);

        $inv7 = Invoice::create([
            'invoice_number' => 'INV-20240415-MN34',
            'customer_id' => $customer3->id,
            'project_id' => $proj2->id,
            'subtotal' => 14000,
            'tax_rate' => 10,
            'tax_amount' => 1400,
            'total' => 15400,
            'amount_paid' => 0,
            'amount_due' => 15400,
            'status' => 'sent',
            'due_date' => now()->addDays(20),
        ]);
        InvoiceItem::create(['invoice_id' => $inv7->id, 'description' => 'Cloud Migration - Phase 2 (Data Migration)', 'quantity' => 1, 'unit_price' => 6000, 'total' => 6000]);
        InvoiceItem::create(['invoice_id' => $inv7->id, 'description' => 'CloudFront CDN Configuration', 'quantity' => 1, 'unit_price' => 2000, 'total' => 2000]);
        InvoiceItem::create(['invoice_id' => $inv7->id, 'description' => 'Auto-Scaling Group Setup', 'quantity' => 1, 'unit_price' => 3000, 'total' => 3000]);
        InvoiceItem::create(['invoice_id' => $inv7->id, 'description' => 'Performance Testing & Optimization', 'quantity' => 30, 'unit_price' => 100, 'total' => 3000]);

        $inv8 = Invoice::create([
            'invoice_number' => 'INV-20240501-OP56',
            'customer_id' => $customer4->id,
            'project_id' => $proj3->id,
            'subtotal' => 2000,
            'tax_rate' => 10,
            'tax_amount' => 200,
            'total' => 2200,
            'amount_paid' => 0,
            'amount_due' => 2200,
            'status' => 'sent',
            'due_date' => now()->addDays(15),
        ]);
        InvoiceItem::create(['invoice_id' => $inv8->id, 'description' => 'Security Remediation - Critical Findings', 'quantity' => 4, 'unit_price' => 500, 'total' => 2000]);

        $inv9 = Invoice::create([
            'invoice_number' => 'INV-20240515-QR78',
            'customer_id' => $customer5->id,
            'subtotal' => 299,
            'tax_rate' => 10,
            'tax_amount' => 29.90,
            'total' => 328.90,
            'amount_paid' => 328.90,
            'amount_due' => 0,
            'status' => 'paid',
            'due_date' => now()->subMonths(1),
            'paid_at' => now()->subMonths(1)->addDays(3),
        ]);
        InvoiceItem::create(['invoice_id' => $inv9->id, 'description' => 'Remote IT Support - March', 'quantity' => 1, 'unit_price' => 299, 'total' => 299]);

        $inv10 = Invoice::create([
            'invoice_number' => 'INV-20240601-ST90',
            'customer_id' => $customer2->id,
            'subtotal' => 999,
            'tax_rate' => 10,
            'tax_amount' => 99.90,
            'total' => 1098.90,
            'amount_paid' => 1098.90,
            'amount_due' => 0,
            'status' => 'paid',
            'due_date' => now()->subMonths(1),
            'paid_at' => now()->subMonths(1)->addDays(7),
        ]);
        InvoiceItem::create(['invoice_id' => $inv10->id, 'description' => 'Managed IT Services - April', 'quantity' => 1, 'unit_price' => 699, 'total' => 699]);
        InvoiceItem::create(['invoice_id' => $inv10->id, 'description' => 'Remote IT Support - April', 'quantity' => 1, 'unit_price' => 299, 'total' => 299]);

        $inv11 = Invoice::create([
            'invoice_number' => 'INV-20240615-UV12',
            'customer_id' => $customer1->id,
            'subtotal' => 1800,
            'tax_rate' => 10,
            'tax_amount' => 180,
            'total' => 1980,
            'amount_paid' => 1980,
            'amount_due' => 0,
            'status' => 'paid',
            'due_date' => now()->subDays(15),
            'paid_at' => now()->subDays(12),
        ]);
        InvoiceItem::create(['invoice_id' => $inv11->id, 'description' => 'On-Site IT Support (12 hours)', 'quantity' => 12, 'unit_price' => 150, 'total' => 1800]);

        $inv12 = Invoice::create([
            'invoice_number' => 'INV-20240701-WX34',
            'customer_id' => $customer3->id,
            'subtotal' => 500,
            'tax_rate' => 10,
            'tax_amount' => 50,
            'total' => 550,
            'amount_paid' => 0,
            'amount_due' => 550,
            'status' => 'cancelled',
            'due_date' => now()->subWeeks(3),
            'notes' => 'Cancelled per client request - scope changed.',
        ]);
        InvoiceItem::create(['invoice_id' => $inv12->id, 'description' => 'AWS Management - Additional Hours', 'quantity' => 5, 'unit_price' => 100, 'total' => 500]);

        // Payments
        Payment::create([
            'payment_number' => 'PAY-XYZ12345',
            'invoice_id' => $inv1->id,
            'customer_id' => $customer1->id,
            'amount' => 1428.90,
            'status' => 'completed',
            'payment_method' => 'credit_card',
            'paid_at' => now()->subDays(5),
        ]);

        Payment::create([
            'payment_number' => 'PAY-ABC54321',
            'invoice_id' => $inv3->id,
            'customer_id' => $customer3->id,
            'amount' => 13200,
            'status' => 'completed',
            'payment_method' => 'bank_transfer',
            'paid_at' => now()->subMonths(1)->addDays(5),
        ]);

        Payment::create([
            'payment_number' => 'PAY-DEF98765',
            'invoice_id' => $inv4->id,
            'customer_id' => $customer4->id,
            'amount' => 5000,
            'status' => 'completed',
            'payment_method' => 'bank_transfer',
            'paid_at' => now()->subWeeks(2),
        ]);

        Payment::create([
            'payment_number' => 'PAY-GHI11223',
            'invoice_id' => $inv9->id,
            'customer_id' => $customer5->id,
            'amount' => 328.90,
            'status' => 'completed',
            'payment_method' => 'credit_card',
            'paid_at' => now()->subMonths(1)->addDays(3),
        ]);

        Payment::create([
            'payment_number' => 'PAY-JKL44556',
            'invoice_id' => $inv10->id,
            'customer_id' => $customer2->id,
            'amount' => 1098.90,
            'status' => 'completed',
            'payment_method' => 'credit_card',
            'paid_at' => now()->subMonths(1)->addDays(7),
        ]);

        Payment::create([
            'payment_number' => 'PAY-MNO77889',
            'invoice_id' => $inv11->id,
            'customer_id' => $customer1->id,
            'amount' => 1980,
            'status' => 'completed',
            'payment_method' => 'credit_card',
            'paid_at' => now()->subDays(12),
        ]);

        Payment::create([
            'payment_number' => 'PAY-PQR00112',
            'invoice_id' => $inv1->id,
            'customer_id' => $customer1->id,
            'amount' => 500,
            'status' => 'completed',
            'payment_method' => 'bank_transfer',
            'paid_at' => now()->subDays(20),
        ]);

        // Financial Transactions
        // Income
        FinancialTransaction::create(['type' => 'income', 'category' => 'Customer Payment', 'description' => 'Payment INV-20240101-AB12', 'amount' => 1428.90, 'status' => 'completed', 'customer_id' => $customer1->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Customer Payment', 'description' => 'Payment INV-20240215-EF56', 'amount' => 13200, 'status' => 'completed', 'customer_id' => $customer3->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Customer Payment', 'description' => 'Partial payment INV-20240301-GH78', 'amount' => 5000, 'status' => 'completed', 'customer_id' => $customer4->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Service Fee', 'description' => 'Monthly retainer - Acme Corp', 'amount' => 299, 'status' => 'completed', 'customer_id' => $customer1->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Customer Payment', 'description' => 'Payment INV-20240515-QR78', 'amount' => 328.90, 'status' => 'completed', 'customer_id' => $customer5->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Customer Payment', 'description' => 'Payment INV-20240601-ST90', 'amount' => 1098.90, 'status' => 'completed', 'customer_id' => $customer2->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Customer Payment', 'description' => 'Payment INV-20240615-UV12', 'amount' => 1980, 'status' => 'completed', 'customer_id' => $customer1->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Service Fee', 'description' => 'Monthly retainer - TechStart Inc', 'amount' => 699, 'status' => 'completed', 'customer_id' => $customer2->id, 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Service Fee', 'description' => 'Monthly retainer - Innovate Labs', 'amount' => 500, 'status' => 'completed', 'customer_id' => $customer3->id, 'created_by' => $financeMgr->id]);
        // Expenses
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Software', 'description' => 'Cloud hosting subscription', 'amount' => 250, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Marketing', 'description' => 'Google Ads campaign', 'amount' => 500, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Hosting', 'description' => 'AWS EC2 instances (monthly)', 'amount' => 1200, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Software', 'description' => 'Microsoft 365 licenses (20 seats)', 'amount' => 400, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Contractor', 'description' => 'Freelancer payment - wireframes', 'amount' => 500, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Hardware', 'description' => 'New laptop for support team', 'amount' => 1500, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Office', 'description' => 'Office supplies and peripherals', 'amount' => 350, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Marketing', 'description' => 'LinkedIn sponsored post campaign', 'amount' => 800, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Software', 'description' => 'Jira & Confluence licenses', 'amount' => 300, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Contractor', 'description' => 'Freelancer payment - frontend development', 'amount' => 1500, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Hosting', 'description' => 'AWS RDS database instances', 'amount' => 800, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Hardware', 'description' => 'Network switches and access points', 'amount' => 2200, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'expense', 'category' => 'Marketing', 'description' => 'Website redesign and SEO', 'amount' => 1000, 'status' => 'completed', 'created_by' => $financeMgr->id]);
        FinancialTransaction::create(['type' => 'income', 'category' => 'Customer Payment', 'description' => 'Payment INV-20240415-MN34 (partial)', 'amount' => 0, 'status' => 'pending', 'customer_id' => $customer3->id, 'created_by' => $financeMgr->id]);

        // Expenses
        $expSoftware = ExpenseCategory::where('slug', 'software')->first();
        $expHosting = ExpenseCategory::where('slug', 'hosting')->first();
        $expMarketing = ExpenseCategory::where('slug', 'marketing')->first();
        $expHardware = ExpenseCategory::where('slug', 'hardware')->first();
        $expContractor = ExpenseCategory::where('slug', 'contractor')->first();
        $expOffice = ExpenseCategory::where('slug', 'office')->first();

        Expense::create([
            'expense_number' => 'EXP-001',
            'category_id' => $expSoftware->id,
            'description' => 'Cloud hosting subscription',
            'amount' => 250,
            'date' => now()->subWeek(),
            'vendor' => 'AWS',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-002',
            'category_id' => $expHosting->id,
            'description' => 'AWS EC2 instances for client projects',
            'amount' => 1200,
            'date' => now()->subWeeks(2),
            'vendor' => 'AWS',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-003',
            'category_id' => $expMarketing->id,
            'description' => 'Google Ads campaign - Q1',
            'amount' => 500,
            'date' => now()->subMonth(),
            'vendor' => 'Google',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-004',
            'category_id' => $expHardware->id,
            'description' => 'New laptop for support team',
            'amount' => 1500,
            'date' => now()->subWeeks(3),
            'vendor' => 'Dell',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-005',
            'category_id' => $expContractor->id,
            'description' => 'Freelancer payment - website wireframes',
            'amount' => 500,
            'date' => now()->subWeeks(2),
            'vendor' => 'Mike Freelancer',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-006',
            'category_id' => $expSoftware->id,
            'description' => 'Microsoft 365 licenses (20 seats)',
            'amount' => 400,
            'date' => now()->subMonth(),
            'vendor' => 'Microsoft',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-007',
            'category_id' => $expOffice->id,
            'description' => 'Office supplies and peripherals',
            'amount' => 350,
            'date' => now()->subWeek(),
            'vendor' => 'Staples',
            'status' => 'pending',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-008',
            'category_id' => $expMarketing->id,
            'description' => 'Sponsored LinkedIn post campaign',
            'amount' => 800,
            'date' => now()->subWeeks(4),
            'vendor' => 'LinkedIn',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-009',
            'category_id' => $expSoftware->id,
            'description' => 'Jira & Confluence annual subscription',
            'amount' => 300,
            'date' => now()->subWeeks(3),
            'vendor' => 'Atlassian',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-010',
            'category_id' => $expContractor->id,
            'description' => 'Freelancer payment - frontend development (Phase 1)',
            'amount' => 1500,
            'date' => now()->subWeeks(2),
            'vendor' => 'Mike Freelancer',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-011',
            'category_id' => $expHosting->id,
            'description' => 'AWS RDS database instances (production)',
            'amount' => 800,
            'date' => now()->subWeek(),
            'vendor' => 'AWS',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-012',
            'category_id' => $expHardware->id,
            'description' => 'Network switches and access points',
            'amount' => 2200,
            'date' => now()->subWeeks(5),
            'vendor' => 'Ubiquiti',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-013',
            'category_id' => $expMarketing->id,
            'description' => 'Website redesign and SEO optimization',
            'amount' => 1000,
            'date' => now()->subMonths(2),
            'vendor' => 'SEO Agency',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-014',
            'category_id' => $expSoftware->id,
            'description' => 'GitHub Enterprise license (10 seats)',
            'amount' => 250,
            'date' => now()->subWeeks(2),
            'vendor' => 'GitHub',
            'status' => 'pending',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-015',
            'category_id' => $expOffice->id,
            'description' => 'Ergonomic chairs for support team',
            'amount' => 1800,
            'date' => now()->subMonths(1),
            'vendor' => 'Herman Miller',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-016',
            'category_id' => $expContractor->id,
            'description' => 'Freelancer payment - penetration testing',
            'amount' => 3000,
            'date' => now()->subWeeks(3),
            'vendor' => 'Lisa Freelancer',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-017',
            'category_id' => $expSoftware->id,
            'description' => 'SSL certificate renewal (wildcard)',
            'amount' => 150,
            'date' => now()->subDays(5),
            'vendor' => 'Let\'s Encrypt / DigiCert',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-018',
            'category_id' => $expHosting->id,
            'description' => 'Cloudflare Enterprise plan',
            'amount' => 350,
            'date' => now()->subWeek(),
            'vendor' => 'Cloudflare',
            'status' => 'pending',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-019',
            'category_id' => $expMarketing->id,
            'description' => 'Google Workspace Business', 'amount' => 200,
            'date' => now()->subDays(10),
            'vendor' => 'Google',
            'status' => 'approved',
            'created_by' => $financeMgr->id,
        ]);
        Expense::create([
            'expense_number' => 'EXP-020',
            'category_id' => $expOffice->id,
            'description' => 'Team lunch and client meeting',
            'amount' => 450,
            'date' => now()->subDays(3),
            'vendor' => 'The Capital Grille',
            'status' => 'pending',
            'created_by' => $financeMgr->id,
        ]);

        // Blog
        $blogCat = BlogCategory::create(['name' => 'Technology', 'slug' => 'technology']);
        $blogCatSecurity = BlogCategory::create(['name' => 'Cybersecurity', 'slug' => 'cybersecurity']);
        $blogCatCloud = BlogCategory::create(['name' => 'Cloud Computing', 'slug' => 'cloud-computing']);
        $blogCatTips = BlogCategory::create(['name' => 'Tips & Tutorials', 'slug' => 'tips-tutorials']);

        $post1 = BlogPost::create([
            'author_id' => $admin->id,
            'category_id' => $blogCat->id,
            'title' => 'The Future of IT Support in 2024',
            'slug' => 'future-of-it-support-2024',
            'excerpt' => 'Explore the emerging trends shaping IT support services including AI-powered helpdesks and predictive maintenance.',
            'content' => "The IT support landscape is evolving rapidly with AI, automation, and remote-first approaches. In this article, we explore the key trends that will shape the future of IT support services.\n\n## AI-Powered Helpdesks\n\nArtificial Intelligence is revolutionizing how support tickets are triaged and resolved. Modern AI systems can understand natural language, categorize issues automatically, and even provide step-by-step solutions for common problems. This frees up human agents to focus on complex, high-value issues.\n\n## Predictive Maintenance\n\nWith machine learning algorithms analyzing system logs and performance metrics, IT teams can now predict hardware failures and software issues before they impact users. This shift from reactive to proactive support reduces downtime and improves user satisfaction.\n\n## Remote-First Support\n\nThe pandemic accelerated the adoption of remote support tools. Today, screen sharing, remote desktop access, and collaborative troubleshooting platforms are standard tools in every IT support arsenal.\n\n## The Rise of Self-Service\n\nKnowledge bases, community forums, and AI chatbots are empowering users to solve their own issues. Organizations that invest in comprehensive self-service portals see a 30-40% reduction in support ticket volume.",
            'is_published' => true,
            'is_featured' => true,
            'published_at' => now()->subWeek(),
            'views_count' => 245,
        ]);

        $post2 = BlogPost::create([
            'author_id' => $admin->id,
            'category_id' => $blogCatSecurity->id,
            'title' => '10 Essential Cybersecurity Practices for Small Businesses',
            'slug' => '10-cybersecurity-practices-small-businesses',
            'excerpt' => 'Protect your business with these fundamental cybersecurity measures that every small business should implement.',
            'content' => "Small businesses are increasingly targeted by cybercriminals. Here are 10 essential practices to protect your organization:\n\n1. **Enable Multi-Factor Authentication** — MFA blocks 99.9% of account compromise attacks.\n2. **Keep Software Updated** — Regular patching closes known vulnerabilities.\n3. **Train Your Employees** — Human error is the #1 cause of breaches. Regular security awareness training is essential.\n4. **Implement Endpoint Protection** — Modern EDR solutions detect and respond to threats in real-time.\n5. **Backup Your Data** — Follow the 3-2-1 rule: 3 copies, 2 media types, 1 offsite.\n6. **Use Strong Passwords** — Implement a password policy and use a password manager.\n7. **Segment Your Network** — Limit lateral movement in case of a breach.\n8. **Monitor for Threats** — 24/7 monitoring ensures rapid detection and response.\n9. **Have an Incident Response Plan** — Know what to do before a breach happens.\n10. **Work with a Security Partner** — Managed security services provide enterprise-grade protection at SMB-friendly prices.",
            'is_published' => true,
            'is_featured' => true,
            'published_at' => now()->subWeeks(2),
            'views_count' => 512,
        ]);

        $post3 = BlogPost::create([
            'author_id' => $admin->id,
            'category_id' => $blogCatCloud->id,
            'title' => 'AWS vs Azure vs Google Cloud: Which is Right for Your Business?',
            'slug' => 'aws-vs-azure-vs-google-cloud',
            'excerpt' => 'A comprehensive comparison of the three major cloud platforms to help you make the right choice.',
            'content' => "Choosing the right cloud provider is one of the most important technology decisions your business will make. Here is our analysis of the Big Three:\n\n## Amazon Web Services (AWS)\n\nThe market leader with the broadest service catalog. Best for: startups, enterprises needing flexibility, and companies with diverse workloads.\n\n## Microsoft Azure\n\nThe best choice for organizations already invested in the Microsoft ecosystem. Seamless integration with Office 365, Active Directory, and .NET applications.\n\n## Google Cloud Platform (GCP)\n\nExcels in data analytics, machine learning, and Kubernetes. Ideal for data-driven companies and those with heavy AI/ML workloads.\n\n## Our Recommendation\n\nThere is no one-size-fits-all answer. We recommend starting with a cloud assessment to understand your specific needs, existing infrastructure, and long-term goals.",
            'is_published' => true,
            'published_at' => now()->subWeeks(3),
            'views_count' => 387,
        ]);

        $post4 = BlogPost::create([
            'author_id' => $admin->id,
            'category_id' => $blogCatTips->id,
            'title' => 'How to Set Up a Secure Home Office Network',
            'slug' => 'secure-home-office-network',
            'excerpt' => 'Step-by-step guide to securing your home office network for remote work.',
            'content' => "Working from home? Here is how to secure your home office network:\n\n1. Change your router's default admin credentials\n2. Enable WPA3 encryption (or WPA2 at minimum)\n3. Create a separate VLAN or guest network for work devices\n4. Keep your router firmware updated\n5. Use a VPN for accessing company resources\n6. Enable your router's built-in firewall\n7. Consider DNS-level filtering for malware protection\n8. Disable WPS and UPnP if not needed\n9. Use strong, unique passwords for all network devices\n10. Regularly review connected devices",            'is_published' => true,
            'published_at' => now()->subDays(3),
            'views_count' => 156,
        ]);

        $post5 = BlogPost::create([
            'author_id' => $supportMgr->id,
            'category_id' => $blogCat->id,
            'title' => 'Why Proactive IT Support Saves Money',
            'slug' => 'proactive-it-support-saves-money',
            'excerpt' => 'Discover how proactive monitoring and maintenance can reduce your IT costs by up to 40%.',
            'content' => "Reactive IT support — waiting for things to break before fixing them — is expensive and disruptive. Proactive IT support flips this model on its head.\n\n## The Cost of Downtime\n\nThe average cost of IT downtime is $5,600 per minute for businesses. Proactive monitoring can prevent up to 85% of these incidents.\n\n## Benefits of Proactive Support\n\n- Reduced downtime and fewer emergencies\n- Lower long-term maintenance costs\n- Extended hardware lifespan\n- Better security posture\n- Improved employee productivity\n\n## Making the Switch\n\nTransitioning to proactive support requires an initial investment in monitoring tools and processes, but the ROI is typically realized within the first 6 months.",
            'is_published' => true,
            'published_at' => now()->subDays(5),
            'views_count' => 89,
        ]);

        // Blog Comments
        BlogComment::create(['post_id' => $post1->id, 'user_id' => $customer1->id, 'name' => 'Alice', 'email' => 'alice@example.com', 'comment' => 'Great article! We have been thinking about implementing AI chatbots for our support desk. Any recommendations on platforms?', 'is_approved' => true]);
        BlogComment::create(['post_id' => $post1->id, 'user_id' => $customer2->id, 'name' => 'Bob', 'email' => 'bob@example.com', 'comment' => 'The section on predictive maintenance really resonated with us. We had a server failure last month that could have been prevented.', 'is_approved' => true]);
        BlogComment::create(['post_id' => $post2->id, 'user_id' => $customer3->id, 'name' => 'Carol', 'email' => 'carol@example.com', 'comment' => 'MFA is a game changer. We implemented it last quarter and have already blocked several unauthorized login attempts.', 'is_approved' => true]);

        // Knowledge Base
        $kbCat = KbCategory::create(['name' => 'Getting Started', 'slug' => 'getting-started', 'icon' => 'rocket', 'sort_order' => 1, 'is_active' => true]);
        $kbCatTroubleshoot = KbCategory::create(['name' => 'Troubleshooting', 'slug' => 'troubleshooting', 'icon' => 'wrench', 'sort_order' => 2, 'is_active' => true]);
        $kbCatSecurity = KbCategory::create(['name' => 'Security', 'slug' => 'kb-security', 'icon' => 'shield', 'sort_order' => 3, 'is_active' => true]);
        $kbCatAccount = KbCategory::create(['name' => 'Account & Billing', 'slug' => 'account-billing', 'icon' => 'credit-card', 'sort_order' => 4, 'is_active' => true]);

        KbArticle::create([
            'category_id' => $kbCat->id,
            'author_id' => $admin->id,
            'title' => 'How to Create a Support Ticket',
            'slug' => 'how-to-create-support-ticket',
            'content' => 'Follow these steps to create a support ticket:\n\n1. Log in to your customer portal dashboard.\n2. Navigate to the **Tickets** section in the sidebar.\n3. Click the **Create Ticket** button.\n4. Fill in the subject, category, priority, and description.\n5. Attach any relevant screenshots or files.\n6. Click **Submit** to create your ticket.\n\nYou will receive a confirmation email and can track the ticket status from your dashboard.',
            'excerpt' => 'Step-by-step guide to creating a support ticket in the customer portal.',
            'is_published' => true,
            'is_featured' => true,
            'visibility' => 'public',
            'difficulty' => 'beginner',
            'helpful_count' => 34,
            'views_count' => 156,
        ]);

        KbArticle::create([
            'category_id' => $kbCat->id,
            'author_id' => $admin->id,
            'title' => 'Navigating Your Customer Dashboard',
            'slug' => 'navigating-customer-dashboard',
            'content' => 'Your customer dashboard provides a centralized view of all your services, tickets, invoices, and projects. Here is a quick overview:\n\n- **Overview Panel** — Key metrics and recent activity\n- **Tickets** — View and manage your support requests\n- **Invoices** — Check billing status and download PDFs\n- **Projects** — Track project progress and milestones\n- **Notifications** — Stay updated on ticket and project changes',
            'excerpt' => 'Learn how to navigate and use your customer portal dashboard effectively.',
            'is_published' => true,
            'visibility' => 'public',
            'difficulty' => 'beginner',
            'helpful_count' => 21,
            'views_count' => 98,
        ]);

        KbArticle::create([
            'category_id' => $kbCatTroubleshoot->id,
            'author_id' => $admin->id,
            'title' => 'Troubleshooting WiFi Connection Issues',
            'slug' => 'troubleshooting-wifi-connection',
            'content' => "Having trouble connecting to WiFi? Try these steps:\n\n### Basic Steps\n1. Restart your computer and router\n2. Toggle WiFi off and on again\n3. Forget the network and reconnect\n4. Check if other devices can connect\n\n### Advanced Steps\n1. Reset your network adapter (Windows: Settings > Network & Internet > Network Reset)\n2. Update your WiFi driver\n3. Check for IP address conflicts\n4. Try a static IP address\n5. Check DNS settings\n\n### When to Contact Support\nIf none of these steps resolve the issue, please create a support ticket with your device model, OS version, and a description of the problem.",
            'excerpt' => 'Step-by-step guide to diagnosing and fixing WiFi connection problems.',
            'is_published' => true,
            'visibility' => 'public',
            'difficulty' => 'beginner',
            'helpful_count' => 67,
            'views_count' => 312,
        ]);

        KbArticle::create([
            'category_id' => $kbCatTroubleshoot->id,
            'author_id' => $admin->id,
            'title' => 'Fixing VPN Connection Drops',
            'slug' => 'fixing-vpn-connection-drops',
            'content' => 'If your VPN keeps disconnecting, here are the most common causes and fixes:\n\n1. **MTU Issues** — Try setting your MTU to 1400\n2. **DNS Leaks** — Configure your VPN to use its own DNS servers\n3. **Firewall Interference** — Ensure your firewall allows VPN traffic\n4. **Outdated Client** — Update to the latest VPN client version\n5. **ISP Throttling** — Some ISPs throttle VPN traffic; try a different protocol\n\nIf the issue persists, check your router settings for any VPN-related configurations that might be interfering.',
            'excerpt' => 'Solutions for unstable VPN connections that keep dropping.',
            'is_published' => true,
            'visibility' => 'public',
            'difficulty' => 'intermediate',
            'helpful_count' => 43,
            'views_count' => 189,
        ]);

        KbArticle::create([
            'category_id' => $kbCatSecurity->id,
            'author_id' => $admin->id,
            'title' => 'How to Enable Two-Factor Authentication (2FA)',
            'slug' => 'enable-two-factor-authentication',
            'content' => 'Two-factor authentication adds an extra layer of security to your account. Here is how to enable it:\n\n1. Go to **Settings > Security** in your dashboard\n2. Click **Enable 2FA**\n3. Choose your preferred method (Authenticator app, SMS, or email)\n4. Scan the QR code with your authenticator app\n5. Enter the verification code to confirm\n6. Save your backup codes in a secure location\n\nWe strongly recommend using an authenticator app (like Google Authenticator or Authy) over SMS for better security.',
            'excerpt' => 'Guide to setting up two-factor authentication on your account.',
            'is_published' => true,
            'visibility' => 'public',
            'difficulty' => 'beginner',
            'helpful_count' => 56,
            'views_count' => 267,
        ]);

        KbArticle::create([
            'category_id' => $kbCatSecurity->id,
            'author_id' => $admin->id,
            'title' => 'Recognizing Phishing Emails',
            'slug' => 'recognizing-phishing-emails',
            'content' => 'Phishing emails are designed to trick you into revealing sensitive information. Here is how to spot them:\n\n### Red Flags\n- Urgent language creating panic\n- Suspicious sender addresses\n- Generic greetings (Dear Customer instead of your name)\n- Links that do not match the displayed URL\n- Unexpected attachments\n- Requests for passwords or financial information\n\n### What to Do\n1. Do not click any links or download attachments\n2. Hover over links to see the actual URL\n3. Forward the email to your IT security team\n4. Report it as phishing in your email client\n5. Delete the email after reporting\n\nWhen in doubt, contact the supposed sender through a known, trusted channel.',
            'excerpt' => 'Learn how to identify and handle phishing email attempts.',
            'is_published' => true,
            'visibility' => 'public',
            'difficulty' => 'beginner',
            'helpful_count' => 89,
            'views_count' => 445,
        ]);

        KbArticle::create([
            'category_id' => $kbCatAccount->id,
            'author_id' => $admin->id,
            'title' => 'Understanding Your Invoice',
            'slug' => 'understanding-your-invoice',
            'content' => 'This guide explains each section of your invoice:\n\n- **Invoice Number** — Unique identifier for tracking\n- **Line Items** — Description, quantity, and price of each service\n- **Subtotal** — Total before tax and discounts\n- **Tax** — Applicable taxes based on your region\n- **Discount** — Any applied discounts\n- **Total** — Final amount due\n\nYou can download a PDF version of any invoice from the **Invoices** section of your dashboard. Payments can be made via credit card or bank transfer.',
            'excerpt' => 'Guide to understanding the components of your invoice.',
            'is_published' => true,
            'visibility' => 'public',
            'difficulty' => 'beginner',
            'helpful_count' => 28,
            'views_count' => 134,
        ]);

        // Quotations
        $q1 = Quotation::create([
            'customer_id' => $customer5->id,
            'notes' => 'Thank you for your interest in our CRM development services. This quotation covers the full scope of the custom CRM application.',
            'terms' => 'This quotation is valid for 30 days. Payment terms: 30% upfront, 40% at midpoint, 30% on completion.',
            'subtotal' => 35000,
            'tax_rate' => 10,
            'tax_amount' => 3500,
            'total' => 38500,
            'status' => 'sent',
            'valid_until' => now()->addDays(30),
            'sent_at' => now()->subDays(3),
        ]);
        QuotationItem::create(['quotation_id' => $q1->id, 'service_id' => Service::where('name', 'Custom Web Application Development')->first()->id ?? null, 'description' => 'CRM Web Application Development', 'quantity' => 1, 'unit_price' => 25000, 'total' => 25000]);
        QuotationItem::create(['quotation_id' => $q1->id, 'service_id' => Service::where('name', 'Cloud Migration')->first()->id ?? null, 'description' => 'Cloud Hosting Setup & Configuration', 'quantity' => 1, 'unit_price' => 5000, 'total' => 5000]);
        QuotationItem::create(['quotation_id' => $q1->id, 'description' => 'Project Management & Documentation', 'quantity' => 50, 'unit_price' => 100, 'total' => 5000]);

        $q2 = Quotation::create([
            'customer_id' => $customer1->id,
            'notes' => 'Proposal for ongoing managed IT services including 24/7 monitoring, helpdesk support, and quarterly reviews.',
            'terms' => 'Monthly billing. 12-month contract with 30-day cancellation notice.',
            'subtotal' => 11988,
            'tax_rate' => 10,
            'tax_amount' => 1198.80,
            'total' => 13186.80,
            'status' => 'accepted',
            'valid_until' => now()->subMonths(1),
            'accepted_at' => now()->subMonths(1)->addDays(5),
        ]);
        QuotationItem::create(['quotation_id' => $q2->id, 'description' => 'Remote IT Support (24/7)', 'quantity' => 12, 'unit_price' => 299, 'total' => 3588]);
        QuotationItem::create(['quotation_id' => $q2->id, 'description' => 'Managed IT Services', 'quantity' => 12, 'unit_price' => 699, 'total' => 8388]);

        $q3 = Quotation::create([
            'customer_id' => $customer2->id,
            'notes' => 'Network overhaul quotation including hardware, installation, and configuration.',
            'terms' => 'Net 30 payment terms. Hardware warranty included for 1 year.',
            'subtotal' => 12000,
            'tax_rate' => 10,
            'tax_amount' => 1200,
            'total' => 13200,
            'status' => 'accepted',
            'valid_until' => now()->subMonths(2),
            'accepted_at' => now()->subMonths(2)->addDays(3),
        ]);
        QuotationItem::create(['quotation_id' => $q3->id, 'description' => 'Network Equipment (Switches, APs, Firewall)', 'quantity' => 1, 'unit_price' => 7000, 'total' => 7000]);
        QuotationItem::create(['quotation_id' => $q3->id, 'description' => 'Installation & Configuration', 'quantity' => 40, 'unit_price' => 100, 'total' => 4000]);
        QuotationItem::create(['quotation_id' => $q3->id, 'description' => 'Network Documentation & Training', 'quantity' => 1, 'unit_price' => 1000, 'total' => 1000]);

        // Useful Links
        UsefulLink::create(['title' => 'AWS Management Console', 'url' => 'https://console.aws.amazon.com', 'description' => 'Access your AWS cloud services and resources', 'category' => 'Cloud', 'is_active' => true]);
        UsefulLink::create(['title' => 'Microsoft 365 Admin Center', 'url' => 'https://admin.microsoft.com', 'description' => 'Manage your Microsoft 365 subscription and users', 'category' => 'Productivity', 'is_active' => true]);
        UsefulLink::create(['title' => 'Cloudflare Dashboard', 'url' => 'https://dash.cloudflare.com', 'description' => 'Manage DNS, CDN, and security settings', 'category' => 'Network', 'is_active' => true]);
        UsefulLink::create(['title' => 'GitHub', 'url' => 'https://github.com', 'description' => 'Code repository and collaboration platform', 'category' => 'Development', 'is_active' => true]);
        UsefulLink::create(['title' => 'Jira', 'url' => 'https://atlassian.net', 'description' => 'Project management and issue tracking', 'category' => 'Project Management', 'is_active' => true]);
        UsefulLink::create(['title' => 'Grafana', 'url' => 'https://grafana.com', 'description' => 'Monitoring dashboards and alerting', 'category' => 'Monitoring', 'is_active' => true]);
        UsefulLink::create(['title' => 'VirusTotal', 'url' => 'https://www.virustotal.com', 'description' => 'Scan files and URLs for malware', 'category' => 'Security', 'is_active' => true]);
        UsefulLink::create(['title' => 'WhatIsMyIP', 'url' => 'https://whatismyipaddress.com', 'description' => 'Check your public IP address', 'category' => 'Network', 'is_active' => true]);

        // Notifications (polymorphic notifiable)
        Notification::create(['id' => \Illuminate\Support\Str::uuid(), 'type' => 'ticket_updated', 'notifiable_type' => 'App\\Models\\User', 'notifiable_id' => $customer1->id, 'data' => json_encode(['title' => 'Ticket Updated', 'message' => 'Your WiFi ticket (TK-ABC12345) has been updated by John Agent.']), 'read_at' => null]);
        Notification::create(['id' => \Illuminate\Support\Str::uuid(), 'type' => 'ticket_created', 'notifiable_type' => 'App\\Models\\User', 'notifiable_id' => $admin->id, 'data' => json_encode(['title' => 'New Ticket Created', 'message' => 'A new urgent ticket has been created by Carol Customer regarding suspicious login activity.']), 'read_at' => null]);
        Notification::create(['id' => \Illuminate\Support\Str::uuid(), 'type' => 'project_milestone', 'notifiable_type' => 'App\\Models\\User', 'notifiable_id' => $pm->id, 'data' => json_encode(['title' => 'Project Milestone Completed', 'message' => 'UI/UX Design milestone for Website Redesign has been completed.']), 'read_at' => now()]);
        Notification::create(['id' => \Illuminate\Support\Str::uuid(), 'type' => 'invoice_overdue', 'notifiable_type' => 'App\\Models\\User', 'notifiable_id' => $financeMgr->id, 'data' => json_encode(['title' => 'Invoice Overdue', 'message' => 'Invoice INV-20240315-IJ90 for Eva Customer is overdue by 7 days.']), 'read_at' => null]);
        Notification::create(['id' => \Illuminate\Support\Str::uuid(), 'type' => 'system_health', 'notifiable_type' => 'App\\Models\\User', 'notifiable_id' => $admin->id, 'data' => json_encode(['title' => 'System Health Warning', 'message' => 'CPU usage on production server exceeded 85% threshold.']), 'read_at' => null]);

        // Audit Logs
        AuditLog::create(['user_id' => $admin->id, 'action' => 'login', 'module' => 'auth', 'description' => 'Admin user logged in', 'ip_address' => '192.168.1.1', 'auditable_type' => User::class, 'auditable_id' => $admin->id]);
        AuditLog::create(['user_id' => $agent1->id, 'action' => 'ticket.update', 'module' => 'ticketing', 'description' => 'Updated ticket TK-ABC12345 status to in_progress', 'ip_address' => '192.168.1.10', 'auditable_type' => Ticket::class, 'auditable_id' => $t1->id]);
        AuditLog::create(['user_id' => $pm->id, 'action' => 'project.update', 'module' => 'projects', 'description' => 'Updated Website Redesign project progress to 35%', 'ip_address' => '192.168.1.20', 'auditable_type' => Project::class, 'auditable_id' => $proj1->id]);

        // Settings
        Setting::set('company_name', 'TechSupport Corp', 'general');
        Setting::set('company_email', 'info@techsupport.com', 'general');
        Setting::set('company_phone', '+1 (555) 123-4567', 'general');
        Setting::set('currency', 'USD', 'general');
        Setting::set('tax_rate', '10', 'general');
        Setting::set('company_address', '123 Tech Avenue, San Francisco, CA, US', 'general');
        Setting::set('company_website', 'https://techsupport.com', 'general');
        Setting::set('timezone', 'America/Los_Angeles', 'general');
        Setting::set('date_format', 'M d, Y', 'general');

        // Service Catalogue (multi-country pricing)
        $this->call(ServiceCatalogueSeeder::class);
        $this->call(ExpandedServiceCatalogueSeeder::class);
        $this->call(ExtraServicesSeeder::class);
    }
}
