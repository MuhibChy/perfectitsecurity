# PERFECTITSECURITY

## INTERNATIONAL IT SUPPORT PLATFORM

### COMPLETE OPERATIONAL VERIFICATION MANUAL

| Field | Value |
|---|---|
| Version | 1.0 |
| Date | 2026-09-12 |
| Tester | ______________________ |
| Environment | [ ] Local / [ ] Staging / [ ] Production |
| Website URL | ______________________ |
| Test customer A | ______________________ |
| Test customer B | ______________________ |
| Test employee | ______________________ |
| Test admin | ______________________ |

> Use synthetic test data only (e.g. `[DEMO]` records, `*.example.test` emails). Never use real customer data. Demo records can be created with `php artisan db:seed --class=DemoDataSeeder` and removed with `php artisan demo:cleanup --confirm` (dry-run first without `--confirm`).

**Result key for every test:** `[ ] PASS` · `[ ] FAIL` · `[ ] PARTIAL` · `[ ] NOT IMPLEMENTED` · `[ ] NOT TESTED` · `[ ] NOT APPLICABLE`

Each test records: `Tester: ___  Date: ___  Evidence: ___  Notes: ___  Issue ID: ___  Severity: ___`

---

## A. WEBSITE (WEB-001 → WEB-012)

### WEB-001 — Home page loads with brand, navigation, 3D scene and AI widget
Steps: 1. Open `/`. 2. Confirm header nav (Home, Services, Industries, Knowledge Base, About, Case Studies, Careers, Portfolio, FAQ, Contact, Customer Login, Free Consultation). 3. Confirm moving 3D hero + AI Support button bottom-right. 4. Open AI, confirm welcome message.
Expected: 200, all elements present, no console errors. Result: ___ Tester: ___ Date: ___ Evidence: ___ Notes: ___

### WEB-002 — Every header nav destination loads
Steps: visit About, Services, Industries, Knowledge Base, Blog, Case Studies, Careers, Portfolio, Pricing, FAQ, Contact, Login, Get-a-Quote.
Expected: each 200 with site header/footer, 3D scene on hero pages. Result: ___

### WEB-003 — Services dropdown + category filter
Steps: 1. Open Services menu. 2. Click a category. 3. Confirm `services.index?category=` filters the list.
Expected: filtered services shown. Result: ___

### WEB-004 — Service details page
Steps: open any service → confirm description, pricing/quote CTA, related services.
Expected: 200, quote button routes to `/get-quote`. Result: ___

### WEB-005 — Industries, Case Studies, Careers, Portfolio pages + details
Steps: open each index and one detail page.
Expected: 200, content renders, no broken images. Result: ___

### WEB-006 — FAQ search and Contact form
Steps: 1. Search FAQ. 2. Submit Contact with valid + invalid data.
Expected: filtered FAQs; validation errors on bad input; success message + CRM lead created on valid submit. Result: ___

### WEB-007 — Knowledge Base public search
Steps: search "password", open an article, vote helpful.
Expected: relevant public articles only; vote recorded. Result: ___

### WEB-008 — Get-a-Quote submission
Steps: submit with service + country + requirements.
Expected: success message; ServiceRequest + Lead visible in admin pipeline. Result: ___

### WEB-009 — Footer links + legal pages
Steps: click Privacy, Terms, Cookies, Refund, SLA, Accessibility.
Expected: all 200, readable content. Result: ___

### WEB-010 — Sitemap and robots
Steps: open `/sitemap.xml` and `/robots.txt`.
Expected: valid XML listing pages; robots allows public, disallows admin/portal. Result: ___

### WEB-011 — Mobile navigation (390px)
Steps: emulate 390×844; open hamburger menu; visit 3 pages; open AI widget.
Expected: no horizontal scroll, menu works, chat usable. Result: ___

### WEB-012 — Reduced motion / no WebGL
Steps: enable reduced-motion (or disable WebGL); reload Home + dashboard.
Expected: static premium background; zero errors; all content usable. Result: ___

---

## B. AUTHENTICATION (AUTH-001 → AUTH-006)

### AUTH-001 — Customer registration + validation
Steps: register with valid data; then attempt duplicate email, weak password, bad email.
Expected: account created; duplicates/weak/invalid rejected with messages. Result: ___

### AUTH-002 — Email verification + login/logout
Steps: verify email (OTP/link), log in, confirm portal dashboard, log out.
Expected: unverified users gated; logout ends session. Result: ___

### AUTH-003 — Password reset
Steps: request reset, use token, set new password, log in.
Expected: token single-use; old password rejected after change. Result: ___

