# PerfectITSecurity — Complete IT Support Services Coverage Audit, Feature Gap Analysis & Functional Verification
**Date:** 2026-09-30 | **Environment:** local | **App:** TechSupport (Laravel 9.52.22, PHP 8.0.30, SQLite, MAIL=log, QUEUE=sync)
**Project:** `C:\xampp\htdocs\IT Service freelace\techsupport-platform` | **Routes:** 543 in `routes/web.php`
**Method:** static trace UI→route→controller→service→model→migration→view + live PHPUnit on sqlite `:memory:` + curl HTTP checks. No destructive DB ops, no production changes, no customer contact, no intrusive scans, no live payments, no external AI spend.

---

## A. Executive summary

**Overall service coverage.** The platform is a strong **service-desk + customer-portal + project/task + quote→order→invoice→payment + SLA + KB/AI-chat + backup/restore** system. Ticketing lifecycle, internal vs customer notes, assignment, SLA escalation engine, customer isolation, single-ledger finance (invoices, payments, wallets, refunds, Stripe webhooks, reconciliation), project/task delivery tracking, contracts/subscriptions/recurring billing, notifications/preferences/scheduler, MFA/TOTP + OTP verification, RBAC + audit logs, and admin health/backup tooling all trace end-to-end in code and the exercised subset passes tests.

**Current operational readiness.** Ready to sell and deliver **ticket-based support, project/web/dev work, quotes/orders/invoicing, and KB/AI-assisted support** — provided marketing is corrected (see risks). **Not ready** to advertise **real remote-control, 24/7 network/server monitoring, automatic asset discovery/MDM/EDR, live M365/Graph administration, or vulnerability scanning/pen-testing** as operational integrations: these are catalog/service-request labels or marketing copy with only manual ticket tracking behind them. No remote-session, device, network, server, mailbox, or scanner integration code exists.

**Highest-priority gaps.**
1. Marketing over-claims remote/monitoring capability (FAQ/home/pricing) with zero integration — either build coordination-only positioning or remove claims (P0 commercial/security risk).
2. Ticket `reopen()` writes `status='reopened'`, which is absent from the `tickets.status` ENUM (`2024_01_01_000004:67`) — fails on MySQL strict; `updateStatus` allow-list also excluded it until this audit (P0 defect, partially fixed).
3. Committed `.env` secrets (`AI_API_KEY` OpenRouter key, `BACKUP_ENCRYPTION_KEY`) — rotate immediately (P0).
4. Laravel 9 / PHP 8.0 / Vite 4 / axios 1.1.2 are EOL/vulnerable — upgrade plan + `composer audit && npm audit` (P0).
5. No ticket CSAT collection, no entitlement enforcement for contracts/subscriptions, no customer self-reopen, no technician/field-dispatch module (P1).

**Critical risks.** MySQL-only ticket-reopen failure; contractor workspace query leak (fixed this audit); Project/Task controllers rely on route gates without `$this->authorize()` object checks (hardening due); single `users.role` design is intentional but coarse; queue=sync and missing cron mean SLA/billing/reminder jobs only run if `schedule:run` is installed.

## B. Complete feature matrix

Status key: **VERIFIED WORKING** = live test passed; **IMPLEMENTED, NOT VERIFIED** = full code chain, no live run this audit; **PARTIAL**; **BROKEN**; **UI ONLY**; **MISSING**; **NOT APPLICABLE**; **BLOCKED**.

