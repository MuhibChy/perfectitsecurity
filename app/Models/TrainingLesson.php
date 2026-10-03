<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingLesson extends Model
{
    protected $fillable = ['module_id', 'title', 'lesson_type', 'body', 'objectives', 'steps', 'why_matters', 'common_mistakes', 'discussion_questions', 'duration_minutes', 'sort_order', 'is_published', 'version'];
    protected $casts = ['objectives' => 'array', 'steps' => 'array', 'why_matters' => 'array', 'common_mistakes' => 'array', 'discussion_questions' => 'array', 'is_published' => 'boolean'];

    public function module() { return $this->belongsTo(TrainingModule::class, 'module_id'); }
    public function progress() { return $this->hasMany(TrainingLessonProgress::class, 'lesson_id'); }

    public function scopePublished($q) { return $q->where('is_published', true); }

    public function isCompletedBy(int $userId): bool
    {
        return $this->progress()->where('user_id', $userId)->where('is_completed', true)->exists();
    }
}
