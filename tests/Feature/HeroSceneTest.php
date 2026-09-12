<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Global 3D hero scene: the same Home implementation renders on all major
 * public heroes; authenticated areas keep the single role-based canvas
 * (no duplicate WebGL instances on dashboards).
 */
class HeroSceneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function hero_scene_renders_on_all_major_public_pages()
    {
        foreach (['home', 'about', 'services.index', 'pricing', 'contact', 'get-quote'] as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
            $response->assertSee('id="hero-canvas"', false);
            $response->assertSee('hero-fallback-bg', false);
        }
    }

    /** @test */
    public function hero_scene_renders_on_index_listing_pages()
    {
        foreach (['industries', 'kb.index', 'blog.index', 'case-studies', 'careers', 'portfolio.index', 'faq'] as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
            $response->assertSee('id="hero-canvas"', false);
            $response->assertSee('hero-fallback-bg', false);
        }
    }

    /** @test */
    public function hero_scene_renders_on_detail_pages()
    {
        $category = \App\Models\ServiceCategory::create(['name' => 'Scene Cat', 'slug' => 'scene-cat']);
        $service = \App\Models\Service::create([
            'category_id' => $category->id, 'name' => 'Scene Service', 'slug' => 'scene-service',
            'short_description' => 'x', 'is_active' => true,
        ]);
        $this->get(route('services.show', $service->slug))
            ->assertStatus(200)->assertSee('id="hero-canvas"', false);

        $admin = User::factory()->create(['role' => 'admin']);
        $kbCat = \App\Models\KbCategory::create(['name' => 'Scene KB', 'slug' => 'scene-kb']);
        $article = \App\Models\KbArticle::create([
            'category_id' => $kbCat->id, 'author_id' => $admin->id,
            'title' => 'Scene Article', 'slug' => 'scene-article',
            'content' => 'body', 'visibility' => 'public', 'is_published' => true,
        ]);
        $this->get(route('kb.show', $article->slug))
            ->assertStatus(200)->assertSee('id="hero-canvas"', false);

        $blogCat = \App\Models\BlogCategory::create(['name' => 'Scene Blog', 'slug' => 'scene-blog']);
        $post = \App\Models\BlogPost::create([
            'author_id' => $admin->id, 'category_id' => $blogCat->id,
            'title' => 'Scene Post', 'slug' => 'scene-post', 'content' => 'body',
            'is_published' => true, 'published_at' => now(),
        ]);
        $this->get(route('blog.show', $post->slug))
            ->assertStatus(200)->assertSee('id="hero-canvas"', false);

        $study = \App\Models\CaseStudy::create([
            'title' => 'Scene Study', 'slug' => 'scene-study', 'is_published' => true, 'published_at' => now(),
        ]);
        $this->get(route('case-studies.show', $study->slug))
            ->assertStatus(200)->assertSee('id="hero-canvas"', false);

        $career = \App\Models\CareerPost::create([
            'title' => 'Scene Role', 'slug' => 'scene-role', 'is_published' => true, 'published_at' => now(),
        ]);
        $this->get(route('careers.show', $career->slug))
            ->assertStatus(200)->assertSee('id="hero-canvas"', false);

        $item = \App\Models\PortfolioItem::create([
            'title' => 'Scene Work', 'slug' => 'scene-work', 'is_published' => true, 'published_at' => now(),
        ]);
        $this->get(route('portfolio.show', $item->slug))
            ->assertStatus(200)->assertSee('id="hero-canvas"', false);
    }

    /** @test */
    public function only_one_hero_canvas_per_page()
    {
        foreach (['home', 'about', 'contact'] as $route) {
            $html = $this->get(route($route))->getContent();
            $this->assertEquals(1, substr_count($html, 'id="hero-canvas"'), $route);
        }
    }

    /** @test */
    public function dashboards_use_single_role_canvas_instead_of_webgl()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $content = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();
        $this->assertStringContainsString('id="role-bg-canvas"', $content);
        $this->assertStringNotContainsString('id="hero-canvas"', $content);

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $content = $this->actingAs($customer)->get(route('portal.dashboard'))->getContent();
        $this->assertStringContainsString('id="role-bg-canvas"', $content);
        $this->assertStringNotContainsString('id="hero-canvas"', $content);
    }
}