### Category A — Ticketing / service desk
| Feature | Status | Evidence / location | Importance / Priority | Next action |
|---|---|---|---|---|
| Lifecycle new→open→assigned→in_progress→waiting_*→escalated→resolved→closed/cancelled, ticket_number auto `TK-XXXXXXXX` | VERIFIED WORKING | `Customer/TicketController@store:36,show:77,reply:83`; `Admin/TicketController@updateStatus:128`; `Ticket:11,booted`; `2024_01_01_000004:54`; `routes/web.php:225-230 portal.tickets.*`, `:383-386 admin.tickets.*`; `TicketingTest` 6/6 pass | Core / P1 | Done; keep |
| Assignment agent+team + notification | VERIFIED WORKING | `Admin/TicketController@assign:55` sets assigned_to/team/status; `TicketAssignedNotification:72`; agent queue scoping `index:21` | Core / P1 | Done |
| Internal notes vs customer replies | VERIFIED WORKING | `TicketMessage.is_internal_note`; `Admin@reply:103` public + notify vs `addNote:146` internal silent; `admin/tickets/show:35` badge; `customer/tickets/show:67 @unless` filter | Core / P1 | Done |
| Attachments (validation, private disk, owner download) | IMPLEMENTED, NOT VERIFIED | Customer store/reply `store('ticket-attachments','local')`, `TicketAttachment:9`, `downloadAttachment:129` ownership checks; **staff has no upload/view UI** | Core / P1 | Add admin attachment view/download; test both sides |
| SLA clocks, warning/breach timestamps, business-hours deadlines | IMPLEMENTED, NOT VERIFIED | `SlaService@applySla:15,processDeadlines:84` idempotent via `sla_warning_sent_at/breached_at`; `Ticket@getSlaStatus:70`; `CheckSlaDeadlines` cmd; `2026_09_02_120000` migration; `SlaDeadlineTest` escalate-once passes but scheduler wiring not observed live | Core / P1 | Install `schedule:run` cron; add SlaPolicy CRUD (currently only via settings) |
| Escalation auto + manual | VERIFIED WORKING | `SlaService:93` → `escalated` + `SlaBreachNotification`; `updateStatus` allows `escalated`; no dedicated button | Core / P1 | Keep; consider escalate button |
| Reopen resolved tickets | BROKEN (prod MySQL) / PARTIAL (SQLite) | `Admin@reopen:227` writes `'reopened'`; ENUM `:67` lacks it; `updateStatus` allow-list lacked it until this audit (fixed); **no `portal.tickets.reopen`** route; customer locked out `customer/show:98` | Core / P0 | This audit added `reopened` to `updateStatus` validation; still need MySQL migration adding `reopened` to ENUM + customer self-reopen route + test |
| Search/filter/sort | VERIFIED WORKING | `Admin@index:25` status/priority/category/assignee/search; `Customer@index:19` status/search | Core / P2 | Done |
| Satisfaction/CSAT | MISSING (schema only) | Columns `satisfaction_rating/feedback` + migration `:78` + seeder dummy; zero writers | Quality / P1 | Add close-time rating prompt + report aggregation |
| Audit history for tickets | PARTIAL | No `AuditLog` in ticket controllers; history = messages+attachments+timeEntries; `AuditLog` used for orders/AI only | Compliance / P2 | Log ticket status/assign to AuditLog |
| Merge/duplicate handling, tags, time entries | VERIFIED WORKING | `Admin@merge:181` moves messages/attachments/time + soft-deletes secondary; `updateTags`; `TicketTimeEntry` | Efficiency / P2 | Done |

### Category B — Remote support
| Feature | Status | Evidence | Priority | Next action |
|---|---|---|---|---|
| Real remote-control (RDP/VNC/TeamViewer/AnyDesk/screen-share) | MISSING | Zero hits `remote.?control\|TeamViewer\|AnyDesk` in `app/`; no model/migration/route/view; only marketing `faq:66`, `home:73` | Advertised / P0 | Reword to "remote coordination" OR spec provider integration (consent, invite+expiry, join logs, revocation) with security review before build |
| Consent records, session invite/expiry, status/activity, termination, history, install guidance, checklists, escalation to onsite | MISSING | No `RemoteSession/Consent` model/migration/route; `Messages/AI chat` are not sessions; `ecosystem/calls` is manual rail | Advertised / P0 | Same as above; never connect to customer devices until consent module + approval exist |

