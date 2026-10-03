<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Controlled refund workflow: requested → approved → processing →
 * refunded | failed ; rejected = denied at approval.
 * Execution reuses the existing Payment RFD + recordRefund rails.
 */
class PaymentRefund extends Model
{
    use HasFactory;

    public const STATUSES = ['requested', 'approved', 'processing', 'refunded', 'failed', 'rejected'];

    protected $fillable = [
        'refund_number', 'idempotency_key', 'payment_transaction_id', 'payment_id',
        'customer_id', 'amount', 'currency', 'provider_refund_id', 'reason',
        'requested_by', 'approved_by', 'status', 'processed_at',
    ];

    protected $casts = ['amount' => 'decimal:2', 'processed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function ($r) {
            if (empty($r->refund_number)) {
                $r->refund_number = 'RFND-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
            if (empty($r->idempotency_key)) {
                $r->idempotency_key = 'rfnd_' . Str::uuid();
            }
        });
    }

    public function paymentTransaction() { return $this->belongsTo(PaymentTransaction::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
