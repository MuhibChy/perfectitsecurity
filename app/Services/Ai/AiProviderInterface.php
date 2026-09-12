<?php

namespace App\Services\Ai;

interface AiProviderInterface
{
    /**
     * Send a chat completion request.
     *
     * @param array $messages Array of {role, content} messages
     * @param array $options  Model, temperature, max_tokens, etc.
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
}
