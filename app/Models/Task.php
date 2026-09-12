<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_demo',
        'task_number', 'project_id', 'service_order_id', 'ticket_id', 'customer_id', 'title', 'description', 'assigned_to',
        'created_by', 'status', 'priority', 'type', 'budget', 'reward_amount',
        'skills_required', 'deadline', 'start_date', 'estimated_minutes', 'actual_minutes',
        'technical_notes', 'completed_at', 'sla_priority', 'progress', 'max_applicants',
        'is_locked', 'locked_by', 'locked_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'start_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($task) {
            if (empty($task->task_number)) {
                $task->task_number = 'TSK-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function locker() { return $this->belongsTo(User::class, 'locked_by'); }
    public function applications() { return $this->hasMany(TaskApplication::class); }
    public function comments() { return $this->hasMany(TaskComment::class); }
    public function attachments() { return $this->hasMany(TaskAttachment::class); }
    public function commissions() { return $this->hasMany(Commission::class); }
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }
    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
}
