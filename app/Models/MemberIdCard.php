<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Digital member ID card. Cards are never deleted: replacement revokes the
 * old row (status=replaced) and issues a new one. The QR token itself is
 * never stored — only its SHA-256 hash — so a DB read cannot mint valid QRs.
 */
class MemberIdCard extends Model
{
    public const STATUSES = ['pending', 'active', 'suspended', 'revoked', 'replaced', 'expired'];

    protected $fillable = [
        'card_number', 'user_id', 'token_hash', 'status',
        'issued_at', 'revoked_at', 'expires_at', 'issued_by', 'revoked_by', 'revoke_reason',
    ];

    protected $casts = ['issued_at' => 'datetime', 'revoked_at' => 'datetime', 'expires_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeValid($query)
    {
        return $query->where('status', 'active');
    }

    public function isValid(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }
}
