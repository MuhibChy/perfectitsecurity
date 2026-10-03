<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements AiProviderInterface
{
    private string $baseUrl;

    private string $model;

    private int $timeout;

    private int $connectTimeout;

    private string $keepAlive;

    public function __construct()
    {
        // Single source of truth: config/ollama.php (env-overridable).
        $this->baseUrl = rtrim((string) config('ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $this->model = (string) config('ollama.models.default', config('ollama.default_model', 'llama3.2:latest'));
        // Admin UI override (admin/ai/settings) wins when set.
        try {
            $adminModel = trim((string) \App\Models\AiSetting::get('ai_model', ''));
            if ($adminModel !== '') {
                $this->model = $adminModel;
            }
        } catch (\Throwable $e) {
            // Settings table unavailable — keep config default.
        }
        $this->timeout = (int) config('ollama.timeout', 120);
        $this->connectTimeout = (int) config('ollama.connect_timeout', 10);
        $this->keepAlive = (string) config('ollama.keep_alive', '30m');
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->model;
        $temperature = $options['temperature'] ?? 0.7;
        // Bound output length (generation time scales with it) and context
        // window (prompt-eval cost scales with it) for local hardware.
        $numPredict = (int) ($options['max_tokens'] ?? 512);
        $numCtx = (int) ($options['num_ctx'] ?? 8192);

        try {
            $response = Http::connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/api/chat", [
                    'model' => $model,
                    'messages' => $messages,
                    'stream' => false,
                    'keep_alive' => $this->keepAlive,
                    'options' => [
                        'temperature' => $temperature,
                        'num_predict' => $numPredict,
                        'num_ctx' => $numCtx,
                    ],
                ]);

            if ($response->failed()) {
                $status = $response->status();
                Log::warning('Ollama chat API error', ['status' => $status]);
                throw AiProviderException::fromStatus($status);
            }

            $data = $response->json();
            $content = $data['message']['content'] ?? '';
            $promptTokens = (int) ($data['prompt_eval_count'] ?? 0);
            $evalTokens = (int) ($data['eval_count'] ?? 0);

            return [
                'content' => $content,
                'tokens_used' => $promptTokens + $evalTokens,
                'input_tokens' => $promptTokens,
                'output_tokens' => $evalTokens,
                'model' => $model,
                'cost' => 0.0, // Local open-source model has 0 API cost
            ];
        } catch (AiProviderException $e) {
            throw $e;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Ollama connection failed', ['category' => AiProviderException::CONNECTION_ERROR]);
            throw AiProviderException::connectionError();
        } catch (\Throwable $e) {
            $category = stripos($e->getMessage(), 'timed out') !== false || stripos($e->getMessage(), 'timeout') !== false
                ? AiProviderException::TIMEOUT
                : AiProviderException::UNKNOWN;
            Log::warning('Ollama request failed', ['category' => $category]);
            throw new AiProviderException($category);
        }
    }

    public function getName(): string
    {
        return 'ollama';
    }

    public function isAvailable(): bool
    {
        try {
            $res = Http::connectTimeout(2)->timeout(5)->get("{$this->baseUrl}/api/tags");

            return $res->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Admin-facing diagnostics. Error strings are generic on purpose —
     * raw transport details stay in the server log, never in responses.
     */
    public function healthCheck(): array
    {
        $started = microtime(true);
        $checkedAt = now()->toDateTimeString();
        try {
            $res = Http::connectTimeout($this->connectTimeout)
                ->timeout(10)
                ->get("{$this->baseUrl}/api/tags");
            if (! $res->successful()) {
                return $this->unhealthy($checkedAt, 'server_error');
            }
            $models = collect($res->json('models') ?? [])->pluck('name')->all();
            $modelAvailable = in_array($this->model, $models, true);

            return [
                'provider' => 'ollama',
                'reachable' => true,
                'model' => $this->model,
                'model_available' => $modelAvailable,
                'models' => $models,
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'checked_at' => $checkedAt,
                'error' => $modelAvailable ? null : 'model_missing',
            ];
        } catch (\Throwable $e) {
            Log::warning('Ollama health check failed', ['error' => $e->getMessage()]);

            return $this->unhealthy($checkedAt, 'unreachable');
        }
    }

    private function unhealthy(string $checkedAt, string $error): array
    {
        return [
            'provider' => 'ollama',
            'reachable' => false,
            'model' => $this->model,
            'model_available' => false,
            'models' => [],
            'latency_ms' => null,
            'checked_at' => $checkedAt,
            'error' => $error,
        ];
    }
}
