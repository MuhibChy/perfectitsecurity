<?php

namespace App\Services\Ai;

/**
 * OpenClawAdapter — STUB, HIGH-RISK runtime. OpenClaw is PRESENT but LIVE
 * HEALTH NOT VERIFIED. It may have broad automation powers, so the boundary
 * is explicit: this adapter NEVER gets DB/shell/filesystem/secrets access.
 * All execution must go AgentGateway → AiAgentService → Service/Policy → DB.
 * Do not connect to production until a sandboxed, approval-gated endpoint is
 * proven on staging with filesystem/network/shell/approval evidence.
 */
class OpenClawAdapter implements AgentRuntimeInterface
{
    public function chat(array $messages, array $options = []): array
    {
        throw new \RuntimeException('OpenClaw runtime not verified — refusing chat.');
    }

    public function stream(array $messages, array $options = []): iterable
    {
        throw new \RuntimeException('OpenClaw runtime not verified — refusing stream.');
    }

    public function health(): array
    {
        return [
            'runtime' => 'openclaw',
            'enabled' => (bool) config('agent.openclaw_enabled', false),
            'verified' => false,
            'reachable' => false,
            'latency_ms' => null,
            'checked_at' => now()->toDateTimeString(),
            'error' => 'not_verified',
        ];
    }

    public function capabilities(): array
    {
        return ['runtime' => 'openclaw', 'verified' => false, 'tools' => [], 'note' => 'sandboxed staging verification required; no prod autonomy'];
    }

    public function executeApprovedTool(string $tool, array $params = []): array
    {
        throw new \RuntimeException('OpenClaw runtime not verified — refusing tool execution.');
    }

    public function cancel(string $callId): bool
    {
        return false;
    }

    public function status(): string
    {
        return config('agent.openclaw_enabled', false) ? 'not_verified' : 'disabled';
    }
}