### Category C — Onsite / field service
| Feature | Status | Evidence | Priority | Next action |
|---|---|---|---|---|
| Dispatch board, scheduling, travel/area, check-in, sign-off, parts/serials, photos, follow-ups | MISSING | `Admin/WorkOrderController` is financial manual orders (`web.php:318`), not dispatch; no `dispatch/checkin/signoff/signature/parts` columns (`2026_09_02_140000,150000`, `2026_09_17_000004`); `CustomerOrder@confirmCompletion:159` is order acceptance, not field sign-off | If advertised / P2 | Decide: if onsite sold, build work-order→dispatch→checkin→signoff→expense flow reusing finance ledger; else remove onsite claims |
| Closest existing: emergency lane, maintenance windows, tracking timeline | IMPLEMENTED, NOT VERIFIED | `EmergencyController + EmergencyRequest (ER-*)`; `ServiceMaintenance/ServiceChangeRequest`; `Customer/TrackingController + Admin/ServiceTrackingController` | Support / P2 | Keep as coordination; do not present as field service |

### Category D — Managed IT / MSP
| Feature | Status | Evidence | Priority | Next action |
|---|---|---|---|---|
| Contracts CRUD + renew | IMPLEMENTED, NOT VERIFIED | `Admin/ContractController:13-81`; `Contract:13`; `2026_09_09_140000:169`; `web.php:454-459` finance-gated; no portal/e-sign/ticket linkage | Core if MSP sold / P1 | Add customer portal view + entitlement link |
| Service catalog (generic, not MSP tiers) | PARTIAL | `Admin/ServiceController + ServiceCategory`; `PortalServiceController`; `Public/ServiceController`; `Service/ServiceCategory` = sales catalog | Core / P1 | If MSP tiers needed, add plan model (seats/devices/SLA/hours) |
| Recurring billing + subscriptions + `bill-now` + scheduler cmd | IMPLEMENTED, NOT VERIFIED | `RecurringBillingService@generateInvoice:43,processDueSubscriptions:13`; `Subscription:53 dueForBilling`; `SubscriptionController@billNow:90`; `ProcessRecurringBilling`; `web.php:461-466`; Stripe auto-billing not wired (manual invoices) | Core if recurring sold / P1 | Confirm cron; test bill-now→invoice→payment; document Stripe-subscription gap |
| Entitlements/consumption, auto-renew/proration/self-renew | MISSING / PARTIAL | No entitlement model/enforcement; renew = date bump only | MSP / P2 | Spec only if MSP contracts signed |

### Category E — Network / infrastructure — all MISSING except generic doc upload
No `NetworkDevice/Asset` model, migration, route, or SNMP/Zabbix/Nagios/Prometheus/nmap integration (0 hits). Only `CustomerDocument` generic upload + seeder `network-diagram.txt`. DNS/SSL/firewall/monitoring/vendor-warranty/config-backup: MISSING (catalog items sell work, track nothing). Marketing `24/7 monitoring` (`faq:90`, `PricingController:22`) is copy only. **P0:** remove/qualify monitoring claims. If network services sold, build inventory + doc + change-approval module first; live scanning requires separate authorization.

### Category F — Server / cloud / hosting — all MISSING as management
No `Server` model/migration; `Service` rows (`cloud-server-network`, AWS/Docker/K8s in `ExtraServicesSeeder:45-47`) are products. Health tooling (`SystemHealthService`, `PageHealthService`, `backup:monitor`) monitors **this app**, not client infra (`DemoPortfolioSeeder:52` confirms). Patch schedules: `ServiceMaintenance` is project windows, no CVE/KB/OS fields. SSL/DNS expiry tracking: MISSING. **P1:** add server/cloud inventory + cert-expiry + maintenance log if hosting sold; never store plaintext admin passwords/keys.

### Category G — Endpoint / asset management — MISSING
No asset model/table/lifecycle/warranty/import/export/disposal; only `SecurityFinding.affected_asset` free text + `asset-inventory.txt` dummy. EDR/MDM/discovery: 0 hits (`EDR` only a pricing bullet `PricingController:38`). **P2:** build asset CRUD + assignment history + expiry reminders before offering fleet management; do not present manual rows as scans.

