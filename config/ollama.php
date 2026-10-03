<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Local Ollama server — single centralized configuration layer.
    | Never hard-code URLs, model names, or timeouts elsewhere in the app;
    | always read them from here (env-overridable, admin-overridable).
    |--------------------------------------------------------------------------
    */

    // Base URL for the Ollama server. Local dev default is loopback only —
    // never expose this port to the public Internet (see docs/AI_AGENT.md).
    'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),

    // Default chat model. Must be a model actually installed on the server
    // (verify with: GET /api/tags). Fast 3B default keeps chat interactive;
    // larger models are ~2 min/response on modest hardware.
    'default_model' => env('OLLAMA_MODEL', 'llama3.2:latest'),

    // Per-workload selection without code changes. The chat service passes
    // ['model' => config('ollama.models.fast')] for quick general answers.
    'models' => [
        'default' => env('OLLAMA_MODEL', 'llama3.2:latest'),
        'fast' => env('OLLAMA_MODEL_FAST', 'llama3.2:latest'),
        'strong' => env('OLLAMA_MODEL_STRONG', 'qwen2.5-coder:14b'),
    ],

    // Optional API token if the Ollama server sits behind auth.
    'api_token' => env('OLLAMA_API_TOKEN', null),

    // Total request timeout (large local models need room to generate).
    'timeout' => (int) env('OLLAMA_TIMEOUT', 120),

    // Fast-fail for unreachable servers so chat falls back gracefully.
    'connect_timeout' => (int) env('OLLAMA_CONNECT_TIMEOUT', 10),

    // Keep models warm in VRAM/RAM between requests (Ollama duration).
    'keep_alive' => env('OLLAMA_KEEP_ALIVE', '30m'),
];
