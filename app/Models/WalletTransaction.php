<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = ['wallet_id', 'transaction_reference', 'type', 'amount', 'currency', 'balance_before', 'balance_after', 'status', 'source_type', 'source_id', 'payment_provider', 'provider_transaction_id', 'provider_event_id', 'description', 'metadata', 'created_by'];
    protected $casts = ['wallet_id' => 'integer', 'source_id' => 'integer', 'created_by' => 'integer', 'amount' => 'decimal:2', 'balance_before' => 'decimal:2', 'balance_after' => 'decimal:2', 'metadata' => 'array'];

    public const STATUSES = ['pending', 'completed', 'failed', 'reversed'];
    public const TYPES = ['deposit', 'invoice_payment', 'refund', 'credit_adjustment', 'debit_adjustment', 'withdrawal', 'reversal'];

    public function wallet() { return $this->belongsTo(Wallet::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function isCredit(): bool
    {
        return Wallet::isCredit($this->type);
    }

    public function signedAmount(): float
    {
        return $this->isCredit() ? (float) $this->amount : -(float) $this->amount;
    }
}
