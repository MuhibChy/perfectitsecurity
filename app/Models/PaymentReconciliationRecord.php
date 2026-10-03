<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Internal-vs-provider comparison flag for finance review. */
class PaymentReconciliationRecord extends Model
{
    use HasFactory;

    public const RESULTS = ['matched', 'mismatch', 'requires_verification'];

    protected $fillable = [
        'provider_key', 'internal_reference', 'provider_reference',
        'internal_amount', 'internal_currency', 'provider_amount',
        'provider_currency', 'internal_status', 'provider_status',
        'result', 'notes', 'checked_at',
    ];

    protected $casts = [
        'internal_amount' => 'decimal:2',
        'provider_amount' => 'decimal:2',
        'checked_at' => 'datetime',
    ];

    public function scopeMismatches($q)
    {
        return $q->where('result', 'mismatch');
    }

    public function scopeNeedsReview($q)
    {
        return $q->whereIn('result', ['mismatch', 'requires_verification']);
    }
}
