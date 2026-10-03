<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Customer bank-transfer submission. NEVER auto-paid: finance staff must
 * verify (→ creates PaymentTransaction + settles) or reject explicitly.
 */
class ManualBankPayment extends Model
{
    use HasFactory;

    public const STATUSES = ['pending_verification', 'verified', 'rejected'];

    protected $fillable = [
        'reference', 'idempotency_key', 'customer_id', 'invoice_id',
        'service_order_id', 'bank_account_id', 'amount', 'currency',
        'sender_name', 'sender_bank', 'transfer_reference',
        'provider_transaction_id', 'transferred_at', 'receipt_path',
        'status', 'verified_by', 'verified_at', 'admin_notes',
        'payment_transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transferred_at' => 'date',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (empty($m->reference)) {
                $m->reference = 'MBP-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
            if (empty($m->idempotency_key)) {
                $m->idempotency_key = 'mbp_'.Str::uuid();
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending_verification');
    }
}
