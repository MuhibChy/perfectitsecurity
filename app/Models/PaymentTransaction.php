<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ONE internal record per payment intent (provider-independent).
 *
 * Lifecycle (centralized — controllers must use transitionTo()):
 * initiated → pending → processing → authorized → paid → (refunded |
 * partially_refunded) | failed | cancelled ; manual rail:
 * pending_verification → verified (settles) | rejected ;
 * requires_verification = uncertain provider state, needs human review.
 */
class PaymentTransaction extends Model
{
    use HasFactory;

    public const STATUSES = [
        'initiated', 'pending', 'processing', 'authorized', 'paid',
        'failed', 'cancelled', 'refunded', 'partially_refunded',
        'pending_verification', 'verified', 'rejected', 'requires_verification',
    ];

    public const TERMINAL = ['paid', 'failed', 'cancelled', 'refunded', 'rejected'];

    /** Allowed transitions (anything else is rejected loudly). */
    public const TRANSITIONS = [
        'initiated' => ['pending', 'processing', 'failed', 'cancelled', 'pending_verification'],
        'pending' => ['processing', 'authorized', 'paid', 'failed', 'cancelled', 'pending_verification', 'requires_verification'],
        'processing' => ['authorized', 'paid', 'failed', 'cancelled', 'requires_verification'],
        'authorized' => ['paid', 'failed', 'cancelled'],
        'paid' => ['refunded', 'partially_refunded'],
        'partially_refunded' => ['refunded', 'partially_refunded'],
        'pending_verification' => ['verified', 'rejected', 'failed'],
        'verified' => ['paid', 'failed'],
        'requires_verification' => ['paid', 'failed', 'cancelled', 'pending_verification'],
        'failed' => [],
        'cancelled' => [],
        'refunded' => [],
        'rejected' => [],
    ];

    protected $fillable = [
        'reference', 'idempotency_key', 'customer_id', 'service_order_id',
        'invoice_id', 'service_id', 'project_id', 'provider_id', 'provider_key',
        'payment_method', 'provider_reference', 'provider_event_id',
        'original_amount', 'original_currency', 'exchange_rate',
        'converted_amount', 'settlement_currency', 'provider_currency',
        'provider_amount', 'gross_amount', 'provider_fee', 'platform_fee',
        'net_amount', 'status', 'paid_at', 'refunded_amount',
        'failure_reason', 'metadata', 'payment_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'original_amount' => 'decimal:2',
        'converted_amount' => 'decimal:2',
        'provider_amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'provider_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        static::creating(function ($txn) {
            if (empty($txn->reference)) {
                $txn->reference = 'PTXN-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
            if (empty($txn->idempotency_key)) {
                $txn->idempotency_key = 'ptxn_' . Str::uuid();
            }
        });
    }

    /** Guarded transition; returns false when illegal (caller decides response). */
    public function transitionTo(string $to, array $attrs = []): bool
    {
        $allowed = self::TRANSITIONS[$this->status] ?? [];
        if (!in_array($to, $allowed, true)) return false;
        $this->fill($attrs);
        $this->status = $to;
        if ($to === 'paid' && !$this->paid_at) $this->paid_at = now();
        $this->save();
        return true;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL, true);
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }
    public function provider() { return $this->belongsTo(PaymentProvider::class, 'provider_id'); }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function refunds() { return $this->hasMany(PaymentRefund::class); }
    public function webhookEvents() { return $this->hasMany(PaymentWebhookEvent::class, 'transaction_reference', 'reference'); }

    public function scopeForCustomer($q, int $customerId) { return $q->where('customer_id', $customerId); }
    public function scopeLatestFirst($q) { return $q->latest('id'); }
}
