<?php

namespace App\Support;

/**
 * TrainingCenterProblems — Problem & Solution Center article library.
 * Format: symptoms → causes → solution steps → verification → escalation
 * → related training. All guidance matches real platform behavior.
 */
class TrainingCenterProblems
{
    public static function filters(): array
    {
        return [
            'all' => 'All', 'customer' => 'Customer', 'sales' => 'Sales',
            'it-support' => 'IT Support', 'cybersecurity' => 'Cybersecurity',
            'development' => 'Development', 'finance' => 'Finance',
            'admin' => 'Administration', 'website' => 'Website', 'technical' => 'Technical Problems',
        ];
    }

    public static function categories(): array
    {
        return [
            'website' => 'Website', 'customer' => 'Customer Access', 'orders' => 'Orders',
            'payments' => 'Payments', 'projects' => 'Projects & Tasks', 'tickets' => 'Tickets & SLA',
            'finance' => 'Finance', 'notifications' => 'Email & Notifications', 'it-support' => 'IT Support',
        ];
    }

    public static function all(): array
    {
        return [
            [
                'slug' => 'page-not-loading', 'category' => 'website', 'filter' => 'website', 'title' => 'Page not loading or shows an error',
                'symptoms' => ['Blank page, spinner that never resolves, or a 4xx/5xx error page', 'Other pages load normally'],
                'causes' => ['Expired session or stale cached page', 'Missing build assets after a deploy', 'Insufficient role permission for the URL', 'Upstream server or network fault'],
                'steps' => ['Hard-refresh and retry the exact URL', 'Log out and back in to refresh the session', 'Try another menu path to the same record instead of a bookmark', 'Check whether colleagues see the same failure (isolates account vs platform)', 'Confirm your role should access that area — 403 means authorization, not a bug'],
                'verification' => 'The page renders with current data and no console errors after a fresh login.',
                'escalation' => 'If all staff see it or 500 errors persist, escalate to the admin/technical team with URL, time, role and screenshots.',
                'related' => ['platform-overview', 'user-roles'],
            ],
            [
                'slug' => 'broken-link', 'category' => 'website', 'filter' => 'website', 'title' => 'Broken link or button that does nothing',
                'symptoms' => ['Click leads to 404 or nothing happens', 'Link worked previously'],
                'causes' => ['Renamed or unpublished content (service, article, case study)', 'Draft content linked before approval', 'JavaScript blocked or console errors'],
                'steps' => ['Copy the URL and confirm what it should open', 'Find the target via site search or the relevant index page', 'If it is draft/unpublished content, route it through review → approval → publish', 'Report the broken source page so the link is fixed at origin'],
                'verification' => 'The button reaches a live page, and the source link is corrected.',
                'escalation' => 'Site-wide link failures go to the content/admin team with both URLs.',
                'related' => ['website-updates', 'knowledge-base'],
            ],
            [
                'slug' => 'missing-content', 'category' => 'website', 'filter' => 'website', 'title' => 'Missing content or outdated information on a page',
                'symptoms' => ['Old price, old date or absent section on a public page', 'KB article contradicts the current workflow'],
                'causes' => ['Content edited without publish/approval step', 'KB article not updated after a workflow change', 'Wrong visibility setting'],
                'steps' => ['Confirm what the correct current information is and its source', 'Update via draft → review → approval → publish → verify-live', 'Check visibility (public vs internal) and mobile rendering'],
                'verification' => 'Live page shows correct information on desktop and mobile.',
                'escalation' => 'If publishing rights are missing, escalate to an admin with the corrected draft.',
                'related' => ['website-updates', 'knowledge-base'],
            ],
            [
                'slug' => 'cannot-login', 'category' => 'customer', 'filter' => 'customer', 'title' => 'Customer cannot log in',
                'symptoms' => ['Correct-looking credentials rejected', 'OTP never arrives or always fails', 'Account locked messages'],
                'causes' => ['Unverified email/phone blocking full access', 'Expired OTP (10-minute lifetime) or resend-cooldown confusion', 'Five failed attempts triggering lockout', 'Deactivated or suspended account', 'Wrong portal URL or caps-lock typos'],
                'steps' => ['Confirm they use the customer portal login, not the staff URL', 'Check verification status; guide email OTP then phone OTP completion', 'If expired, request a fresh code after the 60-second cooldown', 'After lockout, wait out the window and retry carefully', 'Check account active status before anything else'],
                'verification' => 'Customer reaches the dashboard and sees their own records.',
                'escalation' => 'Active, verified accounts still failing go to admin with email, time and error text.',
                'related' => ['customer-journey', 'security'],
            ],
            [
                'slug' => 'cannot-register', 'category' => 'customer', 'filter' => 'customer', 'title' => 'Customer cannot register',
                'symptoms' => ['Validation errors on submit', 'Duplicate-account messages', 'No verification email received'],
                'causes' => ['Existing account under the same email (duplicate)', 'Invalid phone/email format', 'Verification email filtered as spam', 'Rate-limiting after repeated attempts'],
                'steps' => ['Search whether the customer already exists — never create duplicates', 'Correct formats and retry once, slowly', 'Check spam and request one resend after the cooldown', 'Guide existing-account holders to login plus password reset'],
                'verification' => 'One clean account exists and verification completes.',
                'escalation' => 'Persistent validation failures go to the technical team with exact field errors.',
                'related' => ['customer-journey', 'crm'],
            ],
            [
                'slug' => 'customer-cannot-see-order', 'category' => 'customer', 'filter' => 'customer', 'title' => 'Customer cannot see their order',
                'symptoms' => ['Order exists on the staff side but not in /portal/orders'],
                'causes' => ['Order linked to the wrong customer record', 'Order in a non-visible status', 'Customer logged into a different (duplicate) account'],
                'steps' => ['Verify the order’s customer linkage matches the portal account', 'Check order status and visibility rules', 'Confirm no duplicate customer record splits their history', 'Have them re-login and open Orders directly'],
                'verification' => 'Order appears under the correct portal account with correct status.',
                'escalation' => 'Correct linkage plus correct status but still invisible goes to the technical team.',
                'related' => ['orders', 'customer-journey'],
            ],
            [
                'slug' => 'customer-cannot-see-invoice', 'category' => 'customer', 'filter' => 'customer', 'title' => 'Customer cannot see or pay an invoice',
                'symptoms' => ['Invoice missing in /portal invoices, or pay action unavailable'],
                'causes' => ['Invoice still in draft (never sent)', 'Wrong customer linkage', 'Already-paid invoice correctly showing no due', 'Payment method temporarily unavailable'],
                'steps' => ['Check invoice status: draft invoices are invisible until sent', 'Verify customer linkage and totals', 'If paid, show the receipt and £0-due state instead', 'Confirm an available payment method and retry'],
                'verification' => 'Customer sees the correct invoice with correct due and a working pay path — or a correct paid receipt.',
                'escalation' => 'Sent, correctly-linked, unpaid invoices that still fail go to finance plus technical.',
                'related' => ['invoices', 'payments'],
            ],
            [
                'slug' => 'order-missing', 'category' => 'orders', 'filter' => 'sales', 'title' => 'Order missing from dashboard',
                'symptoms' => ['Customer insists they ordered; staff cannot find it'],
                'causes' => ['Only an inquiry or quotation exists — no order was created', 'Order under a duplicate customer record', 'Staff looking in the wrong queue or with restrictive filters'],
                'steps' => ['Trace backwards: acceptance? If none, there is no order yet — say so plainly', 'Search by customer, email and company across records', 'Clear filters and check allVisible queues', 'Create the order from acceptance if it is genuinely missing'],
                'verification' => 'Order exists, linked to the right customer and proposal, with correct status.',
                'escalation' => 'Systemic missing-order patterns go to the technical team with examples.',
                'related' => ['orders', 'quotations'],
            ],
            [
                'slug' => 'order-status-wrong', 'category' => 'orders', 'filter' => 'sales', 'title' => 'Order status looks incorrect',
                'symptoms' => ['Status contradicts known payment or delivery facts'],
                'causes' => ['Payment verified after the last status update', 'Manual status change skipped a step', 'Delivery started before confirmation flow completed'],
                'steps' => ['Re-read the facts: acceptance timestamp, payment records, project state', 'Move status only forward along the real flow with a note', 'Never mark confirmed without payment evidence'],
                'verification' => 'Status matches acceptance, payment and delivery facts with history explaining each move.',
                'escalation' => 'Statuses that revert or contradict locked records go to admin review.',
                'related' => ['orders', 'payments'],
            ],
            [
                'slug' => 'project-not-created', 'category' => 'orders', 'filter' => 'sales', 'title' => 'Project was not created from a confirmed order',
                'symptoms' => ['Confirmed order exists but delivery has no project'],
                'causes' => ['Confirmation step never completed', 'Project creation assigned to nobody', 'Order–project linkage entered incorrectly'],
                'steps' => ['Confirm the order is truly confirmed (not just accepted)', 'Create the project from the order with correct linkage', 'Set milestones, team and dates immediately so work can start'],
                'verification' => 'Project exists, linked to the order, with milestones and owners.',
                'escalation' => 'Repeated handoff failures go to sales and project leads to fix the process.',
                'related' => ['orders', 'projects'],
            ],
            [
                'slug' => 'duplicate-customer', 'category' => 'orders', 'filter' => 'sales', 'title' => 'Duplicate customer records discovered',
                'symptoms' => ['Two records, split orders and tickets, confused history'],
                'causes' => ['Lead created without searching first', 'Name/email spelling variants', 'Conversion creating instead of linking'],
                'steps' => ['Stop and map which record holds orders, tickets, invoices', 'Keep the record with live financial linkage as primary', 'Merge or deactivate per company policy — never delete financial history', 'Note the surviving record clearly for the team'],
                'verification' => 'One primary record holds the full traceable history.',
                'escalation' => 'Merges touching invoices or payments need finance-tier approval first.',
                'related' => ['crm', 'customer-journey'],
            ],
            [
                'slug' => 'lead-not-converting', 'category' => 'orders', 'filter' => 'sales', 'title' => 'Lead stuck and not converting',
                'symptoms' => ['Lead ageing in contacted or proposal with no movement'],
                'causes' => ['No next follow-up set', 'Unqualified lead chased as qualified', 'Proposal never actually sent', 'Customer waiting on an unanswered question'],
                'steps' => ['Read the activity history for the last real touch', 'Re-qualify honestly: need, budget, authority', 'Send or re-send the proposal; set a dated next action', 'Close lost with a reason rather than letting it rot'],
                'verification' => 'Lead moves within one cycle or exits cleanly with a reason.',
                'escalation' => 'Stalled high-value leads go to the sales lead with history attached.',
                'related' => ['crm', 'proposals'],
            ],
            [
                'slug' => 'payment-not-showing', 'category' => 'payments', 'filter' => 'finance', 'title' => 'Payment not showing on the order or invoice',
                'symptoms' => ['Customer paid but records still show due'],
                'causes' => ['Payment still processing or failed silently', 'Payment recorded against the wrong invoice', 'Offline payment not yet recorded per policy', 'Duplicate browser submit creating confusion'],
                'steps' => ['Check payment records and transaction history first — not the customer’s word alone', 'Match references, method, date and amount', 'Record permitted offline payments with reference immediately', 'Reconcile: payments must equal the income lines for that invoice'],
                'verification' => 'Paid and due figures match evidenced records to the penny.',
                'escalation' => 'Gateway-side uncertainty goes to finance with references and timestamps.',
                'related' => ['payments', 'invoices'],
            ],
            [
                'slug' => 'balance-wrong', 'category' => 'payments', 'filter' => 'finance', 'title' => 'Remaining balance looks incorrect',
                'symptoms' => ['Due figure contradicts known receipts'],
                'causes' => ['Partial payment overlooked', 'Duplicate income entry inflating paid', 'Refund or adjustment not recorded', 'Reading the wrong invoice version'],
                'steps' => ['List every payment record and sum independently', 'Hunt duplicates by reference and timestamp', 'Confirm refunds and adjustments are recorded', 'Recompute total minus verified paid equals due'],
                'verification' => 'Independent recomputation matches the platform figures exactly.',
                'escalation' => 'Unexplained gaps go to finance with your worksheet attached.',
                'related' => ['payments', 'finance'],
            ],
            [
                'slug' => 'invoice-problem', 'category' => 'payments', 'filter' => 'finance', 'title' => 'Invoice totals, tax or status disputed',
                'symptoms' => ['Customer challenges the amount or status'],
                'causes' => ['Line-item or tax-rate error', 'Discount applied without authorization', 'Status stale after a recent payment', 'Cancelled invoice still referenced'],
                'steps' => ['Re-read line items, tax, total, paid and due from the record', 'Verify authorization for any discount', 'Refresh status from payment history before responding', 'Correct via proper revision, never by editing history silently'],
                'verification' => 'Customer receives a line-by-line explanation matching records.',
                'escalation' => 'Pricing-authorization disputes go to the finance lead.',
                'related' => ['invoices', 'quotations'],
            ],
            [
                'slug' => 'cannot-access-task', 'category' => 'projects', 'filter' => 'it-support', 'title' => 'Employee cannot access an assigned task',
                'symptoms' => ['Task visible to PM but not to the assignee, or 403 errors'],
                'causes' => ['Assignment never saved to the right person', 'Role lacks project-tier access', 'Task under a different project than expected'],
                'steps' => ['Confirm the exact assignee name on the task record', 'Confirm the employee’s role carries project access', 'Re-assign explicitly and have them open it from Projects, not a bookmark'],
                'verification' => 'Assignee opens the task and its attachments directly.',
                'escalation' => 'Correct assignment plus correct role still failing goes to admin.',
                'related' => ['tasks', 'user-roles'],
            ],
            [
                'slug' => 'progress-not-updating', 'category' => 'projects', 'filter' => 'it-support', 'title' => 'Project progress not moving',
                'symptoms' => ['Milestones stale while tasks claim activity'],
                'causes' => ['Task statuses not updated after work', 'Work logged in chat instead of the task', 'Blocked dependency nobody flagged', 'No milestone mapped to the finished tasks'],
                'steps' => ['Update every task status with evidence attached', 'Flag blockers explicitly with owner and needed help', 'Map finished work to milestones so rollups move', 'Post the customer update for the movement'],
                'verification' => 'Milestone percentages match evidenced task states.',
                'escalation' => 'Blocked-beyond-team work goes to the PM with the blocker documented.',
                'related' => ['projects', 'progress-updates'],
            ],
            [
                'slug' => 'cannot-complete-project', 'category' => 'projects', 'filter' => 'it-support', 'title' => 'Project cannot be completed or closed',
                'symptoms' => ['Completion blocked despite “everything done”'],
                'causes' => ['Open or unverified tasks remaining', 'Quality review not recorded', 'Outstanding invoice balance', 'Missing deliverables or notifications'],
                'steps' => ['Run the closure gate in order: tasks, quality, deliverables, invoice, £0 due, notifications', 'Fix whichever gate fails — do not force closure', 'Close financial status with finance, then the project'],
                'verification' => 'Project completed with £0 due proof and filed evidence.',
                'escalation' => 'Money-gate disputes go to finance; scope disputes to the PM.',
                'related' => ['account-closure', 'invoices'],
            ],
            [
                'slug' => 'ticket-not-assigned', 'category' => 'tickets', 'filter' => 'customer', 'title' => 'Ticket sitting unassigned',
                'symptoms' => ['New ticket with no owner, clock running'],
                'causes' => ['Category unclear so nobody claimed it', 'Arrival outside covered hours without routing', 'Assignee on leave with no backup'],
                'steps' => ['Categorize and prioritize immediately', 'Assign an owner now — a wrong owner who reroutes beats no owner', 'Post the first response inside the response SLA'],
                'verification' => 'Ticket has an owner, a plan and a timestamped first response.',
                'escalation' => 'Unroutable or specialist-only tickets go to the support lead at once.',
                'related' => ['tickets', 'sla'],
            ],
            [
                'slug' => 'sla-at-risk', 'category' => 'tickets', 'filter' => 'customer', 'title' => 'SLA deadline at risk or breached',
                'symptoms' => ['Clock nearly out or red with no customer plan posted'],
                'causes' => ['Queue worked newest-first', 'Waiting on a third party silently', 'Underestimated complexity', 'Owner overloaded'],
                'steps' => ['Post a plan NOW: cause, impact, new time, owner', 'Pull help or escalate before the deadline, not after', 'On breach: own it, remediate, document, learn'],
                'verification' => 'Customer holds a dated plan; the ticket moves visibly.',
                'escalation' => 'At-risk high-priority tickets go to the lead immediately.',
                'related' => ['sla', 'tickets'],
            ],
            [
                'slug' => 'customer-cannot-see-response', 'category' => 'tickets', 'filter' => 'customer', 'title' => 'Customer cannot see the ticket response',
                'symptoms' => ['Reply sent but customer sees nothing'],
                'causes' => ['Response saved as an internal note instead of a reply', 'Notification or email failure', 'Customer checking the wrong ticket or account'],
                'steps' => ['Confirm the message type: reply (visible) vs internal note (hidden)', 'Re-send as a proper reply if misfiled', 'Verify notification and email delivery status', 'Guide the customer to the exact ticket in their portal'],
                'verification' => 'Customer confirms visibility of the response in their portal.',
                'escalation' => 'Correct replies still invisible go to the technical team.',
                'related' => ['tickets', 'customer-communication'],
            ],
            [
                'slug' => 'income-missing', 'category' => 'finance', 'filter' => 'finance', 'title' => 'Income missing from reports',
                'symptoms' => ['Known receipt absent in income or P&L'],
                'causes' => ['Payment completed but income line never created', 'Transaction still pending', 'Income filed under the wrong category or period'],
                'steps' => ['Find the payment record and its reference first', 'Check transaction status: only completed counts', 'Create the single income line against the reference if missing', 'Reconcile the invoice: payments must equal income lines'],
                'verification' => 'Report includes the receipt exactly once with its reference.',
                'escalation' => 'Gateway-settled money with no record goes to finance immediately.',
                'related' => ['finance', 'income-expenses'],
            ],
            [
                'slug' => 'expense-missing', 'category' => 'finance', 'filter' => 'finance', 'title' => 'Expense missing or stuck unapproved',
                'symptoms' => ['Spend invisible in reports or frozen in pending'],
                'causes' => ['Record never created (receipt in a drawer)', 'Missing category or documentation blocking approval', 'Approver never notified'],
                'steps' => ['Create the record complete: category, date, vendor, amount, proof', 'Submit for approval and notify the approver directly', 'Correct rejections the same day'],
                'verification' => 'Expense approved and visible in category reports.',
                'escalation' => 'Policy disputes (reimbursable or not) go to the finance lead.',
                'related' => ['income-expenses', 'finance'],
            ],
            [
                'slug' => 'duplicate-transaction', 'category' => 'finance', 'filter' => 'finance', 'title' => 'Duplicate payment or income entry suspected',
                'symptoms' => ['Paid exceeds the invoice total, or twin identical lines'],
                'causes' => ['Double form submit', 'Offline receipt plus gateway receipt for one payment', 'Two staff recording the same receipt'],
                'steps' => ['Freeze: record nothing further until resolved', 'Match by reference, timestamp and method to find the twin', 'Void or reverse the duplicate per policy — never silent-delete financial history', 'Reconcile and note the correction'],
                'verification' => 'One receipt equals one income line; totals reconcile.',
                'escalation' => 'Duplicates involving payouts or tax filings go to the finance lead.',
                'related' => ['payments', 'profit-loss'],
            ],
            [
                'slug' => 'profit-mismatch', 'category' => 'finance', 'filter' => 'finance', 'title' => 'Profit/loss figure looks wrong',
                'symptoms' => ['P&L contradicts known sales and spend'],
                'causes' => ['Pending counted as received', 'Miscategorized expenses', 'Missing income lines or duplicate entries', 'Wrong period filter'],
                'steps' => ['Confirm the period and filters first', 'Separate completed from pending everywhere', 'Audit categories and hunt duplicates', 'Recompute revenue minus expenses independently'],
                'verification' => 'Independent recomputation matches the report.',
                'escalation' => 'Persistent mismatch after clean data goes to the finance lead with worksheets.',
                'related' => ['profit-loss', 'finance'],
            ],
            [
                'slug' => 'email-not-sent', 'category' => 'notifications', 'filter' => 'technical', 'title' => 'Email or notification not sent',
                'symptoms' => ['Expected message never arrives; no error shown'],
                'causes' => ['Mail configuration or queue backlog', 'Recipient address wrong or mailbox full', 'Notification preferences disabled', 'Message caught by spam filters'],
                'steps' => ['Verify the recipient address and preferences', 'Check spam and mailbox capacity', 'Confirm the triggering action actually completed', 'Retry once, then use the in-portal channel so nothing depends on email alone'],
                'verification' => 'Message visible in-portal and delivered by email on retry.',
                'escalation' => 'System-wide mail failures go to the technical team with timestamps.',
                'related' => ['customer-communication', 'progress-updates'],
            ],
            [
                'slug' => 'customer-update-not-visible', 'category' => 'notifications', 'filter' => 'technical', 'title' => 'Customer update not visible to the customer',
                'symptoms' => ['Update posted but customer sees nothing new'],
                'causes' => ['Posted as internal note instead of customer update', 'Wrong project or ticket thread', 'Notification failure hiding an actually-visible update'],
                'steps' => ['Confirm the entry type and thread first', 'Repost correctly as a customer-visible update if misfiled', 'Verify notification delivery; point the customer at the exact location'],
                'verification' => 'Customer confirms the update in their portal.',
                'escalation' => 'Correctly-posted updates still invisible go to the technical team.',
                'related' => ['progress-updates', 'customer-communication'],
            ],
            [
                'slug' => 'windows-issue', 'category' => 'it-support', 'filter' => 'it-support', 'title' => 'Windows workstation problem (slow, crash, login)',
                'symptoms' => ['Slowness, freezes, blue screens or login failures'],
                'causes' => ['Disk pressure or failing drive', 'Runaway process or pending updates', 'Profile corruption', 'Malware or overheating'],
                'steps' => ['Capture symptoms: when, what changed, error text, scope (one or many machines)', 'Check disk space, Task Manager hogs and pending updates', 'Restart cleanly; test in a fresh profile if login fails', 'Back up data before deeper fixes; scan for malware', 'Document every change for the ticket'],
                'verification' => 'Customer confirms normal operation across a full working session.',
                'escalation' => 'Hardware faults, multi-machine outbreaks or suspected compromise go to senior support.',
                'related' => ['it-services', 'tickets'],
            ],
            [
                'slug' => 'm365-issue', 'category' => 'it-support', 'filter' => 'it-support', 'title' => 'Microsoft 365 access or license problem',
                'symptoms' => ['Login loops, “no license” errors, apps deactivated'],
                'causes' => ['License unassigned or expired', 'Conditional-access or MFA interruption', 'Stale cached credentials', 'Service-side incident'],
                'steps' => ['Verify the user, license assignment and subscription state', 'Clear cached credentials and retry with MFA ready', 'Check the service health dashboard for incidents', 'Reassign license if legitimately missing; document the change'],
                'verification' => 'User signs in and all licensed apps activate.',
                'escalation' => 'Tenant-wide or subscription faults go to senior support with tenant details.',
                'related' => ['it-services', 'security'],
            ],
            [
                'slug' => 'outlook-issue', 'category' => 'it-support', 'filter' => 'it-support', 'title' => 'Outlook send/receive or sync failure',
                'symptoms' => ['Mail stuck in Outbox, sync errors, missing folders'],
                'causes' => ['Oversized OST or corrupt profile', 'Wrong server or authentication settings', 'Add-in conflict', 'Mailbox quota reached'],
                'steps' => ['Check connectivity, quota and exact error code', 'Restart in safe mode to rule out add-ins', 'Repair or recreate the profile (data is server-side for 365)', 'Compact or trim the mailbox if quota-bound'],
                'verification' => 'Send and receive succeed both ways with folders in sync.',
                'escalation' => 'Server-side mailbox corruption goes to senior support.',
                'related' => ['it-services', 'tickets'],
            ],
            [
                'slug' => 'network-issue', 'category' => 'it-support', 'filter' => 'technical', 'title' => 'Network slow or dropping (office)',
                'symptoms' => ['Intermittent access, slow transfers, VoIP breaking up'],
                'causes' => ['Failing switch port or cable', 'DHCP exhaustion or IP conflict', 'Bandwidth hog or broadcast storm', 'ISP degradation'],
                'steps' => ['Scope it: one desk, one switch or whole site?', 'Test wired vs Wi-Fi; swap cable and port', 'Check DHCP scope, conflicts and top bandwidth users', 'Restart network gear in order; document before/after'],
                'verification' => 'Stable speeds and clean calls across the affected area for an hour.',
                'escalation' => 'Site-wide or ISP-side faults go to senior/network specialists.',
                'related' => ['it-services', 'tickets'],
            ],
            [
                'slug' => 'dns-issue', 'category' => 'it-support', 'filter' => 'technical', 'title' => 'DNS resolution failure',
                'symptoms' => ['“Site can’t be reached” but IPs work; some sites fine, others not'],
                'causes' => ['Wrong DNS servers via DHCP', 'Stale local cache', 'Upstream resolver outage', 'Typo in manually-set records'],
                'steps' => ['Confirm with nslookup against two different resolvers', 'Flush local cache and test again', 'Check DHCP-advertised DNS and fix at source', 'Read back any manual records character by character'],
                'verification' => 'Names resolve consistently on affected machines and servers.',
                'escalation' => 'Authoritative-zone or registrar issues go to senior support.',
                'related' => ['it-services', 'tickets'],
            ],
            [
                'slug' => 'printer-issue', 'category' => 'it-support', 'filter' => 'it-support', 'title' => 'Printer offline or jobs stuck',
                'symptoms' => ['Printer shows offline; queue grows; test page fails'],
                'causes' => ['Stuck print spooler', 'IP change breaking the port', 'Driver mismatch after OS update', 'Paper or toner fault masked as offline'],
                'steps' => ['Check physical state first: power, paper, toner, display errors', 'Restart the print spooler and clear the queue', 'Verify the port IP matches the printer’s actual IP; set DHCP reservation', 'Reinstall the correct driver if recently updated'],
                'verification' => 'Test page plus a real customer document print cleanly.',
                'escalation' => 'Hardware faults go to the hardware vendor with the error codes.',
                'related' => ['it-services', 'tickets'],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        foreach (self::all() as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }

        return null;
    }
}
