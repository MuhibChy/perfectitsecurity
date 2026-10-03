<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Problem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'problem_number', 'title', 'description', 'customer_id', 'company_id',
        'category', 'priority', 'impact', 'status', 'assigned_to',
        'root_cause', 'workaround', 'resolution', 'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($problem) {
            if (empty($problem->problem_number)) {
                $problem->problem_number = 'PRB-' . strtoupper(Str::random(8));
            }
        });
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function company() { return $this->belongsTo(Company::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function tickets() { return $this->belongsToMany(Ticket::class, 'problem_ticket')->withTimestamps(); }
    public function approvals() { return $this->morphMany(ServiceApproval::class, 'approvable'); }

    public function scopeOpen($query) { return $query->whereNotIn('status', ['resolved', 'closed']); }

    public function getIsKnownErrorAttribute(): bool { return $this->status === 'known_error'; }
}
