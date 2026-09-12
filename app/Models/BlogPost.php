<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id', 'category_id', 'title', 'slug', 'excerpt', 'content',
        'featured_image', 'meta_title', 'meta_description', 'og_image',
        'is_published', 'is_featured', 'published_at', 'views_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function category() { return $this->belongsTo(BlogCategory::class, 'category_id'); }
    public function tags() { return $this->belongsToMany(BlogTag::class, 'blog_post_tag', 'post_id', 'tag_id'); }
    public function comments() { return $this->hasMany(BlogComment::class)->where('is_approved', true); }

    public function scopePublished($query) { return $query->where('is_published', true)->whereNotNull('published_at'); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
}
