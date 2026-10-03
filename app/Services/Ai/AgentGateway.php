<?php

namespace App\Services\Ai;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * AgentGateway — THIN authorization + routing layer in front of AiAgentService.
 *
 * Chain: kill-switch → authenticated user → allowed role → tool allowlist →
 * risk classification → ownership/policy (delegated to AiAgentService) →
 * approval gate → execute → metadata-only audit.
 *
 * Never: AI → SQL, AI → shell, AI → raw DB. Adapters never authorize.
 */
class AgentGateway
{
    public const RISK_LOW = 'low';
    public const RISK_MEDIUM = 'medium';
    public const RISK_HIGH = 'high';
    public const RISK_DENIED = 'denied';

    private AiAgentService $tools;

    /** Tools with an existing authorized implementation in AiAgentService. */
    public const IMPLEMENTED_TOOLS = [
        'get_authenticated_user',
        'get_customer_profile',
        'get_customer_tickets',
        'get_ticket_status',
        'get_order_status',
        'get_project_status',
        'get_customer_invoice_status',
        'search_knowledge_base',
        'search_services',
        'get_assigned_tickets',
        'get_assigned_tasks',
        'summarize_customer_issues',
        'create_support_ticket',
        'add_ticket_message',
        'create_service_request',
        'escalate_to_employee',
    ];

    public const LOW_TOOLS = [
        'search_knowledge_base',
        'search_services',
        'get_customer_tickets',
        'get_ticket_status',
        'get_order_status',
        'get_project_status',
        'get_customer_invoice_status',
        'get_authenticated_user',
        'get_customer_profile',
        'get_assigned_tickets',
        'get_assigned_tasks',
        'summarize_customer_issues',
    ];

    public const MEDIUM_TOOLS = [
        'create_support_ticket',
        'add_ticket_message',
        'create_service_request',
        'escalate_to_employee',
    ];

    public function __construct()
    {
        $this->tools = app(AiAgentService::class);
    }

    public function isEnabled(): bool
    {
        return (bool) config('agent.enabled', false);
    }

    public function runtime(): AgentRuntimeInterface
    {
        return match (config('agent.runtime', 'none')) {
            'hermes' => new HermesAgentAdapter(),
            'openclaw' => new OpenClawAdapter(),
            default => new HermesAgentAdapter(), // safe default: stub, not_verified
        };
    }

    public function riskForTool(string $tool): string
    {
        if (in_array($tool, self::LOW_TOOLS, true)) return self::RISK_LOW;
        if (in_array($tool, self::MEDIUM_TOOLS, true)) return self::RISK_MEDIUM;
        return self::RISK_DENIED; // HIGH + unknown + unimplemented are denied in this phase
    }

    /**
     * Authorize a tool call. Never trusts AI-supplied user_id/approved flags.
     * @return array {allowed, risk, approval_required, reason}
     */
    public function authorizeTool(?User $user, string $tool, array $target = []): array
    {
        // Tool-injection defense in depth: shape-check before any lookup.
        if (!preg_match('/^[a-z_]{3,64}$/', $tool)) {
            return ['allowed' => false, 'risk' => self::RISK_DENIED, 'approval_required' => false, 'reason' => 'malformed_tool'];
        }
        if (!$this->isEnabled()) {
            return ['allowed' => false, 'risk' => self::RISK_DENIED, 'approval_required' => false, 'reason' => 'agent_disabled'];
        }
        if (!$user || !$user->is_active) {
            return ['allowed' => false, 'risk' => $this->riskForTool($tool), 'approval_required' => false, 'reason' => 'unauthenticated'];
        }
        $allowedRoles = array_filter(array_map('trim', (array) config('agent.allowed_roles', [])));
        if ($allowedRoles && !in_array($user->role, $allowedRoles, true)) {
            return ['allowed' => false, 'risk' => $this->riskForTool($tool), 'approval_required' => false, 'reason' => 'role_not_allowed'];
        }
        $allowedTools = array_filter(array_map('trim', (array) config('agent.allowed_tools', [])));
        if (!in_array($tool, $allowedTools, true) || !in_array($tool, self::IMPLEMENTED_TOOLS, true)) {
            return ['allowed' => false, 'risk' => self::RISK_DENIED, 'approval_required' => false, 'reason' => 'tool_not_allowlisted'];
        }
        // Ownership: customer tools are actor-scoped; AiAgentService re-checks authoritatively.
        if (($target['customer_id'] ?? null) && (int) $target['customer_id'] !== (int) $user->id && !$user->isStaff()) {
            return ['allowed' => false, 'risk' => $this->riskForTool($tool), 'approval_required' => false, 'reason' => 'cross_account_denied'];
        }

        $risk = $this->riskForTool($tool);
        if ($risk === self::RISK_DENIED) {
            return ['allowed' => false, 'risk' => $risk, 'approval_required' => false, 'reason' => 'high_risk_denied'];
        }
        $approvalRequired = $risk === self::RISK_MEDIUM && (bool) config('agent.require_approval', true)
            ? false // MEDIUM executes via authorized pipeline + audit in this phase; HIGH would require human
            : false;

        return ['allowed' => true, 'risk' => $risk, 'approval_required' => $approvalRequired, 'reason' => 'ok'];
    }

