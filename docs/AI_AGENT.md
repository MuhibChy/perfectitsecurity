# AI Customer Service & Service Management Agent

Production feature, integrated into the PerfectITSecurity application.
No separate app, no direct model-to-database access.

## Architecture

- `app/Services/Ai/AiChatService.php` — conversation orchestration: injection
  screening, incident/escalation/quote/service/ticket intent branches,
  deterministic status answers (no provider call), RAG prompt build, provider
  call, uncertainty escalation offer, source titles.
- `app/Services/Ai/AiAgentService.php` — the ONLY write path. Controlled tools
  with server-side role checks, validation, audit logging, notifications.
- `app/Services/Ai/AiKnowledgeService.php` — role-scoped KB retrieval
  (guest→public; customer→public+customer; employee→+employee; admin→all),
  LIKE + keyword-overlap scoring. No embeddings/vector store.
- Providers: `AiProviderInterface`; `AiProviderFactory::make()` reads
  `AiSetting ai_provider` then `config/services.ai`. Implementations:
  `OpenAiProvider`, `OllamaProvider`. Failures return a safe fallback message;
  secrets are never exposed.
- Controller: `app/Http/Controllers/AiChatController.php`. Rate limits:
  10 conversation starts/min/IP, 30 messages/min/IP. Guest identity via
  `X-Session-ID` compared with `hash_equals`; customers own their
  conversations; staff may view any.
- Widget: `resources/views/components/ai-chat-widget.blade.php`, mounted in
  `layouts/public.blade.php` and `layouts/app.blade.php`. Home page hero CTA
  ("Ask our AI Service Assistant") dispatches `open-ai-chat`, handled by the
  widget. Monochrome black/white styling via centralized Tailwind tokens.

## Environment

```
AI_PROVIDER=ollama              # primary provider (openai/openrouter preserved)
AI_API_KEY=                     # openai only; never commit
AI_MODEL=gpt-4o-mini
AI_BASE_URL=https://api.openai.com/v1

OPENROUTER_API_KEY=             # cloud models; .env only, never elsewhere
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
OPENROUTER_TIMEOUT=60

OLLAMA_BASE_URL=http://127.0.0.1:11434   # loopback only for local dev
OLLAMA_MODEL=llama3.2:latest             # must be installed (GET /api/tags)
OLLAMA_MODEL_FAST=llama3.2:latest        # quick general answers
OLLAMA_MODEL_STRONG=qwen2.5-coder:14b    # heavier reasoning (slow locally)
OLLAMA_API_TOKEN=                        # only if the server sits behind auth
OLLAMA_TIMEOUT=120                       # total request budget (seconds)
OLLAMA_CONNECT_TIMEOUT=10                # fast-fail so chat falls back kindly
OLLAMA_KEEP_ALIVE=30m                    # keep models warm between requests
```

Central layer: `config/ollama.php` (no URLs, models, or timeouts hard-coded
anywhere else). Provider tree: `AiProviderInterface` → `OllamaProvider`,
`OpenAiProvider`, `OpenRouterProvider` (OpenAI-compatible completions with
per-model cost accounting and sanitized errors). Admin UI
(`admin/ai/settings`) switches provider, sets the model, and shows a live
health panel: provider, connection, model, model availability, latency,
last check. Health details are admin-only; customers only ever see
"AI Assistant temporarily unavailable."

## Local Ollama — measured results (2026-09-14, this PC)

Server `127.0.0.1:11434`: reachable. Installed: `llama3.2:latest` (3.2B),
`qwen2.5-coder:14b` (14.8B), `elli:latest` (14.8B), `gemma4:26b` (25.2B).
Response latency: llama3.2 interactive (seconds); qwen2.5-coder:14b ~119s;
gemma4:26b ~124s after model load (first call timed out at 280s during
load). Conclusion: only the 3B model is usable for live chat on this
hardware; 14B+ models are documented as strong-tier but need server-grade
hardware or patience. Streaming is deliberately NOT used: the chat UI is
request/response, and bolting on SSE would risk partial-history corruption
for no functional gain.

## Production warning

`127.0.0.1:11434` on a remote host means the REMOTE host, not this PC.
Options: (A) website + Ollama on the same secured server; (B) website +
private AI server over an authenticated private connection; (C) private
network tunnel. Never expose Ollama's port to the public Internet.
Per-environment config via env vars; never commit `.env`, keys, or dumps.

DB overrides (admin/ai/settings): `ai_provider`, `ai_model`, `ai_enabled`,
`ai_system_prompt` (appended to the system prompt as authoritative
administrator instructions), `daily_*_limit`, `monthly_*_limit`,
`escalation_enabled`, `ticket_creation_enabled`.

## Tool registry (AiAgentService)

Readers (scoped to authenticated customer): `getCustomerProfile`,
`getCustomerTickets/Orders/Projects/Contracts/Services/Quotes`,
`getCustomerInvoiceStatus`, `getTicketStatus`, `getOrderStatus`,
`getProjectStatus`, `searchServices`, `searchKnowledgeBase`,
`searchCompanyInformation`. Writers (explicit confirmation in chat first):
`createSupportTicket`, `createServiceRequest`, `createQuoteRequest`,
`addTicketMessage` (own open tickets only, `is_internal_note` forced false).
Escalation: `requestHumanSupport` (single `ai_escalations` row, audit
`ai.escalated`, staff notifications). Staff: `getAssignedTickets/Tasks`,
`summarizeCustomerIssues` (audited). Admin: `getOperationalSummary`,
`getUnresolvedRequests`, `getServiceEnquiryTrends`.

