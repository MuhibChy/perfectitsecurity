<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingPracticalAssessment extends Model
{
    protected $fillable = ['course_id', 'title', 'instructions', 'checklist', 'is_published'];
    protected $casts = ['checklist' => 'array', 'is_published' => 'boolean'];

    public function course() { return $this->belongsTo(TrainingCourse::class, 'course_id'); }
    public function submissions() { return $this->hasMany(TrainingAssessmentSubmission::class, 'assessment_id'); }
}