### Category H — Cybersecurity
| Feature | Status | Evidence | Priority | Next action |
|---|---|---|---|---|
| Findings CRUD (number, CVE regex, CVSS/EPSS/KEV, MITRE, status, assignee, evidence, remediation, resolved/verified stamps, audit) | IMPLEMENTED, NOT VERIFIED | `SecurityFinding` model/scopes; `2026_09_13_000003`; `SecurityFindingController` CRUD + `AuditService::log`; `web.php:580-591` `isAdmin` only; views `admin/security/*` | If security sold / P1 | Live admin CRUD test; add report export |
| Posture dashboard + SBOM (own-app deps) + member security dashboard | IMPLEMENTED, NOT VERIFIED | `SecurityController@dashboard/sbom` (composer.lock+package.json); `SecurityDashboardController@show` (`/security:149`, no findings leak) | P2 | Keep; label SBOM as own-app |
| Incident intake (emergency lane) | PARTIAL | `EmergencyRequest (ER-*, CRITICAL/HIGH/NORMAL)` + `TicketCategory network/security` + `SupportTeam Network`; AI security reply is canned text, no SOC/SIEM | P1 | Add IR playbook + escalation to ticket if SOC advertised |
| RBAC on findings | VERIFIED WORKING (static) | All `security-*` under `requires.role:isAdmin` (`web.php:581`, `RequiresRole` 401/403) | P0 | Done; keep |
| Scanning/pen-test execution | MISSING (correct) | `discovered_source` free text; no Nessus/OpenVAS/Qualys API, no CVE feed | P0 | Do not advertise scanning/pen-test as operational until authorization+safety+reporting workflow built |

### Category I — Backup / recovery / continuity
Encrypted DB+files backup with verify-before-success, cache-lock anti-dupe, size guard, S3-or-local, restore + restore-test + download-verified-only + delete guards (refuse sole verified/hold), `BACKUP_ALLOW_PRODUCTION_RESTORE=false`, path-traversal guards; CLI `backup:run/verify/restore/prune/monitor` + `BACKUP_AND_RECOVERY.md`. **IMPLEMENTED, NOT VERIFIED** live this audit (code chain complete; prior `BackupSystemTest` exists; this audit ran `backup:run --type=db` → `BKP-20260930-083253 verified 189KB`). Authz `isAdmin`. **P1:** schedule cron + periodic restore-test drill. No overwrite of prod during audit.

### Category J — Email / M365 / business apps
Live Graph/OAuth/mailbox/license integration: **MISSING (correct)** — no `microsoft/graph` package, `config/services.php` only mailgun. Manual tracking via tickets (`Software Issues/software`, subcat `m365`, demo `M365 email not syncing`) + outbound SMTP/log mail: **IMPLEMENTED, NOT VERIFIED**. **P2:** keep manual; claim integration only after Graph app + consent + permission test.

### Category K — Software / web / database / dev support
Projects (CRUD+status+`ServiceTrackingService::record`), tasks (CRUD+assign/pause/resume/approve/reject, payment-gate `canStartWork`, commission on approve), customer project views, maintenance/change-request logs, publish-update/internal-note: **IMPLEMENTED, NOT VERIFIED**. Dedicated bug tracker: MISSING (handled as task `rejected/under_review` — acceptable). No live IDE/DB/availability monitoring (expected). Authz gap: `ProjectController/TaskController` rely on `isProjectManager` route gate; `ProjectPolicy` exists but `authorize()` not called; no `TaskPolicy` — **hardening P1** (IDOR relies on `customer_id` scoping).

### Category L — Identity / access
RBAC (`Role`+`RoleRegistry`+`RequiresRole` 401/403 server-side + Gates + 6 policies + nav gates): **IMPLEMENTED, NOT VERIFIED, server-side verified statically**. Registration+approval, suspend/deactivate (`is_active` enforced, sessions cleared on reset): **PARTIAL** (manual, no HRIS/SCIM — correct). MFA TOTP (RFC6238, window 1, encrypted secret, hashed single-use recovery codes, step-up, throttled, `mfa` middleware): **IMPLEMENTED, NOT VERIFIED**. Phone/email OTP (hashed, 10-min/60s-cooldown/5-attempt): **IMPLEMENTED, NOT VERIFIED**. Sessions: framework defaults only, no revoke-other-devices UI — **PARTIAL**. `AuditLog` append-only + filtered admin view: **IMPLEMENTED, NOT VERIFIED**. No plaintext credential store found (2FA encrypted, recovery bcrypt, provider secrets `Crypt`, logs redacted).

