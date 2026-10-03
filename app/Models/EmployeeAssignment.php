<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Audited employee → work links (customer/project/order/service/ticket/
 * task). Rows are transitioned active→completed|revoked; history is never
 * overwritten or deleted by application code.
 */
class EmployeeAssignment extends Model
{
    public const STATUSES = ['active', 'completed', 'revoked'];

    protected $fillable = [
        'employee_id', 'assignable_type', 'assignable_id', 'assigned_by',
        'status', 'started_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }
    public function assignable() { return $this->morphTo(); }

    public function scopeActive($query) { return $query->where('status', 'active'); }
}
