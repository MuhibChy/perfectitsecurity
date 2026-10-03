# AI Agent Gateway Architecture — Thin Adapter Phase (No Business Changes)

> Baseline: read-only audit is authoritative. This doc extends — never replaces —
> `config/ollama.php`, `AiProviderFactory`, `AiChatService`, `AiKnowledgeService`,
> `AiAgentService`, existing AI routes/widget/tables, RBAC/policies, audit logs.
> Status of external runtimes at doc time: Ollama READY locally; Hermes / OpenClaw /
> Omniroute PRESENT but LIVE HEALTH NOT VERIFIED (previous `status`/`doctor` hung).

## 1. Existing AI architecture (preserved)

- `AiProviderInterface` + `AiProviderFactory` (`ollama|openai|openrouter`) backed by
  `config/ollama.php` + `config/services.php`. `OllamaProvider::healthCheck()` is the
  proven pattern for safe admin diagnostics (no secrets in output).
- `AiChatService` owns conversation lifecycle, injection refusal, greeting/security/
  escalation branches, ticket-draft via authorized pipeline.
- `AiKnowledgeService` owns visibility-filtered retrieval (`public/customer/employee/admin`
  + `ai_readable`, LIKE-escaped).
- `AiAgentService` owns ~20 controlled tools. LLM never touches DB; every method enforces
  role checks server-side and writes metadata-only `AuditLog`.
- Routes: 8 public throttled `/api/ai/*` + 20 admin `admin/ai/*`; widget
  `ai-chat-widget.blade.php`; tables `ai_conversations/messages/escalations/gaps/usage/
  settings/skills`; hardening tests enforce cross-account 403 + audit + notifications.

## 2. Gateway architecture (new thin layer)

New files only (no edits to business services):

- `config/agent.php` — env-driven runtime selection + kill switches + limits.
- `App\Services\Ai\AgentRuntimeInterface` — `chat/stream/health/capabilities/
  executeApprovedTool/cancel/status`. Contract only; no invented endpoints.
- `App\Services\Ai\HermesAgentAdapter`, `OpenClawAdapter` — stubs. Default DISABLED,
  `health()` returns `verified:false, reason:not_verified`, `chat/stream/execute`
  throw `RuntimeException('agent runtime not verified')` unless explicitly enabled AND
  verified on staging. They must never be called when `AI_AGENT_ENABLED=false`.
- `App\Services\Ai\AgentGateway` — single entry used by future controllers:
  kill-switch → auth → tool allowlist → risk → approval gate → delegate to
  `AiAgentService` (tools) or `AiProviderFactory` (chat) or external adapter (only when
  verified). All decisions audit-logged via existing `AuditLog::log()`.

## 3. Provider architecture (unchanged + optional router)

```
AiChatService → AiProviderFactory → OllamaProvider (primary, direct 127.0.0.1:11434)
                                  → OpenAiProvider / OpenRouterProvider (approved externals)
Future: AiProviderFactory → OmnirouteProvider (OpenAI-compat base_url) → Ollama/external
```

Omniroute is OPTIONAL and never a single point of failure: direct Ollama path stays.
No new provider config file; reuse `config/ollama.php` + `services.php` + new
`config/agent.php` (routing flags only).

## 4. Tool architecture

External agent has ZERO direct DB/shell/filesystem access. Only path:

```
Hermes/OpenClaw → AgentGateway::executeApprovedTool() → AiAgentService method
→ existing Service + Policy → Database
```

`AiAgentService` remains the only tool implementation. Gateway adds: allowlist
(`agent.allowed_tools`), role allowlist, ownership check (actor.id === target owner or
staff scope), risk classification, approval requirement. No SQL/shell construction from
AI output — ever.

## 5. Authentication flow

Session/Sanctum → `auth` middleware → `User` (+ `is_active`) → MFA (`mfa` session) →
email/phone OTP (`customer` + `verified`) for portal scope. Guests: `X-Session-ID`
conversation binding only (no business tools). Gateway receives `?User $user` (nullable
for guests) and denies all non-`public` tools when null. Never trusts `approved=true`
or `user_id` from AI payload.