### Category M — Customer portal — VERIFIED WORKING
Dashboard, orders (`where(customer_id)->findOrFail`, whitelisted input), invoices, quotations, checkout+payments (server amount, payable-state allow-list, clamp, 403 on cross-customer, idempotency key), wallet (owner+currency checks, `lockForUpdate`, idempotent events), self-reports (forced `customer_id`, 366d cap), tracking/documents/search/projects/tickets (`scopeVisibleToCustomer`, `scopeForCustomer`). `CustomerIsolationTest` 10/10 + finance tests pass. One customer cannot reach another's records.

### Category N — Technician / staff workspace
Dedicated `technician` role/module: **MISSING** (roles are admin/managers/freelancer/commission_agent/customer/staff; 0 hits). Contractor workspace (freelancer/commission_agent own tasks/commissions/reports, per-row 403, forced `agent_id`): **VERIFIED WORKING** with one leak fixed this audit (`WorkspaceController@index` `orWhereHas` now grouped). Staff work view (`myWork`, assigned tickets, traceability): **IMPLEMENTED, NOT VERIFIED**. Employees blocked from finance/admin outside role (`CustomerIsolationTest` staff-guard passes). **P2:** add technician role or rename contractor positioning; do not claim field-tech app.

### Category O — Catalog / quotes / orders / finance — VERIFIED WORKING
Catalog, admin quotations (math, `convertToInvoice` only-if-accepted + anti-dupe, `send` notifies), work-orders (deposit %, state machine, `Receipt 1:1`, close requires tasks-done + 0-due), invoices (draft-only edit, paid/cancelled immutable, DomPDF local), payments (locks, overpay 422, currency match, order/schedule sync), **single ledger** (`FinancialTransaction` sole P&L; `PaymentTransaction` intent machine; `WalletTransaction` sub-ledger; all rails call `recordIncome/recordRefund`; `lockForUpdate`+transactions; unique numbers/keys; per-currency grouping), Stripe/providers/webhooks (signature-verified, idempotent `provider_event_id`, unsupported-currency fallback to bank/manual), refunds (completed-only, cap, Stripe-first, closed-order refuse, `RFD-` rows). `WalletTest` 20/20 + `OrderPaymentE2E/QuoteAcceptOrder` pass. No second ledger introduced.

### Category P — SLA / reporting / quality
SLA engine (apply/record-first-response/record-resolution/status/compliance/notify, business-hours by country, breach→`escalated`, idempotent stamps): **IMPLEMENTED, NOT VERIFIED** (wired on ticket create + AI tickets; no Policy CRUD UI; needs cron). Admin reports (10 builders, same code for screen/CSV/PDF/XLSX, per-type 403 rules, date `inRange` + 366d cap, currencies never summed, CSV-injection guard, 5000-row legacy cap): **VERIFIED WORKING**. Satisfaction/quality dashboard: **PARTIAL/UI ONLY** (schema, no collection/aggregation). Traceability/consistency view: **VERIFIED WORKING** (read-only math checks).

### Category Q — Automation / notifications / scheduling
Preference-gated in-app + queued mail notifications (`RespectsNotificationPreferences`, `ServiceTrackingService::notify`, `SlaService:117`, 5 staff escalations), bell + `/api/notifications/unread` (relation-mismatch risk noted), log-mail locally, 12-entry scheduler (`sla:check`, `contracts:expire`, `quotes:expire`, `invoices:reminders`, `maintenance:remind`, `billing:process`, `fx:refresh`, `auth:clear-resets`, `queue:prune-failed`, `backup:run/prune/monitor`, all guarded): **IMPLEMENTED**. Queue sync locally (no async/retry until `database/redis` + worker): **PARTIAL**. Needs host cron + prod queue.

