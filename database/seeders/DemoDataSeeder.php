<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Country;
use App\Models\CustomerDocument;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\KbTag;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Proposal;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCountryPrice;
use App\Models\ServiceOrder;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Models\TicketTimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * DemoDataSeeder — realistic-looking synthetic records for pre-production
 * verification of every form, field, workflow, and relationship.
 *
 * Presentation vs identification:
 * - Display names/titles are realistic and professional (no demo labels).
 * - Every record carries is_demo=true (hidden machine flag) for safe
 *   targeting by tests and `demo:cleanup`. Operational markers that are
 *   never customer-visible (demo.*@example.test emails, DEMO-TXN refs,
 *   type=demo notifications, documents/demo-* paths) are retained.
 * - All emails use the reserved .test TLD (never real people).
 * - All phones are synthetic fictional ranges.
 * - Passwords are hashed; convention documented as DemoPass123!.
 * - Idempotent: re-running exits early when the batch already exists.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'demo.customer.001@example.test')->exists()) {
            $this->command?->warn('Sample batch already present — skipping (use demo:cleanup to remove first).');
            return;
        }

        DB::transaction(function () {
            $users = $this->seedUsers();
            $companies = $this->seedCompanies($users);
            $leads = $this->seedLeads($users);
            $services = $this->seedServices();
            $requests = $this->seedServiceRequests($users, $services, $leads);
            $quotes = $this->seedQuotations($users);
            $proposals = $this->seedProposals($users, $quotes);
            $contracts = $this->seedContracts($users, $proposals);
            $orders = $this->seedOrders($users, $services);
            $projects = $this->seedProjects($users);
            $this->seedTasks($users, $projects, $orders);
            $this->seedTickets($users);
            $this->seedKnowledgeBase($users);
            $invoices = $this->seedInvoices($users, $orders);
            $this->seedPayments($invoices);
            $this->seedExpenses($users, $projects);
            $this->seedNotifications($users);
            $this->seedDocuments($users);
        });

        $this->command?->info('Sample batch seeded: 5 records per entity, flagged is_demo.');
    }

    // ---------------------------------------------------------------- users
    private function seedUsers(): array
    {
        $customers = [];
        $profiles = [
            ['Sarah Mitchell', 'demo.customer.001@example.test', '+44 20 7946 0001', 'GB'],
            ['James Carter', 'demo.customer.002@example.test', '+1 555 010 0002', 'US'],
            ['Priya Rahman', 'demo.customer.003@example.test', '+880 2550 100003', 'BD'],
            ['Thomas Weber', 'demo.customer.004@example.test', '+49 30 901820004', 'DE'],
            ['Emily Chen', 'demo.customer.005@example.test', '+1 416 555 0005', 'CA'],
        ];
        foreach ($profiles as [$name, $email, $phone, $country]) {
            $customers[] = User::create([
                'is_demo' => true,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('DemoPass123!'),
                'phone' => $phone,
                'country' => $country,
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        $staff = [];
        foreach ([
            ['Daniel Okafor', 'demo.support@example.test', 'support_agent'],
            ['Rachel Kim', 'demo.pm@example.test', 'project_manager'],
            ['Michael Brown', 'demo.finance@example.test', 'finance_manager'],
            ['Sofia Garcia', 'demo.sales@example.test', 'sales_agent'],
            ['Alex Novak', 'demo.freelancer@example.test', 'freelancer'],
        ] as [$name, $email, $role]) {
            $staff[$role] = User::create([
                'is_demo' => true,
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('DemoPass123!'),
                'phone' => '+44 20 7946 0100',
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        return ['customers' => $customers, 'staff' => $staff];
    }

    // ------------------------------------------------------------ companies
    private function seedCompanies(array $users): array
    {
        $out = [];
        $data = [
            ['Northbridge Technologies Ltd', 'northbridge-technologies-ltd', 'billing@acme-uk.example.test', '+44 20 7946 0101', 'London', 'United Kingdom', 'https://acme-uk.example.test'],
            ['Globex Inc', 'globex-inc', 'billing@globex.example.test', '+1 555 010 0102', 'New York', 'United States', 'https://globex.example.test'],
        ];
        foreach ($data as $i => [$name, $slug, $email, $phone, $city, $country, $website]) {
            $out[] = Company::create([
                'is_demo' => true,
                'name' => $name, 'slug' => $slug, 'email' => $email, 'phone' => $phone,
                'address' => '1 Business Street, Suite 100', 'city' => $city, 'country' => $country,
                'website' => $website, 'status' => 'active',
            ]);
            $users['customers'][$i]->update(['company_id' => $out[count($out) - 1]->id]);
        }
        return $out;
    }

    // ---------------------------------------------------------------- leads
    private function seedLeads(array $users): array
    {
        $specs = [
            ['David Osei', 'Northgate Retail Ltd', 'Initial enquiry logged. Follow-up scheduled with client contact.', 'website', 'new', 'low', 500.00, 'USD', null],
            ['Maria Santos', 'Santos Logistics Ltd', 'Interested in managed support cover.', 'get-quote', 'contacted', 'medium', 5000.00, 'GBP', '+30 days'],
            ['Kenji Tanaka', 'Tanaka Manufacturing', 'Requested cloud migration scoping call.', 'contact', 'qualified', 'high', 15000.50, 'USD', '+7 days'],
            ['Anna Kowalski', 'Kowalski Financial Services', 'Evaluating security review options.', 'referral', 'proposal', 'urgent', 75000.00, 'EUR', '+1 day'],
            ['Sarah Mitchell', 'Northbridge Technologies Ltd', 'Converted to customer account.', 'manual', 'won', 'medium', 1200.00, 'BDT', null],
        ];
        $out = [];
        foreach ($specs as $i => [$name, $company, $notes, $source, $status, $priority, $value, $currency, $followUp]) {
            $lead = Lead::create([
                'is_demo' => true,
                'service_request_id' => null,
                'customer_id' => $status === 'won' ? $users['customers'][0]->id : null,
                'assigned_to' => $users['staff']['sales_agent']->id,
                'name' => $name,
                'email' => 'demo.lead.00' . ($i + 1) . '@example.test',
                'phone' => '+1 555 020 000' . ($i + 1),
                'company_name' => $company,
                'source' => $source,
                'status' => $status,
                'priority' => $priority,
                'estimated_value' => $value,
                'currency' => $currency,
                'notes' => $notes,
                'tags' => ['verification-batch', "batch-00" . ($i + 1)],
                'next_follow_up_at' => $followUp ? \Carbon\Carbon::parse($followUp) : null,
                'converted_at' => $status === 'won' ? now() : null,
            ]);
            LeadActivity::create([
                'lead_id' => $lead->id, 'user_id' => $users['staff']['sales_agent']->id,
                'type' => 'note', 'subject' => 'Verification note', 'body' => 'Record created for verification.',
            ]);
            $out[] = $lead;
        }
        return $out;
    }

    // ------------------------------------------------------------- services
    private function seedServices(): array
    {
        $category = ServiceCategory::first() ?? ServiceCategory::create(['name' => 'General Services', 'slug' => 'general-services', 'is_active' => true]);
        $countries = Country::active()->take(3)->get();
        if ($countries->isEmpty()) {
            $countries = collect([Country::create(['name' => 'Sample Region', 'code' => 'DL', 'currency_code' => 'USD', 'currency_symbol' => '$', 'is_active' => true])]);
        }
        $specs = [
            ['Network Security Review', 'network-security-review', 'External security review covering firewall rules, exposed services, and baseline hardening.', 'fixed', 0.00, true],
            ['Cloud Migration Assessment', 'cloud-migration-assessment', 'Structured assessment of workloads, dependencies, and migration sequencing.', 'hourly', 49.99, true],
            ['Managed Detection Trial', 'managed-detection-trial', 'Time-boxed trial of managed monitoring with weekly summary reports.', 'monthly', 199.00, false],
            ['Website Care Plan', 'website-care-plan', 'Monthly maintenance covering updates, backups, uptime checks, and small content changes.', 'annual', 1999.95, true],
            ['IT Health Check', 'it-health-check', 'One-off review of workstations, network, backups, and security basics with a findings report.', 'custom', 10000.00, true],
        ];
        $out = [];
        foreach ($specs as [$name, $slug, $short, $priceType, $price, $featured]) {
            $service = Service::create([
                'is_demo' => true,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug . '-' . Str::random(4),
                'short_description' => $short,
                'description' => $short . "\nDelivered by qualified engineers with a written findings report.",
                'price_type' => $priceType,
                'starting_price' => $price,
                'hourly_rate' => $priceType === 'hourly' ? $price : null,
                'allows_custom_quote' => true,
                'features' => ['assessment', 'reporting'],
                'is_featured' => $featured,
                'is_active' => true,
                'seo_title' => "{$name} — Professional Services",
                'seo_description' => "Learn about {$name} and how to request it.",
            ]);
            foreach ($countries->take(2) as $country) {
                ServiceCountryPrice::create([
                    'service_id' => $service->id,
                    'country_id' => $country->id,
                    // pricing_type enum: fixed, starting_from, hourly, daily,
                    // monthly, recurring, custom_quote.
                    'pricing_type' => match ($priceType) {
                        'annual' => 'recurring',
                        'custom' => 'custom_quote',
                        default => $priceType,
                    },
                    'price' => $price,
                    'is_active' => true,
                ]);
            }
            $out[] = $service;
        }
        return $out;
    }

    // ------------------------------------------------------ service requests
    private function seedServiceRequests(array $users, array $services, array $leads): array
    {
        $out = [];
        $statuses = ['new', 'reviewing', 'quoted', 'accepted', 'rejected'];
        $requesters = ['Sarah Mitchell', 'James Carter', 'Priya Rahman', 'Thomas Weber', 'Emily Chen'];
        $requestCompanies = ['Northbridge Technologies Ltd', 'Carter Retail Group', 'Rahman Logistics Ltd', 'Weber Manufacturing', 'Chen Financial Services'];
        $requestSubjects = ['Network assessment request', 'Cloud migration enquiry', 'Support cover enquiry', 'Website project enquiry', 'Security review request'];
        foreach (range(0, 4) as $i) {
            $sr = ServiceRequest::create([
                'is_demo' => true,
                'user_id' => $users['customers'][$i]->id,
                'service_id' => $services[$i]->id,
                'name' => $requesters[$i],
                'email' => 'demo.request.00' . ($i + 1) . '@example.test',
                'phone' => '+880 1700 00000' . ($i + 1),
                'company' => $requestCompanies[$i],
                'subject' => $requestSubjects[$i],
                'requirements' => "Requirement details as discussed.\nTimeline and scope to be confirmed with the client.",
                'budget' => [0, 99.99, 2500.00, 50000.00, 999999.99][$i],
                'budget_range' => ['under-5k', '5k-15k', '15k-50k', '50k-100k', '100k-plus'][$i],
                'timeline' => ['urgent', '1-month', '3-months', '6-months', 'exploring'][$i],
                'lead_source' => 'demo',
                'status' => $statuses[$i],
                'review_status' => ['new', 'under_review', 'quoted', 'accepted', 'rejected'][$i],
                'priority' => ['low', 'medium', 'high', 'urgent', 'medium'][$i],
            ]);
            $leads[$i]->update(['service_request_id' => $sr->id]);
            $out[] = $sr;
        }
        return $out;
    }

    // ------------------------------------------------------------ quotations
    private function seedQuotations(array $users): array
    {
        $out = [];
        $combos = [
            [['Penetration test — small scope', 1, 500.00, 0], ['Retest window', 2, 150.00, 25.00], 20.0, 0, 'draft', '+30 days'],
            [['Consulting day block', 10, 120.00, 0], [], 10.0, 100.00, 'sent', '+14 days'],
            [['Server hardening (3 hosts)', 3, 299.99, 10.00], [], 0, 0, 'accepted', '+7 days'],
            [['Scoping workshop', 1, 0.00, 0], [], 15.0, 0, 'sent', '-2 days'],
            [['Annual monitoring', 1, 120000.00, 5000.00], ['Onboarding', 1, 2500.00, 0], 8.5, 0, 'converted', '+60 days'],
        ];
        foreach ($combos as $i => [$items, $extra, $taxRate, $discount, $status, $valid]) {
            $all = array_merge([$items], $extra ? [$extra] : []);
            $q = Quotation::create([
                'is_demo' => true,
                'customer_id' => $users['customers'][$i]->id,
                'assigned_to' => $users['staff']['sales_agent']->id,
                'currency' => ['USD', 'GBP', 'BDT', 'EUR', 'USD'][$i],
                'notes' => 'Prepared following discovery call ' . ($i + 1) . '.',
                'terms' => 'Standard terms: net 14.',
                'valid_until' => \Carbon\Carbon::parse($valid),
                'tax_rate' => $taxRate,
                'discount_amount' => $discount,
                'status' => $status,
            ]);
            $subtotal = 0;
            foreach ($all as [$desc, $qty, $price, $disc]) {
                $total = ($qty * $price) - $disc;
                $subtotal += $total;
                $q->items()->create([
                    'description' => $desc, 'quantity' => $qty,
                    'unit_price' => $price, 'discount' => $disc, 'total' => $total,
                ]);
            }
            // Mirror controller math: subtotal → tax → total.
            $q->subtotal = $subtotal;
            $q->tax_amount = $subtotal * ($taxRate / 100);
            $q->total = $subtotal + $q->tax_amount;
            $q->save();
            $out[] = $q;
        }
        return $out;
    }

    // ------------------------------------------------------------ proposals
    private function seedProposals(array $users, array $quotes): array
    {
        $out = [];
        $titles = ['Q3 Network Security Proposal', 'Cloud Migration Proposal', 'Monitoring Service Proposal', 'Website Rebuild Proposal', 'Branch Setup Proposal'];
        foreach (range(0, 4) as $i) {
            $subtotal = [800.00, 2500.00, 0.00, 15000.00, 99999.99][$i];
            $taxRate = [10.0, 20.0, 0.0, 7.5, 15.0][$i];
            $tax = round($subtotal * ($taxRate / 100), 2);
            $p = Proposal::create([
                'is_demo' => true,
                'customer_id' => $users['customers'][$i]->id,
                'lead_id' => null,
                'quotation_id' => $quotes[$i]->id,
                'created_by' => $users['staff']['sales_agent']->id,
                'title' => $titles[$i],
                'status' => ['draft', 'sent', 'viewed', 'accepted', 'rejected'][$i],
                'version' => 1,
                'scope_of_work' => "Scope of work as discussed.\nDeliverables and timeline per attached schedule.",
                'deliverables' => "- Deliverable A\n- Deliverable B",
                'timeline' => ($i + 1) . ' weeks from kickoff.',
                'terms' => 'Standard proposal terms.',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $tax,
                'total' => $subtotal + $tax,
                'currency' => ['USD', 'GBP', 'BDT', 'EUR', 'USD'][$i],
                'valid_until' => now()->addDays(30),
            ]);
            $p->sections()->create(['heading' => 'Scope Overview', 'body' => 'Scope details as discussed.', 'sort_order' => 0]);
            $out[] = $p;
        }
        return $out;
    }

    // ------------------------------------------------------------ contracts
    private function seedContracts(array $users, array $proposals): array
    {
        $out = [];
        $titles = ['Managed Support Agreement', 'Cloud Migration Agreement', 'Monitoring Service Agreement', 'Website Build Agreement', 'Branch Setup Agreement'];
        foreach (range(0, 4) as $i) {
            $out[] = Contract::create([
                'is_demo' => true,
                'customer_id' => $users['customers'][$i]->id,
                'proposal_id' => $proposals[$i]->id,
                'created_by' => $users['staff']['sales_agent']->id,
                'title' => $titles[$i],
                'status' => ['draft', 'sent', 'active', 'expired', 'terminated'][$i],
                'body' => "Agreement terms as discussed.\nSecond line.",
                'value' => [800.00, 2500.00, 0.00, 15000.00, 99999.99][$i],
                'currency' => ['USD', 'GBP', 'BDT', 'EUR', 'USD'][$i],
                'start_date' => now()->subDays([60, 10, 0, 400, 200][$i])->toDateString(),
                'end_date' => now()->addDays([300, 350, 30, -30, -10][$i])->toDateString(),
            ]);
        }
        return $out;
    }

    // --------------------------------------------------------------- orders
    private function seedOrders(array $users, array $services): array
    {
        $out = [];
        foreach (range(0, 4) as $i) {
            $out[] = ServiceOrder::create([
                'is_demo' => true,
                'customer_id' => $users['customers'][$i]->id,
                'service_id' => $services[$i]->id,
                'created_by' => $users['staff']['sales_agent']->id,
                'assigned_to' => $users['staff']['support_agent']->id,
                'requirements' => "Service requirements as discussed.\nScope confirmed with the client.",
                'priority' => ['low', 'medium', 'high', 'urgent', 'medium'][$i],
                'status' => ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'][$i],
                'currency' => ['USD', 'GBP', 'BDT', 'EUR', 'USD'][$i],
                'total' => [800.00, 2500.00, 0.00, 15000.00, 99999.99][$i],
                'amount_paid' => [0, 2500.00, 0, 5000.00, 0][$i],
                'amount_due' => [800.00, 0, 0, 10000.00, 99999.99][$i],
            ]);
        }
        return $out;
    }

    // ------------------------------------------------------------- projects
    private function seedProjects(array $users): array
    {
        $out = [];
        $statuses = ['planning', 'in_progress', 'review', 'completed', 'on_hold'];
        $projectNames = ['Head Office Network Refresh', 'E-commerce Security Review', 'Cloud Migration Phase 1', 'Company Website Rebuild', 'Branch Office Setup'];
        $projectSlugs = ['head-office-network-refresh', 'ecommerce-security-review', 'cloud-migration-phase-1', 'company-website-rebuild', 'branch-office-setup'];
        foreach (range(0, 4) as $i) {
            $project = Project::create([
                'is_demo' => true,
                'project_number' => 'PRJ-00' . ($i + 1) . '-' . date('Y'),
                'slug' => $projectSlugs[$i] . '-' . Str::random(4),
                'name' => $projectNames[$i],
                'description' => "Delivery project.\nMilestones tracked against agreed dates.",
                'customer_id' => $users['customers'][$i]->id,
                'project_manager_id' => $users['staff']['project_manager']->id,
                'budget' => [1000.00, 10000.00, 0.00, 250000.00, 750.50][$i],
                'estimated_cost' => [800.00, 8000.00, 0.00, 200000.00, 600.00][$i],
                'actual_cost' => [200.00, 9500.00, 0.00, 210000.00, 100.00][$i],
                'start_date' => now()->subDays(30)->toDateString(),
                'deadline' => now()->addDays([60, -5, 90, 10, 365][$i])->toDateString(),
                'progress' => [10, 55, 0, 100, 30][$i],
                'status' => $statuses[$i],
                'priority' => ['low', 'medium', 'high', 'urgent', 'medium'][$i],
            ]);
            $project->members()->attach($users['staff']['freelancer']->id, ['role' => 'contributor']);
            foreach ([1, 2] as $m) {
                ProjectMilestone::create([
                    'project_id' => $project->id,
                    'name' => $m === 1 ? 'Phase 1 — Discovery complete' : 'Phase 2 — Delivery complete',
                    'description' => "Milestone acceptance recorded on completion.",
                    'due_date' => now()->addDays($m * 15)->toDateString(),
                    'is_completed' => $m === 1 && $i === 3,
                    'completed_at' => $m === 1 && $i === 3 ? now() : null,
                    'sort_order' => $m,
                ]);
            }
            $out[] = $project;
        }
        return $out;
    }

    // ---------------------------------------------------------------- tasks
    private function seedTasks(array $users, array $projects, array $orders): void
    {
        // tasks.status enum: pending, in_progress, submitted, under_review,
        // approved, rejected, completed, cancelled.
        $statuses = ['pending', 'in_progress', 'under_review', 'completed', 'cancelled'];
        $types = ['open', 'assigned', 'application_required', 'first_come', 'team'];
        $taskTitles = ['Configure firewall rule set', 'Migrate shared mailboxes', 'Harden server baselines', 'Deploy monitoring agent', 'Document network topology', 'Review backup restores', 'Patch workstation fleet', 'Draft acceptable-use policy', 'Test failover procedure', 'Prepare client handover docs'];
        foreach (range(0, 9) as $i) {
            Task::create([
                'is_demo' => true,
                'project_id' => $projects[$i % 5]->id,
                'customer_id' => $users['customers'][$i % 5]->id,
                'created_by' => $users['staff']['project_manager']->id,
                'assigned_to' => $i % 2 ? $users['staff']['freelancer']->id : $users['staff']['support_agent']->id,
                'title' => $taskTitles[$i],
                'description' => "Delivery task with acceptance tracked on completion.",
                'priority' => ['low', 'medium', 'high', 'urgent', 'medium'][$i % 5],
                'type' => $types[$i % 5],
                'status' => $statuses[$i % 5],
                'budget' => [0, 100.00, 500.00, 2500.00, 10000.00][$i % 5],
                'reward_amount' => [0, 50.00, 200.00, 1000.00, 5000.00][$i % 5],
                'deadline' => now()->addDays([7, -2, 30, 0, 90][$i % 5])->toDateString(),
                'estimated_minutes' => [0, 60, 240, 960, 10000][$i % 5],
                'actual_minutes' => [0, 45, 300, 1200, 500][$i % 5],
                'progress' => [0, 25, 50, 100, 10][$i % 5],
                'technical_notes' => $i % 2 ? 'Notes recorded during delivery.' : null,
            ]);
        }
    }

    // -------------------------------------------------------------- tickets
    private function seedTickets(array $users): void
    {
        $categories = TicketCategory::take(3)->get();
        if ($categories->isEmpty()) {
            $categories = collect([TicketCategory::create(['name' => 'General Support', 'slug' => 'general-support'])]);
        }
        $statuses = ['new', 'open', 'in_progress', 'resolved', 'closed'];
        $ticketSubjects = ['VPN connectivity issue at head office', 'Email delivery delays for sales team', 'Workstation running slowly', 'New starter account setup', 'Office printer offline on floor 2'];
        foreach (range(0, 4) as $i) {
            $ticket = Ticket::create([
                'is_demo' => true,
                'customer_id' => $users['customers'][$i]->id,
                'assigned_to' => $i < 3 ? $users['staff']['support_agent']->id : null,
                'category_id' => $categories[$i % $categories->count()]->id,
                'subject' => $ticketSubjects[$i],
                'description' => "Reported issue:\nSteps to reproduce:\n1. Symptom observed by reporter\n2. Business impact noted for triage",
                'priority' => ['low', 'medium', 'high', 'urgent', 'critical'][$i],
                'status' => $statuses[$i],
            ]);
            app(\App\Services\SlaService::class)->applySla($ticket);
            TicketMessage::create([
                'ticket_id' => $ticket->id, 'user_id' => $users['customers'][$i]->id,
                'message' => 'Following up on this issue — please advise on next steps.',
            ]);
            TicketMessage::create([
                'ticket_id' => $ticket->id, 'user_id' => $users['staff']['support_agent']->id,
                'message' => 'Thanks — our team is investigating and will update you shortly.', 'is_internal_note' => $i === 4,
            ]);
            if ($i === 0) {
                TicketTimeEntry::create([
                    'ticket_id' => $ticket->id, 'user_id' => $users['staff']['support_agent']->id,
                    'minutes' => 45, 'description' => 'Investigation and diagnostics work.', 'date' => now()->toDateString(),
                ]);
            }
        }
    }

    // -------------------------------------------------------------------- kb
    private function seedKnowledgeBase(array $users): void
    {
        $category = KbCategory::first() ?? KbCategory::create(['name' => 'Getting Started', 'slug' => 'getting-started', 'is_active' => true]);
        $tag = KbTag::firstOrCreate(['slug' => 'how-to'], ['name' => 'How To']);
        $specs = [
            ['How to Reset Your Password', 'how-to-reset-your-password', 'public', true],
            ['How to Open a Support Ticket', 'how-to-open-a-support-ticket', 'customer', true],
            ['VPN Setup Guide for Staff', 'vpn-setup-guide-for-staff', 'employee', true],
            ['Understanding Your Invoice', 'understanding-your-invoice', 'admin', true],
            ['New Starter Checklist (Draft)', 'new-starter-checklist-draft', 'public', false],
        ];
        $bodies = [
            'Use the password reset link on the login page, then choose a strong unique password.',
            'Sign in, open Tickets, choose New Ticket, and describe the issue with steps to reproduce.',
            'Install the client, import the provided profile, and connect before accessing internal systems.',
            'Each invoice lists line items, tax, payments applied, and the remaining balance with the due date.',
            'Draft checklist covering accounts, devices, and access reviews for new starters.',
        ];
        foreach ($specs as $idx => [$title, $slug, $visibility, $published]) {
            $article = KbArticle::create([
                'is_demo' => true,
                'category_id' => $category->id,
                'author_id' => $users['staff']['support_agent']->id,
                'title' => $title,
                'slug' => $slug . '-' . Str::random(4),
                'content' => $bodies[$idx] . "\nContact support if anything is unclear.",
                'excerpt' => mb_substr($bodies[$idx], 0, 120),
                'visibility' => $visibility,
                'language' => 'en',
                'is_published' => $published,
                'is_featured' => $idx === 0,
            ]);
            $article->tags()->sync([$tag->id]);
        }
    }

    // -------------------------------------------------------------- invoices
    private function seedInvoices(array $users, array $orders): array
    {
        $out = [];
        $combos = [
            [[['Penetration testing — external', 2, 400.00, 0.00]], 'fixed', 0.00, 10.0, 'sent', '+14 days', 'USD'],
            [[['Consulting block (10h)', 5, 200.00, 50.00], ['Travel expenses', 1, 99.99, 0]], 'fixed', 100.00, 20.0, 'sent', '-10 days', 'GBP'],
            [[['Scoping workshop', 1, 0.00, 0.00]], 'fixed', 0.00, 0.0, 'draft', '+30 days', 'BDT'],
            [[['Annual monitoring', 1, 100000.00, 0.00]], 'percentage', 10.00, 8.5, 'sent', '+7 days', 'EUR'],
            [[['Support block (10h)', 4, 250.00, 0.00]], 'fixed', 0.00, 5.0, 'sent', '+21 days', 'USD'],
        ];
        foreach ($combos as $i => [$items, $discType, $discAmt, $taxRate, $status, $due, $currency]) {
            $invoice = Invoice::create([
                'is_demo' => true,
                'customer_id' => $users['customers'][$i]->id,
                'service_order_id' => $orders[$i]->id,
                'notes' => 'Thank you for your business.',
                'terms' => 'Standard terms: net 14.',
                'due_date' => \Carbon\Carbon::parse($due)->toDateString(),
                'tax_rate' => $taxRate,
                'discount_amount' => $discAmt,
                'discount_type' => $discType,
                'currency' => $currency,
                'status' => $status,
            ]);
            foreach ($items as [$desc, $qty, $price, $disc]) {
                $invoice->items()->create([
                    'description' => $desc, 'quantity' => $qty,
                    'unit_price' => $price, 'discount' => $disc, 'total' => ($qty * $price) - $disc,
                ]);
            }
            $invoice->recalculate();
            $out[] = $invoice->fresh();
        }
        return $out;
    }

    // -------------------------------------------------------------- payments
    private function seedPayments(array $invoices): void
    {
        // Invoice 1: paid in full. Invoice 2: partial. Invoice 4: partial.
        // (Invoices 3=draft and 5 are covered by test flows; #5 left unpaid.)
        $plan = [
            [0, 'full', 'bank_transfer', 'DEMO-TXN-0001'],
            [1, 'partial-half', 'card', 'DEMO-TXN-0002'],
            [3, 'partial-fixed', 'cash', 'DEMO-TXN-0003'],
        ];
        foreach ($plan as [$idx, $mode, $method, $txn]) {
            $invoice = $invoices[$idx]->fresh();
            if (!in_array($invoice->status, ['sent', 'viewed', 'overdue', 'partially_paid'], true)) {
                continue;
            }
            $amount = $mode === 'full' ? $invoice->amount_due
                : ($mode === 'partial-half' ? round($invoice->amount_due / 2, 2) : 1000.00);
            $amount = min($amount, $invoice->amount_due);
            $payment = Payment::create([
                'is_demo' => true,
                'payment_number' => $txn,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $amount,
                'currency' => $invoice->currency,
                'status' => 'completed',
                'payment_method' => $method,
                'transaction_id' => $txn,
                'notes' => 'Payment received — thank you.',
                'paid_at' => now(),
            ]);
            $invoice->amount_paid += $amount;
            $invoice->amount_due = max(0, $invoice->total - $invoice->amount_paid);
            $invoice->status = $invoice->amount_due <= 0 ? 'paid' : 'partially_paid';
            if ($invoice->status === 'paid') {
                $invoice->paid_at = now();
            }
            $invoice->save();
            app(\App\Services\FinancialService::class)->recordIncome(
                $amount, 'Customer Payment', "Customer payment {$txn}", ['payment_id' => $payment->id, 'is_demo' => true]
            );
        }
    }

    // -------------------------------------------------------------- expenses
    private function seedExpenses(array $users, array $projects): void
    {
        $category = ExpenseCategory::first() ?? ExpenseCategory::create(['name' => 'Operations', 'slug' => 'operations', 'is_active' => true]);
        $specs = [
            ['Cloud hosting renewal', 0.00, 'pending', 'CloudHost Ltd'],
            ['Software licence renewal', 199.99, 'approved', 'SoftLicence Co'],
            ['Client site travel', 1250.50, 'approved', 'City Travel'],
            ['Server hardware purchase', 87500.00, 'pending', 'Hardware Plus'],
            ['Contractor services', 500.00, 'rejected', 'Northbridge Contractors'],
        ];
        foreach ($specs as $i => [$desc, $amount, $status, $vendor]) {
            Expense::create([
                'is_demo' => true,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'description' => $desc,
                'amount' => $amount,
                'date' => now()->subDays($i * 6)->toDateString(),
                'vendor' => $vendor,
                'payment_method' => ['bank_transfer', 'card', 'cash', 'bank_transfer', 'card'][$i],
                'status' => $status,
                'project_id' => $i < 2 ? $projects[$i]->id : null,
                'created_by' => $users['staff']['finance_manager']->id,
                'approved_by' => in_array($status, ['approved', 'rejected'], true) ? $users['staff']['finance_manager']->id : null,
                'approved_at' => in_array($status, ['approved', 'rejected'], true) ? now() : null,
            ]);
        }
    }

    // ---------------------------------------------------------- notifications
    private function seedNotifications(array $users): void
    {
        $titles = ['Invoice due reminder', 'Payment received', 'Ticket update', 'Project milestone reached', 'Order confirmation'];
        foreach (range(0, 4) as $i) {
            Notification::create([
                'is_demo' => true,
                'id' => (string) Str::uuid(),
                'type' => 'demo',
                'notifiable_type' => User::class,
                'notifiable_id' => $users['customers'][$i]->id,
                'data' => ['title' => $titles[$i], 'message' => 'This is an update regarding your account.'],
                'read_at' => $i % 2 ? now() : null,
            ]);
        }
    }

    // ------------------------------------------------------------- documents
    private function seedDocuments(array $users): void
    {
        Storage::disk('private')->makeDirectory('documents');
        $categories = ['general', 'project', 'ticket', 'invoice', 'contract'];
        $docNames = [['Network diagram', 'network-diagram.txt'], ['Asset inventory', 'asset-inventory.txt'], ['Support log extract', 'support-log-extract.txt'], ['Service checklist', 'service-checklist.txt'], ['Meeting notes', 'meeting-notes.txt']];
        foreach (range(0, 4) as $i) {
            $filename = 'demo-document-00' . ($i + 1) . '.txt';
            $content = "Reference document for verification.\nLine 2.";
            Storage::disk('private')->put("documents/{$filename}", $content);
            CustomerDocument::create([
                'is_demo' => true,
                'user_id' => $users['customers'][$i]->id,
                'name' => $docNames[$i][0],
                'original_name' => $docNames[$i][1],
                'mime_type' => 'text/plain',
                'size' => strlen($content),
                'path' => "documents/{$filename}",
                'category' => $categories[$i],
                'description' => 'Reference document.',
            ]);
        }
    }
}
