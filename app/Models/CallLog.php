<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Voice-communication records. The `provider` column names the telephony
 * rail (`manual` default); a real vendor plugs in via the CallProvider
 * contract without schema changes. Customer visibility is row-level.
 */
class CallLog extends Model
{
    public const OUTCOMES = ['completed', 'missed', 'failed', 'voicemail', 'scheduled'];
    public const DIRECTIONS = ['outbound', 'inbound'];

    protected $fillable = [
        'uuid', 'caller_id', 'recipient_id', 'direction', 'provider', 'provider_call_id',
        'started_at', 'duration_seconds', 'outcome', 'related_type', 'related_id',
        'subject', 'notes', 'is_customer_visible', 'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'is_customer_visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            if (empty($log->uuid)) $log->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }

    public function caller() { return $this->belongsTo(User::class, 'caller_id'); }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function related() { return $this->morphTo(); }

    public function scopeVisibleTo($query, User $viewer)
    {
        if ($viewer->isCustomer()) {
            return $query->where('is_customer_visible', true)->where(function ($w) use ($viewer) {
                $w->where('caller_id', $viewer->id)->orWhere('recipient_id', $viewer->id);
            });
        }
        return $query;
    }
}
