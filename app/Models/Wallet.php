<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'wallet_reference', 'currency', 'status', 'balance'];

    protected $casts = ['user_id' => 'integer', 'balance' => 'decimal:2'];

    public const STATUSES = ['active', 'frozen', 'closed'];

    public const CREDIT_TYPES = ['deposit', 'refund', 'credit_adjustment'];

    public const DEBIT_TYPES = ['invoice_payment', 'debit_adjustment', 'withdrawal', 'reversal'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public static function isCredit(string $type): bool
    {
        return in_array($type, self::CREDIT_TYPES, true);
    }

    public static function isDebit(string $type): bool
    {
        return in_array($type, self::DEBIT_TYPES, true);
    }

    public function isUsable(): bool
    {
        return $this->status === 'active';
    }

    /** Ledger-derived balance (completed rows only). */
    public function ledgerBalance(): float
    {
        $credits = (float) $this->transactions()->where('status', 'completed')->whereIn('type', self::CREDIT_TYPES)->sum('amount');
        $debits = (float) $this->transactions()->where('status', 'completed')->whereIn('type', self::DEBIT_TYPES)->sum('amount');

        return round($credits - $debits, 2);
    }

    public function reconcile(): array
    {
        $ledger = $this->ledgerBalance();
        $stored = round((float) $this->balance, 2);

        return ['stored' => $stored, 'ledger' => $ledger, 'match' => abs($stored - $ledger) < 0.005, 'difference' => round($stored - $ledger, 2)];
    }
}