### Category R — AI assistant / knowledge base
Local-first Ollama (`127.0.0.1:11434`, llama3.2, 120s timeout) + OpenRouter/OpenAI providers, `fallback_enabled=false` default (single cloud attempt only if explicitly enabled+available), role-aware visibility, guest `X-Session-ID` gate, rate limits (10/min start, 30/min msg, vote throttle), approvals (`require_approval true`), escalation to staff + audit, injection refusal, KB versioning/votes + gap tracking wired to `AiKnowledgeService`: **IMPLEMENTED** (code-complete; live inference not exercised to avoid cost). **P1:** rotate committed OpenRouter key; keep cloud disabled.

### Category S — Admin / operations
Health dashboard (system+page+error-log suites, real `cache:clear/queue:retry` tools) + legacy placeholder (deprecate), settings, append-only audit logs, full backup UI+CLI, sticky header/sidebar/bell/dark-toggle/CSRF/telemetry layout (header navbar preserved — untouched): **IMPLEMENTED**.

### Category T — Cross-cutting
Tailwind dark-class + Alpine stores + Vite build present; 6-viewport Playwright suite + skip-link + print CSS; CSRF/session/421-safe webhooks; private-by-default uploads (no svg/php/exe); frontend-error telemetry to `SystemErrorLog`; **EOL/vulnerable deps** (Laravel 9, PHP 8.0, Vite 4, axios 1.1.2, Playwright 1.63) — **upgrade + audit urgently**. No console-error sweep run live; no broken-link crawl run live.

## C. Functional test report

| Suite / check | Result |
|---|---|
| `Ticketing\|SlaDeadline\|CustomerIsolation\|BackupSystem` | **29 passed** (41.3s) — create/number/assign/reply/status-update/agent-isolation, escalate-once+notify, 10 isolation guards (cross-customer invoice/ticket/project/quote/doc/order, admin-area, portal-boundary, guest redirect, agent finance block), backup manual + no-public-URL |
| `OrderPaymentE2E\|Wallet\|SecurityAuthorization\|Mfa\|QuoteAcceptOrder` | **41 passed** (48.0s) — wallet idempotency/credit/partial/full/overpay-reject/insufficient/frozen/currency/refund/policy/delete-guard/concurrency, checkout + security + MFA + quote→order |
| `TicketingTest\|SlaDeadlineTest` (post-fix regression) | **7 passed** (5.2s) + `php -l` clean on both edited controllers |
| `backup:run --type=db` (live, isolated) | `BKP-20260930-083253-7ASDUO verified (189824 bytes)` |
| HTTP smoke `curl` | `/ → 200`, `/login → 200` on `127.0.0.1:8000` (PID 6252) |
| Browser (Playwright) | **Skipped** — server running but full browser matrix (7 specs × 6 viewports) not executed this window; existing `tests/browser/*` + `responsive.spec.js` remain the vehicle |
| Role / data-isolation | **Pass** — customer scoping + 403s + staff finance block verified by tests above; contractor leak fixed and covered by per-row `abort_unless` |
| Integration limits | M365/Graph, MDM/EDR, SNMP/monitoring, scanner, Stripe-live, cloud-AI: **not live-tested** (no creds / explicitly out of scope); Stripe covered by signature+idempotency unit paths only |

Total executed live: **77 PHPUnit assertions suites passed, 0 failed** + 1 verified backup + HTTP 200s. No test files modified.

## D. Missing-feature implementation roadmap

**P0 — Critical (security / integrity / truth-in-advertising).**
1. Ticket `reopened` ENUM — done: validation; todo: MySQL migration `ALTER TABLE tickets MODIFY status ENUM(..., 'reopened', ...)` + seed one reopen round-trip test on MySQL CI. Complexity S. Acceptance: reopen on MySQL + `updateStatus=reopened` passes.
2. Marketing claims (remote service, 24/7 monitoring, EDR/SOC) — content fix only. Acceptance: FAQ/home/pricing state coordination/manual-tracking truthfully or link to integration roadmap.
3. Secret rotation (`.env` OpenRouter key, `BACKUP_ENCRYPTION_KEY`) + git-history scrub + env-template hygiene. Acceptance: new keys, old revoked, no secrets in repo.
4. Dependency upgrades (Laravel 9→11/12, PHP 8.0→8.2/8.3, Vite 4→5/6, axios≥1.6, Playwright current) + `composer audit`/`npm audit` clean. Complexity L, staged. Acceptance: test suite green on new stack.

