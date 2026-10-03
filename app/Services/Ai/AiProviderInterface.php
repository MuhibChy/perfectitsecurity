<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    /**
     * Send a chat completion request.
     *
     * @param  array  $messages Array of {role, content} messages
     * @param  array  $options  Model, temperature, max_tokens, etc.
     * @return array {content, tokens_used, model, cost}
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Check if the provider is configured and available.
     */
    public function isAvailable(): bool;

    /**
     * Safe diagnostics for admin health views. Must never include secrets,
     * keys, tokens, or internal infrastructure beyond the configured
     * endpoint host (which admins already manage).
     *
     * @return array {provider, reachable, model, model_available,
     *               latency_ms, checked_at, error?}
     */
    public function healthCheck(): array;
}
