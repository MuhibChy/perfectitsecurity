<?php

namespace App\Services;

/**
 * Single authoritative payment state model (Phase 7).
 *
 * Stored values stay backwards compatible (lowercase, e.g. 'completed',
 * 'partially_paid', 'paid'). This class exposes the canonical UPPERCASE
 * vocabulary required by the business spec and derives aggregate
 * invoice/order finance status ONLY from authoritative payment rows.
 *
 * Transaction-level (canonical): CREATED, PENDING, REQUIRES_ACTION,
 *   PROCESSING, SUCCEEDED, FAILED, CANCELLED, VOID, REFUND_PENDING,
 *   PARTIALLY_REFUNDED, REFUNDED, DISPUTED.
 * Aggregate (derived): UNPAID, PARTIALLY_PAID, PAID, OVERDUE,
 *   REFUNDED, PARTIALLY_REFUNDED, CLOSED.
 */
class PaymentState
{
    public const CREATED = 'CREATED';
    public const PENDING = 'PENDING';
    public const REQUIRES_ACTION = 'REQUIRES_ACTION';
    public const PROCESSING = 'PROCESSING';
    public const SUCCEEDED = 'SUCCEEDED';
    public const FAILED = 'FAILED';
    public const CANCELLED = 'CANCELLED';
    public const VOID = 'VOID';
    public const REFUND_PENDING = 'REFUND_PENDING';
    public const PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';
    public const REFUNDED = 'REFUNDED';
    public const DISPUTED = 'DISPUTED';

    public const UNPAID = 'UNPAID';
    public const PARTIALLY_PAID = 'PARTIALLY_PAID';
    public const PAID = 'PAID';
    public const OVERDUE = 'OVERDUE';
    public const AGG_REFUNDED = 'REFUNDED';
    public const AGG_PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';
    public const CLOSED = 'CLOSED';

    /**
     * Map a stored payment status to the canonical transaction state.
     */
    public static function canonicalTransactionStatus(string $stored): string
    {
        return match (strtolower($stored)) {
            'completed' => self::SUCCEEDED,
            'pending' => self::PENDING,
            'processing' => self::PROCESSING,
            'failed' => self::FAILED,
            'cancelled' => self::CANCELLED,
            'refunded' => self::REFUNDED,
            default => self::PENDING,
        };
    }

    /**
     * Derive aggregate invoice finance status from authoritative numbers.
     * Never trust frontend input — callers must pass DB values.
     */
    public static function deriveInvoiceStatus(float $total, float $paid, string $currentStored = 'sent', bool $isOverdue = false, bool $isClosed = false): string
    {
        $total = round($total, 2);
        $paid = round($paid, 2);
        if ($isClosed) return self::CLOSED;
        if (strtolower($currentStored) === 'refunded') return self::AGG_REFUNDED;
        if (strtolower($currentStored) === 'cancelled') return self::CANCELLED;
        if ($paid <= 0) return $isOverdue ? self::OVERDUE : self::UNPAID;
        if (round($total - $paid, 2) <= 0 && $total > 0) return self::PAID;
        return self::PARTIALLY_PAID;
    }

    /** Stored (lowercase) equivalent of a derived aggregate status. */
    public static function toStoredInvoiceStatus(string $canonical, string $fallback = 'sent'): string
    {
        return match (strtoupper($canonical)) {
            'PAID' => 'paid',
            'PARTIALLY_PAID' => 'partially_paid',
            'UNPAID' => 'sent',
            'OVERDUE' => 'overdue',
            'REFUNDED' => 'refunded',
            'CANCELLED' => 'cancelled',
            'CLOSED' => 'paid',
            default => $fallback,
        };
    }

    /** Verify TOTAL = PAID + DUE within 1p tolerance. */
    public static function balancesReconcile(float $total, float $paid, float $due): bool
    {
        return abs(round($total, 2) - (round($paid, 2) + round($due, 2))) < 0.015;
    }
}
