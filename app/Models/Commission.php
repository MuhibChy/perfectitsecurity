<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Commission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commission_number', 'worker_id', 'rule_id', 'task_id', 'customer_id',
        'project_id', 'commission_type', 'revenue_amount', 'commission_rate',
        'commission_amount', 'status', 'approved_by', 'approved_at',
        'payment_status', 'paid_at', 'notes',
    ];

    protected $casts = [
        'revenue_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($commission) {
            if (empty($commission->commission_number)) {
                $commission->commission_number = 'COM-' . strtoupper(Str::random(8));
            }
        });
    }

    public function worker() { return $this->belongsTo(User::class, 'worker_id'); }
    public function rule() { return $this->belongsTo(CommissionRule::class, 'rule_id'); }
    public function task() { return $this->belongsTo(Task::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function project() { return $this->belongsTo(Project::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
