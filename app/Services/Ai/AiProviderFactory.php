<?php

namespace App\Services\Ai;

use App\Models\AiSetting;

class AiProviderFactory
{
    private static ?AiProviderInterface $instance = null;

    public static function make(): AiProviderInterface
    {
        if (self::$instance) return self::$instance;

        $provider = AiSetting::get('ai_provider', config('services.ai.default_provider', 'openai'));

        self::$instance = match ($provider) {
            'openai' => new OpenAiProvider(),
            default => new OpenAiProvider(),
        };

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