## Routing: Skills → Knowledge Base → local model

```
User → intent/draft branches → deterministic status answers
  → skill detection (AiSkillService: keyword score → priority → role gate)
  → KB search (role + ai_readable scoped, keyword scored)
  → top score ≥ 2 → INTERNAL: grounded generation + "✓ Company Knowledge Base"
  → else → GENERAL: fallback-framed generation + "AI-generated general guidance"
```

- Skills table `ai_skills` (migrations `2026_09_14_000001`,
  `2026_09_14_000002`), admin CRUD at `admin/ai/skills` (create/edit/
  enable/disable/delete/duplicate, priority, roles, trigger keywords,
  temperature/max-token overrides, versioning). Twenty skills seeded:
  customer-support, it-support, network-support, m365-support,
  cybersecurity-education, website-security, web-development,
  digital-marketing, cloud-server-support, backup-disaster-recovery,
  service-discovery, service-recommendation, quote-assistance,
  ticket-assistance, order-support, payment-invoice-assistance,
  project-support, knowledge-search, platform-navigation,
  human-escalation. Detection is deterministic backend scoring — the model
  never picks its own instructions.
- Relevance gate: `AiChatService::INTERNAL_RELEVANCE_THRESHOLD = 2` on the
  top KB keyword score. Below it, no KB excerpts reach the model and the
  system prompt switches to fallback framing (general knowledge only, never
  company policy/prices, admit gaps + support path).
- Source labels: every assistant message stores
  `metadata.answer_source` (internal/general/deterministic), skill slug,
  `kb_top_score`, `ollama_fallback`; the widget shows "✓ Company Knowledge
  Base" vs "AI-generated general guidance" pills.
- KB `ai_readable` flag (default true, admin checkbox on KB create/edit)
  excludes articles from retrieval without unpublishing them.
- Knowledge library (`AiKnowledgeSeeder`, 21 categories, 41 articles):
  company/portal/billing FAQs, troubleshooting with safe-checks vs
  professional-help splits, defensive security guidance. No fabricated
  certs, awards, prices, or customer stories; scope-dependent answers
  route to quote/ticket workflows.
- Admin testing bench (`admin/ai/test-bench`): enter a question, see
  detected skill, ranked KB hits with scores, threshold verdict, and the
  Ollama-used decision — computed without spending a model call.
- Pure greetings/thanks/farewells are answered deterministically (no model
  call), so the assistant responds even when the model server is down.
- Generation budget: provider sends `num_predict` (from max_tokens, default
  512) and `num_ctx` 8192 to keep local CPU inference inside the 120s
  request timeout. Dev `.env` uses `AI_PROVIDER=ollama`.
- Widget tolerates error-shaped responses (`{error}`) with a friendly
  fallback line instead of a generic crash message.

## Customer isolation

Enforced in backend queries (`where customer_id = auth id`), never by prompt.
Cross-customer lookups return null/404. Verified by `AiAgentTest`
(isolation) and `AiAgentHardeningTest` (cross-account draft → 403).

## Prompt-injection protection

`AiChatService::isPromptInjectionAttempt()` blocks override/secret-dump/DB
access attempts with a fixed refusal; KB excerpts are labeled UNTRUSTED DATA
in the system prompt and never followed as instructions.

## Storage and audit

Tables (migration `2024_02_01_000002_create_ai_system_tables`):
`ai_conversations` (uuid `session_id`, status active/escalated/closed),
`ai_messages` (role/content/metadata/sources/tokens/cost),
`ai_escalations` (single row per escalation, status default pending),
`ai_knowledge_gaps`, `ai_usage_records`, `ai_settings`. Audit actions:
`ai.ticket_created`, `ai.service_request_created`, `ai.quote_request_created`,
`ai.ticket_reply`, `ai.escalated`, `ai.customer_summary`,
`ai.operational_summary`. Audit metadata only — no prompt content or secrets.

## Rate limits and cost control

Controller throttles above; `AiAnalyticsService::checkUsageLimits()` enforces
daily/monthly cost caps; RAG limited to top-5 articles; provider
`temperature 0.7, max_tokens 1024`; status queries answered deterministically
with zero provider cost.

## Testing

`AiAgentTest` (tools, isolation, audit), `AiSupportTest`,
`KbAiAuditTest` (visibility, untrusted-data wrapping), `AiAgentHardeningTest`
(draft pipeline audit+notification, cross-account 403, controller escalation
staff notify+audit, admin prompt honored). Full suite: 203 passed.

## Deployment

Requires `AI_API_KEY` (or reachable Ollama), mail for notifications, queue for
queued notifications, scheduler for SLA/overdue jobs. Never commit `.env`,
keys, or customer data. Backup pre-change DB snapshot before AI upgrades.
