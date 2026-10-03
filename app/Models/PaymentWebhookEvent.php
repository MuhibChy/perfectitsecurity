<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Per-provider webhook/callback event log with idempotency on event_id. */
class PaymentWebhookEvent extends Model
{
    use HasFactory;

    public const STATUSES = ['received', 'processing', 'processed', 'duplicate', 'failed'];

    protected $fillable = [
        'provider_key', 'event_id', 'transaction_reference', 'payload',
        'signature_valid', 'status', 'attempts', 'error', 'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function scopePending($q) { return $q->whereIn('status', ['received', 'failed']); }
}
