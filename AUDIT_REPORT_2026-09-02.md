# IT Support Platform Audit — 2026-09-02

## Scope and evidence

Static and workflow review covered 208 web routes, controllers, models, migrations, service classes, middleware, configuration, Blade templates, and the automated test suite. The review verified code paths; it did not run a production-like browser, payment-gateway, mail, queue-worker, or scheduled-cron environment. `COMPLETE` below means implemented and covered by the reviewed application flow, not externally certified.

## Fixes applied during the audit

| Finding | Severity | Remediation | Verification |
|---|---:|---|---|
| Guest/customer AI conversations could be read, closed, rated, escalated, or used for ticket creation by ID | P0 | Central ownership check now requires the owning authenticated user, staff, or matching guest session token on every state/data endpoint | New automated authorization tests |
| Support agents could load or mutate unassigned ticket IDs | P0 | Ticket show/reply/status/note now enforce assignee ownership; assignment requires management | New negative IDOR test |
| Finance invoices, payments, expenses, and commissions were accessible to any staff user | P0 | Routes now require `isFinanceManager` (admin/super-admin/finance manager) | New route authorization tests |
| Ticket attachments were stored beneath publicly served storage | P0 | New uploads use private local storage, restricted MIME types, and an ownership-checked download route | Route and source review |
| Payment posting allowed overpayment and had a race window | P0 | Invoice row lock, issued-invoice check, balance cap, transaction-ID uniqueness, and database transaction added | Source review; suite pass |
| Draft/unaccepted quotation could become an invoice | P1 | Customer acceptance checks status/expiry; conversion locks and requires accepted status | Source review |
| Staff ticket resource exposed unimplemented CRUD methods | P1 | Route reduced to the implemented index/show actions | `route:list` verified |
| Project creation referenced `Str` without importing it | P1 | Imported `Illuminate\\Support\\Str` | PHP lint |
| Public frontend-error ingestion could be used to flood error logs | P2 | Added 60/minute throttling | Route review |

## Feature matrix

