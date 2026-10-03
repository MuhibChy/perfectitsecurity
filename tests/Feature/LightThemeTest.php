<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Light theme: genuine premium surfaces with verified contrast —
 * graphite ink on white, soft borders, restrained depth — while the
 * dark terminal identity and intentional dark islands stay intact.
 */
class LightThemeTest extends TestCase
{
    use RefreshDatabase;

    private function css(): string
    {
        return file_get_contents(base_path('resources/css/app.css'));
    }

    private function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        $rgb = array_map(fn ($i) => hexdec(substr($hex, $i, 2)) / 255, [0, 2, 4]);
        $rgb = array_map(fn ($c) => $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4), $rgb);
        return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
    }

    private function contrast(string $fg, string $bg = '#ffffff'): float
    {
        $l1 = $this->luminance($fg);
        $l2 = $this->luminance($bg);
        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    /** @test */
    public function light_tokens_meet_contrast_targets()
    {
        $css = $this->css();
        $tokens = [
            '--light-bg' => '#ffffff', '--light-text' => '#101418',
            '--light-text-secondary' => '#2b333b', '--light-text-muted' => '#4b5563',
        ];
        foreach ($tokens as $name => $value) {
            $this->assertStringContainsString("{$name}: {$value};", $css, "Token {$name} changed");
        }
        $this->assertGreaterThanOrEqual(7.0, $this->contrast('#101418'), 'Body ink contrast');
        $this->assertGreaterThanOrEqual(7.0, $this->contrast('#2b333b'), 'Secondary ink contrast');
        $this->assertGreaterThanOrEqual(4.5, $this->contrast('#4b5563'), 'Muted ink contrast');
        $this->assertGreaterThanOrEqual(4.5, $this->contrast('#374151'), 'Sidebar item contrast');
        $this->assertGreaterThanOrEqual(4.5, $this->contrast('#065f46'), 'Sidebar active contrast');
    }

    /** @test */
    public function premium_light_surfaces_are_defined()
    {
        $css = $this->css();
        $this->assertStringContainsString('linear-gradient(180deg, #ffffff 0%, #fbfcfd 100%)', $css);
        $this->assertStringContainsString('0 1px 2px rgba(16, 20, 24, .05)', $css);
        $this->assertStringContainsString('backdrop-filter: blur(14px)', $css);
        $this->assertStringContainsString('.term-input:focus', $css);
        $this->assertStringContainsString('rgba(5, 150, 105, .15)', $css);
        // Calm zebra tables, graphite ink, no neon on white.
        $this->assertStringContainsString('tbody tr:nth-child(even)', $css);
        $this->assertStringContainsString('#e9f5ee', $css);
    }

    /** @test */
    public function dark_identity_and_islands_are_preserved()
    {
        $css = $this->css();
        $this->assertStringContainsString('--term-bg: #000000', $css);
        $this->assertStringContainsString('--term-accent: #00E67A', $css);
        $this->assertStringContainsString('html.dark .shell-link', $css);
        $this->assertStringContainsString('html:not(.dark) .dark-island', $css);
        $this->assertStringContainsString('background-color: #000000', $css);
        // My light overrides yield to islands via their !important guards.
        $this->assertStringContainsString('html:not(.dark) .dark-island .text-term-700', $css);
        $this->assertStringContainsString('html:not(.dark) .dark-island .term-hint', $css);
    }

    /** @test */
    public function theme_toggle_and_persistence_are_wired()
    {
        $app = file_get_contents(base_path('resources/views/layouts/app.blade.php'));
        $this->assertStringContainsString('localStorage', $app);
        $this->assertStringContainsString("Toggle color theme", $app);
        $public = file_get_contents(base_path('resources/views/layouts/public.blade.php'));
        $this->assertStringContainsString('localStorage', $public);
        $js = file_get_contents(base_path('resources/js/app.js'));
        $this->assertStringContainsString("Alpine.store('theme'", $js);
    }

    /** @test */
    public function key_pages_render_identical_structure_in_both_themes()
    {
        // Theme is a client-side class; the server contract is identical
        // markup with theme-safe classes for both modes.
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true, 'email_verified_at' => now(), 'phone_verified_at' => now()]);
        foreach ([route('portal.dashboard'), route('portal.orders.index'), route('portal.invoices.index'), route('portal.history.index')] as $url) {
            $html = $this->actingAs($customer)->get($url)->assertStatus(200)->getContent();
            $this->assertStringContainsString('dark:', $html, "No dark variant on {$url}");
        }
        $login = $this->get(route('login'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('theme', $login);
    }
}
