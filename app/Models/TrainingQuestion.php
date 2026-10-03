<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingQuestion extends Model
{
    protected $fillable = ['quiz_id', 'type', 'prompt', 'options', 'correct', 'explanation', 'points', 'sort_order'];
    protected $casts = ['options' => 'array', 'correct' => 'array'];

    public function quiz() { return $this->belongsTo(TrainingQuiz::class, 'quiz_id'); }

    /**
     * Grade one answer. Returns earned points (0..points).
     * Types: single (one index), multiple (set of indexes, all-or-nothing),
     * boolean (true/false string), ordering (exact sequence match).
     */
    public function grade(mixed $given): int
    {
        $correct = $this->correct ?? [];
        if ($this->type === 'single' || $this->type === 'boolean') {
            return ((string) $given === (string) ($correct[0] ?? null)) ? $this->points : 0;
        }
        if ($this->type === 'multiple') {
            $g = array_values(array_unique(array_map('strval', (array) $given)));
            $c = array_values(array_unique(array_map('strval', $correct)));
            sort($g); sort($c);
            return ($g === $c && count($g) > 0) ? $this->points : 0;
        }
        if ($this->type === 'ordering') {
            if (is_string($given)) $given = explode(',', $given);
            $g = array_values(array_map(fn ($v) => trim((string) $v), (array) $given));
            $c = array_values(array_map(fn ($v) => trim((string) $v), $correct));
            return ($g === $c && count($g) > 0) ? $this->points : 0;
        }
        return 0;
    }
}
