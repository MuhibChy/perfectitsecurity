<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class OllamaMcpService
{
    protected string $baseUrl;
    protected ?string $apiToken;
    protected int $timeout;

    public function __construct()
    {
        $config = config('ollama');
        $this->baseUrl = rtrim($config['base_url'] ?? 'http://127.0.0.1:11434', '/');
        $this->apiToken = $config['api_token'] ?? null;
        $this->timeout = $config['timeout'] ?? 30;
    }

    /**
     * Generate text from a prompt using the specified model.
     *
     * @param string $prompt The prompt to send to the model.
     * @param string|null $model Optional model name; if null, default model is used.
     * @param int $maxTokens Maximum number of tokens to generate.
     * @return string Generated text.
     */
    public function generate(string $prompt, ?string $model = null, int $maxTokens = 512): string
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => $this->apiToken ? ['Authorization' => "Bearer {$this->apiToken}"] : [],
        ]);

        $payload = [
            'model' => $model ?? config('ollama.default_model'),
            'prompt' => $prompt,
            'options' => [
                'num_predict' => $maxTokens,
            ],
        ];

        try {
            $response = $client->post('/api/generate', [
                'json' => $payload,
                'stream' => false,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['response'] ?? '';
        } catch (GuzzleException $e) {
            Log::error('Ollama MCP generation failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to generate text via Ollama.', 0, $e);
        }
    }

    /**
     * List locally available models.
     *
     * @return array Array of model names.
     */
    public function listModels(): array
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => $this->apiToken ? ['Authorization' => "Bearer {$this->apiToken}"] : [],
        ]);
        try {
            $response = $client->get('/api/tags');
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['models'] ?? [];
        } catch (GuzzleException $e) {
            Log::error('Ollama MCP list models failed: ' . $e->getMessage());
            return [];
        }
    }
}
