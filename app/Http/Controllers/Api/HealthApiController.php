<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Health\ErrorLoggingService;
use Illuminate\Http\Request;

class HealthApiController extends Controller
{
    protected ErrorLoggingService $errorService;

    public function __construct(ErrorLoggingService $errorService)
    {
        $this->errorService = $errorService;
    }

    /**
     * Ingest frontend JavaScript runtime errors safely without capturing sensitive keys/passwords.
     */
    public function logFrontendError(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'url' => 'nullable|string|max:500',
            'line' => 'nullable|integer',
            'col' => 'nullable|integer',
            'stack' => 'nullable|string|max:5000',
        ]);

        $module = 'Frontend UI';
        $uri = $validated['url'] ? parse_url($validated['url'], PHP_URL_PATH) : '/';

        $this->errorService->logError(
            module: $module,
            errorType: 'FrontendJS',
            message: $validated['message'],
            route: $uri,
            file: $validated['url'] ?? null,
            line: $validated['line'] ?? null,
            trace: $validated['stack'] ?? null
        );

        return response()->json(['status' => 'logged']);
    }
}
