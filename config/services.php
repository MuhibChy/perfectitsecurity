<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'verify_service_sid' => env('TWILIO_VERIFY_SERVICE_SID'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'ai' => [
        // Local-first policy (AgentGateway::chat honors this): Ollama is the
        // primary provider. A cloud provider is contacted ONLY when
        // fallback_enabled is explicitly set true AND fallback_provider names
        // an approved cloud provider. A configured API key alone never
        // triggers cloud use. Local-only mode works without any credential.
        'default_provider' => env('AI_PROVIDER', 'ollama'),
        'fallback_enabled' => env('AI_FALLBACK_ENABLED', false),
        'fallback_provider' => env('AI_FALLBACK_PROVIDER', ''),
        // At most one attempt per provider (no retry loops); the gateway
        // makes at most two inference attempts in total, then stops.
        'max_attempts_per_provider' => (int) env('AI_MAX_ATTEMPTS_PER_PROVIDER', 1),
        // Prompt-size guard: oversized prompts are rejected before any
        // provider is contacted (no truncation that could change meaning).
        'max_prompt_chars' => (int) env('AI_MAX_PROMPT_CHARS', 8000),
        // Privacy: prompts, completions, keys, headers, and raw provider
        // bodies are never written to logs; only metadata + error category.
        'log_prompt_content' => env('AI_LOG_PROMPT_CONTENT', false),
        'openai' => [
            'api_key' => env('AI_API_KEY'),
            'model' => env('AI_MODEL', 'gpt-4o-mini'),
            'base_url' => env('AI_BASE_URL', 'https://api.openai.com/v1'),
        ],
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY', env('AI_API_KEY')),
        'model' => env('AI_MODEL', 'meta-llama/llama-3.2-3b-instruct'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'timeout' => (int) env('OPENROUTER_TIMEOUT', 60),
    ],

];
