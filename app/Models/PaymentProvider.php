<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * DB-configured payment provider (registry row).
 *
 * Secrets are encrypted at rest via accessors (never plaintext, never
 * logged). Non-secret tunables live in `config` (plain JSON).
 */
class PaymentProvider extends Model
{
    use HasFactory;

    public const STATUSES = ['enabled', 'disabled', 'test', 'live', 'maintenance'];
    public const ENVIRONMENTS = ['test', 'live'];
    public const TYPES = ['mobile_wallet', 'bank', 'gateway', 'card', 'manual', 'wallet'];

    protected $fillable = [
        'key', 'name', 'type', 'country', 'currencies', 'payment_methods',
        'environment', 'status', 'priority', 'min_amount', 'max_amount',
        'fee_type', 'fee_value', 'platform_fee_value',
        'webhook_url', 'config', 'notes', 'is_active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'currencies' => 'array',
        'payment_methods' => 'array',
        'config' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'fee_value' => 'decimal:4',
        'platform_fee_value' => 'decimal:4',
    ];

    protected $hidden = ['credentials', 'webhook_secret'];

    /** Encrypted credentials JSON: set plain array, stored encrypted. */
    public function setCredentialsAttribute($value): void
    {
        $this->attributes['credentials'] = $value === null || $value === ''
            ? null
            : Crypt::encryptString(is_string($value) ? $value : json_encode($value));
    }

    public function getCredentialsAttribute($value): array
    {
        if ($value === null || $value === '') return [];
        try {
            $decoded = json_decode(Crypt::decryptString($value), true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function setWebhookSecretAttribute($value): void
    {
        $this->attributes['webhook_secret'] = $value === null || $value === ''
            ? null
            : Crypt::encryptString((string) $value);
    }

    public function getWebhookSecretAttribute($value): ?string
    {
        if ($value === null || $value === '') return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Single credential value by key (never dumped whole into logs). */
    public function credential(string $key, $default = null)
    {
        $creds = $this->credentials ?? [];
        return $creds[$key] ?? $default;
    }

    public function isUsable(): bool
    {
        return (bool) $this->is_active && in_array($this->status, ['enabled', 'test', 'live'], true);
    }

    public function isLive(): bool
    {
        return $this->isUsable() && $this->environment === 'live' && $this->status === 'live';
    }

    public function supportsCurrency(string $code): bool
    {
        $list = $this->currencies ?? [];
        if ($list === []) return true;
        return in_array(strtoupper($code), array_map('strtoupper', $list), true);
    }

    public function supportsAmount(float $amount): bool
    {
        if ($this->min_amount !== null && $amount < (float) $this->min_amount) return false;
        if ($this->max_amount !== null && $amount > (float) $this->max_amount) return false;
        return true;
    }

    /** Fee split for a gross amount: provider fee + platform fee, net remainder. */
    public function quoteFees(float $gross): array
    {
        $providerFee = 0.0;
        if ($this->fee_type === 'percent' && $this->fee_value) {
            $providerFee = round($gross * ((float) $this->fee_value / 100), 2);
        } elseif ($this->fee_type === 'fixed' && $this->fee_value) {
            $providerFee = round((float) $this->fee_value, 2);
        } elseif ($this->fee_type === 'mixed' && $this->fee_value) {
            $providerFee = round((float) $this->fee_value, 2);
        }
        $platformFee = round((float) ($this->platform_fee_value ?? 0), 2);
        return [
            'gross' => round($gross, 2),
            'provider_fee' => $providerFee,
            'platform_fee' => $platformFee,
            'net' => round($gross - $providerFee - $platformFee, 2),
        ];
    }

    public function transactions() { return $this->hasMany(PaymentTransaction::class, 'provider_id'); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeUsable($q) { return $q->where('is_active', true)->whereIn('status', ['enabled', 'test', 'live']); }
    public function scopeOrdered($q) { return $q->orderBy('priority')->orderBy('name'); }
}
