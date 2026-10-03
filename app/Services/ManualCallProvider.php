<?php

namespace App\Services;

use App\Contracts\CallProvider;

/** Default provider: calls happen off-platform and are logged by staff. */
class ManualCallProvider implements CallProvider
{
    public function name(): string
    {
        return 'manual';
    }

    public function initiate(int $callerId, int $recipientId, array $context = []): ?string
    {
        return null;
    }

    public function status(?string $providerCallId): array
    {
        return ['provider' => 'manual', 'status' => 'logged'];
    }
}
