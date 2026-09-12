<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CommissionPayout extends Model
{
    protected $fillable = ['payout_number', 'worker_id', 'amount', 'payment_method', 'transaction_reference', 'notes', 'status', 'paid_at', 'processed_by'];

    protected static function booted()
    {
        static::creating(function ($p) {
            if (empty($p->payout_number)) {
                $p->payout_number = 'PO-' . strtoupper(Str::random(8));
            }
        });
    }

    public function worker() { return $this->belongsTo(User::class, 'worker_id'); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }
    public function items() { return $this->hasMany(CommissionPayoutItem::class, 'payout_id'); }
}
