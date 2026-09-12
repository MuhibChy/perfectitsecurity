<?php
return [
    // Base URL for the local Ollama server
    'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),

    // Default model to use when none is specified
    'default_model' => env('OLLAMA_MODEL', 'gemma2:27b'),

    // Optional API token if you secure Ollama with auth (rare for local dev)
    'api_token' => env('OLLAMA_API_TOKEN', null),

    // Request timeout in seconds
    'timeout' => env('OLLAMA_TIMEOUT', 30),
];