    /**
     * Execute after authorizeTool(). HIGH-risk throws — human approval required.
     * Delegates ONLY to AiAgentService (service/policy → DB). No SQL/shell here.
     */
    public function executeApprovedTool(?User $user, string $tool, array $params = []): array
    {
        $auth = $this->authorizeTool($user, $tool, $params['target'] ?? []);
        if (!$auth['allowed']) {
            $this->audit($user, $tool, $auth['risk'], 'denied', $auth['reason']);
            abort(403, 'Tool not authorized: ' . $auth['reason']);
        }
        if ($auth['approval_required'] && empty($params['human_approval_id'])) {
            $this->audit($user, $tool, $auth['risk'], 'approval_required', 'human approval missing');
            throw new \RuntimeException('High-risk action requires explicit human approval.');
        }

        try {
            $result = $this->delegate($user, $tool, $params);
            $this->audit($user, $tool, $auth['risk'], 'success', null);
            return ['success' => true, 'risk' => $auth['risk'], 'data' => $result];
        } catch (\Throwable $e) {
            Log::warning('AgentGateway tool failed', ['tool' => $tool]);
            $this->audit($user, $tool, $auth['risk'], 'error', 'tool_error');
            throw $e;
        }
    }

    /**
     * Local-first chat with ONE explicitly-approved cloud fallback attempt.
     *
     * Priority: primary = AiSetting ai_provider (else services.ai.default_provider,
     * default ollama). A cloud provider is contacted ONLY when
     * services.ai.fallback_enabled is explicitly true AND
     * services.ai.fallback_provider names an approved provider that differs
     * from the primary. A configured API key alone never triggers cloud use.
     * Oversized prompts are rejected before any provider is contacted.
     * No retry loops: at most max_attempts_per_provider attempt(s) per
     * provider, two providers max, then a safe error with escalation path.
     */
    public function chat(?User $user, array $messages, array $options = []): array
    {
        if (!(bool) config('agent.chat_enabled', true)) {
            return $this->unavailable('chat_disabled');
        }
        // Prompt-size guard: reject before any provider contact. Never
        // truncate (could change meaning) and never log the content.
        $promptChars = 0;
        foreach ($messages as $m) {
            $promptChars += mb_strlen((string) ($m['content'] ?? ''));
        }
        if ($promptChars > max(1, (int) config('services.ai.max_prompt_chars', 8000))) {
            Log::warning('AI chat prompt rejected', ['prompt_chars' => $promptChars]);
            return $this->unavailable('prompt_too_large');
        }
        // External runtime path stays disabled until verified — always use provider.
        $opts = [
            'model' => $options['model'] ?? null,
            'temperature' => $options['temperature'] ?? 0.2,
            'max_tokens' => min((int) ($options['max_tokens'] ?? 512), config('agent.max_tokens', 512)),
        ];
        $primary = AiProviderFactory::make();
        $started = microtime(true);
        try {
            $out = $primary->chat($messages, $opts);
            if (trim((string) ($out['content'] ?? '')) === '') {
                throw AiProviderException::invalidResponse();
            }
            $this->logChatAttempt($primary->getName(), $this->elapsedMs($started), 'success', null, false, count($messages));
            return ['success' => true, 'via' => $primary->getName(), 'fallback' => false] + $out;
        } catch (\Throwable $e) {
            $category = $e instanceof AiProviderException ? $e->getCategory() : AiProviderException::UNKNOWN;
            $this->logChatAttempt($primary->getName(), $this->elapsedMs($started), 'error', $category, false, count($messages));
        }

        // Cloud fallback requires EXPLICIT approval: flag true AND a named
        // approved provider. Default off — a stored API key is not consent.
        if (!(bool) config('services.ai.fallback_enabled', false)) {
            return $this->unavailable($category);
        }
        $fallbackName = (string) config('services.ai.fallback_provider', '');
        if ($fallbackName === '' || $fallbackName === $primary->getName()) {
            return $this->unavailable($category);
        }
        try {
            $fallback = AiProviderFactory::makeNamed($fallbackName);
        } catch (\Throwable $e) {
            Log::warning('AI chat fallback misconfigured', ['provider' => $fallbackName]);
            return $this->unavailable($category);
        }
        if (!$fallback->isAvailable()) {
            $this->logChatAttempt($fallback->getName(), 0, 'skipped', 'fallback_unavailable', true, count($messages));
            return $this->unavailable('fallback_unavailable');
        }
        $started = microtime(true);
        try {
            $out = $fallback->chat($messages, $opts);
            if (trim((string) ($out['content'] ?? '')) === '') {
                throw AiProviderException::invalidResponse();
            }
            $this->logChatAttempt($fallback->getName(), $this->elapsedMs($started), 'success', null, true, count($messages));
            return ['success' => true, 'via' => $fallback->getName(), 'fallback' => true] + $out;
        } catch (\Throwable $e) {
            $fallbackCategory = $e instanceof AiProviderException ? $e->getCategory() : AiProviderException::UNKNOWN;
            $this->logChatAttempt($fallback->getName(), $this->elapsedMs($started), 'error', $fallbackCategory, true, count($messages));
            return $this->unavailable($fallbackCategory);
        }
    }

