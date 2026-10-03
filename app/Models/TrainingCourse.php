<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingCourse extends Model
{
    use SoftDeletes;

    protected $fillable = ['slug', 'title', 'description', 'role_target', 'difficulty', 'duration_minutes', 'is_mandatory', 'is_published', 'version', 'created_by'];
    protected $casts = ['is_mandatory' => 'boolean', 'is_published' => 'boolean'];

    public function modules() { return $this->hasMany(TrainingModule::class, 'course_id')->orderBy('sort_order'); }
    public function lessons() { return $this->hasManyThrough(TrainingLesson::class, TrainingModule::class, 'course_id', 'module_id'); }
    public function quizzes() { return $this->hasMany(TrainingQuiz::class, 'course_id'); }
    public function practicals() { return $this->hasMany(TrainingPracticalAssessment::class, 'course_id'); }
    public function assignments() { return $this->hasMany(TrainingAssignment::class, 'course_id'); }
    public function certificates() { return $this->hasMany(TrainingCertificate::class, 'course_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopePublished($q) { return $q->where('is_published', true); }

    public function getRouteKeyName(): string { return 'slug'; }

    public function lessonIds(): array
    {
        return $this->lessons()->pluck('training_lessons.id')->all();
    }
}
