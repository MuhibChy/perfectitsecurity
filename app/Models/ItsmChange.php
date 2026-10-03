<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ItsmChange extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'change_number', 'title', 'description', 'type', 'risk', 'impact',
        'status', 'customer_id', 'company_id', 'requested_by', 'assigned_to',
        'implementation_plan', 'rollback_plan', 'scheduled_start', 'scheduled_end',
        'implemented_at', 'implementation_result', 'failure_notes',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'implemented_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($change) {
            if (empty($change->change_number)) {
                $change->change_number = 'CHG-'.strtoupper(Str::random(8));
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvals()
    {
        return $this->hasMany(ItsmChangeApproval::class, 'itsm_change_id');
    }

    public function workflowApprovals()
    {
        return $this->morphMany(ServiceApproval::class, 'approvable');
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['completed', 'failed', 'cancelled']);
    }

    public function getIsApprovedAttribute(): bool
    {
        return in_array($this->status, ['approved', 'scheduled', 'implementing', 'completed'], true);
    }
}
