<?php

namespace App\Contracts;

/**
 * Voice-provider abstraction (§10). The platform ships with the `manual`
 * provider (human-logged calls, no vendor). A future telephony vendor
 * (Twilio, Vonage, …) implements this contract and is bound in a service
 * provider — application code never hard-codes a vendor.
 */
interface CallProvider
{
    public function name(): string;

    /** Initiate an outbound call; returns the provider-side call identifier (or null for manual). */
    public function initiate(int $callerId, int $recipientId, array $context = []): ?string;

    /** Retrieve provider-side status for a call identifier. */
    public function status(?string $providerCallId): array;
}