| Module | Feature | Status | Priority | Working | Secure | Integrated | Tested | Action |
|---|---|---|---:|---|---|---|---|---|
| Public site | Home, about, contact, service catalogue, pricing, blog, KB | PARTIAL | P1 | Yes | Yes | DB-driven content/services | Route/view smoke tests | Add industries, solutions, careers, legal, cookie controls, case studies/testimonials and emergency support |
| Service catalogue | Categories, services, country prices, requests | PARTIAL | P1 | Yes | Staff-only admin | Request → staff review exists | Partial | Add service FAQ/media/commission-rule management and all catalogue verticals as seed/admin data |
| Pricing | GBP/USD/BDT country pricing | PARTIAL | P1 | Model/migration exists | Yes | Not proven in quote/invoice flow | No | Carry country/currency/tax through quote, invoice and payment; add pricing tests |
| CRM | Customer/company, tickets, invoices, projects | PARTIAL | P1 | Core records work | Customer scopes checked | Core links exist | Partial | Add contacts, assets, documents, notes/activity timeline and company-level isolation |
| Customer portal | Dashboard, tickets, requests, quotes, invoices, projects, services | PARTIAL | P1 | Core pages exist | Ownership filters present | Core workflow partial | Partial | Add payments, documents, tasks, profile/company settings and notification preferences |
| Ticketing | Create/reply/assignment/status/SLA/category/team/search | PARTIAL | P1 | Core flow works | Agent/customer isolation fixed | SLA service attached | Partial | Add merge, escalation rules, reopen policy, tags UI, customer reply attachments and full timeline/audit |
| SLA | Priority policies, deadlines/status/report | PARTIAL | P1 | Basic calculation exists | N/A | Used on ticket creation | No | Add business hours/holidays, scheduled breach warnings/escalation and SLA test coverage |
| Support workforce | Agents, teams, teams lead | PARTIAL | P2 | Basic records | Role guard only | Assignment manual | No | Add availability, workload balancing, routing and performance metrics |
| Quote workflow | Quote/items/approval/conversion | PARTIAL | P1 | Core flow works | Customer ownership; conversion hardened | Quote → invoice | Partial | Add revisions/versioning, staff review assignment, email/send notifications and project conversion |
| Projects/tasks | Projects, tasks, milestones, comments/files models | PARTIAL | P1 | Core CRUD | Staff-wide access still coarse | Customer project view | No | Add project-member authorization, time/expenses/files UX, dependencies/checklists and profitability tests |
| Freelancers/commission | Rules, earnings, payout records | PARTIAL | P1 | Calculation/payout services exist | Finance route guard | Not automatically driven by payment | Partial | Build freelancer portal, assignment acceptance, payment history, ratings and payment-triggered commission calculation |
| Time tracking | Ticket time entries | PARTIAL | P2 | Model exists | Not fully surfaced | Ticket-only | No | Add start/stop/manual timesheets, billable flags and approvals |
| Invoicing/payments | Items, calculations, partial payment, P&L records | PARTIAL | P1 | Core calculation/manual payment works | Finance routes and overpayment guard fixed | Financial transaction recorded | Partial | Gateway/webhook verification, refunds/credit notes, real PDF and notification delivery |
| Finance | Income, expenses, transactions, P&L/reports | PARTIAL | P1 | Core services/views exist | Finance restricted | Payments/expense/commission records link | Partial | Reconciliation, accounts/cash flow/export tests, immutable accounting adjustments |
| Knowledge base | Categories/articles/tags/search | PARTIAL | P2 | CRUD/public read exists | Staff access | AI knowledge service present | No | Add versions, helpful votes, related articles and staff-only visibility |
| AI assistant | Chat, KB context, escalation, ticket draft | PARTIAL | P1 | Core endpoints exist | Conversation isolation fixed | KB/ticket services | New ownership tests | Add durable consent/retention controls, provider failure tests and explicit scoped ticket/invoice lookup rules |
| Notifications | In-app models/preferences | PARTIAL | P2 | Basic UI/routes | User scoped | No mail/queue proof | No | Add event-driven email/in-app notifications and preference enforcement |
| Files | Ticket storage/download | PARTIAL | P1 | New ticket files secured | Private new storage | Ticket relation | No | Migrate old public files; secure project/task/quote/invoice files; add malware scanning and tests |
| Reporting | Finance, tickets, SLA, employees, profitability | PARTIAL | P2 | Routes/views exist | Finance restricted | Report services | No | Verify metrics against fixtures; add CSV/XLSX/PDF exports and date filters consistently |
| RBAC | 10 requested roles | PARTIAL | P0 | Role helpers/middleware | Coarse route groups; P0 paths fixed | No policies/gates | Partial | Adopt permissions/policies and explicit role-module matrix below |
| Audit logging | Auth/payment/invoice audit service | PARTIAL | P1 | Some actions logged | Admin view guarded | Not all modules | No | Log all approval, role, file, ticket, quote, project and settings changes with before/after/IP |
| Health centre | DB/storage/errors/pages/history | PARTIAL | P2 | UI/services exist | Staff-only, not admin-only | Health records present | No | Implement mail/API/queue checks; scheduler currently empty |
| API | Sanctum user, AI and telemetry | PARTIAL | P1 | Endpoints work | AI ownership corrected | No versioned API | Partial | Move APIs to `routes/api.php`, add resource responses, auth/rate-limit/error-contract tests |
| DevOps/performance | Laravel config, indexes, health view | PARTIAL | P2 | Baseline | APP_DEBUG must be production-verified | No queue/schedule workflow | No | Configure deployment secrets, cache/queue worker, backups, monitoring, indexes/profiling and CI |
| Testing | Feature/unit/view/route tests | PARTIAL | P1 | 33 tests | Authorization regression coverage added | Core only | Yes | Add P0/P1 workflow, API, file, SLA, payment and browser responsiveness tests |

## Confirmed missing essential features

