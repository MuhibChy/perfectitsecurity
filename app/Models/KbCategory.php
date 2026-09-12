<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'sort_order', 'is_active', 'parent_id', 'is_internal'];

    public function articles() { return $this->hasMany(KbArticle::class, 'category_id'); }
    public function parent() { return $this->belongsTo(KbCategory::class, 'parent_id'); }
    public function children() { return $this->hasMany(KbCategory::class, 'parent_id'); }
    public function tags() { return $this->hasManyThrough(KbTag::class, KbArticle::class, 'category_id', 'id', 'id', 'id')->distinct(); }
}
