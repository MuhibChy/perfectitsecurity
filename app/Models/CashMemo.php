<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CashMemo extends Model
{
    protected $fillable = ['cash_memo_number', 'payment_id', 'issued_by', 'issued_at'];
    protected $casts = ['issued_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $memo) {
            $memo->cash_memo_number ??= 'CM-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
            $memo->issued_at ??= now();
        });
    }

    public function payment() { return $this->belongsTo(Payment::class); }
    public function issuer() { return $this->belongsTo(User::class, 'issued_by'); }
}
