<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_number', 'approvable_type', 'approvable_id', 'requested_by',
        'approver_id', 'type', 'status', 'comments', 'decided_at',
    ];

    protected $casts = ['decided_at' => 'datetime'];

    protected static function booted()
    {
        static::creating(function ($approval) {
            if (empty($approval->approval_number)) {
                $approval->approval_number = 'APR-' . strtoupper(Str::random(8));
            }
        });
    }

    public function approvable() { return $this->morphTo(); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approver_id'); }

    public function scopePending($query) { return $query->where('status', 'pending'); }
}
