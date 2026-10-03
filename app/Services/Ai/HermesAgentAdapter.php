<?php

namespace App\Services\Ai;

/**
 * HermesAgentAdapter — STUB. Hermes is PRESENT but LIVE HEALTH NOT VERIFIED
 * (status/doctor hung in audit). No Hermes API calls are made here on purpose:
 * implementing against undocumented endpoints would be fabrication.
 * Enable only after staging verification documents: executable, version,
 * gateway/proxy endpoint, auth, tool-calling, approvals, MCP, egress, logging.
 */
class HermesAgentAdapter implements AgentRuntimeInterface
{
    public function chat(array $messages, array $options = []): array
    {
        throw new \RuntimeException('Hermes runtime not verified — refusing chat.');
    }

    public function stream(array $messages, array $options = []): iterable
    {
        throw new \RuntimeException('Hermes runtime not verified — refusing stream.');
    }

    public function health(): array
    {
        return [
            'runtime' => 'hermes',
            'enabled' => (bool) config('agent.hermes_enabled', false),
            'verified' => false,
            'reachable' => false,
            'latency_ms' => null,
            'checked_at' => now()->toDateTimeString(),
            'error' => 'not_verified',
        ];
    }

    public function capabilities(): array
    {
        return ['runtime' => 'hermes', 'verified' => false, 'tools' => [], 'note' => 'staging verification required'];
    }

    public function executeApprovedTool(string $tool, array $params = []): array
    {
        throw new \RuntimeException('Hermes runtime not verified — refusing tool execution.');
    }

    public function cancel(string $callId): bool
    {
        return false;
    }

    public function status(): string
    {
        return config('agent.hermes_enabled', false) ? 'not_verified' : 'disabled';
    }
}
