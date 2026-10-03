<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Responsive regression: shared guards that keep every viewport usable.
 * Markup/CSS-only assertions — no backend behavior is altered by them.
 */
class ResponsiveAuditTest extends TestCase
{
    use RefreshDatabase;

    private function layout(string $view): string
    {
        return file_get_contents(resource_path("views/{$view}.blade.php"));
    }

    /** @test */
    public function mobile_menu_scrolls_and_matches_desktop_links()
    {
        $layout = $this->layout('layouts/public');
        // Scrollable mobile menu sized to the h-14 (3.5rem) nav.
        $this->assertStringContainsString('max-h-[calc(100dvh-3.5rem)] overflow-y-auto', $layout);
        $this->assertStringContainsString('aria-expanded', $layout);
        $this->assertStringContainsString('@keydown.escape', $layout);
        // Mobile menu links cover the primary desktop destinations; primary links appear exactly once each.
        $mobileMenu = substr($layout, strpos($layout, 'id="mobile-menu"'));
        $mobileMenu = substr($mobileMenu, 0, strpos($mobileMenu, 'pt-4 space-y-2.5'));
        foreach (["route('services.index')", "route('case-studies')", "route('portfolio.index')", "route('kb.index')", "route('about')", "route('contact')"] as $r) {
            $this->assertEquals(1, substr_count($mobileMenu, $r), $r);
        }
        // FAQ is a single footer link (not duplicated inside the mobile menu); the desktop-only
        // Information pages remain reachable somewhere on the page (mobile menu or footer).
        $this->assertEquals(1, substr_count($layout, "route('faq')"));
        foreach (["route('industries')", "route('case-studies')", "route('portfolio.index')", "route('careers')"] as $r) {
            $this->assertStringContainsString($r, $layout);
        }
    }

    /** @test */
    public function expense_table_scrolls_inside_its_card()
    {
        $view = $this->layout('admin/work-orders/show');
        $this->assertStringContainsString('overflow-x-auto -mx-6 px-6', $view);
        $this->assertStringContainsString('min-w-[640px]', $view);
    }

    /** @test */
    public function upload_modal_fits_short_viewports()
    {
        $view = $this->layout('customer/documents/index');
        $this->assertStringContainsString('p-4 overflow-y-auto', $view);
        $this->assertStringContainsString('max-h-[calc(100dvh-2rem)] overflow-y-auto', $view);
    }

    /** @test */
    public function ai_widget_handles_narrow_viewports()
    {
        $widget = $this->layout('components/ai-chat-widget');
        $this->assertStringContainsString('ai-messages', $widget);
        $this->assertStringContainsString('break-words', $widget);
        $this->assertStringContainsString('min-w-0', $widget);
        $this->assertStringContainsString('open-ai-chat', $widget);
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('100dvh - 1.5rem', $css);
        $this->assertStringContainsString('safe-area-inset-bottom', $css);
    }

    /** @test */
    public function global_css_backstops_overflow_and_reveal()
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('overflow-x: clip', $css);
        $this->assertStringContainsString('.data-table tbody td { overflow-wrap: anywhere; }', $css);
        $this->assertStringContainsString('Pagination Navigation', $css);
        $this->assertStringContainsString('.reveal, .reveal-left, .reveal-right { opacity: 1; transform: none; }', $css);
    }

    /** @test */
    public function premium_cta_gradients_use_brand_identity()
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));
        $mono = [];
        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $content = file_get_contents($file->getPathname());
                if (str_contains($content, 'linear-gradient(135deg, #333333, #000000)')) {
                    $mono[] = $file->getPathname();
                }
            }
        }
        $this->assertEmpty($mono, 'Retired monochrome CTAs remain: ' . implode(', ', $mono));
    }

    /** @test */
    public function canvases_pause_and_cap_cost_on_mobile()
    {
        $cosmic = file_get_contents(resource_path('js/cosmic-bg.js'));
        $this->assertStringContainsString('visibilitychange', $cosmic);
        $this->assertStringContainsString('devicePixelRatio', $cosmic);
        $hero = file_get_contents(resource_path('js/hero-3d.js'));
        // Premium green/blue identity carried into the 3D rig.
        $this->assertStringContainsString('0x06b6d4', $hero);
        $role = file_get_contents(resource_path('js/role-bg.js'));
        $this->assertStringContainsString('#22C55E', $role);
    }

    /** @test */
    public function key_pages_render_for_customer_and_admin()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($customer)->get(route('portal.dashboard'))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.tickets.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.tickets.index'))->assertStatus(200);
        $this->get(route('home'))->assertStatus(200);
        $this->get(route('login'))->assertStatus(200);
    }
}
