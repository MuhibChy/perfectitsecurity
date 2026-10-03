<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCountryPrice;
use App\Models\Setting;
use App\Models\User;
use App\Services\CurrencyService;
use App\Services\PromotionService;
use Database\Seeders\AssetLifecycleCategorySeeder;
use Database\Seeders\DemoContentExpansionSeeder;
use Database\Seeders\PromoCampaignSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePopulationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $finance;
    protected $customer;
    protected $us;
    protected $uk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'wpop-admin@test.com', 'password' => bcrypt('password'), 'role' => 'super_admin', 'is_active' => true, 'email_verified_at' => now()]);
        $this->finance = User::create(['name' => 'Finance', 'email' => 'wpop-fin@test.com', 'password' => bcrypt('password'), 'role' => 'finance_manager', 'is_active' => true, 'email_verified_at' => now()]);
        $this->customer = User::create(['name' => 'Customer', 'email' => 'wpop-cus@test.com', 'password' => bcrypt('password'), 'role' => 'customer', 'is_active' => true, 'email_verified_at' => now()]);
        $this->us = Country::firstOrCreate(['code' => 'US'], ['name' => 'United States', 'currency_code' => 'USD', 'currency_symbol' => '$', 'currency_name' => 'US Dollar', 'is_active' => true, 'sort_order' => 2]);
        $this->uk = Country::firstOrCreate(['code' => 'UK'], ['name' => 'United Kingdom', 'currency_code' => 'GBP', 'currency_symbol' => '£', 'currency_name' => 'British Pound', 'is_active' => true, 'sort_order' => 1]);
        app(CurrencyService::class)->refreshRates(false);
    }

    private function enablePromo(array $categoryIds): void
    {
        Setting::set('promo.enabled', '1', 'promo');
        Setting::set('promo.name', 'Test Offer', 'promo');
        Setting::set('promo.percent', '33', 'promo');
        Setting::set('promo.category_ids', json_encode($categoryIds), 'promo');
        Setting::set('promo.starts_at', now()->subDay()->toIso8601String(), 'promo');
        Setting::set('promo.ends_at', now()->addDays(30)->toIso8601String(), 'promo');
        Setting::set('promo.timezone', 'UTC', 'promo');
        Setting::set('promo.terms', 'Test terms.', 'promo');
    }

    public function test_demo_content_seeders_are_idempotent()
    {
        $this->seed(AssetLifecycleCategorySeeder::class);
        $this->seed(DemoContentExpansionSeeder::class);
        $first = [
            \App\Models\PortfolioItem::where('is_demo', true)->count(),
            \App\Models\CaseStudy::where('is_demo', true)->count(),
            Service::where('is_demo', true)->count(),
        ];
        $this->seed(AssetLifecycleCategorySeeder::class);
        $this->seed(DemoContentExpansionSeeder::class);
        $this->assertEquals($first, [
            \App\Models\PortfolioItem::where('is_demo', true)->count(),
            \App\Models\CaseStudy::where('is_demo', true)->count(),
            Service::where('is_demo', true)->count(),
        ]);
        $this->assertEquals(10, \App\Models\PortfolioItem::where('is_demo', true)->where('category', 'Sample Team Profile')->count());
        $this->assertEquals(10, \App\Models\CaseStudy::where('is_demo', true)->count());
        $this->assertTrue(ServiceCategory::where('slug', AssetLifecycleCategorySeeder::CATEGORY_SLUG)->exists());
    }

    public function test_promo_math_and_eligibility()
    {
        $cat = ServiceCategory::create(['name' => 'Promo Cat', 'slug' => 'promo-cat', 'is_active' => true]);
        $other = ServiceCategory::create(['name' => 'Other Cat', 'slug' => 'other-cat', 'is_active' => true]);
        $this->enablePromo([$cat->id]);
        $svc = new PromotionService;

        $service = Service::create(['category_id' => $cat->id, 'name' => 'Promo Service', 'slug' => 'promo-service', 'price_type' => 'fixed', 'starting_price' => 100, 'is_active' => true]);
        $row = ServiceCountryPrice::create(['service_id' => $service->id, 'country_id' => $this->us->id, 'pricing_type' => 'fixed', 'price' => 100, 'is_active' => true]);

        $deal = $svc->priceFor($service, $row);
        $this->assertTrue($deal['applies']);
        $this->assertEquals(100.0, $deal['original']);
        $this->assertEquals(33.0, $deal['discount']);
        $this->assertEquals(67.0, $deal['final']);

        // Ineligible category.
        $service2 = Service::create(['category_id' => $other->id, 'name' => 'Other Service', 'slug' => 'other-service', 'price_type' => 'fixed', 'starting_price' => 100, 'is_active' => true]);
        $row2 = ServiceCountryPrice::create(['service_id' => $service2->id, 'country_id' => $this->us->id, 'pricing_type' => 'fixed', 'price' => 100, 'is_active' => true]);
        $this->assertFalse($svc->priceFor($service2, $row2)['applies']);

        // Quote-based service excluded.
        $service3 = Service::create(['category_id' => $cat->id, 'name' => 'Quote Service', 'slug' => 'quote-service', 'price_type' => 'custom_quote', 'starting_price' => 0, 'is_active' => true]);
        $row3 = ServiceCountryPrice::create(['service_id' => $service3->id, 'country_id' => $this->us->id, 'pricing_type' => 'custom_quote', 'price' => 0, 'is_active' => true]);
        $this->assertFalse($svc->priceFor($service3, $row3)['applies']);

        // Row-level discount wins — no stacking.
        $row4 = ServiceCountryPrice::create(['service_id' => $service->id, 'country_id' => $this->uk->id, 'pricing_type' => 'fixed', 'price' => 100, 'discount_price' => 80, 'discount_valid_until' => now()->addDays(5), 'is_active' => true]);
        $stacked = $svc->priceFor($service, $row4);
        $this->assertFalse($stacked['applies']);
        $this->assertEquals(80.0, $stacked['final']);
    }

    public function test_promo_expiry_and_disable()
    {
        $cat = ServiceCategory::create(['name' => 'Expiry Cat', 'slug' => 'expiry-cat', 'is_active' => true]);
        $this->enablePromo([$cat->id]);
        $svc = new PromotionService;
        $this->assertTrue($svc->isActive());

        Setting::set('promo.ends_at', now()->subHour()->toIso8601String(), 'promo');
        $this->assertFalse($svc->isActive());

        Setting::set('promo.ends_at', now()->addDays(30)->toIso8601String(), 'promo');
        Setting::set('promo.enabled', '0', 'promo');
        $this->assertFalse($svc->isActive());
    }

    public function test_promo_quote_snapshot_and_no_double_apply()
    {
        $cat = ServiceCategory::create(['name' => 'Quote Cat', 'slug' => 'quote-cat', 'is_active' => true]);
        $this->enablePromo([$cat->id]);
        $service = Service::create(['category_id' => $cat->id, 'name' => 'Quote Promo Service', 'slug' => 'quote-promo-service', 'price_type' => 'fixed', 'starting_price' => 100, 'is_active' => true]);
        ServiceCountryPrice::create(['service_id' => $service->id, 'country_id' => $this->us->id, 'pricing_type' => 'fixed', 'price' => 100, 'is_active' => true]);

        $quote = Quotation::create(['customer_id' => $this->customer->id, 'valid_until' => now()->addDays(30), 'tax_rate' => 10, 'status' => 'draft']);
        $quote->items()->create(['description' => 'Base service', 'quantity' => 1, 'unit_price' => 100, 'discount' => 0, 'total' => 100]);
        $quote->update(['subtotal' => 100, 'tax_amount' => 10, 'total' => 110]);

        $res = $this->actingAs($this->finance)->post("/admin/quotations/{$quote->id}/apply-promo", [
            'promo_service_id' => $service->id, 'promo_country_id' => $this->us->id,
        ]);
        $res->assertRedirect();
        $quote->refresh();
        $this->assertEquals('Test Offer', $quote->promo_campaign);
        $this->assertEquals(33.0, (float) $quote->promo_percent);
        $this->assertEquals(100.0, $quote->promo_snapshot['original_price']);
        $this->assertEquals(33.0, $quote->promo_snapshot['discount_amount']);
        $this->assertEquals(67.0, $quote->promo_snapshot['final_price']);
        // Totals stay consistent: subtotal 67, tax 6.7, total 73.7.
        $this->assertEquals(67.0, (float) $quote->subtotal);
        $this->assertEquals(73.7, (float) $quote->total);

        // Second application rejected.
        $this->actingAs($this->finance)->post("/admin/quotations/{$quote->id}/apply-promo", [
            'promo_service_id' => $service->id, 'promo_country_id' => $this->us->id,
        ])->assertStatus(422);

        // Expiring the campaign leaves history intact.
        Setting::set('promo.enabled', '0', 'promo');
        $quote->refresh();
        $this->assertEquals('Test Offer', $quote->promo_campaign);
        $this->assertEquals(73.7, (float) $quote->total);
    }

    public function test_services_page_shows_usd_reference_and_compact_cards()
    {
        $this->seed(AssetLifecycleCategorySeeder::class);
        $this->seed(PromoCampaignSeeder::class);
        $res = $this->get('/services');
        $res->assertOk();
        $res->assertSee('USD $', false); // USD reference line rendered
        $res->assertSee('term-panel p-4', false); // compact cards
    }

    public function test_industries_page_lists_new_sectors()
    {
        $res = $this->get('/industries');
        $res->assertOk();
        foreach (['Logistics, Transport', 'Hospitality & Accommodation', 'Construction & Property', 'Nonprofits & Membership', 'Home Services & Field Trades'] as $name) {
            $res->assertSee($name);
        }
        // Original sectors preserved.
        $res->assertSee('Healthcare & Life Sciences');
    }

    public function test_portfolio_and_case_studies_render_demo_labels()
    {
        $this->seed(DemoContentExpansionSeeder::class);
        $this->get('/portfolio')->assertOk()->assertSee('Daniel Whitfield', false);
        $this->get('/case-studies')->assertOk()->assertSee('Multi-Office IT Support Modernisation', false);
        $item = \App\Models\PortfolioItem::where('is_demo', true)->first();
        $this->get("/portfolio/{$item->slug}")->assertOk()->assertSee('DEMO / SAMPLE PROFILE', false);
        $case = \App\Models\CaseStudy::where('is_demo', true)->first();
        $this->get("/case-studies/{$case->slug}")->assertOk()->assertSee('ILLUSTRATIVE DEMO SCENARIO', false);
    }

    public function test_promo_settings_admin_roundtrip()
    {
        $cat = ServiceCategory::create(['name' => 'Admin Cat', 'slug' => 'admin-cat', 'is_active' => true]);
        $res = $this->actingAs($this->admin)->post('/admin/settings/promotion', [
            'promo_enabled' => '1', 'promo_name' => 'Admin Offer', 'promo_percent' => '33',
            'promo_category_ids' => [$cat->id],
            'promo_starts_at' => now()->format('Y-m-d\TH:i'), 'promo_ends_at' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'promo_timezone' => 'UTC', 'promo_terms' => 'Admin terms.',
        ]);
        $res->assertRedirect();
        $this->assertTrue(app(PromotionService::class)->isActive());
        $this->actingAs($this->admin)->get('/admin/settings/promotion')->assertOk()->assertSee('Admin Offer', false);
        // Customers cannot reach promo admin.
        $this->actingAs($this->customer)->get('/admin/settings/promotion')->assertStatus(403);
    }
}
