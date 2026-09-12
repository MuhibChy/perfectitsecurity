<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\PortfolioItem;
use App\Models\User;
use Database\Seeders\DemoPortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Demo portfolio (20) + learning case studies (20): counts, honest
 * labeling, search/filter, detail pages, admin authorization, cleanup.
 */
class DemoPortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoPortfolioSeeder::class);
    }

    /** @test */
    public function twenty_demo_records_exist_per_type()
    {
        $this->assertEquals(20, PortfolioItem::where('is_demo', true)->count());
        $this->assertEquals(20, CaseStudy::where('is_demo', true)->count());
        $this->assertEquals(20, PortfolioItem::where('is_demo', true)->where('is_published', true)->count());
        $this->assertEquals(20, CaseStudy::where('is_demo', true)->where('is_published', true)->count());
    }

    /** @test */
    public function display_content_has_no_demo_labels()
    {
        $this->assertEquals(0, PortfolioItem::where('title', 'like', '%DEMO%')->count());
        $this->assertEquals(0, CaseStudy::where('title', 'like', '%DEMO%')->count());
        $this->assertEquals(0, PortfolioItem::where('client_name', 'like', '%emo%')->count());
        $this->assertEquals(0, CaseStudy::where('client_name', 'like', '%emo%')->count());

        $portfolio = PortfolioItem::where('is_demo', true)->firstOrFail();
        $this->assertEquals('Sample scenario organization', $portfolio->client_name);
        $study = CaseStudy::where('is_demo', true)->firstOrFail();
        // Every case study carries its own distinct fictional client name.
        $clients = CaseStudy::where('is_demo', true)->pluck('client_name')->all();
        $this->assertCount(20, array_unique($clients));
    }

    /** @test */
    public function public_listings_show_professional_titles_search_and_filters()
    {
        $this->get(route('portfolio.index'))->assertStatus(200)->assertSee('Enterprise IT Support Portal');
        $this->get(route('portfolio.index', ['search' => 'Vulnerability Management']))
            ->assertStatus(200)->assertSee('Vulnerability Management');
        $this->get(route('portfolio.index', ['category' => 'Cybersecurity']))
            ->assertStatus(200)->assertSee('Cybersecurity');

        $this->get(route('case-studies'))->assertStatus(200)->assertSee('Role-Based Access Control');
        $this->get(route('case-studies', ['search' => 'Role-Based Access Control']))
            ->assertStatus(200)->assertSee('Role-Based Access Control');
        $this->get(route('case-studies', ['industry' => 'Finance']))
            ->assertStatus(200)->assertSee('Finance');

        // No demo badges or labels remain in rendered output.
        $this->get(route('portfolio.index'))->assertDontSee('DEMO PROJECT');
        $this->get(route('case-studies'))->assertDontSee('DEMO / LEARNING');
    }

    /** @test */
    public function detail_pages_render_professionally()
    {
        $item = PortfolioItem::where('is_demo', true)->firstOrFail();
        $this->get(route('portfolio.show', $item->slug))->assertStatus(200)->assertDontSee('DEMO PROJECT');

        $study = CaseStudy::where('is_demo', true)->firstOrFail();
        $this->get(route('case-studies.show', $study->slug))->assertStatus(200)->assertDontSee('DEMO / LEARNING');
    }

    /** @test */
    public function admin_can_manage_but_customers_cannot()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.content.portfolio.store'), [
            'title' => 'Extra Verification Item', 'category' => 'Testing',
        ])->assertRedirect();
        $this->assertDatabaseHas('portfolio_items', ['title' => 'Extra Verification Item']);

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($customer)->post(route('admin.content.portfolio.store'), [
            'title' => 'Hijack', 'category' => 'x',
        ])->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.content.case-studies'))->assertStatus(403);
    }

    /** @test */
    public function cleanup_removes_only_demo_content()
    {
        $real = PortfolioItem::create([
            'title' => 'Real Client Work', 'slug' => 'real-client-work',
            'category' => 'IT Support', 'is_published' => true,
        ]);
        $this->artisan('demo:cleanup', ['--confirm' => true])->assertSuccessful();

        $this->assertEquals(0, PortfolioItem::where('is_demo', true)->count());
        $this->assertEquals(0, CaseStudy::where('is_demo', true)->count());
        $this->assertDatabaseHas('portfolio_items', ['id' => $real->id]);
    }
}
