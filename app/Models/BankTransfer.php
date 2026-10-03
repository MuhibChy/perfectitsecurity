<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BankTransfer extends Model
{
    public const STATUSES = ['draft', 'pending_approval', 'approved', 'processing', 'completed', 'failed', 'cancelled', 'reversed'];

    public const PURPOSES = ['salary', 'commission', 'contractor', 'franchise_share', 'expense'];

    protected $fillable = ['reference', 'idempotency_key', 'beneficiary_id', 'franchise_id', 'purpose', 'related_id', 'amount', 'currency', 'provider', 'external_reference', 'status', 'requested_by', 'approved_by', 'executed_by', 'requested_at', 'approved_at', 'completed_at', 'failure_reason', 'masked_destination'];

    protected $casts = ['amount' => 'decimal:2', 'requested_at' => 'datetime', 'approved_at' => 'datetime', 'completed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $t) {
            if (empty($t->reference)) {
                $t->reference = 'BT-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
            if (empty($t->idempotency_key)) {
                $t->idempotency_key = 'BTK-'.Str::uuid();
            }
        });
    }

    public function beneficiary()
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    /** UI helper: never render full account numbers (mask to last 4). */
    public static function mask(?string $destination): ?string
    {
        if (! $destination) {
            return null;
        }
        $digits = preg_replace('/\s+/', '', $destination);

        return '…'.substr($digits, -4);
    }
}