## 6. Authorization flow

```
Authenticated user → Role (RoleRegistry, User::is*) → Policy (Ticket/Project/Invoice/
Quotation/Service/Wallet) → Target ownership/scope (customer_id/user_id checks)
→ Tool allowlist → Risk → Approval → Execute → Audit
```

`AgentGateway::authorizeTool($user,$tool,$target)` mirrors policy logic by delegating to
`AiAgentService` require/assert methods; it never duplicates policy rules in prompts.

## 7. Customer-data flow

`get_authenticated_user → get_customer_profile/services/tickets/projects/contracts/
invoice/payment status` — all `where(customer_id, actor.id)`, metadata only (no internal
notes, no other-customer rows). `search_knowledge_base` filters `public+customer` +
`ai_readable`. Ticket create/message/escalate bind `customer_id = actor.id`.

## 8. Employee-data flow

`get_assigned_tickets/tasks`, `summarize_customer_issues(customerId)` require
`requireStaff()`; admin summaries require `requireAdmin()`. Cross-employee access denied
unless staff scope + assignment. Training/internal KB (`employee` visibility) never sent
to customers.

## 9. AI-data flow

Prompts assembled in `AiChatService` from: role, allowed KB articles, owned-record
summaries, skill instructions. No secrets, no other-tenant rows, no raw tool output
beyond capped metadata. Responses labeled AUTHORITATIVE (KB-sourced + ref) / GENERAL
(model) / UNCERTAIN (fallback + escalate offer). Never claims unperformed diagnostics.

## 10. Audit flow

Reuse immutable `audit_logs` (`AuditLog::log(action, module, model, desc)` + ip/ua).
Gateway logs: `agent.gateway_call` (agent, model, tool, target_type/id, risk,
approval_required, result). No passwords/keys/tokens/MFA secrets/prompt content. Hash
inputs where needed. Admin views reuse existing audit/traceability screens.

## 11. Approval flow

```
AI proposes → risk assess (LOW auto / MEDIUM auto+capped / HIGH block)
→ approval required? NO → execute + audit
→ YES → human authenticated action → approve → execute + audit (approver/at/result/ref)
```

HIGH (finance/refund/payment-state/permission/access/delete/config/server/security/
destructive) never auto-executes. Approval originates from human UI action, never AI JSON.

## 12. Failure/fallback flow

Any layer down (Hermes/OpenClaw/Omniroute/Ollama/external/timeout) → catch →
`ai.temporary_unavailable` → user-safe message: "AI assistance is temporarily
unavailable. Your normal support services remain available." No infra details to
customers. Core site/tickets/portal/finance unaffected. Kill switches:
`AI_AGENT_ENABLED` (master), `AI_CHAT_ENABLED`, `HERMES_ENABLED`, `OPENCLAW_ENABLED`,
`OMNIROUTE_ENABLED` — all default false/off except chat+ollama existing behavior.

## Data-flow diagram

```
User
 ↓
Laravel Authentication (session/Sanctum/MFA/OTP)
 ↓
RBAC / Policy (RequiresRole, staff/customer, Ticket/Project/Invoice/Quotation/Service/Wallet)
 ↓
AiChatService (injection guard, escalation, ticket-draft pipeline)
 ↓
AgentGateway (kill-switch, authorizeTool, risk, approval, audit)
 ↓
Agent Adapter (AgentRuntimeInterface → HermesAdapter / OpenClawAdapter — stubs until verified)
 ↓ (optional) Omniroute (OpenAI-compat router) → Ollama / approved external
 ↓
Allowed Tool (AiAgentService method only)
 ↓
Laravel Service / Policy (SlaService, FinancialService, TicketPolicy, …)
 ↓
Database (system of record; agent never direct)
```

All privileged actions re-check policy at the service layer even if the model is
manipulated. Prompts are untrusted input; authorization is authoritative.

