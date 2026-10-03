<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 9 — Accessibility audit (public + auth surfaces).
 *
 * Verifies the durable accessibility contract of the shared layouts and
 * auth forms: language metadata, viewport, skip-link + target, single
 * h1 landmark, accessible mobile-menu toggle, focus-visible ring, and
 * associated form labels on the login/register pages.
 */
class AccessibilityAuditTest extends TestCase
{
    use RefreshDatabase;

    public static function publicPageRoutes(): array
    {
        return [
            ['home'],
            ['about'],
            ['contact'],
            ['services.index'],
            ['get-quote'],
            ['faq'],
            ['pricing'],
        ];
    }

    /** @test */
    public function public_pages_have_skip_link_unique_main_target_and_lang()
    {
        foreach (self::publicPageRoutes() as [$route]) {
            $html = $this->get(route($route))->getContent();

            $this->assertMatchesRegularExpression('/<html[^>]*\slang="/', $html, "[$route] html lang attribute missing");
            $this->assertStringContainsString('name="viewport"', $html, "[$route] viewport meta missing");
            $this->assertStringContainsString('href="#main-content"', $html, "[$route] skip link missing");
            $this->assertEquals(1, substr_count($html, 'id="main-content"'), "[$route] main-content target must exist exactly once");
            $this->assertMatchesRegularExpression('/<h1\b/', $html, "[$route] missing h1 heading");
        }
    }

    /** @test */
    public function mobile_menu_toggle_is_screen_reader_accessible()
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString('aria-label="Toggle menu"', $html);
        $this->assertStringContainsString('aria-controls="mobile-menu"', $html);
        $this->assertStringContainsString('id="mobile-menu"', $html);
        $this->assertStringContainsString(':aria-expanded', $html);
    }

    /** @test */
    public function focus_visible_ring_and_skip_link_css_are_delivered()
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('.skip-link', $css);
        $this->assertStringContainsString('.skip-link:focus', $css);
    }

    /** @test */
    public function auth_forms_have_html_lang_and_accessible_headings()
    {
        foreach (['login', 'register'] as $route) {
            $html = $this->get(route($route))->getContent();

            $this->assertMatchesRegularExpression('/<html[^>]*\slang="/', $html, "[$route] html lang attribute missing");
            $this->assertMatchesRegularExpression('/<h1\b/', $html, "[$route] missing h1 heading");
            $this->assertStringContainsString('name="viewport"', $html, "[$route] viewport meta missing");
        }
    }

    /** @test */
    public function every_login_control_has_an_associated_label()
    {
        $html = $this->get(route('login'))->getContent();

        $controls = $this->labelledControls($html);
        $this->assertNotCount(0, $controls, 'no id-bearing form controls found on login');
        foreach ($controls as $id) {
            $this->assertTrue(
                str_contains($html, 'for="' . $id . '"'),
                "login control #$id has no matching label[for=]"
            );
        }
    }

    /** @test */
    public function every_register_control_has_an_associated_label()
    {
        $html = $this->get(route('register'))->getContent();

        $controls = $this->labelledControls($html);
        $this->assertNotCount(0, $controls, 'no id-bearing form controls found on register');
        foreach ($controls as $id) {
            $this->assertTrue(
                str_contains($html, 'for="' . $id . '"'),
                "register control #$id has no matching label[for=]"
            );
        }
    }

    /** @return string[] */
    private function labelledControls(string $html): array
    {
        preg_match_all('/<(input|select|textarea)\b[^>]*\bid="([^"]+)"/', $html, $m, PREG_SET_ORDER);

        return array_map(fn ($c) => $c[2], array_filter($m, function ($c) {
            return ! str_contains($c[0], 'type="hidden"');
        }));
    }
}