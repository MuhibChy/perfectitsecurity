<?php

namespace App\Payments;

/**
 * Payment-provider abstraction (Phase 6).
 *
 * Business logic must depend on this interface, never on one concrete
 * gateway. Each provider implements hosted/redirect-friendly flows and
 * never handles raw card numbers, CVV or passwords.
 */
interface PaymentProviderInterface
{
    /** Stable provider key, e.g. 'manual', 'stripe', 'wallet', 'bank_transfer'. */
    public function key(): string;

    /** Human label for admin/customer UI. */
    public function label(): string;

    /** Whether this provider can charge the given ISO currency code. */
    public function supportsCurrency(string $currencyCode): bool;

    /**
     * Create a payment intent / checkout for an invoice balance.
     * Returns ['provider_reference' => string, 'redirect_url' => ?string, ...].
     */
    public function createPayment(\App\Models\Invoice $invoice, float $amount, array $options = []): array;

    /** Server-side verification of a provider callback (never trust the browser). */
    public function verifyPayment(string $providerReference, array $payload = []): array;

    /** Handle an incoming webhook payload idempotently. */
    public function handleWebhook(string $payload, ?string $signature = null): array;

    /** Refund a settled payment (full or partial). Never deletes history. */
    public function refund(\App\Models\Payment $payment, float $amount, string $reason): array;

    /** Canonical transaction-level status for a provider reference. */
    public function status(string $providerReference): string;
}