## Phase 23 — Staging verification (2026-09-26, `AI_AGENT_ENABLED=false`, runtime `none`)

### Runtime evidence (single controlled probes, no invented endpoints)

- Ollama READY: `/api/tags` 132 ms, 12 models; `OllamaProvider::healthCheck()`
  reachable, `llama3.2:latest` present, 2489 ms. Live generation `Reply with
  exactly: OK` (temp 0, num_predict 16, num_ctx 2048): llama3.2 28 s (prompt 30 /
  eval 2), qwen2.5-coder:7b 67 s (34/2), qwen2.5-coder:14b 245 s (34/2) — all
  correct. Finding: 14b exceeds default `OLLAMA_TIMEOUT=120`; keep 14b staging-only
  or raise per-workload timeout before any production use. Caps: llama3.2 128k ctx +
  tools; qwen7b/14b 32k ctx + tools; nous-hermes:13b 4k completion-only (legacy).
- Hermes v0.20.0: `hermes version` verified. `model --help` shows Nous-portal OAuth +
  `/v1/models` inference; `status/doctor` hang → gateway/proxy/auth/tools/approvals/
  MCP/egress/logging NOT VERIFIED. Adapter stays fail-closed.
- OpenClaw 2026.7.1-2: version verified only; interface/sandbox/FS/network/shell/
  approvals/logging NOT VERIFIED, HIGH RISK. `OPENCLAW_ENABLED=false`, no prod link.
- Omniroute 3.8.50: `providers --help` shows catalog/test/validate/status via an
  "active local/remote server"; no server verified (`status` hangs). Listening
  endpoint, OpenAI-compat routing, fallback, timeouts NOT VERIFIED.
  `OMNIROUTE_ENABLED=false`; direct Ollama path mandatory.

### Hardening added (this phase, no business-logic changes)

- `AgentApprovalService`: HMAC-signed (APP_KEY), single-use (cache-consumed), 15-min
  expiring tokens bound to exact tool+scalar-params; HIGH verify requires admin;
  metadata-only audit (`agent.approval_issued/consumed/denied`). No new tables.
  MEDIUM tools keep executing via the existing user-confirmed pipeline
  (draft confirm + validation + SLA + audit + notification = implicit approval);
  HIGH stays denied; the token service is proven by tests for future HIGH gating.
- `AgentGateway`: tool-name shape check, limit clamp 1..10, query cap 200 chars,
  record-number allowlist, message/reason bounds, conversation instanceof + ownership
  check, enriched audit (actor id/role, runtime; never secrets/content).
- `generate_service_report` / `create_work_log` remain explicitly disabled (no
  implementation exists; not exposed).
- Admin health: via `AgentGateway::health()` (READY/DISABLED/NOT VERIFIED/ERROR
  semantics in adapters + provider check); no new HTTP endpoints added on purpose
  (new attack surface deferred until staging approval UI).

### Verification table

| Component        | Status       | Evidence |
| ---------------- | ------------ | -------- |
| Ollama           | READY        | tags 132 ms + 3 live generations OK + provider health reachable |
| Hermes           | NOT VERIFIED | v0.20.0 version only; live API/approvals/sandbox unverified; stub refuses |
| OpenClaw         | NOT VERIFIED | version only; HIGH RISK; stub refuses; disabled |
| Omniroute        | NOT VERIFIED | 3.8.50 only; no live server/route/fallback verified; disabled |
| Gateway          | PASS         | 9 + 7 gateway/staging tests pass |
| RBAC             | PASS         | role gate + service policies; matrix tests pass |
| Isolation        | PASS         | A↔B customer, employee matrix tests pass |
| Prompt injection | PASS         | detector flags classic bypass; authZ authoritative regardless (tests) |
| Tool injection   | PASS         | 10 attack tool names DENIED (tests) |
| Approval         | PASS         | 5 token tests (bind/expiry/single-use/tamper/roles) |
| Fallback         | PASS         | safe message, no infra leak (tests) |
| Kill switch      | PASS         | master/runtime/chat switches tested independently |
| Backup/restore   | PARTIAL      | file backup+verify OK (users=33 tickets=14 payments=9 audit=176 ai_conv=3, restore byte-identical); isolated staging boot not performed |
| Full regression  | NOT COMPLETED| exceeds 10-min window; targeted 54/54 AI tests pass in groups (Gateway 9, Approval 5, Staging 7, AiAgent 7, Hardening 4, Support 10, Skills/Kb 12) |

