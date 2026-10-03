<?php

namespace App\Http\Controllers;

use App\Services\PaymentWebhookService;
use Illuminate\Http\Request;

/**
 * Per-provider webhook endpoint: POST /payments/webhook/{provider}.
 * No session/auth (providers call server-to-server); signature checked
 * inside PaymentWebhookService. Throttled; never logs secrets.
 */
class ProviderWebhookController extends Controller
{
    public function handle(Request $request, string $provider, PaymentWebhookService $webhooks)
    {
        $provider = strtolower($provider);
        abort_unless(preg_match('/^[a-z0-9_]{2,60}$/', $provider), 404);
        $raw = $request->getContent();
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return response()->json(['ok' => false, 'error' => 'Invalid payload.'], 400);
        }
        $eventId = $payload['event_id'] ?? $payload['id'] ?? null;
        if (!$eventId) {
            // Synthesize a deterministic id so retries stay idempotent.
            $eventId = $provider . ':' . hash('sha256', $raw);
        }
        $signature = $request->header('X-Provider-Signature')
            ?? $request->header('X-Webhook-Signature')
            ?? $request->input('signature');
        $result = $webhooks->ingest($provider, (string) $eventId, $payload, $signature ? (string) $signature : null, $raw);
        if (!empty($result['duplicate'])) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }
        if (!empty($result['error']) && empty($result['settled']) && empty($result['needs_review'])) {
            return response()->json(['ok' => false, 'error' => 'Processing failed; retry with the same event id.'], 500);
        }
        return response()->json(['ok' => true]);
    }
}
