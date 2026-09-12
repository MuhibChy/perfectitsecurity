<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_demo',
        'payment_number', 'invoice_id', 'customer_id', 'service_order_id', 'amount', 'currency', 'refunded_amount',
        'status', 'payment_method', 'transaction_id', 'gateway',
        'stripe_checkout_session_id', 'stripe_payment_intent_id', 'notes', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = 'PAY-' . date('Y') . '-' . str_pad((string) ((int) DB::table('payments')->lockForUpdate()->max('id') + 1), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }
    public function receipt() { return $this->hasOne(Receipt::class); }
    public function financialTransactions() { return $this->hasMany(FinancialTransaction::class); }
}
