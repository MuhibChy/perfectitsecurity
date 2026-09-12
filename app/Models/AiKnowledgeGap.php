<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKnowledgeGap extends Model
{
    protected $fillable = [
        'question', 'occurrence_count', 'sample_answers', 'status',
        'resolved_by', 'created_kb_article_id',
    ];

    protected $casts = ['sample_answers' => 'array'];

    public function resolver() { return $this->belongsTo(User::class, 'resolved_by'); }
    public function kbArticle() { return $this->belongsTo(KbArticle::class, 'created_kb_article_id'); }

    public function incrementOccurrence()
    {
        $this->increment('occurrence_count');
    }
}
