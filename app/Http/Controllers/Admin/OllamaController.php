<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OllamaMcpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OllamaController extends Controller
{
    protected OllamaMcpService $ollama;

    public function __construct(OllamaMcpService $ollama)
    {
        $this->ollama = $ollama;
    }

    /**
     * List available Ollama models.
     */
    public function listModels()
    {
        $models = $this->ollama->listModels();
        return response()->json(['models' => $models]);
    }

    /**
     * Generate a response from the selected model.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'model' => 'nullable|string',
            'max_tokens' => 'nullable|integer|min:1',
        ]);

        $prompt = $request->input('prompt');
        $model = $request->input('model');
        $maxTokens = $request->input('max_tokens', 512);

        try {
            $response = $this->ollama->generate($prompt, $model, $maxTokens);
            return response()->json(['response' => $response]);
        } catch (\Throwable $e) {
            Log::error('Ollama generation error: ' . $e->getMessage());
            return response()->json(['error' => 'Generation failed'], 500);
        }
    }
}