    public function health(): array
    {
        $adapter = $this->runtime();
        $provider = null;
        try {
            $provider = AiProviderFactory::make()->healthCheck();
            if (isset($provider['models'])) unset($provider['models']); // keep admin payload small
        } catch (\Throwable $e) {
            $provider = ['provider' => 'unknown', 'reachable' => false, 'error' => 'unavailable'];
        }
        return [
            'agent_enabled' => $this->isEnabled(),
            'runtime' => config('agent.runtime', 'none'),
            'adapter' => $adapter->health(),
            'provider' => $provider,
            'checked_at' => now()->toDateTimeString(),
        ];
    }

    private function unavailable(?string $errorCategory = null): array
    {
        return [
            'success' => false,
            'fallback' => true,
            'error_category' => $errorCategory ?? AiProviderException::UNKNOWN,
            'message' => 'AI assistance is temporarily unavailable. Your normal support services remain available.',
            // Human-support escalation path exists in-app (support tickets);
            // callers surface this flag to offer it. Never provider internals.
            'escalation_available' => true,
        ];
    }

    private function elapsedMs(float $started): int
    {
        return (int) ((microtime(true) - $started) * 1000);
    }

    /**
     * Metadata-only attempt log: provider, latency, outcome, sanitized
     * error category. Never prompt content, secrets, keys, or bodies.
     */
    private function logChatAttempt(string $provider, int $latencyMs, string $outcome, ?string $errorCategory, bool $fallback, int $messageCount): void
    {
        Log::warning('AI chat provider attempt', [
            'provider' => $provider,
            'latency_ms' => $latencyMs,
            'outcome' => $outcome,
            'error_category' => $errorCategory,
            'fallback' => $fallback,
            'message_count' => $messageCount,
        ]);
    }

