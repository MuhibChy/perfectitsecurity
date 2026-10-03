<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingAssignment extends Model
{
    protected $fillable = ['course_id', 'user_id', 'assigned_by', 'due_at', 'status'];
    protected $casts = ['due_at' => 'datetime'];

    public const STATUSES = ['assigned', 'in_progress', 'assessment_pending', 'passed', 'needs_improvement', 'completed'];

    public function course() { return $this->belongsTo(TrainingCourse::class, 'course_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast() && !in_array($this->status, ['completed', 'passed'], true);
    }
}
