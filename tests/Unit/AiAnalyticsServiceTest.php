<?php

namespace Tests\Unit;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Services\Ai\AiAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function most_used_articles_tolerates_mixed_source_shapes()
    {
        $category = KbCategory::create(['name' => 'General', 'slug' => 'general']);
        $author = \App\Models\User::factory()->create();
        $article = KbArticle::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Test article',
            'slug' => 'test-article',
            'content' => 'Body',
            'is_published' => true,
        ]);
        $conversation = AiConversation::create(['status' => 'active']);

        // Mixed shapes as seen in production: assoc arrays, numeric strings,
        // nulls, junk arrays, and plain ids.
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'hi',
            'sources' => [['id' => $article->id, 'title' => 'x'], (string) $article->id, null, ['foo' => 'bar'], $article->id],
        ]);

        $result = (new AiAnalyticsService)->getMostUsedArticles();

        $this->assertNotEmpty($result);
        $this->assertSame($article->id, $result[0]['id']);
        $this->assertSame(3, $result[0]['usage_count']);
    }

    /** @test */
    public function most_used_articles_handles_empty_and_missing_category()
    {
        $this->assertSame([], (new AiAnalyticsService)->getMostUsedArticles());

        $conversation = AiConversation::create(['status' => 'active']);
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'hi',
            'sources' => [['no' => 'id here']],
        ]);

        $this->assertSame([], (new AiAnalyticsService)->getMostUsedArticles());
    }
}
