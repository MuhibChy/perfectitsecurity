<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbTag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function articles() { return $this->belongsToMany(KbArticle::class, 'kb_article_tag', 'tag_id', 'article_id'); }
}
