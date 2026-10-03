<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Company receiving bank account (BD + international).
 * Account numbers are encrypted at rest; only non-sensitive instruction
 * fields are ever shown to customers.
 */
class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'label', 'country', 'currency', 'account_name', 'bank_name', 'branch',
        'routing_number', 'swift_bic', 'iban', 'payment_instructions',
        'purpose', 'is_active', 'sort_order', 'created_by',
    ];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    protected $hidden = ['account_number'];

    public function setAccountNumberAttribute($value): void
    {
        $this->attributes['account_number'] = $value === null || $value === ''
            ? null
            : Crypt::encryptString((string) $value);
    }

    public function getAccountNumberAttribute($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Customer-safe display number (masked, e.g. ****4521). */
    public function maskedNumber(): string
    {
        $raw = $this->account_number;
        if (! $raw) {
            return '—';
        }
        $len = strlen($raw);

        return $len <= 4 ? '****' : '****'.substr($raw, -4);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order')->orderBy('label');
    }

    public function scopeForCurrency($q, string $currency)
    {
        return $q->where('currency', strtoupper($currency));
    }
}
