<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingAssessmentSubmission extends Model
{
    protected $fillable = ['assessment_id', 'user_id', 'responses', 'status', 'score', 'reviewed_by', 'feedback', 'submitted_at', 'reviewed_at'];
    protected $casts = ['responses' => 'array', 'submitted_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public const STATUSES = ['submitted', 'passed', 'needs_improvement'];

    public function assessment() { return $this->belongsTo(TrainingPracticalAssessment::class, 'assessment_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
