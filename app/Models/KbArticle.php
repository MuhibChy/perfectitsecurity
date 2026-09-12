<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KbArticle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_demo',
        'category_id', 'author_id', 'title', 'slug', 'content', 'excerpt',
        'meta_title', 'meta_description', 'keywords', 'visibility', 'language',
        'difficulty', 'helpful_count', 'not_helpful_count',
        'views_count', 'is_published', 'is_featured',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category() { return $this->belongsTo(KbCategory::class, 'category_id'); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function tags() { return $this->belongsToMany(KbTag::class, 'kb_article_tag', 'article_id', 'tag_id'); }
    public function relatedArticles() { return $this->belongsToMany(KbArticle::class, 'kb_related_article', 'article_id', 'related_article_id'); }

    public function scopePublished($q) { return $q->where('is_published', true); }
    public function scopePublic($q) { return $q->where('visibility', 'public'); }
    public function scopeForCustomer($q) { return $q->whereIn('visibility', ['public', 'customer']); }
    public function scopeForStaff($q) { return $q->whereIn('visibility', ['public', 'customer', 'employee']); }
    public function scopeForLanguage($q, $lang) { return $q->where('language', $lang); }

    public function getVisibilityForUser($user = null)
    {
        if (!$user) return $this->visibility === 'public';
        if ($user->isAdmin()) return true;
        if ($user->isEmployee()) return in_array($this->visibility, ['public', 'customer', 'employee']);
        return in_array($this->visibility, ['public', 'customer']);
    }

    public function versions() { return $this->hasMany(KbArticleVersion::class, 'article_id'); }
    public function votes() { return $this->hasMany(KbArticleVote::class, 'article_id'); }

    public function markHelpful() { $this->increment('helpful_count'); }
    public function markNotHelpful() { $this->increment('not_helpful_count'); }
    public function incrementViewCount() { $this->increment('view_count'); }

    /**
     * Save a new version of this article.
     */
    public function saveVersion(array $data, $editedBy = null, ?string $editSummary = null): KbArticleVersion
    {
        $versionNumber = ($this->current_version ?? 0) + 1;

        $version = KbArticleVersion::create([
            'article_id' => $this->id,
            'edited_by' => $editedBy,
            'title' => $data['title'] ?? $this->title,
            'content' => $data['content'] ?? $this->content,
            'version_number' => $versionNumber,
            'edit_summary' => $editSummary,
        ]);

        $this->update(['current_version' => $versionNumber]);

        return $version;
    }

    /**
     * Get the latest version of this article.
     */
    public function latestVersion()
    {
        return $this->versions()->latest('version_number')->first();
    }
}
