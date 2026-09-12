<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbArticleVersion extends Model
{
    protected $fillable = [
        'article_id', 'edited_by', 'title', 'content',
        'version_number', 'edit_summary',
    ];

    public function article() { return $this->belongsTo(KbArticle::class, 'article_id'); }
    public function editor() { return $this->belongsTo(User::class, 'edited_by'); }
}