### AUTH-004 — MFA enrolment + challenge (staff)
Steps: open Two-Factor Auth from user menu; scan QR; confirm code; log out/in; enter code at challenge; disable with code.
Expected: challenge enforced until verified; wrong codes rejected. Result: ___

### AUTH-005 — Brute-force lockout
Steps: 6 failed logins on one account.
Expected: temporary lockout with warning; audit entry. Result: ___

### AUTH-006 — Session security
Steps: log in on valid session; try `/admin` as customer (403) and `/portal` as guest (redirect to login).
Expected: boundaries hold. Result: ___

---

## C. CUSTOMER (CUS-001 → CUS-012)

### CUS-001 — Profile view/update + password change
Expected: changes persist; validation enforced. Result: ___

### CUS-002 — View own projects + milestones + files
Expected: read-only; only own projects. Result: ___

### CUS-003 — Quotations: view, PDF, accept, reject
Steps: accept one sent quote; reject another.
Expected: statuses update; accepted quote convertible by staff. Result: ___

### CUS-004 — Invoices: view, PDF, online payment button
Expected: correct totals; Stripe button degrades gracefully without keys. Result: ___

### CUS-005 — Tickets: create, reply, attachment
Expected: ticket numbered; SLA deadlines set; staff reply visible. Result: ___

### CUS-006 — Orders: view, negotiate, accept price
Expected: ownership enforced; negotiation recorded. Result: ___

### CUS-007 — Documents: upload (txt/pdf), download, delete
Steps: upload valid file (works); upload `.exe` (rejected).
Expected: owner-only access (403 for others). Result: ___

### CUS-008 — Notifications center + preferences
Expected: notifications listed; preferences persist. Result: ___

### CUS-009 — Cross-customer isolation (Alpha vs Beta)
Steps: as Alpha, guess Beta's invoice/ticket/project/quotation/document URLs.
Expected: 404/403 everywhere; own records 200. Result: ___

### CUS-010 — Service catalogue browsing in portal
Expected: services listed with country pricing. Result: ___

### CUS-011 — Service request submission
Expected: creates request + lead; visible to staff. Result: ___

### CUS-012 — Subscription/contract visibility limits
Known limitation: customer portal has no proposals/contracts/subscriptions views (admin-side only). Mark NOT IMPLEMENTED if required. Result: ___

---

## D. EMPLOYEE (EMP-001 → EMP-010)

### EMP-001 — Staff login + dashboard
Expected: staff areas reachable per role. Result: ___

### EMP-002 — Lead pipeline: view, assign, status change, activity log
Expected: changes persist; status history recorded. Result: ___

### EMP-003 — Service request review → graduate to quotation
Expected: graduation creates customer + draft quotation; `quoted` stage works. Result: ___

### EMP-004 — Ticket assignment, reply, internal note, merge, reopen, tags
Expected: each action works; merge moves history; customer sees replies, never internal notes. Result: ___

### EMP-005 — Project + task management (assign, progress, approve)
Expected: assignment locks; approval may generate commission. Result: ___

### EMP-006 — Quotation/invoice edit + send (finance roles only)
Expected: support agents get 403 on finance routes. Result: ___

### EMP-007 — Expense submit + receipt download (finance)
Expected: receipts private; downloads audited. Result: ___

### EMP-008 — Knowledge Base read per role
Expected: employee sees public+customer+employee articles; never admin-only. Result: ___

### EMP-009 — Commission visibility (freelancer/agent)
Expected: own commissions visible to assignee roles. Result: ___

### EMP-010 — Notifications + profile
Expected: scoped to own account. Result: ___

---

## E. ADMIN (ADM-001 → ADM-019)

### ADM-001 — User CRUD + role assignment + activation toggle
### ADM-002 — Companies CRUD
### ADM-003 — Services + categories + country prices CRUD
### ADM-004 — Blog CRUD + publish
### ADM-005 — KB CRUD + visibility + versioning (check version history grows)
### ADM-006 — Content (case studies/careers/portfolio) CRUD
### ADM-007 — Leads + pipeline + activities
### ADM-008 — Quotations: draft → send → convert to invoice (paid-guard: only accepted)
### ADM-009 — Proposals: create → send → revise (version bump) → status flow
### ADM-010 — Contracts CRUD + status flow
### ADM-011 — Subscriptions + bill-now invoice generation
### ADM-012 — Invoices: create → send (email queued) → PDF → record payment → paid
### ADM-013 — Payments list + over-payment refusal (422)
### ADM-014 — Expenses: approve (posts to finance) / reject
### ADM-015 — Commissions: approve/reject/payout + rules
### ADM-016 — Financials, P&L, reports + CSV exports
### ADM-017 — Tickets admin, SLA report, breach list
### ADM-018 — AI management (conversations, gaps, settings, usage), backups (run/verify/monitor), health, audit logs, settings
### ADM-019 — MFA + users cannot escalate own role (verify mass-assignment guard)

