<?php

namespace Database\Seeders;

use App\Models\TrainingCourse;
use App\Models\TrainingLesson;
use App\Models\TrainingModule;
use App\Models\TrainingPracticalAssessment;
use App\Models\TrainingQuiz;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * TrainingAcademySeeder — the complete PerfectITSecurity Academy curriculum.
 * All examples are synthetic and labelled [TRAINING]. Content mirrors the
 * real platform workflows (lead statuses, invoice statuses, RBAC methods).
 */
class TrainingAcademySeeder extends Seeder
{
    protected int $order = 0;

    public function run(): void
    {
        if (TrainingCourse::where('slug', 'platform-introduction')->exists()) {
            return;
        }
        $trainer = User::whereIn('role', ['super_admin', 'admin'])->first();

        $this->coursePlatformIntro($trainer);
        $this->courseRolesRbac($trainer);
        $this->courseCustomerJourney($trainer);
        $this->courseLeadCrm($trainer);
        $this->courseQuotations($trainer);
        $this->courseOrdersPayments($trainer);
        $this->courseProjectsTasks($trainer);
        $this->courseTicketsSla($trainer);
        $this->courseCommunication($trainer);
        $this->courseProgressNotes($trainer);
        $this->courseFinance($trainer);
        $this->courseInvoices($trainer);
        $this->courseClosure($trainer);
        $this->courseItDelivery($trainer);
        $this->courseSecurity($trainer);
        $this->courseEscalation($trainer);
        $this->courseContentKb($trainer);
        $this->courseDailySales($trainer);
        $this->courseDailySupport($trainer);
        $this->courseSimulation($trainer);
        $this->courseTrainerPlaybook($trainer);
    }

    // ── helpers ──────────────────────────────────────────────
    protected function course(array $a, ?User $trainer): TrainingCourse
    {
        return TrainingCourse::create($a + ['created_by' => $trainer?->id, 'is_published' => true, 'version' => '1.0']);
    }

    protected function module(TrainingCourse $c, string $title, string $desc = ''): TrainingModule
    {
        return $c->modules()->create(['title' => $title, 'description' => $desc, 'sort_order' => $this->order++]);
    }

    protected function lesson(TrainingModule $m, array $a): TrainingLesson
    {
        return $m->lessons()->create($a + ['is_published' => true, 'version' => '1.0', 'sort_order' => $this->order++]);
    }

    protected function quiz(TrainingCourse $c, string $title, array $questions, int $pass = 70): TrainingQuiz
    {
        $quiz = $c->quizzes()->create(['title' => $title, 'pass_score' => $pass, 'max_attempts' => 3, 'is_published' => true]);
        foreach ($questions as $i => $q) {
            $quiz->questions()->create($q + ['sort_order' => $i, 'points' => $q['points'] ?? 1]);
        }

        return $quiz;
    }

    protected function practical(TrainingCourse $c, string $title, string $instructions, array $checklist): TrainingPracticalAssessment
    {
        return $c->practicals()->create(['title' => $title, 'instructions' => $instructions, 'checklist' => $checklist, 'is_published' => true]);
    }