| Feature | Why essential | Recommended implementation | Dependencies | Priority |
|---|---|---|---|---:|
| Fine-grained RBAC policies | Route-level staff access is insufficient for project, service and operations data | Laravel policies/permissions with role-permission seed data and policy tests | Role schema | P0 |
| Payment gateway/webhook verification | Manual payment records are not gateway settlement | Signed webhook processor with idempotency, reconciliation and refund lifecycle | Gateway account | P0 |
| Real invoice PDF/email delivery | Current PDF routes render HTML and send has a TODO | Dompdf/snappy plus queued notification and storage | Mail/queue config | P1 |
| Scheduler/queue operations | No scheduled commands are registered | SLA breach/escalation, overdue invoice, notification jobs plus worker monitoring | Server cron/queue | P1 |
| Customer document/file management | Only ticket-upload flow is present | Private disk, authorization policies, scan job, download controllers | Storage/scanner | P1 |
| Complete service-request → quote → project workflow | Request review does not prove conversion through all stages | Explicit state machine and conversion services | Quotes/projects | P1 |
| Project/task workforce workflow | No freelancer/customer task portal or timesheets | Task assignment/acceptance, time entries, approvals and scoped portals | RBAC | P1 |
| Compliance/legal public pages | Required commercial privacy/terms/cookie controls absent | CMS/legal views and consent management | Legal copy | P1 |

## Remaining security findings

| Finding | Severity | Affected component | Remediation |
|---|---:|---|---|
| Broad staff middleware remains on operational modules | High | Projects, services, tasks, KB, AI admin and other non-financial data | Implement policies/permissions and assert direct-URL/AJAX access per resource |
| Existing ticket attachments may remain public | High | Files uploaded before this audit | Migrate `storage/app/public/ticket-attachments` into private storage, then remove the public paths after validating records |
| New self-registered users are marked email-verified | High | Registration | Require verified email before portal access; enable verification notification flow |
| CORS allows every origin | Medium | API paths | Restrict production origins to the application domains and add preflight tests |
| No production evidence for debug/secrets/headers | Medium | Environment/web server | Verify `APP_DEBUG=false`, secrets outside repository, TLS/HSTS/CSP/X-Frame-Options and safe error pages in deployment |

## RBAC target matrix

`C/R/U/D/A` = allowed operations; `X` = no access. This is the target policy matrix, not a claim that every permission is already enforced.

| Module | Super Admin | Admin | Finance | Support Manager | Support Agent | Project Manager | Employee | Sales/Agent | Freelancer | Customer |
|---|---|---|---|---|---|---|---|---|---|---|
| Users/roles/settings/audit | CRUD A | CRUD A | X | X | X | X | X | X | X | X |
| Services/catalogue | CRUD | CRUD | R | R | R | R | R | CRU | R | R |
| Tickets/SLA | CRUD A | CRUD A | R | CRUD A | RU assigned | R project | R assigned | R own leads | R assigned | CRU own |
| Quotes | CRUD A | CRUD A | R A | R | X | R project | R assigned | CRUD A | R assigned | R/A own |
| Projects/tasks | CRUD A | CRUD A | R finance | R linked | R assigned | CRUD A | RU assigned | R linked | RU assigned | R own |
| Invoices/payments/finance | CRUD A | CRUD A | CRUD A | X | X | R linked | X | R commission | R payout | R own |
| Commissions | CRUD A | CRUD A | CRUD A | R | X | R linked | R own | R own | R own | X |
| KB/AI | CRUD | CRUD | R | CRUD | R | R | R | R | R | R scoped |

## Production readiness

| Dimension | Score |
|---|---:|
| Business functionality | 58/100 |
| ITSM | 55/100 |
| CRM/customer portal | 52/100 |
| Finance/commission | 54/100 |
| Project management | 45/100 |
| Security | 60/100 |
| RBAC | 45/100 |
| UX/UI | 65/100 |
| Performance/API | 45/100 |
| Testing/DevOps | 40/100 |
| **Overall readiness** | **52/100** |

### Final result

**NOT READY for production.** The platform has a useful implemented foundation and all currently automated tests pass after the audit fixes, but it does not yet meet the stated acceptance criteria for complete P1 functionality, fine-grained RBAC, production payment verification, scheduled SLA operations, secure all-module document management, or end-to-end workflow tests.
