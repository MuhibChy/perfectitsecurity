<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbArticleVote extends Model
{
    protected $fillable = [
        'article_id', 'user_id', 'session_id', 'is_helpful',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    public function article() { return $this->belongsTo(KbArticle::class, 'article_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
}
