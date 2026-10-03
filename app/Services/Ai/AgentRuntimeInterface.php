<?php

namespace App\Services\Ai;

/**
 * AgentRuntimeInterface — replaceable external agent runtime contract.
 *
 * Hermes OR OpenClaw implement this. Laravel business code depends ONLY on
 * this interface (via AgentGateway), never on runtime-specific APIs.
 * Only implement methods the verified runtime actually supports; stubs MUST
 * report verified=false and refuse chat/stream/execute until staging proves
 * a documented endpoint. No invented API calls.
 */
interface AgentRuntimeInterface
{
    /** One-shot chat via the external runtime (verified only). */
    public function chat(array $messages, array $options = []): array;

    /** Streaming chat; default throws when unsupported. */
    public function stream(array $messages, array $options = []): iterable;

    /**
     * Safe diagnostics. Never include secrets/keys/tokens.
     *
     * @return array {runtime, enabled, verified, reachable, latency_ms, checked_at, error?}
     */
    public function health(): array;

    /** Documented capabilities of the verified runtime (tools, approvals, mcp…). */
    public function capabilities(): array;

    /**
     * Execute an already-authorized tool. Gateway authorizes FIRST;
     * adapters must never authorize, never touch DB/shell directly.
     */
    public function executeApprovedTool(string $tool, array $params = []): array;

    /** Best-effort cancel of a running call. */
    public function cancel(string $callId): bool;

    /** Runtime status string for admin views (disabled|not_verified|ready|error). */
    public function status(): string;
}
