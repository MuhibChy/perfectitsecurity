<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Visual theme: authenticated areas render on a solid-black base with the
 * role-based 3D environment stack (canvas → overlay → glass UI), and each
 * role resolves to its designated background theme.
 */
class RoleThemeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_uses_black_base_and_login_theme()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('role-bg-canvas', false);
        $response->assertSee('role-bg-overlay', false);
        $response->assertSee('role-bg-fallback', false);
        $response->assertSee('data-rolebg="login"', false);
        $response->assertDontSee('cosmic-canvas', false);
    }

    /** @test */
    public function green_blue_brand_identity_is_present()
    {
        // Green + blue primary CTA gradient on auth + quote surfaces.
        $this->get(route('login'))->assertSee('linear-gradient(135deg, #16A34A, #2563EB)', false);
        $this->get(route('get-quote'))->assertSee('linear-gradient(135deg, #16A34A, #2563EB)', false);
        // Compiled theme tokens carry both brand scales (built CSS artifact).
        $css = collect(glob(public_path('build/assets/*.css')))
            ->map(fn ($f) => file_get_contents($f))->join("\n");
        $this->assertStringContainsString('#16A34A', $css);
        $this->assertStringContainsString('#2563EB', $css);
        $this->assertStringNotContainsString('#FF0000', $css);
    }

    /** @test */
    public function each_role_resolves_to_its_background_theme()
    {
        $cases = [
            'super_admin' => ['command', 'admin.dashboard'],
            'admin' => ['command', 'admin.dashboard'],
            'finance_manager' => ['fintech', 'admin.dashboard'],
            'support_agent' => ['soc', 'admin.dashboard'],
            'project_manager' => ['datacenter', 'admin.dashboard'],
            'sales_agent' => ['business', 'admin.dashboard'],
            'customer' => ['secure', 'portal.dashboard'],
        ];

        foreach ($cases as $role => [$theme, $route]) {
            $user = User::factory()->create(['role' => $role, 'is_active' => true]);
            $response = $this->actingAs($user)->get(route($route));
            $response->assertStatus(200);
            $response->assertSee('role-bg-canvas', false);
            $response->assertSee('data-rolebg="' . $theme . '"', false);
        }
    }

    /** @test */
    public function background_canvas_never_blocks_ui()
    {
        // Canvas + overlay must be non-interactive and behind content (z-0/z-1).
        $response = $this->get(route('login'));
        $response->assertSee('pointer-events-none', false);
        $response->assertSee('role-bg-overlay', false);
    }

    /** @test */
    public function global_space_background_renders_on_all_shells()
    {
        // Public layout, auth screens, and authenticated shell each mount
        // exactly one global universe (component, not per-page copies).
        $this->get(route('home'))->assertSee('gsb-spotlight', false);
        $this->get(route('login'))->assertSee('gsb-spotlight', false);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $content = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();
        $this->assertEquals(1, substr_count($content, 'gsb-spotlight'));
        $this->assertEquals(1, substr_count($content, 'id="role-bg-canvas"'));
    }
}
