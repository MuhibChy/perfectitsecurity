<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Receipt extends Model
{
    protected $fillable = ['receipt_number', 'payment_id', 'invoice_id', 'customer_id', 'service_order_id', 'amount', 'remaining_balance', 'currency', 'issued_at'];
    protected $casts = ['amount' => 'decimal:2', 'remaining_balance' => 'decimal:2', 'issued_at' => 'datetime'];
    protected static function booted(): void { static::creating(fn (self $receipt) => $receipt->receipt_number ??= 'RCP-' . now()->format('Y') . '-' . strtoupper(Str::random(6))); }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function order() { return $this->belongsTo(ServiceOrder::class, 'service_order_id'); }
}
