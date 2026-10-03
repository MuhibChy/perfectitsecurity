<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenRouter provider (OpenAI-compatible chat completions).
 * Backend-only: the key never leaves the server, and chat failures surface
 * as generic errors so no provider internals reach customers.
 */
class OpenRouterProvider implements AiProviderInterface
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private int $timeout;

    // Per-1K-token pricing (approximate, USD) for cost accounting.
    private const PRICING = [
        'meta-llama/llama-3.2-3b-instruct' => ['input' => 0.00005, 'output' => 0.00033],
        'meta-llama/llama-3.1-8b-instruct' => ['input' => 0.00005, 'output' => 0.00008],
    ];

    private const FALLBACK_PRICING = ['input' => 0.0005, 'output' => 0.0015];

    public function __construct()
    {
        $this->apiKey = (string) config('services.openrouter.api_key', env('OPENROUTER_API_KEY', env('AI_API_KEY')));
        // Reuses the OpenAI-compatible model setting; admin ai_model wins.
        $this->model = (string) config('services.openrouter.model', env('AI_MODEL', 'meta-llama/llama-3.2-3b-instruct'));
        try {
            $adminModel = trim((string) \App\Models\AiSetting::get('ai_model', ''));
            if ($adminModel !== '') {
                $this->model = $adminModel;
            }
        } catch (\Throwable $e) {
            // Settings table unavailable — keep config default.
        }
        $this->baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
        $this->timeout = (int) config('services.openrouter.timeout', 60);
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->model;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 512;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name').' AI Assistant',
            ])->connectTimeout(10)->timeout($this->timeout)->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->failed()) {
                $status = $response->status();
                Log::warning('OpenRouter chat API error', ['status' => $status]);
                throw AiProviderException::fromStatus($status);
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $usage = $data['usage'] ?? [];
            $inputTokens = (int) ($usage['prompt_tokens'] ?? 0);
            $outputTokens = (int) ($usage['completion_tokens'] ?? 0);

            return [
                'content' => $content,
                'tokens_used' => $inputTokens + $outputTokens,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'model' => $model,
                'cost' => $this->calculateCost($model, $inputTokens, $outputTokens),
            ];
        } catch (AiProviderException $e) {
            throw $e;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('OpenRouter connection failed', ['category' => AiProviderException::CONNECTION_ERROR]);
            throw AiProviderException::connectionError();
        } catch (\Throwable $e) {
            // Timeouts surface as generic throwables; categorize without
            // logging message content (may echo request details).
            $category = stripos($e->getMessage(), 'timed out') !== false || stripos($e->getMessage(), 'timeout') !== false
                ? AiProviderException::TIMEOUT
                : AiProviderException::UNKNOWN;
            Log::warning('OpenRouter request failed', ['category' => $category]);
            throw new AiProviderException($category);
        }
    }

    public function getName(): string
    {
        return 'openrouter';
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function healthCheck(): array
    {
        $configured = ! empty($this->apiKey);

        return [
            'provider' => 'openrouter',
            'reachable' => $configured,
            'model' => $this->model,
            'model_available' => $configured,
            'models' => [],
            'latency_ms' => null,
            'checked_at' => now()->toDateTimeString(),
            'error' => $configured ? null : 'missing_api_key',
        ];
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::PRICING[$model] ?? self::FALLBACK_PRICING;

        return (($inputTokens * $pricing['input']) + ($outputTokens * $pricing['output'])) / 1000;
    }
}