Expected for all: 200 flows, validation errors on bad input, audit entries for money/security actions. Results: ___ each.

---

## F. AI (AI-001 → AI-010)

### AI-001 — Home widget visible + opens (desktop + mobile)
### AI-002 — Guest general question (services/pricing/process)
### AI-003 — KB-grounded question (answer references approved content)
### AI-004 — Unknown question (no hallucination; human-support offer)
### AI-005 — Identity verification (guest ticket confirm → login required)
### AI-006 — Authenticated request creation with ownership check
### AI-007 — Cross-customer isolation via AI (Alpha/Beta)
### AI-008 — Prompt injection attempts (system prompt, keys, other-customer data) → refused, no leakage
### AI-009 — Provider failure (invalid key) → friendly fallback, no secrets in output
### AI-010 — Rate limiting (31 rapid messages → 429 page/message)

---

## G. SECURITY (SEC-001 → SEC-008)

### SEC-001 — Authentication/authorization matrix (guest/customer/agent/admin across 10+ routes)
### SEC-002 — IDOR sweep (manipulate IDs in URLs for invoices/tickets/projects/docs/orders)
### SEC-003 — XSS (submit `<script>` in ticket/message/KB; confirm escaped output)
### SEC-004 — CSRF (POST without token → 419)
### SEC-005 — SQL injection (search fields with `' OR 1=1 --`; confirm bound queries)
### SEC-006 — File security (exe upload rejected; private-disk direct URL blocked; cross-user download 403)
### SEC-007 — Session/cookie flags + security headers present (HTTPS/HSTS in production)
### SEC-008 — Secrets scan (`sk_live`, private keys) across repo + logs clean

---

## H. FINANCE (FIN-001 → FIN-008)

### FIN-001 — Invoice math (fixed + percentage discounts, tax, totals recomputed independently)
### FIN-002 — Partial + full payments; balance invariant (total = paid + due)
### FIN-003 — Over-payment refused (422); paid invoices locked from editing
### FIN-004 — Refund workflow status (verify supported/unsupported and document)
### FIN-005 — Expense approval posts income/expense ledger entries
### FIN-006 — Commission calculation (fixed/percent/tiered + payout in transaction)
### FIN-007 — P&L + profitability reports tie to ledger
### FIN-008 — Multi-currency invoices (USD/GBP/BDT/EUR) display correctly

---

## I. OPERATIONS (OPS-001 → OPS-006)

### OPS-001 — Ticket lifecycle: create → assign → reply → resolve → close → reopen
### OPS-002 — SLA countdown, breach detection, daily overdue-invoice reminders (run commands, check output)
### OPS-003 — Recurring billing + FX refresh commands execute cleanly
### OPS-004 — Project/task assignment → progress → completion
### OPS-005 — Backup run → verify → monitor (all OK); restore procedure rehearsed on staging
### OPS-006 — Scheduler list matches production cron (`schedule:list`)

---

## BUSINESS SCENARIOS

### SCN-1 New customer: Home → AI → Get-a-Quote → register → verify → quote accepted → order → project → ticket → payment. Result: ___
### SCN-2 IT support: login → ticket (priority) → assignment → replies → SLA met → resolve → close → reopen check. Result: ___
### SCN-3 Cybersecurity sale: service → consultation → requirements → quote → proposal → contract → payment → project → deliverables. Result: ___
### SCN-4 Web development: lead → quote → proposal → contract → payment → milestones → tasks → review → completion. Result: ___
### SCN-5 Recurring support: subscription → bill-now → invoice → payment → next cycle. Result: ___

---

## SIGN-OFF

| Role | Name | Date | Signature | Result |
|---|---|---|---|---|
| Tester | | | | |
| Business owner | | | | |
| Release decision | [ ] GO / [ ] NO-GO | | | |

Known accepted limitations (pre-existing, documented): customer portal lacks proposal/contract/subscription views; AI needs provider key for live answers; MFA optional (not enforced); recurring billing schedule needs production cron; S3/offsite backup optional unconfigured; Laravel 9/PHP 8.0 EOL upgrade path pending.