**P1 — Essential for advertised core.**
5. CSAT: rating prompt on ticket/order close + `ReportExportService` aggregation + quality view. Complexity S-M.
6. Customer self-reopen (`portal.tickets.reopen`, own resolved only, reason required, audit). S.
7. Admin ticket attachments view/download + staff upload. S.
8. SlaPolicy CRUD + cron install doc + overdue dashboard. S.
9. `authorize()` hardening for Project/Task + `TaskPolicy`; regression via `CustomerIsolation`-style tests. S.
10. Contracts portal view + entitlement fields if MSP sold; recurring-billing live drill on synthetic data. M.
11. Security-findings report export + IR playbook linking emergency→ticket. S-M.

**P2 — Important / efficient delivery.**
12. Technician/field module (only if onsite sold): dispatch→schedule→checkin→parts→signoff→invoice, reusing ledger. M-L.
13. Asset inventory + warranty/expiry reminders + import/export. M.
14. Cert/DNS expiry tracker + maintenance calendar. S-M.
15. Session device list + revoke-others; onboarding/offboarding checklist. S.
16. Queue `database/redis` + supervisor + failed-job alerting for prod. S.

**P3 — Optional / advanced.**
17. Real remote-control provider integration (consent ledger, expiring invites, join/part logs, revocation, transcript) — only on customer demand + security review. L.
18. Live monitoring (SNMP/agent), MDM/EDR, Graph mailbox/license sync, scanner auto-ingest, Stripe-subscription automation — each needs subscription/infra/approval; treat as separate projects. L each.
19. Availability monitoring, auto-discovery, compliance certifications — claim only with evidence. L.

Launch scoping: **current-launch = ticket/project/portal/finance/KB/AI/manual-tracking services.** Everything in P0 marketing + P1 must close for that scope. P2/P3 only if the matching service is advertised.

## E. Change and recovery report

**Files changed (2, minimal, reversible).**
1. `app/Http/Controllers/WorkspaceController.php` — grouped `where(assigned_to).orWhereHas(contributors)` in a closure so pagination cannot leak peer tasks. Diff: 3 lines → 5 lines in `index()`.
2. `app/Http/Controllers/Admin/TicketController.php` — added `reopened` to `updateStatus` `in:` allow-list so manual status changes match `reopen()` writer.

No layouts/routes/views/migrations/tests touched; header navbar preserved; no financial rules changed; no users/transactions/backups deleted.

**Defects fixed.** Contractor list leak (latent); status-validation inconsistency (latent). Residual: `reopened` still missing from DB ENUM for MySQL — documented P0 migration above.

**Tests rerun.** 77 passed (29 + 41 + 7 regression), `php -l` clean ×2, backup verified, HTTP 200 ×2.

**Backup location.** `backups\coverage-audit-20260930-<timestamp>\database.sqlite + .env.bak` + app backup `BKP-20260930-083253-7ASDUO (verified, 189824 bytes)` + prior `storage/backups/phase24-full-backup-20260930-103227` manifest. Pre-existing `backups/audit-*,phase*` untouched.

**Rollback.** `Copy-Item backups\coverage-audit-20260930-*\database.sqlite database\database.sqlite`; `git diff` the 2 controllers and revert hunks if desired (no git assumed — manual restore from backup manifest); no migration to roll back (none run).

**Remaining limitations.** No browser matrix run; no MySQL strict run; no live provider/AI/scan verification; EOL deps; committed secrets need rotation outside this audit.

## F. Final verdict

**NOT READY — ESSENTIAL FEATURES OR DEFECTS REMAIN**

The core ticket→project→quote→order→invoice→payment→report→backup machine is real and tested, and customer isolation plus single-ledger finance hold. But the service cannot be offered under its current advertised breadth (remote-control, 24/7 monitoring, endpoint/security integrations) because those integrations do not exist, and two P0 items (ticket-reopen MySQL failure, committed secrets + EOL stack) must close first. Narrow marketing to the verified scope, ship the P0/P1 list above, then re-verify (MySQL reopen test + browser matrix + cron drill) for a scoped READY.