### Production blockers (unchanged, still closed)

`AI_AGENT_ENABLED=false`, runtime `none`; Hermes/OpenClaw/Omniroute unverified;
14b timeout finding open; full regression + isolated staging boot + staging
provider round-trip pending. No autonomous production execution.

## Phase 24 — Re-validation (2026-09-28, no code changes, `AI_AGENT_ENABLED=false`)

This phase added NO application logic. The gateway/adapter/config/test boundary
from Phase 23 was re-verified read-only; the only file touched is this doc.

### Runtime evidence (controlled probes, 15 s timeout each, no `status`/`doctor`)

| Runtime | COMMAND | TIMEOUT | RESULT | EXIT | SECURITY IMPLICATION |
| ------- | ------- | ------- | ------ | ---- | -------------------- |
| Ollama | `Invoke-WebRequest /api/tags` | 10 s | HTTP 200, 1.38 s, 12 models (llama3.2, qwen2.5-coder:7b/14b, nous-hermes:13b, deepseek-r1:7b, deepseek-coder + remotes) | 0 | READY locally; loopback only, no secret in output |
| Hermes | `hermes --version` | 15 s | `Hermes Agent v0.20.0` | 0 | Version only; gateway/proxy/auth/tools/approvals/MCP/egress/logging NOT VERIFIED; adapter stays fail-closed |
| OpenClaw | `openclaw --version` | 15 s | `2026.7.1-2` | 0 | Version only; HIGH RISK; sandbox/FS/network/shell/approvals NOT VERIFIED; `OPENCLAW_ENABLED=false`, no prod link |
| Omniroute | `omniroute --version` | 15 s | `3.8.50` | 0 | Version only; endpoint/routing/fallback/timeout NOT VERIFIED; `OMNIROUTE_ENABLED=false`; direct Ollama path mandatory |

`status`/`doctor` probes were deliberately NOT re-run: the prior audit proved
they hang, and re-running them adds no evidence while risking blocked sessions.

### Test + health evidence (this session)

- `AgentGatewayTest` + `AgentApprovalTest` + `AgentStagingVerificationTest`: 21 passed.
- `AiAgentTest` + `AiAgentHardeningTest` + `AiSkillsRoutingTest` + `KbAiAuditTest`: 32 passed.
- `AgentGateway::health()` live: `agent_enabled=false`, runtime `none`,
  adapter `hermes/disabled/not_verified`, provider `openrouter/reachable`,
  no secret substring in payload (secret_scan=0). Full suite NOT re-run
  (exceeds window); verdict unchanged.
- Live `.env` note: `AI_PROVIDER=openrouter` with a cloud key present and
  reachable. Key value is never printed, logged, or committed. Rotation via
  secret store remains recommended; local Ollama path stays the safe fallback.

### Verdict

NOT READY for production autonomy — same blockers as Phase 23. Allowed:
READ/SEARCH/SUMMARIZE/DRAFT + LOW-risk controlled tools via the authorized
pipeline. Denied without explicit human approval: DELETE, financial change,
permission change, server change, security test, production config,
destructive actions.

## Phase 25 — Full AI integration audit (2026-09-28, read-only + tests, no code changes)

Master-prompt audit (role: integration engineer/architect/DevSecOps/QA).
No application code, config, model, or data changed. Temp probes lived outside
the workspace; production `.env` untouched; no secret printed.

### Integration inventory (verified)