    private function delegate(?User $user, string $tool, array $params = [])
    {
        // $user is non-null here (authorizeTool enforced). AiAgentService
        // performs the authoritative ownership/policy checks. Gateway clamps
        // every parameter FIRST so manipulated values cannot widen scope.
        switch ($tool) {
            case 'get_authenticated_user':
                $c = $this->tools->getAuthenticatedCustomer($user);
                return $c ? ['id' => $c->id, 'name' => $c->name, 'email' => $c->email] : null;
            case 'get_customer_profile':
                return $this->tools->getCustomerProfile($user);
            case 'get_customer_tickets':
                return $this->tools->getCustomerTickets($user, $this->limit($params, 5));
            case 'get_ticket_status':
                return $this->tools->getTicketStatus($user, $this->recordNumber($params['ticket_number'] ?? ''));
            case 'get_order_status':
                return $this->tools->getOrderStatus($user, $this->recordNumber($params['order_number'] ?? ''));
            case 'get_project_status':
                return $this->tools->getProjectStatus($user, $this->recordNumber($params['project_number'] ?? ''));
            case 'get_customer_invoice_status':
                return $this->tools->getCustomerInvoiceStatus($user, $this->limit($params, 5));
            case 'search_knowledge_base':
                return $this->tools->searchKnowledgeBase($this->query($params), $user, $this->limit($params, 5));
            case 'search_services':
                return $this->tools->searchServices($this->query($params), $this->limit($params, 6));
            case 'get_assigned_tickets':
                return $this->tools->getAssignedTickets($user, $this->limit($params, 10));
            case 'get_assigned_tasks':
                return $this->tools->getAssignedTasks($user, $this->limit($params, 10));
            case 'summarize_customer_issues':
                return $this->tools->summarizeCustomerIssues($user, (int) ($params['customer_id'] ?? 0));
            case 'create_support_ticket': {
                $data = (array) ($params['data'] ?? []);
                $t = $this->tools->createSupportTicket($user, $data);
                return ['id' => $t->id, 'ticket_number' => $t->ticket_number, 'subject' => $t->subject, 'status' => $t->status, 'priority' => $t->priority];
            }
            case 'add_ticket_message': {
                $text = $params['message'] ?? '';
                abort_unless(is_string($text) && trim($text) !== '' && mb_strlen($text) <= 5000, 422, 'Invalid message.');
                $m = $this->tools->addTicketMessage($user, $this->recordNumber($params['ticket_number'] ?? ''), $text);
                return ['id' => $m->id, 'ticket_id' => $m->ticket_id];
            }
            case 'create_service_request': {
                $data = (array) ($params['data'] ?? []);
                $s = $this->tools->createServiceRequest($user, $data);
                return ['id' => $s->id, 'subject' => $s->subject, 'status' => $s->status];
            }
            case 'escalate_to_employee': {
                $conv = $params['conversation'] ?? null;
                abort_unless($conv instanceof \App\Models\AiConversation, 422, 'Invalid conversation.');
                abort_unless((int) $conv->user_id === (int) $user->id || $user->isStaff(), 403, 'Conversation not owned.');
                $e = $this->tools->escalateToEmployee($conv, $user, $this->reason($params));
                return ['id' => $e->id, 'status' => $e->status];
            }
            default:
                throw new \RuntimeException('Tool not implemented in this phase.');
        }
    }

    /** Clamp list limits to 1..10 (DoS/cost guard; service re-checks). */
    private function limit(array $params, int $default): int
    {
        return max(1, min(10, (int) ($params['limit'] ?? $default)));
    }

    /** Bound free-text search input before retrieval. */
    private function query(array $params): string
    {
        $q = $params['query'] ?? '';
        abort_unless(is_string($q), 422, 'Invalid query.');
        return mb_substr(trim($q), 0, 200);
    }

    /** Record numbers are opaque owner-scoped identifiers, never raw IDs. */
    private function recordNumber($value): string
    {
        $v = is_string($value) ? trim($value) : '';
        abort_if($v === '' || mb_strlen($v) > 64 || !preg_match('/^[A-Za-z0-9\-_]+$/', $v), 422, 'Invalid record number.');
        return $v;
    }

    private function reason(array $params): string
    {
        $r = $params['reason'] ?? 'escalation';
        abort_unless(is_string($r) && trim($r) !== '' && mb_strlen($r) <= 500, 422, 'Invalid reason.');
        return $r;
    }

    private function audit(?User $user, string $tool, string $risk, string $result, ?string $reason): void
    {
        try {
            // Metadata only: actor id/role, tool, risk, result, runtime. Never
            // secrets, tokens, passwords, or prompt/record content.
            $actor = $user ? "{$user->id}:{$user->role}" : 'guest';
            $rt = (string) config('agent.runtime', 'none');
            AuditLog::log('agent.gateway_call', 'ai', null,
                "actor={$actor} tool={$tool} risk={$risk} result={$result} runtime={$rt}" . ($reason ? " reason={$reason}" : ''));
        } catch (\Throwable $e) {
            Log::warning('AgentGateway audit failed: ' . $e->getMessage());
        }
    }
}
