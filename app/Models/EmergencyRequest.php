<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmergencyRequest extends Model
{
    public const SEVERITIES = ['CRITICAL', 'HIGH', 'NORMAL'];
    public const STATUSES = ['new', 'acknowledged', 'assigned', 'in_progress', 'resolved', 'closed'];

    protected $fillable = ['reference', 'requester_id', 'severity', 'category', 'description', 'status', 'assignee_id', 'acknowledged_at', 'resolved_at', 'closed_at'];
    protected $casts = ['acknowledged_at' => 'datetime', 'resolved_at' => 'datetime', 'closed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $e) {
            if (empty($e->reference)) $e->reference = 'ER-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        });
    }

    public function requester() { return $this->belongsTo(User::class, 'requester_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
}
