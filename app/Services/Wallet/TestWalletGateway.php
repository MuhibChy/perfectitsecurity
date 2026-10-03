<?php

namespace App\Services\Wallet;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;

/**
 * Sandbox gateway for development/testing. NEVER available in production:
 * callbacks carry an HMAC signature over the pending transaction so forged
 * browser requests cannot mint money.
 */
class TestWalletGateway implements WalletGatewayInterface
{
    public function name(): string
    {
        return 'test';
    }

    public function isAvailable(Wallet $wallet): bool
    {
        return ! app()->isProduction() && (bool) config('wallet.test_gateway', false);
    }

    public function createTopUpSession(Wallet $wallet, float $amount, string $successUrl, string $cancelUrl, int $userId): array
    {
        if (! $this->isAvailable($wallet)) {
            throw new \RuntimeException('Test top-ups are disabled.');
        }
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }
        $event = 'test-event:'.$wallet->id.':'.str_replace('.', '', microtime(true));
        $pending = app(WalletService::class)->reserveTopUp($wallet, $amount, 'test', $event, $userId);
        $token = hash_hmac('sha256', $pending->id.':'.$pending->transaction_reference, config('app.key'));
        $url = $successUrl.(str_contains($successUrl, '?') ? '&' : '?').http_build_query([
            'topup_id' => $pending->id, 'token' => $token,
        ]);

        return ['provider' => 'test', 'session_id' => (string) $pending->id, 'redirect_url' => $url];
    }

    public function verifyReturn(array $payload): array
    {
        $pending = WalletTransaction::find($payload['topup_id'] ?? null);
        if (! $pending || $pending->payment_provider !== 'test') {
            return ['verified' => false, 'reason' => 'unknown_topup'];
        }
        $expected = hash_hmac('sha256', $pending->id.':'.$pending->transaction_reference, config('app.key'));
        if (! hash_equals($expected, (string) ($payload['token'] ?? ''))) {
            return ['verified' => false, 'reason' => 'bad_signature'];
        }
        $wallet = $pending->wallet;
        if ((int) $payload['user_id'] !== (int) $wallet->user_id) {
            return ['verified' => false, 'reason' => 'owner_mismatch'];
        }

        return [
            'verified' => true,
            'wallet_id' => $wallet->id,
            'user_id' => (int) $wallet->user_id,
            'amount' => round((float) $pending->amount, 2),
            'currency' => $wallet->currency,
            'topup_id' => $pending->id,
            'provider_event' => $pending->provider_event_id,
            'provider_txn' => 'test-txn:'.$pending->id,
        ];
    }
}
