<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    // Pricing per 1K tokens (approximate)
    private const PRICING = [
        'gpt-4o' => ['input' => 0.005, 'output' => 0.015],
        'gpt-4o-mini' => ['input' => 0.00015, 'output' => 0.0006],
        'gpt-3.5-turbo' => ['input' => 0.0005, 'output' => 0.0015],
    ];

    public function __construct()
    {
        $this->apiKey = config('services.ai.openai.api_key', '');
        $this->model = config('services.ai.openai.model', 'gpt-4o-mini');
        // Admin UI override (admin/ai/settings) wins when set.
        try {
            $adminModel = trim((string) \App\Models\AiSetting::get('ai_model', ''));
            if ($adminModel !== '') {
                $this->model = $adminModel;
            }
        } catch (\Throwable $e) {
            // Settings table unavailable — keep config default.
        }
        $this->baseUrl = config('services.ai.openai.base_url', 'https://api.openai.com/v1');
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->model;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 1024;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->failed()) {
                $status = $response->status();
                Log::warning('OpenAI chat API error', ['status' => $status]);
                throw AiProviderException::fromStatus($status);
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $usage = $data['usage'] ?? [];
            $inputTokens = $usage['prompt_tokens'] ?? 0;
            $outputTokens = $usage['completion_tokens'] ?? 0;
            $cost = $this->calculateCost($model, $inputTokens, $outputTokens);

            return [
                'content' => $content,
                'tokens_used' => $inputTokens + $outputTokens,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'model' => $model,
                'cost' => $cost,
            ];
        } catch (AiProviderException $e) {
            throw $e;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('OpenAI connection failed', ['category' => AiProviderException::CONNECTION_ERROR]);
            throw AiProviderException::connectionError();
        } catch (\Exception $e) {
            $category = stripos($e->getMessage(), 'timed out') !== false || stripos($e->getMessage(), 'timeout') !== false
                ? AiProviderException::TIMEOUT
                : AiProviderException::UNKNOWN;
            Log::warning('OpenAI request failed', ['category' => $category]);
            throw new AiProviderException($category);
        }
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function healthCheck(): array
    {
        // No network call: must not burn customer budget on a status page.
        $configured = ! empty($this->apiKey);

        return [
            'provider' => 'openai',
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
        $pricing = self::PRICING[$model] ?? self::PRICING['gpt-4o-mini'];

        return (($inputTokens * $pricing['input']) + ($outputTokens * $pricing['output'])) / 1000;
    }
}
