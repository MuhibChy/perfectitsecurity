<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dual-glove background guarantees: both glove meshes kept and animated,
 * entire 3D scene layered strictly behind content, never blocking input,
 * exactly one scene instance, responsive fallbacks intact.
 */
class GloveBackgroundLayerTest extends TestCase
{
    use RefreshDatabase;

    private function css(): string
    {
        return (string) file_get_contents(resource_path('css/app.css'));
    }

    private function sceneJs(): string
    {
        return (string) file_get_contents(resource_path('js/global-3d.js'));
    }

    private function fgRule(): string
    {
        preg_match('/\.global-3d-fg\s*\{[^}]*\}/', $this->css(), $m);
        $this->assertNotEmpty($m, '.global-3d-fg rule must exist');

        return $m[0];
    }

    /** @test */
    public function shield_layer_sits_below_content_and_above_world()
    {
        $fg = $this->fgRule();
        preg_match('/z-index:\s*(\d+)/', $fg, $z);
        $this->assertNotEmpty($z, 'fg needs explicit z-index');
        $fgZ = (int) $z[1];
        $this->assertGreaterThanOrEqual(1, $fgZ, 'fg above world canvas layer');
        $this->assertLessThan(10, $fgZ, 'fg strictly below content (z-10)');
    }

    /** @test */
    public function background_never_captures_input_and_never_hides_gloves()
    {
        $css = $this->css();
        // Pointer-transparent layers.
        foreach (['.global-3d {', '.global-3d-fg {', '.global-3d-canvas {', '.global-3d-canvas-fg {'] as $sel) {
            $this->assertStringContainsString($sel, $css);
        }
        $this->assertMatchesRegularExpression('/\.global-3d-fg[^{]*\{[^}]*pointer-events:\s*none/', $css);
        // Contained, not clipped away.
        $this->assertMatchesRegularExpression('/\.global-3d-fg[^{]*\{[^}]*overflow:\s*clip/', $css);
        // Layering only: no hiding outside print + static-fallback selectors.
        $screen = preg_replace('/@media print\s*\{[^}]*\{[^}]*\}[^}]*\}/', '', $css);
        $screen = str_replace('body.global-3d-static .global-3d-canvas, body.global-3d-static .global-3d-canvas-fg { display: none; }', '', $screen);
        $this->assertDoesNotMatchRegularExpression('/\.global-3d-fg[^{]*\{[^}]*(display:\s*none|visibility:\s*hidden|opacity:\s*0[^.0-9])/', $screen);
    }

    /** @test */
    public function both_glove_meshes_kept_and_animated_in_one_scene()
    {
        $js = $this->sceneJs();
        // Both hero meshes of the shield group still built…
        foreach (['shieldWire', 'shieldCore', 'crestMesh', 'dataRings', 'layoutShield'] as $token) {
            $this->assertStringContainsString($token, $js);
        }
        // …and still driven by the single animation loop (no frozen scene)…
        foreach (['shieldWire.rotation', 'shieldCore.rotation', 'crestMesh.position'] as $token) {
            $this->assertStringContainsString($token, $js);
        }
        // …exactly one renderer per layer (world + shield), driven by one loop.
        $this->assertEquals(2, substr_count($js, 'new THREE.WebGLRenderer'), 'world + shield renderers only');
        $this->assertStringContainsString('cancelAnimationFrame', $js, 'destroy path kept');
        $this->assertStringContainsString('window._global3D', $js, 'singleton guard kept');
    }

    /** @test */
    public function dashboards_mount_exactly_one_scene_with_no_hero_duplicate()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $adminHtml = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $portalHtml = $this->actingAs($customer)->get(route('portal.dashboard'))->getContent();
        foreach ([$adminHtml, $portalHtml] as $html) {
            $this->assertEquals(1, substr_count($html, 'id="global-3d-canvas"'));
            $this->assertEquals(1, substr_count($html, 'id="global-3d-canvas-fg"'));
            $this->assertStringNotContainsString('id="hero-canvas"', $html);
        }
    }

    /**
     * Layering contract (numeric, not literal): decorative 3D layers sit
     * strictly below content, and dashboard chrome sits strictly above
     * both. The header was promoted from z-[100] to z-[1000] after this
     * test was written (stronger guarantee, same intent), so the test
     * parses the actual z values and verifies their ORDER instead of one
     * arbitrary class string. A header at/below content level still fails.
     *
     * @test
     */
    public function content_and_chrome_stack_above_the_shield_layer()
    {
        $css = $this->css();
        // Content layer above the shield foreground layer.
        $this->assertMatchesRegularExpression('/main,\s*#main-content\s*\{[^}]*z-index:\s*10/', $css);
        $contentZ = $this->cssZIndex($css, 'main,\s*#main-content');
        $fgZ = $this->cssZIndex($css, '\.global-3d-fg');
        $worldZ = $this->cssZIndex($css, '\.global-3d(?![-\w])');
        $this->assertGreaterThan($fgZ, $contentZ, 'content paints above shield foreground');
        $this->assertGreaterThan($worldZ, $fgZ, 'shield foreground paints above world canvas');

        // Dashboard chrome parsed from the layout (not a hardcoded literal).
        $appBlade = (string) file_get_contents(resource_path('views/layouts/app.blade.php'));
        $headerZ = $this->bladeZIndex($appBlade, 'header');
        $sidebarZ = $this->bladeZIndex($appBlade, 'aside');
        $this->assertGreaterThanOrEqual(100, $headerZ, 'header above all 3D/decor');
        $this->assertGreaterThan($sidebarZ, $headerZ, 'header above sidebar');
        $this->assertGreaterThan($contentZ, $sidebarZ, 'sidebar above content');
        $this->assertGreaterThan($fgZ, $sidebarZ, 'sidebar above shield foreground');
    }

    private function cssZIndex(string $css, string $selector): int
    {
        preg_match('/'.$selector.'\s*\{[^}]*z-index:\s*(\d+)/', $css, $m);
        $this->assertNotEmpty($m, "z-index rule must exist for {$selector}");

        return (int) $m[1];
    }

    private function bladeZIndex(string $blade, string $tag): int
    {
        // Matches Tailwind `z-<n>` and arbitrary `z-[<n>]` on the element.
        preg_match('/<'.$tag.'\b[^>]*\bz-(?:\[(\d+)\]|(\d+))/', $blade, $m);
        $this->assertNotEmpty($m, "<{$tag}> must declare an explicit z-index");

        return (int) ((($m[1] ?? '') !== '') ? $m[1] : $m[2]);
    }
}
