<?php

namespace App\Services\Wallet;

use App\Models\Wallet;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Stripe adapter for wallet top-ups. Money moves only after server-side
 * verification (webhook signature or API-retrieved session state).
 */
class StripeWalletGateway implements WalletGatewayInterface
{
    public function name(): string { return 'stripe'; }

    public function isAvailable(Wallet $wallet): bool
    {
        $stripe = app(StripePaymentService::class);
        return $stripe->isConfigured() && StripePaymentService::supportsCurrency($wallet->currency);
    }

    public function createTopUpSession(Wallet $wallet, float $amount, string $successUrl, string $cancelUrl, int $userId): array
    {
        $stripe = app(StripePaymentService::class);
        if (!$this->isAvailable($wallet)) {
            throw new \RuntimeException('Stripe top-ups are unavailable for this wallet.');
        }
        $stripe->configure();
        $amount = round($amount, 2);
        if ($amount <= 0) throw new \InvalidArgumentException('Amount must be positive.');

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $wallet->wallet_reference,
            'customer_email' => $wallet->owner?->email,
            'metadata' => [
                'purpose' => 'wallet_topup',
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'currency' => $wallet->currency,
            ],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($wallet->currency),
                    'unit_amount' => (int) round($amount * 100),
                    'product_data' => ['name' => 'Wallet top-up ' . $wallet->wallet_reference, 'description' => 'Add funds to FWallet'],
                ],
            ]],
        ]);

        return ['provider' => 'stripe', 'session_id' => $session->id, 'redirect_url' => $session->url];
    }

    public function verifyReturn(array $payload): array
    {
        $sessionId = $payload['session_id'] ?? null;
        if (!$sessionId) return ['verified' => false, 'reason' => 'missing_session'];
        try {
            app(StripePaymentService::class)->configure();
            $session = Session::retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::warning('Wallet Stripe return verification failed: ' . $e->getMessage());
            return ['verified' => false, 'reason' => 'retrieve_failed'];
        }
        $meta = $session->metadata ?? [];
        $metaArr = is_object($meta) ? (array) $meta : (array) $meta;
        if (($session->payment_status ?? null) !== 'paid') {
            return ['verified' => false, 'reason' => 'not_paid'];
        }
        if (($metaArr['purpose'] ?? null) !== 'wallet_topup' || empty($metaArr['wallet_id'])) {
            return ['verified' => false, 'reason' => 'not_a_topup'];
        }
        return [
            'verified' => true,
            'wallet_id' => (int) $metaArr['wallet_id'],
            'user_id' => (int) ($metaArr['user_id'] ?? 0),
            'amount' => round(($session->amount_total ?? 0) / 100, 2),
            'currency' => strtoupper($session->currency ?? 'USD'),
            'provider_event' => 'stripe-session:' . $session->id,
            'provider_txn' => is_string($session->payment_intent ?? null) ? $session->payment_intent : null,
        ];
    }
}