| Integration | Purpose | Location | Provider/runtime | Status | Evidence |
| ----------- | ------- | -------- | ---------------- | ------ | -------- |
| Customer AI chat | Support Q&A, ticket draft, escalation | `AiChatController` + 8 throttled `api/ai/*` routes + `ai-chat-widget` | `AiProviderFactory` → openrouter (live) / ollama | PASS | 10/10 `AiSupportTest`; widget served to guests/staff (tests); no secrets in widget (0 matches) |
| Ollama provider | Local inference | `OllamaProvider`, `config/ollama.php` | llama3.2 etc. @127.0.0.1:11434 | PASS | `/api/tags` 1.38 s/12 models; live `OK` 33.7 s; health/model-presence tests |
| OpenRouter provider | Cloud fallback | `OpenRouterProvider`, `config/services.php` | meta-llama/3.2-3b | PASS | Gateway health reachable; mapping/cost/leak tests 5/5 |
| OpenAI provider | Alt cloud | `OpenAiProvider` | via `AI_*` env | PARTIAL | Code + factory mapping exist; testing-env 401 handled as safe fallback (log 08:14:29); no live key verification attempted |
| KB retrieval | Grounded answers | `AiKnowledgeService` | DB LIKE + visibility | PASS | 9/9 `KbAiAuditTest` (role matrix, isolation, untrusted wrapper) |
| Agent tools | Controlled actions | `AiAgentService` → services/policies | internal | PASS | Owner-scoped ticket/order/invoice/project status; cross-account 403 |
| Agent gateway | AuthZ boundary | `AgentGateway` + `AgentRuntimeInterface` | none (stubs refuse) | PASS | 21/21 gateway/approval/staging tests |
| Hermes/OpenClaw/Omniroute | External runtimes | stub adapters only, all `*_ENABLED=false` | v0.20.0 / 2026.7.1-2 / 3.8.50 | NOT VERIFIED | Version-only probes (15 s); no endpoint assumed; classified external deps, not app integrations |
| Admin observability | Health/usage/gaps/skills/test-bench | `Admin\AiController`, 20 `admin/ai/*` routes | internal | PASS | Health panel asserted in tests; no secrets in health payload (scan=0) |
| Staff copilot reporting | Summaries, trends | `AiAnalyticsService`, usage/gaps | internal | PASS | Existing tests; HIGH-risk auto-actions denied |

Claude Code/OpenCode/Hermes/OpenClaw on this machine are developer tools, NOT
customer-facing integrations — no app wiring exists, correctly left unconnected.

### Scenario sample (live, llama3.2, temp 0.2, ≤220 tokens)

- Windows-slow (25.8 s): safe steps, escalation offer, no destructive cmds — PASS.
- Compromise (33.4 s): disconnect, contact technician, no password ask, no scans — PASS.
- Ticket create/enquire, KB Q, booking/quote, email/net/outage: covered by
  pipeline tests (real refs, owner scope, confirm-gated) — PASS by test evidence,
  not live-replayed (no synthetic writes to prod DB per safety rules).

### Repairs

None. No confirmed defect found: failure paths fall back safely, allowlists
deny unknown/HIGH tools, audit is metadata-only. Two observations (not defects):
`AI_AGENT_ALLOWED_TOOLS` advertises `generate_service_report`/`create_work_log`
which the gateway denies (no implementation) — fail-closed, left as-is;
SC5 model text suggests power-down before evidence triage — model phrasing,
app prompt already mandates human review.

### Performance (measured, local)

- Tags 1.38 s; tiny inference 33.7 s (30 prompt/2 eval); SC1 25.8 s; SC5 33.4 s.
- Timeouts: Ollama 120/10 s, OpenRouter 60/10 s, agent 60/10 s; throttle 60/min;
  max_tokens clamped to ≤512; no retry loops. p95 NOT computed (n too small).
- Suite: 70 passed this session across 6 filters; full suite omitted (window) —
  residual risk recorded, not waived.
