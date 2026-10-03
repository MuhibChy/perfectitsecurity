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
        // Terminal auth shell: AUTH identity on the global fixed HUD backdrop
        // (single shared scene + single HUD instance, never per-page copies).
        $response->assertSee('class="term-bg"', false);
        $response->assertSee('AUTH://IDENTITY', false);
        $response->assertSee('data-lights="auth"', false);
        $response->assertSee('bg-black', false);
        $response->assertDontSee('cosmic-canvas', false);
        $response->assertSee('id="global-3d-canvas"', false);
        $response->assertSee('id="global-hud"', false);
        $response->assertDontSee('id="role-bg-canvas"', false);
    }

    /** @test */
    public function green_blue_brand_identity_is_present()
    {
        // Terminal CTA system on auth + quote surfaces (accent-green primary).
        $this->get(route('login'))->assertSee('term-btn', false);
        $this->get(route('get-quote'))->assertSee('term-btn', false);
        // Compiled theme tokens carry the terminal accent scale (built CSS artifact).
        $css = collect(glob(public_path('build/assets/*.css')))
            ->map(fn ($f) => file_get_contents($f))->join("\n");
        $this->assertStringContainsString('#00E67A', $css);
        $this->assertStringContainsString('#050807', $css);
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
        // Terminal environment must be decorative and behind content (z-0).
        $response = $this->get(route('login'));
        $response->assertSee('class="term-bg"', false);
        $response->assertSee('aria-hidden="true"', false);
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('pointer-events: none;', $css);
    }

    /** @test */
    public function global_space_background_renders_on_all_shells()
    {
        // Public shell uses the pure-CSS terminal environment (no WebGL);
        // auth screens use the same terminal environment standalone;
        // the authenticated shell mounts exactly one role canvas.
        $this->get(route('home'))->assertSee('class="term-bg"', false);
        $this->get(route('login'))->assertSee('class="term-bg"', false);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $content = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();
        $this->assertEquals(1, substr_count($content, 'id="role-bg-canvas"'));
    }
}
