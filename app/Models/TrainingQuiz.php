<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingQuiz extends Model
{
    protected $fillable = ['course_id', 'lesson_id', 'title', 'description', 'pass_score', 'max_attempts', 'is_published'];
    protected $casts = ['is_published' => 'boolean'];

    public function course() { return $this->belongsTo(TrainingCourse::class, 'course_id'); }
    public function lesson() { return $this->belongsTo(TrainingLesson::class, 'lesson_id'); }
    public function questions() { return $this->hasMany(TrainingQuestion::class, 'quiz_id')->orderBy('sort_order'); }
    public function attempts() { return $this->hasMany(TrainingQuizAttempt::class, 'quiz_id'); }

    public function scopePublished($q) { return $q->where('is_published', true); }

    public function maxPoints(): int
    {
        return (int) $this->questions()->sum('points');
    }

    public function bestAttemptFor(int $userId): ?TrainingQuizAttempt
    {
        return $this->attempts()->where('user_id', $userId)->orderByDesc('score')->first();
    }

    public function attemptsUsedBy(int $userId): int
    {
        return $this->attempts()->where('user_id', $userId)->count();
    }
}
