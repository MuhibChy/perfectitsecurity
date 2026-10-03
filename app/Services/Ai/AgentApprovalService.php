<?php

namespace App\Services\Ai;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AgentApprovalService — smallest safe application-side approval mechanism.
 *
 * - Approval originates from an authenticated human action (issue() requires
 *   an active User; HIGH-risk verify() requires admin).
 * - Token is HMAC-signed (APP_KEY) over approval_id|approver|tool|params_hash|expiry.
 * - Bound to EXACT params via sha256(canonical JSON); any change invalidates.
 * - 15-minute expiry, single-use (consumed from cache on verify).
 * - Metadata-only audit; never stores secrets or prompt content.
 * - No new tables; uses Cache + existing immutable AuditLog.
 */
class AgentApprovalService
{
    public const TTL_SECONDS = 900;

    /**
     * @return array {approval_id, token, tool, expires_at}
     */
    public function issue(User $approver, string $tool, array $params, int $ttlSeconds = self::TTL_SECONDS): array
    {
        abort_unless($approver && $approver->is_active, 403, 'Approver must be active.');
        $tool = $this->cleanTool($tool);
        $canonical = $this->canonicalize($params);
        $expires = time() + max(60, min($ttlSeconds, 3600));
        $approvalId = (string) Str::uuid();
        $paramsHash = hash('sha256', $canonical);
        $payload = implode('|', [$approvalId, $approver->id, $tool, $paramsHash, $expires]);
        $sig = hash_hmac('sha256', $payload, (string) config('app.key'));
        $token = base64_encode($payload.'|'.$sig);

        Cache::put($this->cacheKey($approvalId), true, $expires - time());
        $this->audit($approver, 'agent.approval_issued', "tool={$tool} approval={$approvalId}");

        return ['approval_id' => $approvalId, 'token' => $token, 'tool' => $tool, 'expires_at' => date('c', $expires)];
    }

    /** Verify + consume (single-use). Returns true only on full match. */
    public function verify(User $approver, string $token, string $tool, array $params, bool $requireAdmin = true): bool
    {
        if (! $approver || ! $approver->is_active) {
            return $this->deny($approver, $tool, 'inactive_approver');
        }
        if ($requireAdmin && ! $approver->isAdmin()) {
            return $this->deny($approver, $tool, 'admin_required');
        }

        $raw = base64_decode($token, true);
        if ($raw === false) {
            return $this->deny($approver, $tool, 'malformed_token');
        }
        $parts = explode('|', $raw);
        if (count($parts) !== 6) {
            return $this->deny($approver, $tool, 'malformed_token');
        }
        [$approvalId, $approverId, $tokenTool, $paramsHash, $expires, $sig] = $parts;

        $expected = hash_hmac('sha256', implode('|', [$approvalId, $approverId, $tokenTool, $paramsHash, $expires]), (string) config('app.key'));
        if (! hash_equals($expected, $sig)) {
            return $this->deny($approver, $tool, 'bad_signature');
        }
        if ((int) $approverId !== (int) $approver->id) {
            return $this->deny($approver, $tool, 'approver_mismatch');
        }
        if ($tokenTool !== $this->cleanTool($tool)) {
            return $this->deny($approver, $tool, 'tool_mismatch');
        }
        if (! hash_equals($paramsHash, hash('sha256', $this->canonicalize($params)))) {
            return $this->deny($approver, $tool, 'params_changed');
        }
        if (time() > (int) $expires) {
            return $this->deny($approver, $tool, 'expired');
        }
        if (! Cache::pull($this->cacheKey($approvalId), false)) {
            return $this->deny($approver, $tool, 'reused_or_unknown');
        }

        $this->audit($approver, 'agent.approval_consumed', "tool={$tool} approval={$approvalId}");

        return true;
    }

    private function cleanTool(string $tool): string
    {
        $tool = trim($tool);
        abort_if(! preg_match('/^[a-z_]{3,64}$/', $tool), 422, 'Invalid tool name.');

        return $tool;
    }

    /** Deterministic scalar-only encoding; rejects nested structures/secrets. */
    private function canonicalize(array $params): string
    {
        $flat = [];
        foreach ($params as $k => $v) {
            abort_if(! is_string($k) || ! preg_match('/^[a-z_]{1,64}$/', $k), 422, 'Invalid param key.');
            abort_if(! is_scalar($v) && $v !== null, 422, 'Only scalar approval params allowed.');
            $flat[$k] = $v === null ? null : (string) $v;
        }
        ksort($flat);

        return json_encode($flat, JSON_UNESCAPED_SLASHES);
    }

    private function cacheKey(string $approvalId): string
    {
        return "agent_approval:{$approvalId}";
    }

    private function deny(?User $approver, string $tool, string $reason): bool
    {
        $this->audit($approver, 'agent.approval_denied', "tool={$tool} reason={$reason}");

        return false;
    }

    private function audit(?User $approver, string $action, string $detail): void
    {
        try {
            AuditLog::log($action, 'ai', null,
                'actor='.($approver ? "{$approver->id}:{$approver->role}" : 'guest')." {$detail}");
        } catch (\Throwable $e) {
            Log::warning('AgentApproval audit failed: '.$e->getMessage());
        }
    }
}
