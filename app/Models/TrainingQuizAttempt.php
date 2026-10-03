<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingQuizAttempt extends Model
{
    protected $fillable = ['quiz_id', 'user_id', 'score', 'max_score', 'passed', 'answers'];
    protected $casts = ['answers' => 'array', 'passed' => 'boolean'];

    public function quiz() { return $this->belongsTo(TrainingQuiz::class, 'quiz_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }

    public function percent(): int
    {
        if ($this->max_score <= 0) return 0;
        return (int) round($this->score * 100 / $this->max_score);
    }
}