    // ── COURSE 1: platform introduction ──────────────────────
    protected function coursePlatformIntro(?User $t): void
    {
        $c = $this->course(['slug' => 'platform-introduction', 'title' => 'PerfectITSecurity Platform Introduction', 'description' => 'What the company does and how work flows end-to-end through the platform.', 'difficulty' => 'beginner', 'duration_minutes' => 45, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'The company and the lifecycle', 'Services, customers and the money-to-delivery chain.');
        $this->lesson($m, ['title' => 'What PerfectITSecurity does', 'lesson_type' => 'guide', 'duration_minutes' => 10,
            'body' => "PerfectITSecurity sells and delivers IT support, cybersecurity, web development, software services, digital marketing, server management and consulting. The website is the single system that connects visitors, customers, staff work and company money.\n\nEvery customer engagement follows one lifecycle: Visitor becomes a Lead or Customer, submits a Service Request, receives a Quotation or Proposal, places an Order, pays (partially or in full), work runs as a Project with Tasks, quality is reviewed, the final Invoice is settled, the account is closed, and the customer is followed up.",
            'objectives' => ['Name the service lines', 'Recite the end-to-end lifecycle in order'],
            'steps' => ['Visitor discovers the site (services page, contact form, referral, campaign)', 'Visitor becomes Lead or registered Customer', 'Service Request records the requirement', 'Quotation / Proposal prices the work', 'Order confirms the sale', 'Payment is recorded against invoice', 'Project + Tasks deliver the work', 'Quality review, final invoice, closure, follow-up'],
            'why_matters' => ['What: you place every action inside this chain', 'Why: a step done out of order (e.g. work before payment terms) breaks finance and delivery', 'Who: every role owns one segment', 'When: always check what came before and what must come next'],
            'common_mistakes' => ['Starting work before the order/payment position is clear', 'Treating an inquiry as if it were already an order'],
            'discussion_questions' => ['Where does your role sit in the lifecycle?']]);
        $this->lesson($m, ['title' => 'Portals and dashboards: where work lives', 'lesson_type' => 'procedure', 'duration_minutes' => 10,
            'body' => 'Customers live in /portal (dashboard, orders, tickets, services, invoices, projects, quotations, documents, notifications, profile). Staff live in /admin (dashboard, work orders, sales pipeline, leads, tickets, projects, tasks, finance, services, health, users, content, AI, audit). Your sidebar only shows what your role may access — if you cannot see a menu item, you are not responsible for that area.',
            'objectives' => ['Locate the customer portal and staff console areas'],
            'steps' => ['STEP 1: Log in and note your landing dashboard', 'STEP 2: Open the sidebar and list your menu items', 'STEP 3: Open Notifications and check for assigned work', 'STEP 4: Confirm theme, profile and logout location'],
            'why_matters' => ['What: orient yourself before touching records', 'Why: acting in the wrong area creates records you cannot see or fix', 'Who: every employee, on day one'],
            'common_mistakes' => ['Bookmarking deep URLs from another role and getting 403 errors', 'Ignoring notifications where assignments arrive'],
            'discussion_questions' => ['Which three menu items will you use daily?']]);
        $this->quiz($c, 'Platform introduction check', [
            ['type' => 'ordering', 'prompt' => 'Put the lifecycle in order.', 'options' => ['Order', 'Service Request', 'Payment', 'Quotation'], 'correct' => ['1', '0', '3', '2'], 'explanation' => 'Request → Quotation → Order → Payment.'],
            ['type' => 'single', 'prompt' => 'A visitor who only asked a question is a…', 'options' => ['Lead / inquiry', 'Order', 'Invoice', 'Project'], 'correct' => ['0'], 'explanation' => 'Until there is a priced, accepted commitment there is no order.'],
            ['type' => 'boolean', 'prompt' => 'Work should always start before payment terms are clear.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Payment position must be clear before delivery begins.'],
        ]);
    }

    // ── COURSE 2: roles & RBAC ───────────────────────────────
    protected function courseRolesRbac(?User $t): void
    {
        $c = $this->course(['slug' => 'user-roles-and-rbac', 'title' => 'User Roles & Access Control', 'description' => 'Permissions matrix: what each role can access, own and do.', 'difficulty' => 'beginner', 'duration_minutes' => 40, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Roles and the permissions matrix');
        $this->lesson($m, ['title' => 'Role permissions matrix', 'lesson_type' => 'reference', 'duration_minutes' => 15,
            'body' => 'Access is enforced server-side by the staff/customer boundary plus requires.role checks (isAdmin, isFinanceManager, isSupportAgent, isProjectManager). Key rules: customers see only /portal and their own records. Finance records (invoices, payments, expenses, commissions, quotations, proposals, contracts) require finance-manager tier. Tickets require the support hierarchy. Projects/tasks require the project hierarchy. Users, settings, audit logs, security findings and backups are admin-only. Trainers (training_manager) manage Academy content; admins inherit that right.',
            'objectives' => ['State which tier owns finance, tickets, projects and admin areas'],
            'steps' => ['STEP 1: Identify the record type (finance / ticket / project / content)', 'STEP 2: Match it to the owning tier', 'STEP 3: If you lack access, escalate instead of sharing accounts'],
            'why_matters' => ['What: you only act inside your tier', 'Why: RBAC protects customer data and money', 'Who: everyone', 'What happens next: unauthorized attempts return 403 and are logged'],
            'common_mistakes' => ['Sharing logins to bypass access', 'Asking customers for data your role should not hold'],
            'discussion_questions' => ['What do you do when you need data outside your tier?']]);
        $this->lesson($m, ['title' => 'Trainer, employee and admin in the Academy', 'lesson_type' => 'guide', 'duration_minutes' => 10,
            'body' => 'Trainers create courses, modules, lessons, quizzes, scenarios and practicals; assign training to roles or individuals; monitor progress; review assessments with feedback; add trainer notes; and reset training. Employees study assigned courses, complete lessons, attempt quizzes, submit practicals and earn completion records. Admins manage trainer permissions and view all reports.',
            'objectives' => ['Describe trainer vs employee vs admin capabilities'],
            'steps' => ['Trainer assigns → employee studies → quizzes/practicals evidence understanding → trainer reviews → completion record issues'],
            'why_matters' => ['Why: completion must be earned, never self-marked', 'Who: trainers verify, employees demonstrate'],
            'common_mistakes' => ['Marking lessons read without attempting the quiz', 'Trainers passing practicals without feedback'],
            'discussion_questions' => ['What evidence proves an employee understands a workflow?']]);
        $this->quiz($c, 'Roles & access check', [
            ['type' => 'single', 'prompt' => 'Who can open finance records (invoices, payments, expenses)?', 'options' => ['Any staff member', 'Finance-manager tier and admins', 'Customers', 'Support agents'], 'correct' => ['1'], 'explanation' => 'Finance routes require isFinanceManager.'],
            ['type' => 'multiple', 'prompt' => 'Which areas are admin-only?', 'options' => ['Users & settings', 'Audit logs', 'Security findings & backups', 'My own tickets as a customer'], 'correct' => ['0', '1', '2'], 'explanation' => 'Admin tier owns identity, audit and platform security.'],
            ['type' => 'boolean', 'prompt' => 'A trainer can mark their own required training complete without evidence.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Completion derives from lessons + passed quizzes + passed practicals.'],
        ]);
    }

    // ── COURSE 3: customer journey ───────────────────────────
    protected function courseCustomerJourney(?User $t): void
    {
        $c = $this->course(['slug' => 'customer-journey', 'title' => 'Customer Journey Start to Finish', 'description' => 'Follow [TRAINING] NorthBridge Retail Ltd from discovery to follow-up.', 'difficulty' => 'beginner', 'duration_minutes' => 60, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Discovery to account', 'The [TRAINING] NorthBridge Retail Ltd story.');
        $this->lesson($m, ['title' => '3.1 Discovery: where the interaction begins', 'lesson_type' => 'scenario', 'duration_minutes' => 10,
            'body' => '[TRAINING] Alex Morgan of NorthBridge Retail Ltd (United Kingdom) needs a Website Security Assessment (~£750). They may arrive via the services page, contact form, service inquiry, appointment request, support request, referral or campaign. Your job: capture the source (it drives lead source), the requirement in their words, and contact details — nothing more at this stage.',
            'objectives' => ['List arrival channels', 'Record source + requirement + contact correctly'],
            'steps' => ['STEP 1: Identify the channel (service page / contact / referral / campaign)', 'STEP 2: Record the requirement verbatim: “Please review our website for common security weaknesses and provide a professional report.”', 'STEP 3: Save source and contact; create the lead'],
            'why_matters' => ['Why: source tells marketing what works; verbatim requirements prevent scope errors', 'What happens next: lead enters CRM for sales'],
            'common_mistakes' => ['Paraphrasing away technical detail', 'Forgetting to record the source'],
            'discussion_questions' => ['Which channel needs the fastest response?']]);
        $this->lesson($m, ['title' => '3.2 Registration, verification and first login', 'lesson_type' => 'procedure', 'duration_minutes' => 10,
            'body' => 'Guide the customer: open the site, register with accurate business information, verify email and phone via OTP (10-minute lifetime, 60-second resend cooldown, five-attempt lockout), log in, open the customer dashboard. Collect only what delivery and billing need. Never collect passwords, card numbers or unrelated personal data over chat.',
            'objectives' => ['Walk a customer through registration to dashboard'],
            'steps' => ['STEP 1: Registration with business name, email, phone', 'STEP 2: Email OTP then phone OTP verification', 'STEP 3: Login → customer dashboard tour (orders, tickets, invoices, projects)'],
            'why_matters' => ['Why: unverified accounts cannot use the full portal', 'Who: customer acts, support guides', 'What happens next: they can request the service'],
            'common_mistakes' => ['Collecting sensitive data the platform never asks for', 'Skipping verification then wondering why features are locked'],
            'discussion_questions' => ['What do you do when an OTP expires?']]);
        $this->lesson($m, ['title' => '3.3–3.4 Service selection and the service request', 'lesson_type' => 'procedure', 'duration_minutes' => 15,
            'body' => 'Match the requirement to the Website Security Assessment service; confirm scope and missing info with the customer. Then the service request records: customer, service, requirement details, priority, attachments (permitted types only), notes, assignee and status. Every field has a purpose: priority drives SLA attention, assignee drives ownership, status drives what happens next.',
            'objectives' => ['Explain every service-request field: purpose, required?, example, owner, failure mode'],
            'steps' => ['STEP 1: Confirm service match and fill gaps with the customer', 'STEP 2: Create the request with priority + notes + attachments', 'STEP 3: Assign responsible staff and set status', 'STEP 4: Message the customer confirming receipt and next step'],
            'why_matters' => ['What happens next: request flows to quotation', 'What the customer sees: confirmation + timeline', 'Common failure: wrong priority → missed SLA'],
            'common_mistakes' => ['Vague requirements (“check our site”)', 'Unassigned requests nobody owns'],
            'discussion_questions' => ['What is missing from: “Please review our website”?']]);
        $this->quiz($c, 'Customer journey check', [
            ['type' => 'single', 'prompt' => 'First thing to record when [TRAINING] NorthBridge inquires?', 'options' => ['Card number', 'Channel/source + requirement + contact', 'Project milestones', 'Invoice total'], 'correct' => ['1'], 'explanation' => 'Source, requirement, contact — nothing sensitive.'],
            ['type' => 'multiple', 'prompt' => 'A good service request must include…', 'options' => ['Priority', 'Responsible assignee', 'Status', 'Employee salary'], 'correct' => ['0', '1', '2'], 'explanation' => 'Priority, owner and status drive the workflow.'],
            ['type' => 'boolean', 'prompt' => 'Phone/email OTP verification is required before full portal access.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'Both verifications gate full access.'],
        ]);
        $this->practical($c, 'Register and request: NorthBridge walkthrough', 'Using only synthetic [TRAINING] data, write the exact steps and messages you would send to take Alex Morgan from website discovery to a complete, assigned service request for the Website Security Assessment.', ['Discovery channel + recorded source', 'Registration and OTP guidance message', 'Requirement captured verbatim', 'Service-request fields completed (priority, assignee, status)', 'Customer confirmation message']);
    }

    // ── COURSE 4: leads & CRM ────────────────────────────────
    protected function courseLeadCrm(?User $t): void
    {
        $c = $this->course(['slug' => 'lead-and-crm', 'title' => 'Lead & CRM Management', 'description' => 'Lead lifecycle new → contacted → qualified → proposal → won/lost, assignment, conversion, dedupe.', 'role_target' => 'sales_agent', 'difficulty' => 'beginner', 'duration_minutes' => 45, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Working the pipeline');
        $this->lesson($m, ['title' => 'Lead lifecycle and CRM hygiene', 'lesson_type' => 'procedure', 'duration_minutes' => 15,
            'body' => '[TRAINING] NorthBridge Retail Ltd enters as a Website Inquiry lead (Website Security Assessment, assigned to Sales). Move it new → contacted → qualified → proposal → won (convert) or lost with a reason. Log every touch as an activity with next follow-up date. Search before creating: duplicates split history and double-contact customers. Conversion creates the customer record the rest of the lifecycle depends on.',
            'objectives' => ['Run the full lead lifecycle without duplicates'],
            'steps' => ['STEP 1: Search CRM for existing name/email/company before creating', 'STEP 2: Create lead with source, value (£750), priority, assignee', 'STEP 3: Log contact activity + set next follow-up', 'STEP 4: Qualify (need + budget + authority confirmed)', 'STEP 5: Convert on acceptance; close lost with reason'],
            'why_matters' => ['Why: the pipeline is the company forecast', 'What happens next: won leads become customers with quotations', 'What finance sees: estimated value only after qualification'],
            'common_mistakes' => ['Creating duplicate customers', 'Leads with no next action rotting in “new”', 'Converting before acceptance'],
            'discussion_questions' => ['When is a lead qualified, not just contacted?']]);
        $this->quiz($c, 'CRM check', [
            ['type' => 'ordering', 'prompt' => 'Order the lead statuses.', 'options' => ['Qualified', 'New', 'Proposal', 'Contacted'], 'correct' => ['1', '3', '0', '2'], 'explanation' => 'New → Contacted → Qualified → Proposal.'],
            ['type' => 'boolean', 'prompt' => 'Always search for duplicates before creating a lead.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'Dedupe protects history and customer trust.'],
            ['type' => 'single', 'prompt' => 'A lead becomes a customer when…', 'options' => ['It is created', 'It is contacted', 'The proposal is accepted and it is converted', 'It is assigned'], 'correct' => ['2'], 'explanation' => 'Conversion follows acceptance.'],
        ]);
        $this->practical($c, 'Qualify the NorthBridge lead', 'Record the CRM actions (search, create, activities, status moves, conversion decision) for [TRAINING] NorthBridge Retail Ltd with timestamps and next actions.', ['Dedupe search documented', 'Activities with next follow-up', 'Qualification rationale', 'Correct terminal status']);
    }

    // ── COURSE 5: quotations & proposals ─────────────────────
    protected function courseQuotations(?User $t): void
    {
        $c = $this->course(['slug' => 'quotations-and-proposals', 'title' => 'Quotations & Proposals', 'description' => 'Pricing, scope, validity, revisions and acceptance. Inquiry ≠ quotation ≠ proposal ≠ order ≠ invoice ≠ payment.', 'role_target' => 'sales_agent', 'difficulty' => 'intermediate', 'duration_minutes' => 50, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Pricing and acceptance');
        $this->lesson($m, ['title' => 'Inquiry → quotation → proposal → order → invoice → payment', 'lesson_type' => 'guide', 'duration_minutes' => 15,
            'body' => 'An inquiry is interest. A quotation prices a defined scope (line items × quantity, tax rate, total, validity period, terms). A proposal packages scope + approach + timeline for approval. An order is the accepted commitment. An invoice demands money. A payment settles it. Example: [TRAINING] NorthBridge Website Security Assessment £750 — one line item, 0% tax shown explicitly, 30-day validity, scope (authorized assessment + findings report) and exclusions (no remediation work, no out-of-scope systems). Revisions re-version; acceptance timestamps the deal.',
            'objectives' => ['Distinguish the six commercial documents', 'Build a correct quotation'],
            'steps' => ['STEP 1: Confirm scope + exclusions + timeline with the customer', 'STEP 2: Price line items, apply authorized discounts only, set tax explicitly', 'STEP 3: Set validity + terms, send, track sent status', 'STEP 4: Handle revisions as new versions', 'STEP 5: Record acceptance with timestamp before creating the order'],
            'why_matters' => ['Why: each document is a legal/financial commitment point', 'What finance sees: accepted totals become expected revenue', 'Common failure: work starting on a mere inquiry'],
            'common_mistakes' => ['Changing prices without authorization', 'Missing exclusions that later cause disputes', 'Orders created before acceptance'],
            'discussion_questions' => ['What belongs in exclusions for a security assessment?']]);
        $this->quiz($c, 'Commercial documents check', [
            ['type' => 'single', 'prompt' => 'Which document demands money?', 'options' => ['Quotation', 'Proposal', 'Invoice', 'Inquiry'], 'correct' => ['2'], 'explanation' => 'Only the invoice requests payment.'],
            ['type' => 'boolean', 'prompt' => 'An order may be created before the customer accepts.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Acceptance must be recorded first.'],
            ['type' => 'multiple', 'prompt' => 'A valid quotation states…', 'options' => ['Line items and total', 'Tax treatment', 'Validity period', 'Employee passwords'], 'correct' => ['0', '1', '2'], 'explanation' => 'Price, tax and validity are mandatory.'],
        ]);
    }

    // ── COURSE 6: orders & payments ──────────────────────────
    protected function courseOrdersPayments(?User $t): void
    {
        $c = $this->course(['slug' => 'orders-and-payments', 'title' => 'Orders & Customer Payments', 'description' => 'Order confirmation, payment verification (£400/£1,000 walkthrough), partial payments and receipts.', 'difficulty' => 'intermediate', 'duration_minutes' => 50, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'From acceptance to confirmed order');
        $this->lesson($m, ['title' => 'Order workflow and who owns each stage', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Customer accepts proposal → order created (sales) → order reviewed (sales lead) → payment requirement checked (finance) → payment received → order confirmed → project/task created (project tier) → employee assigned → work begins. Track order status, payment status, task and project status separately, and notify the customer at confirmation and at work start.',
            'objectives' => ['Name the owner of each order stage'],
            'steps' => ['STEP 1: Create the order from the accepted proposal only', 'STEP 2: Review pricing, scope, customer details', 'STEP 3: Confirm payment requirement with finance', 'STEP 4: On payment evidence, confirm the order', 'STEP 5: Trigger project creation and assignment', 'STEP 6: Notify the customer'],
            'why_matters' => ['Why: the order is the handoff from sales to delivery', 'What happens next: delivery plans against the confirmed order'],
            'common_mistakes' => ['Confirming orders with no payment position', 'Orders disconnected from their proposal'],
            'discussion_questions' => ['Who confirms the order in your team?']]);
        $this->lesson($m, ['title' => 'Payment truth: records, partials and receipts', 'lesson_type' => 'procedure', 'duration_minutes' => 15,
            'body' => 'Customer side: the customer opens the invoice/order in /portal, pays by an available method, and receives confirmation plus a receipt/invoice with the outstanding balance. Employee side: verify paid / unpaid / partial / due ONLY from platform records. Example: £1,000 service, £400 received → Paid £400, Due £600, status partially_paid. Final £600 → Paid £1,000, Due £0, PAID. Never claim payment without evidence; record permitted offline payments per policy with reference; partials keep the invoice open; always check payment history before telling a customer an invoice is paid.',
            'objectives' => ['Compute paid/due and status from records'],
            'steps' => ['STEP 1: Open the invoice/order and read amount_paid and amount_due', 'STEP 2: Match each payment record (method, reference, date)', 'STEP 3: State status: unpaid / partially_paid / paid / overdue', 'STEP 4: Issue or verify the receipt; communicate the remaining due'],
            'why_matters' => ['Why: wrong payment statements destroy trust and break accounts', 'What finance sees: every verified payment as income evidence'],
            'common_mistakes' => ['Marking unpaid invoices as paid', 'Ignoring partial balances', 'Recording duplicate income entries'],
            'discussion_questions' => ['£400 of £1,000 received — what exactly do you tell the customer?']]);
        $this->quiz($c, 'Orders & payments check', [
            ['type' => 'multiple', 'prompt' => 'Customer paid £400 of £1,000. Verify…', 'options' => ['Payment record', 'Paid amount', 'Remaining balance', 'Invoice/order status', 'Your lunch'], 'correct' => ['0', '1', '2', '3'], 'explanation' => 'Record, paid, due and status — all four.'],
            ['type' => 'single', 'prompt' => 'Status after the final £600 arrives?', 'options' => ['partially_paid', 'paid', 'draft', 'cancelled'], 'correct' => ['1'], 'explanation' => 'Due £0 → PAID.'],
            ['type' => 'boolean', 'prompt' => 'You may confirm payment based on the customer saying so by phone.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Only platform payment records count.'],
        ]);
        $this->practical($c, 'Reconcile a partial payment', 'Given [TRAINING] invoice £1,000 with payments £400 + £600, show the ledger state after each payment, the status transitions, and the exact customer message at each step.', ['Paid/due math after each payment', 'Status transitions correct', 'Evidence references cited', 'Customer messages accurate']);
    }

    // ── COURSE 7: projects & tasks ───────────────────────────
    protected function courseProjectsTasks(?User $t): void
    {
        $c = $this->course(['slug' => 'projects-and-tasks', 'title' => 'Projects & Task Management', 'description' => 'Order → project → milestones → tasks → review → completion. Six-task security assessment plan.', 'difficulty' => 'intermediate', 'duration_minutes' => 55, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Delivery structure');
        $this->lesson($m, ['title' => 'Project creation done right', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Confirmed orders become projects: name it after customer + service ([TRAINING] NorthBridge — Website Security Assessment), link customer and service, set start and expected completion, assign the project manager and team, define milestones, set status. The project is the customer-visible container; tasks are the internal work units.',
            'objectives' => ['Create a complete, linked project'],
            'steps' => ['STEP 1: Create from the confirmed order (never free-floating)', 'STEP 2: Set dates, manager, team', 'STEP 3: Define milestones (e.g. Assessment → Analysis → Report → Review → Delivery)', 'STEP 4: Set status and notify the customer of the plan'],
            'why_matters' => ['Why: unlinked projects break finance traceability', 'What happens next: tasks distribute the work'],
            'common_mistakes' => ['Projects with no milestones or dates', 'Wrong customer linked'],
            'discussion_questions' => ['What makes a good milestone?']]);
        $this->lesson($m, ['title' => 'Six tasks for the security assessment', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Task 1 Requirement Review, Task 2 Authorized Security Testing, Task 3 Findings Analysis, Task 4 Report Preparation, Task 5 Quality Review, Task 6 Customer Delivery. Each task: clear title, assignee, priority, deadline, status flow, notes, attachments, dependencies. Testing tasks stay strictly inside authorized scope — non-destructive, agreed systems only. Completion requires verification, not just effort.',
            'objectives' => ['Run task lifecycle: assign → work → verify → complete'],
            'steps' => ['STEP 1: Create with assignee + priority + deadline', 'STEP 2: Record dependencies (analysis waits for testing)', 'STEP 3: Work, attach evidence, update status', 'STEP 4: Submit for verification; reviewer approves or rejects'],
            'why_matters' => ['Why: tasks are where SLAs and quality live or die', 'Who: assignee works, reviewer verifies'],
            'common_mistakes' => ['Closing tasks before verification', 'Testing outside authorized scope'],
            'discussion_questions' => ['What evidence closes Task 2?']]);
        $this->quiz($c, 'Projects & tasks check', [
            ['type' => 'boolean', 'prompt' => 'A project may be created without a confirmed order.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Projects trace to confirmed orders.'],
            ['type' => 'single', 'prompt' => 'Who verifies a completed task?', 'options' => ['The assignee alone', 'An authorized reviewer', 'The customer', 'Nobody'], 'correct' => ['1'], 'explanation' => 'Verification is independent of execution.'],
            ['type' => 'multiple', 'prompt' => 'Every task needs…', 'options' => ['Assignee', 'Deadline', 'Status', 'A horoscope'], 'correct' => ['0', '1', '2'], 'explanation' => 'Owner, due date and state are mandatory.'],
        ]);
    }

    // ── COURSE 8: tickets & SLA ──────────────────────────────
    protected function courseTicketsSla(?User $t): void
    {
        $c = $this->course(['slug' => 'tickets-and-sla', 'title' => 'Tickets, Support & SLA', 'description' => 'Ticket lifecycle, categorization, priority, SLA clocks, escalation, resolution and closure.', 'role_target' => 'support_agent', 'difficulty' => 'intermediate', 'duration_minutes' => 50, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Support operations');
        $this->lesson($m, ['title' => 'Ticket lifecycle and SLA clocks', 'lesson_type' => 'procedure', 'duration_minutes' => 15,
            'body' => 'Customer creates ticket → received → categorized → prioritized → assigned → investigated → responded → resolved → customer confirms → closed (reopenable). Priority starts two clocks: response time (first human reply) and resolution time (fix delivered). Breaches escalate automatically in attention: warn the customer before the deadline, never after. Overdue tickets need a plan and an owner, not silence.',
            'objectives' => ['Operate the full ticket lifecycle inside SLA'],
            'steps' => ['STEP 1: Categorize (area) + prioritize (impact × urgency)', 'STEP 2: Assign an owner immediately', 'STEP 3: Respond inside response SLA with a plan', 'STEP 4: Investigate, resolve, request customer confirmation', 'STEP 5: Close; reopen if the customer reports recurrence'],
            'why_matters' => ['Why: SLA is a contractual promise', 'What happens next: breach → escalation → management attention', 'What the customer sees: every response timestamped'],
            'common_mistakes' => ['Wrong category hiding tickets from specialists', 'Closing without customer confirmation', 'Silent breaches'],
            'discussion_questions' => ['Response vs resolution — which clock are you on right now?']]);
        $this->quiz($c, 'Tickets & SLA check', [
            ['type' => 'single', 'prompt' => 'Response-time SLA measures…', 'options' => ['First human reply', 'Final fix', 'Invoice payment', 'Project closure'], 'correct' => ['0'], 'explanation' => 'Response is the first substantive reply.'],
            ['type' => 'boolean', 'prompt' => 'Close the ticket as soon as you believe it is fixed.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Customer confirmation comes first.'],
            ['type' => 'multiple', 'prompt' => 'Correct triage sets…', 'options' => ['Category', 'Priority', 'Owner', 'Company profit'], 'correct' => ['0', '1', '2'], 'explanation' => 'What it is, how urgent, who owns it.'],
        ]);
        $this->practical($c, 'Work a [TRAINING] SLA ticket', 'Handle a synthetic urgent ticket (customer reports email outage): triage, respond inside SLA, escalate once with rationale, resolve and close with confirmation.', ['Triage correct', 'SLA-aware response', 'Escalation justified', 'Confirmed closure']);
    }

    // ── COURSE 9: communication ──────────────────────────────
    protected function courseCommunication(?User $t): void
    {
        $c = $this->course(['slug' => 'customer-communication', 'title' => 'Customer Communication', 'description' => 'Professional templates for every customer moment; confidentiality by default.', 'difficulty' => 'beginner', 'duration_minutes' => 35, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Professional messaging');
        $this->lesson($m, ['title' => 'Templates: started, progress, delay, completion, payment, follow-up', 'lesson_type' => 'reference', 'duration_minutes' => 12,
            'body' => 'Use these shapes and adapt facts only. Work Started: “Your service request has been reviewed and the assigned team has started the scheduled work.” Progress: “Your project has progressed to the analysis stage. The assigned team is currently reviewing the collected information.” Completion: “The requested service has been completed and the final deliverables are now available through your customer portal.” Delay: state cause, impact, new date, owner — early. Payment reminder: amount, due date, where to pay in /portal. Follow-up: value first, then the ask. Never expose internal names, costs, or security details.',
            'objectives' => ['Send correct, confidential customer messages'],
            'steps' => ['STEP 1: Pick the template for the moment', 'STEP 2: Fill facts (stage, date, amount, link)', 'STEP 3: Strip internal/confidential content', 'STEP 4: Send via the ticket/project channel so history is kept'],
            'why_matters' => ['Why: written words are the service record', 'What the customer sees: clarity and dates', 'What stays hidden: internals'],
            'common_mistakes' => ['Promising dates you do not own', 'Pasting internal notes to customers'],
            'discussion_questions' => ['Rewrite a blunt delay notice professionally.']]);
        $this->quiz($c, 'Communication check', [
            ['type' => 'boolean', 'prompt' => 'Internal staff names and costs may appear in customer updates.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Customer updates carry facts and dates, not internals.'],
            ['type' => 'single', 'prompt' => 'A delay notice must include…', 'options' => ['Blame', 'Cause, impact, new date, owner', 'Silence', 'A discount promise'], 'correct' => ['1'], 'explanation' => 'Own the delay with facts.'],
        ]);
    }

    // ── COURSE 10: progress updates & notes ──────────────────
    protected function courseProgressNotes(?User $t): void
    {
        $c = $this->course(['slug' => 'progress-updates-and-notes', 'title' => 'Progress Updates & Internal Notes', 'description' => 'Customer-visible updates vs internal notes; percentages, milestones and notification discipline.', 'difficulty' => 'intermediate', 'duration_minutes' => 40, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Two channels, zero leaks');
        $this->lesson($m, ['title' => 'Work progress updates that customers trust', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Example: [TRAINING] Website Security Assessment, stage Analysis, 60%: “Initial assessment completed. The team is now reviewing findings and preparing the technical report.” Next step: technical review. Next update: tomorrow. Post: completed work, current stage, honest percentage, next step, expected date. Never post: guesses, blame, security weaknesses in plain text, internal costs, unreviewed findings.',
            'objectives' => ['Publish complete, honest progress updates'],
            'steps' => ['STEP 1: Confirm actual stage and evidence', 'STEP 2: Set percentage against milestones, not feelings', 'STEP 3: State next step + next update date', 'STEP 4: Attach appropriate files; notify the customer'],
            'why_matters' => ['Why: updates are the product until delivery', 'What finance sees: progress justifies milestone billing'],
            'common_mistakes' => ['90% for weeks', 'Updates with no next date'],
            'discussion_questions' => ['What proves “60%” is honest?']]);
        $this->lesson($m, ['title' => 'Internal notes vs customer notes', 'lesson_type' => 'guide', 'duration_minutes' => 10,
            'body' => 'Internal notes hold planning, assignments, vendor detail and debate — visible to staff only. Customer updates hold completed work, next steps and timelines. The training rule: if a sentence would embarrass you on the customer portal, it belongs in an internal note. Double-check the channel selector before every post.',
            'objectives' => ['Never leak internal content to customers'],
            'steps' => ['STEP 1: Draft the fact', 'STEP 2: Choose channel deliberately', 'STEP 3: Re-read as the customer before sending'],
            'why_matters' => ['Why: one leak can breach confidentiality or panic a customer'],
            'common_mistakes' => ['Posting internal notes publicly', 'Security findings pasted to the portal unreviewed'],
            'discussion_questions' => ['Internal or customer: “Awaiting freelancer rate confirmation”?']]);
        $this->quiz($c, 'Updates & notes check', [
            ['type' => 'multiple', 'prompt' => 'A customer update contains…', 'options' => ['Completed work', 'Next step + date', 'Internal cost debate', 'Honest percentage'], 'correct' => ['0', '1', '3'], 'explanation' => 'Facts and dates only.'],
            ['type' => 'boolean', 'prompt' => 'Percentages should reflect milestone evidence.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'Evidence, not optimism.'],
        ]);
    }

    // ── COURSE 11: finance academy ───────────────────────────
    protected function courseFinance(?User $t): void
    {
        $c = $this->course(['slug' => 'finance-academy', 'title' => 'Finance Management Academy', 'description' => 'Income, expenses, commissions and profit/loss the way the platform actually computes them.', 'role_target' => 'finance_manager', 'difficulty' => 'advanced', 'duration_minutes' => 70, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Money in, money out, truth kept');
        $this->lesson($m, ['title' => 'Income: when, how, and without duplicates', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Income is recorded when payment evidence exists — completed payment records and completed financial transactions, e.g. [TRAINING] NorthBridge £400 initial receipt. Reconcile every entry to a payment reference; the same receipt must never create two income lines. Pending amounts are expectations, not income.',
            'objectives' => ['Record income once, against evidence'],
            'steps' => ['STEP 1: Match receipt to invoice + payment reference', 'STEP 2: Record income once with the reference', 'STEP 3: Reconcile totals: payments == income for the invoice'],
            'why_matters' => ['Why: duplicates inflate revenue and tax exposure', 'What happens next: income feeds profit/loss'],
            'common_mistakes' => ['Recording duplicate income', 'Counting pending as received'],
            'discussion_questions' => ['How do you prove £400 is recorded exactly once?']]);
        $this->lesson($m, ['title' => 'Expenses and profit/loss', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Expenses need category (e.g. Infrastructure), date, amount (£120 cloud hosting), description and supporting documentation, then approval where required. Profit = Revenue − Expenses. Training example: Revenue £5,000 − Expenses £1,500 = Profit £3,500. The platform sums completed income and approved/completed expenses — your job is correct categorization and documentation, not inventing formulas.',
            'objectives' => ['Create documented expenses; explain P&L'],
            'steps' => ['STEP 1: Create with category + date + amount + vendor + receipt', 'STEP 2: Submit for approval; correct rejections fast', 'STEP 3: Read P&L as completed income minus completed expenses'],
            'why_matters' => ['Why: miscategorized expenses corrupt every report'],
            'common_mistakes' => ['Missing receipts', 'Personal judgment replacing platform math'],
            'discussion_questions' => ['Which category for an SSL renewal?']]);
        $this->lesson($m, ['title' => 'Commissions: eligibility and the five-way split', 'lesson_type' => 'guide', 'duration_minutes' => 12,
            'body' => 'Commission agents earn on qualifying, PAID sales per commission rules — unpaid or cancelled sales never qualify. Keep five concepts apart: Customer Payment (cash in) → Company Revenue (recognized income) → Employee Commission (a company expense) → Company Expenses → Company Profit (what remains). Approve/reject commissions with evidence; payouts batch approved items.',
            'objectives' => ['Explain the five-way money split'],
            'steps' => ['STEP 1: Confirm sale is paid and qualifying', 'STEP 2: Apply the rule rate; review; approve or reject with reason', 'STEP 3: Include approved items in payout batches'],
            'why_matters' => ['Why: commission is both payroll fairness and a cost line'],
            'common_mistakes' => ['Paying commission on unpaid sales', 'Confusing revenue with profit'],
            'discussion_questions' => ['Is a £1,000 unpaid order commissionable?']]);
        $this->quiz($c, 'Finance check', [
            ['type' => 'single', 'prompt' => 'Revenue £5,000, expenses £1,500. Profit?', 'options' => ['£6,500', '£3,500', '£5,000', '£1,500'], 'correct' => ['1'], 'explanation' => 'Profit = Revenue − Expenses.'],
            ['type' => 'boolean', 'prompt' => 'Pending expected payments count as income.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Only evidenced, completed receipts.'],
            ['type' => 'multiple', 'prompt' => 'An expense record needs…', 'options' => ['Category', 'Date and amount', 'Supporting document', 'A poem'], 'correct' => ['0', '1', '2'], 'explanation' => 'Category, date, amount, proof.'],
            ['type' => 'boolean', 'prompt' => 'Unpaid sales can earn commission.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Only qualifying paid sales.'],
        ]);
        $this->practical($c, 'Close the books on NorthBridge', 'Given [TRAINING] NorthBridge revenue £1,000 (paid £400 + £600) and expenses £120 hosting + £80 tools: reconcile income to payment references, categorize expenses, compute profit, and state what remains open.', ['Income tied to references', 'Expenses documented', 'Profit math correct', 'Open items listed']);
    }

    // ── COURSE 12: invoices ──────────────────────────────────
    protected function courseInvoices(?User $t): void
    {
        $c = $this->course(['slug' => 'invoices-and-receipts', 'title' => 'Invoices & Receipts', 'description' => 'Invoice lifecycle draft → sent → paid/partial/overdue; receipts and balance verification.', 'role_target' => 'finance_manager', 'difficulty' => 'intermediate', 'duration_minutes' => 40, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Billing truth');
        $this->lesson($m, ['title' => 'Invoice states and the paid checklist', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Invoices move draft → sent → (paid | partially_paid | overdue) or cancelled. Subtotal + tax rate + tax amount = total; amount_paid vs amount_due derives status. Before telling any customer an invoice is paid, verify: invoice totals, every payment record, amount_due == 0, and transaction history. Receipts prove each payment; the invoice PDF is the formal demand.',
            'objectives' => ['Verify paid status from records, never memory'],
            'steps' => ['STEP 1: Read subtotal, tax, total, paid, due', 'STEP 2: List all payment records with references', 'STEP 3: Confirm due is £0 and history is clean', 'STEP 4: Send receipt/invoice and state any balance'],
            'why_matters' => ['Why: false “paid” statements create legal and trust exposure'],
            'common_mistakes' => ['Forgetting overdue vs unpaid', 'Skipping transaction history'],
            'discussion_questions' => ['Invoice shows paid but history has a failed payment — now what?']]);
        $this->quiz($c, 'Invoices check', [
            ['type' => 'single', 'prompt' => 'Total £550, paid £0, past due date. Status?', 'options' => ['paid', 'draft', 'overdue', 'cancelled'], 'correct' => ['2'], 'explanation' => 'Unpaid past due = overdue.'],
            ['type' => 'boolean', 'prompt' => 'Verify records before confirming paid to a customer.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'Always verify.'],
        ]);
    }

    // ── COURSE 13: closure ───────────────────────────────────
    protected function courseClosure(?User $t): void
    {
        $c = $this->course(['slug' => 'account-closure', 'title' => 'Account Closure After Completion', 'description' => 'Task done ≠ account closed. Verify delivery, money, documents and notifications.', 'difficulty' => 'intermediate', 'duration_minutes' => 35, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'The closure gate');
        $this->lesson($m, ['title' => 'Closure checklist', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Close in order: tasks completed and verified → quality checked → deliverables provided → customer notified → final invoice checked → final payment confirmed → due £0 → refunds/adjustments cleared → project completed → tasks closed → financial status closed → follow-up scheduled. “Task completed” never implies “financially closed” — money verification is a separate gate owned with finance.',
            'objectives' => ['Run the full closure gate without skipping money checks'],
            'steps' => ['STEP 1: Verify all tasks + quality + deliverables', 'STEP 2: Confirm final invoice and £0 due with finance', 'STEP 3: Close project and financial status', 'STEP 4: Notify customer; schedule follow-up'],
            'why_matters' => ['Why: premature closure hides debt and breaks reports', 'Who: delivery verifies work, finance verifies money'],
            'common_mistakes' => ['Closing projects with open tasks', 'Closing accounts with outstanding balances'],
            'discussion_questions' => ['Who signs the money gate on your team?']]);
        $this->quiz($c, 'Closure check', [
            ['type' => 'boolean', 'prompt' => 'Completed tasks mean the account is financially closed.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Money verification is a separate gate.'],
            ['type' => 'multiple', 'prompt' => 'Closure verifies…', 'options' => ['Deliverables sent', '£0 due confirmed', 'Customer notified', 'Horoscope'], 'correct' => ['0', '1', '2'], 'explanation' => 'Work, money, communication.'],
        ]);
        $this->practical($c, 'Close NorthBridge correctly', 'Produce the signed closure pack for [TRAINING] NorthBridge (£750 paid in full): task evidence, quality note, invoice state, £0-due proof, notifications sent, follow-up date.', ['Work evidence', '£0-due proof', 'Notifications logged', 'Follow-up set']);
    }

    // ── COURSE 14: IT service delivery ───────────────────────
    protected function courseItDelivery(?User $t): void
    {
        $c = $this->course(['slug' => 'it-service-delivery', 'title' => 'IT Service Delivery', 'description' => 'Support, cybersecurity (authorized only), web development and marketing delivery patterns.', 'role_target' => 'support_agent', 'difficulty' => 'intermediate', 'duration_minutes' => 55, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Delivering each service line');
        $this->lesson($m, ['title' => 'IT support delivery pattern', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Windows, Microsoft 365, email, network, server, backup, remote, installation and hardware requests all follow: requirement → scope confirmation → quotation → approval → order → payment → project → tasks → work → quality control → customer update → completion. Confirm scope in writing before touching systems; record every change; verify with the customer, not just the console.',
            'objectives' => ['Deliver support work with written scope and evidence'],
            'steps' => ['STEP 1: Confirm scope + access + backup state in writing', 'STEP 2: Execute per task with notes and attachments', 'STEP 3: Quality-check against the requirement', 'STEP 4: Customer update + confirmation'],
            'why_matters' => ['Why: undocumented changes are indistinguishable from incidents'],
            'common_mistakes' => ['Scope creep without quotation', 'No rollback plan'],
            'discussion_questions' => ['What do you confirm before touching a server?']]);
        $this->lesson($m, ['title' => 'Authorized cybersecurity work only', 'lesson_type' => 'guide', 'duration_minutes' => 12,
            'body' => 'Security assessments, authorized web-application testing, configuration reviews, reporting and remediation guidance happen ONLY inside written, agreed scope. Non-destructive methods, agreed systems and time windows, findings documented with severity and evidence, remediation guidance — never exploitation beyond proof, never out-of-scope systems, never public disclosure of customer weaknesses.',
            'objectives' => ['State the authorization boundaries'],
            'steps' => ['STEP 1: Confirm written authorization + scope + window', 'STEP 2: Test non-destructively inside scope', 'STEP 3: Document findings with evidence', 'STEP 4: Report + remediation guidance through approved channels'],
            'why_matters' => ['Why: out-of-scope testing is a security incident, not a service'],
            'common_mistakes' => ['“Helpful” extra testing outside scope', 'Raw findings sent to customers unreviewed'],
            'discussion_questions' => ['Testing finds a critical on a neighboring system — what now?']]);
        $this->quiz($c, 'Delivery check', [
            ['type' => 'boolean', 'prompt' => 'Security testing may extend to systems that look related but are out of scope.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Only agreed scope, always.'],
            ['type' => 'multiple', 'prompt' => 'Support work requires…', 'options' => ['Written scope', 'Work notes + evidence', 'Customer confirmation', 'Guessing'], 'correct' => ['0', '1', '2'], 'explanation' => 'Scope, evidence, confirmation.'],
        ]);
    }

    // ── COURSE 15: security & data protection ────────────────
    protected function courseSecurity(?User $t): void
    {
        $c = $this->course(['slug' => 'security-and-data-protection', 'title' => 'Security & Data Protection', 'description' => 'Least privilege, MFA, confidentiality, phishing, safe uploads, audit trails.', 'difficulty' => 'beginner', 'duration_minutes' => 35, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'Protecting customers and the company');
        $this->lesson($m, ['title' => 'Everyday operational security', 'lesson_type' => 'guide', 'duration_minutes' => 12,
            'body' => 'Least privilege: access only what your tasks need. Password safety plus MFA where available (authenticator app preferred). Customer confidentiality: minimum necessary data, secure document handling, no customer data in chat/CC, no training examples with real data. Phishing awareness: verify senders, hover links, report suspicious mail. Report anomalies fast. Safe uploads: permitted types, scan mentally for secrets. Everything access-related lands in audit logs — act accordingly.',
            'objectives' => ['Apply least privilege, MFA, confidentiality and reporting'],
            'steps' => ['STEP 1: Enable MFA and use unique passwords', 'STEP 2: Handle documents by classification (internal vs customer-visible)', 'STEP 3: Verify before clicking; report phishing', 'STEP 4: Report suspicious activity immediately with facts'],
            'why_matters' => ['Why: most breaches start with routine mistakes', 'What happens next: reports trigger security review'],
            'common_mistakes' => ['Real customer data in training', 'Password reuse', 'Ignoring audit-visible actions'],
            'discussion_questions' => ['What is “minimum necessary” for your current task?']]);
        $this->quiz($c, 'Security check', [
            ['type' => 'boolean', 'prompt' => 'Real customer emails may be used in training examples.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Synthetic [TRAINING] data only.'],
            ['type' => 'multiple', 'prompt' => 'Good practice includes…', 'options' => ['MFA enabled', 'Least-privilege access', 'Reporting phishing', 'Sharing accounts for speed'], 'correct' => ['0', '1', '2'], 'explanation' => 'Never share accounts.'],
        ]);
    }

    // ── COURSE 16: escalation ────────────────────────────────
    protected function courseEscalation(?User $t): void
    {
        $c = $this->course(['slug' => 'escalation-playbook', 'title' => 'Escalation Playbook', 'description' => 'When to escalate: employee → team lead → specialist → manager/admin.', 'difficulty' => 'beginner', 'duration_minutes' => 25], $t);
        $m = $this->module($c, 'Escalating well');
        $this->lesson($m, ['title' => 'Can-resolve test and handoff pack', 'lesson_type' => 'procedure', 'duration_minutes' => 10,
            'body' => 'Escalate when: outside your tier, SLA at risk, security/compliance involved, customer asks for management, or two attempts failed. The handoff pack: what was tried, evidence, impact, SLA clock state, what you need. Employee → team lead → specialist → manager/admin. Escalation is ownership transfer, not blame transfer — stay attached until accepted.',
            'objectives' => ['Decide and execute escalations with a handoff pack'],
            'steps' => ['STEP 1: Apply the can-resolve test', 'STEP 2: Build the handoff pack', 'STEP 3: Transfer to the next tier; confirm acceptance', 'STEP 4: Tell the customer who owns it now'],
            'why_matters' => ['Why: bad handoffs restart the clock and the customer story'],
            'common_mistakes' => ['Silent escalations', 'Escalating without evidence'],
            'discussion_questions' => ['What goes in your handoff pack today?']]);
        $this->quiz($c, 'Escalation check', [
            ['type' => 'single', 'prompt' => 'Escalate when…', 'options' => ['The task is boring', 'Outside tier, SLA at risk, or two attempts failed', 'Always, immediately', 'Never'], 'correct' => ['1'], 'explanation' => 'Principled escalation, not avoidance.'],
            ['type' => 'boolean', 'prompt' => 'You may drop a ticket once escalated, no confirmation needed.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Stay attached until acceptance.'],
        ]);
    }

    // ── COURSE 17: content & KB ──────────────────────────────
    protected function courseContentKb(?User $t): void
    {
        $c = $this->course(['slug' => 'website-content-and-kb', 'title' => 'Website Content, Knowledge Base & AI Assistant', 'description' => 'Draft → review → approve → publish → verify; KB discipline; AI boundaries.', 'role_target' => 'admin', 'difficulty' => 'intermediate', 'duration_minutes' => 45], $t);
        $m = $this->module($c, 'Publishing and knowledge');
        $this->lesson($m, ['title' => 'Publish without regret', 'lesson_type' => 'procedure', 'duration_minutes' => 12,
            'body' => 'Service updates, news, portfolio, case studies, knowledge articles: draft → review → approval → publish → verify the public page. Verify spelling, pricing, images, links, dates, customer confidentiality (synthetic only), SEO fields, visibility and mobile appearance. Portfolio/case-study example: [TRAINING] Secure Website Assessment — challenge, authorized work, report result, completed status. Unapproved content never goes public.',
            'objectives' => ['Ship publishable content first time'],
            'steps' => ['STEP 1: Draft with sources', 'STEP 2: Peer review + approval', 'STEP 3: Publish; verify public page desktop + mobile'],
            'why_matters' => ['Why: public errors price directly into trust'],
            'common_mistakes' => ['Publishing unapproved content', 'Real customer names in case studies', 'Wrong prices or broken links'],
            'discussion_questions' => ['What is your pre-publish checklist?']]);
        $this->lesson($m, ['title' => 'Knowledge Base and the AI assistant', 'lesson_type' => 'guide', 'duration_minutes' => 10,
            'body' => 'Search KB first; read, then improve outdated articles in your area with categorization and internal-vs-public visibility set correctly. The AI assistant answers from approved KB sources and escalates to humans on uncertainty; it must never invent policies, prices, payment states or customer facts. Employees review AI-drafted content before it reaches customers; customer identity is verified before account-specific answers.',
            'objectives' => ['Use KB + AI without hallucinations reaching customers'],
            'steps' => ['STEP 1: Search KB; use approved articles', 'STEP 2: Update what you own; flag the rest', 'STEP 3: Treat AI output as draft; verify; escalate identity/payment questions to humans'],
            'why_matters' => ['Why: AI confidence is not evidence'],
            'common_mistakes' => ['Pasting AI answers to customers unverified', 'Internal KB marked public'],
            'discussion_questions' => ['What must AI never answer?']]);
        $this->quiz($c, 'Content & knowledge check', [
            ['type' => 'ordering', 'prompt' => 'Order the publishing flow.', 'options' => ['Publish', 'Draft', 'Verify', 'Review'], 'correct' => ['1', '3', '0', '2'], 'explanation' => 'Draft → Review → Publish → Verify (approval before publish).'],
            ['type' => 'boolean', 'prompt' => 'AI may state a customer invoice balance from its own knowledge.', 'options' => ['True', 'False'], 'correct' => ['1'], 'explanation' => 'Payment states come from records via humans.'],
        ]);
    }

    // ── COURSE 18: daily sales ───────────────────────────────
    protected function courseDailySales(?User $t): void
    {
        $c = $this->course(['slug' => 'daily-workflow-sales', 'title' => 'My Working Day: Sales', 'description' => 'Role-specific daily workflow for sales staff.', 'role_target' => 'sales_agent', 'difficulty' => 'beginner', 'duration_minutes' => 20], $t);
        $m = $this->module($c, 'The sales day');
        $this->lesson($m, ['title' => 'Login to logout for sales', 'lesson_type' => 'procedure', 'duration_minutes' => 10,
            'body' => 'Login → dashboard → notifications → pipeline/leads (new arrivals, follow-ups due) → qualify and contact → update CRM activities with next actions → prepare/send quotations → chase acceptances → convert won leads → hand confirmed orders to delivery with a complete pack → log out with zero ownerless leads.',
            'objectives' => ['Run a complete sales day with clean handoffs'],
            'steps' => ['STEP 1: Triage notifications and due follow-ups', 'STEP 2: Work the pipeline oldest-first', 'STEP 3: Advance quotations; record every touch', 'STEP 4: Convert + hand over with scope, price, terms'],
            'why_matters' => ['Why: pipeline hygiene is revenue hygiene'],
            'common_mistakes' => ['Follow-ups without next actions', 'Handoffs missing terms'],
            'discussion_questions' => ['What does a perfect handover pack contain?']]);
        $this->quiz($c, 'Sales day check', [
            ['type' => 'boolean', 'prompt' => 'Every lead touch needs a next action and date.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'No ownerless leads.'],
        ]);
    }

    // ── COURSE 19: daily support ─────────────────────────────
    protected function courseDailySupport(?User $t): void
    {
        $c = $this->course(['slug' => 'daily-workflow-support', 'title' => 'My Working Day: Support & Delivery', 'description' => 'Role-specific daily workflow for support and delivery staff.', 'role_target' => 'support_agent', 'difficulty' => 'beginner', 'duration_minutes' => 20], $t);
        $m = $this->module($c, 'The support day');
        $this->lesson($m, ['title' => 'Login to logout for support', 'lesson_type' => 'procedure', 'duration_minutes' => 10,
            'body' => 'Login → dashboard → notifications → assigned tasks and tickets (SLA order) → customer messages → prioritize by SLA then impact → work with notes → update task/project progress → post customer updates → submit for review → close verified work → review remaining queue → logout with nothing silent and overdue.',
            'objectives' => ['Run a complete support day inside SLA'],
            'steps' => ['STEP 1: Sort queue by SLA deadline', 'STEP 2: Work, document, update visibly', 'STEP 3: Review, confirm, close', 'STEP 4: End-of-day queue sweep'],
            'why_matters' => ['Why: silence is the only unforgivable status'],
            'common_mistakes' => ['Working newest-first while SLA burns', 'Closing without confirmation'],
            'discussion_questions' => ['How do you order five tickets at 9am?']]);
        $this->quiz($c, 'Support day check', [
            ['type' => 'boolean', 'prompt' => 'SLA deadline order beats newest-first ordering.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'Deadlines first.'],
        ]);
    }

    // ── COURSE 20: full lifecycle simulation (capstone) ──────
    protected function courseSimulation(?User $t): void
    {
        $c = $this->course(['slug' => 'full-lifecycle-simulation', 'title' => 'Full Lifecycle Simulation: [TRAINING] BrightWave Digital Ltd', 'description' => 'Capstone: process Sarah Wilson / BrightWave (£3,500, IT support + security + maintenance) inquiry to closure.', 'difficulty' => 'advanced', 'duration_minutes' => 90, 'is_mandatory' => true], $t);
        $m = $this->module($c, 'The 22-stage examination', 'Every stage from inquiry to follow-up.');
        $this->lesson($m, ['title' => 'Stages 1–8: inquiry to initial payment', 'lesson_type' => 'simulation', 'duration_minutes' => 20,
            'body' => '[TRAINING/SIMULATION] Sarah Wilson, BrightWave Digital Ltd: Managed IT Support + Website Security Assessment + Website Maintenance = £3,500. Stage 1: visits the services pages. Stage 2: submits inquiry (record source + requirements). Stage 3: sales receives the lead. Stage 4: qualifies (need, budget, authority). Stage 5: prepares the quotation (line items, tax, validity, exclusions). Stage 6: customer approves the proposal. Stage 7: order created and reviewed. Stage 8: initial payment received and verified against records — state paid vs due explicitly.',
            'objectives' => ['Execute stages 1–8 with correct records'],
            'steps' => ['STEP 1: Inquiry logged with source', 'STEP 2: Lead qualified with evidence', 'STEP 3: Quotation priced and versioned', 'STEP 4: Acceptance timestamped; order created', 'STEP 5: Initial payment verified; paid/due stated'],
            'why_matters' => ['Why: the commercial half funds everything after', 'What finance sees: expected £3,500 with evidenced receipts'],
            'common_mistakes' => ['Skipping qualification', 'Orders before acceptance', 'Unverified payment claims'],
            'discussion_questions' => ['What proves qualification for BrightWave?']]);
        $this->lesson($m, ['title' => 'Stages 9–15: delivery and quality', 'lesson_type' => 'simulation', 'duration_minutes' => 20,
            'body' => 'Stage 9: project created from the order with milestones. Stage 10: tasks assigned across IT support, authorized security testing and maintenance. Stage 11: employees start work per task with notes. Stage 12: progress updates posted (stage, honest %, next step, date). Stage 13: customer receives and acknowledges updates. Stage 14: tasks completed and verified. Stage 15: independent quality review before anything is called done.',
            'objectives' => ['Deliver all three service lines with evidence'],
            'steps' => ['STEP 1: Project + milestones from order', 'STEP 2: Tasks assigned with deadlines', 'STEP 3: Work + updates + acknowledgements', 'STEP 4: Verification + quality review'],
            'why_matters' => ['Why: quality review is the last cheap place to catch errors'],
            'common_mistakes' => ['Security work outside scope', 'Updates without evidence', 'Skipping quality review'],
            'discussion_questions' => ['What does quality review check for each service line?']]);
        $this->lesson($m, ['title' => 'Stages 16–22: money, closure, follow-up', 'lesson_type' => 'simulation', 'duration_minutes' => 20,
            'body' => 'Stage 16: final invoice generated and checked. Stage 17: customer pays the remainder. Stage 18: finance verifies every receipt. Stage 19: project marked completed. Stage 20: account balance checked — due must be £0. Stage 21: customer receives completion notification with deliverables. Stage 22: order closed per platform workflow, then follow-up scheduled. Profit impact: £3,500 revenue minus delivery expenses; every line evidenced.',
            'objectives' => ['Close with £0 due and a follow-up set'],
            'steps' => ['STEP 1: Final invoice verified line by line', 'STEP 2: Final payment matched to references', 'STEP 3: £0-due proof + project completion', 'STEP 4: Closure notification + follow-up date'],
            'why_matters' => ['Why: closure without money proof is fiction', 'What happens next: follow-up feeds the next sale'],
            'common_mistakes' => ['Closing with balances open', 'No follow-up scheduled'],
            'discussion_questions' => ['What is the P&L impact of BrightWave?']]);
        $this->quiz($c, 'Lifecycle simulation check', [
            ['type' => 'ordering', 'prompt' => 'Order these stages.', 'options' => ['Project created', 'Quotation prepared', 'Final payment verified', 'Lead qualified'], 'correct' => ['3', '1', '0', '2'], 'explanation' => 'Qualified → Quotation → Project → Final payment.'],
            ['type' => 'multiple', 'prompt' => 'Closure requires…', 'options' => ['Verified tasks', '£0 due proof', 'Customer notification', 'Follow-up scheduled'], 'correct' => ['0', '1', '2', '3'], 'explanation' => 'All four gates.'],
            ['type' => 'boolean', 'prompt' => 'All simulation data must carry [TRAINING]/[SIMULATION] markers and never touch real payments.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'Synthetic only, always.'],
        ]);
        $this->practical($c, 'Final examination: run BrightWave end-to-end', 'Process [TRAINING] BrightWave Digital Ltd (£3,500) through all 22 stages. Submit: CRM entries, quotation, order record, payment ledger (paid/due after each receipt), project/task plan, two progress updates, quality note, final invoice state, £0-due proof, closure notification and follow-up date. No real data, no real payments.', ['All 22 stages evidenced', 'Payment ledger exact', 'Quality + closure gates signed', 'Follow-up scheduled']);
    }

    // ── COURSE 21: trainer playbook ──────────────────────────
    protected function courseTrainerPlaybook(?User $t): void
    {
        $c = $this->course(['slug' => 'trainer-playbook', 'title' => 'Trainer Playbook: Running the Academy', 'description' => 'Content management, assignment, presentation mode, review standards and versioning.', 'role_target' => 'training_manager', 'difficulty' => 'intermediate', 'duration_minutes' => 30], $t);
        $m = $this->module($c, 'Teaching with the platform');
        $this->lesson($m, ['title' => 'Courses, versions, presentation and review', 'lesson_type' => 'guide', 'duration_minutes' => 12,
            'body' => 'Build Course → Module → Lesson → Scenario → Quiz → Question → Practical, each with title, description, role, difficulty, duration, ordering, published/draft state and version (e.g. 1.0, updated 2026-09-17, by trainer). Assign to roles or individuals with due dates; use search/filter by role, difficulty and status. Present lessons classroom-style with Previous/Next/Scenario/Quiz/Exercise navigation. Review practicals against checklists with written feedback; require retests where needed; reset training when roles change. Update lessons when platform workflows change and tell employees what changed.',
            'objectives' => ['Operate the full trainer workflow'],
            'steps' => ['STEP 1: Create and version content; publish when ready', 'STEP 2: Assign with due dates; monitor dashboards and reports', 'STEP 3: Present, review with feedback, certify completions'],
            'why_matters' => ['Why: the Academy is a classroom with evidence, not a library'],
            'common_mistakes' => ['Unversioned edits', 'Passing without feedback', 'Stale lessons after workflow changes'],
            'discussion_questions' => ['What triggers a lesson version bump?']]);
        $this->quiz($c, 'Trainer check', [
            ['type' => 'boolean', 'prompt' => 'Content edits that follow platform changes need a version bump and employee notice.', 'options' => ['True', 'False'], 'correct' => ['0'], 'explanation' => 'Versioning keeps trust.'],
            ['type' => 'single', 'prompt' => 'Completion is issued when…', 'options' => ['The employee asks', 'Lessons + quizzes + practicals all pass', 'The deadline passes', 'The trainer likes them'], 'correct' => ['1'], 'explanation' => 'Evidence-based completion only.'],
        ]);
    }
}
