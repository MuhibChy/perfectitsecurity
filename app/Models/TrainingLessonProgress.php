<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingLessonProgress extends Model
{
    protected $table = 'training_lesson_progress';

    protected $fillable = ['lesson_id', 'user_id', 'is_completed', 'completed_at'];

    protected $casts = ['is_completed' => 'boolean', 'completed_at' => 'datetime'];

    public function lesson()
    {
        return $this->belongsTo(TrainingLesson::class, 'lesson_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
