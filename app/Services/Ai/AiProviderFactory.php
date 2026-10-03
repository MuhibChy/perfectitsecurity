<?php

namespace App\Services\Ai;

use App\Models\AiSetting;

class AiProviderFactory
{
    private static ?AiProviderInterface $instance = null;

    public static function make(): AiProviderInterface
    {
        if (self::$instance) return self::$instance;

        $provider = AiSetting::get('ai_provider', config('services.ai.default_provider', 'ollama'));

        self::$instance = self::makeNamed($provider);

        return self::$instance;
    }

    /**
     * Build a provider by explicit name (used for the fallback path).
     * Never cached: the fallback must not silently replace the primary.
     */
    public static function makeNamed(string $provider): AiProviderInterface
    {
        return match ($provider) {
            'ollama' => new OllamaProvider(),
            'openai' => new OpenAiProvider(),
            'openrouter' => new OpenRouterProvider(),
            default => new OpenAiProvider(),
        };
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
