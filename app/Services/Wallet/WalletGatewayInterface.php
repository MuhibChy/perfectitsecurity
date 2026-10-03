<?php

namespace App\Services\Wallet;

use App\Models\Wallet;

interface WalletGatewayInterface
{
    public function name(): string;

    public function isAvailable(Wallet $wallet): bool;

    /** @return array{provider: string, session_id: string, redirect_url: string} */
    public function createTopUpSession(Wallet $wallet, float $amount, string $successUrl, string $cancelUrl, int $userId): array;

    /** Verify a return-from-provider callback server-side. */
    public function verifyReturn(array $payload): array;
}
