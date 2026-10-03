<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlovePreferenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_touch_preferences_but_get_moving_defaults()
    {
        $this->get(route('glove-preference.show'))->assertRedirect(route('login'));
        $this->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 0.8, 'y' => 0.4])->assertStatus(401);
        $this->postJson(route('glove-preference.reset'))->assertStatus(401);
    }

    /** @test */
    public function default_mode_is_moving_for_everyone()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $response = $this->actingAs($customer)->getJson(route('glove-preference.show'));
        $response->assertOk()->assertJson(['mode' => 'moving', 'x' => null, 'y' => null]);

        $content = $this->actingAs($customer)->get(route('portal.dashboard'))->getContent();
        $this->assertStringContainsString('data-glove-mode="moving"', $content);
        $this->assertStringContainsString('id="glove-control"', $content);
    }

    /** @test */
    public function save_and_restore_fixed_position()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($customer)->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 0.8, 'y' => 0.45])
            ->assertOk()->assertJson(['mode' => 'fixed', 'x' => 0.8, 'y' => 0.45]);

        $content = $this->actingAs($customer)->get(route('portal.dashboard'))->getContent();
        $this->assertStringContainsString('data-glove-mode="fixed"', $content);
        $this->assertStringContainsString('data-glove-x="0.8"', $content);
        $this->assertStringContainsString('data-glove-y="0.45"', $content);
    }

    /** @test */
    public function resume_moving_and_reset_restore_defaults()
    {
        $staff = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $this->actingAs($staff)->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 0.7, 'y' => 0.3])->assertOk();

        // Resume keeps the stored position for later reuse.
        $this->actingAs($staff)->patchJson(route('glove-preference.update'), ['mode' => 'moving', 'x' => 0.7, 'y' => 0.3])
            ->assertOk()->assertJson(['mode' => 'moving', 'x' => 0.7, 'y' => 0.3]);

        $this->actingAs($staff)->postJson(route('glove-preference.reset'))
            ->assertOk()->assertJson(['mode' => 'moving', 'x' => null, 'y' => null]);
        $this->assertNull($staff->fresh()->glove_x);
    }

    /** @test */
    public function invalid_coordinates_are_rejected_and_clamped()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($customer)->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 5, 'y' => 0.4])->assertStatus(422);
        $this->actingAs($customer)->patchJson(route('glove-preference.update'), ['mode' => 'fixed'])->assertStatus(422);
        $this->actingAs($customer)->patchJson(route('glove-preference.update'), ['mode' => 'orbit'])->assertStatus(422);

        // Margins clamp extremes inside the safe area.
        $this->actingAs($customer)->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 0.0, 'y' => 1.0])
            ->assertOk()->assertJson(['x' => 0.08, 'y' => 0.92]);
        $this->assertSame(['moving', null, null], array_values(User::factory()->create()->glovePreference()));
    }

    /** @test */
    public function preferences_are_isolated_per_user()
    {
        $a = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $b = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);

        $this->actingAs($a)->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 0.8, 'y' => 0.45])->assertOk();
        $this->actingAs($b)->getJson(route('glove-preference.show'))->assertOk()->assertJson(['mode' => 'moving']);
        $this->actingAs($b)->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 0.2, 'y' => 0.7])->assertOk();

        $this->assertSame(0.8, $a->fresh()->glovePreference()['x']);
        $this->assertSame(0.2, $b->fresh()->glovePreference()['x']);
    }

    /** @test */
    public function no_user_id_is_accepted_or_trusted()
    {
        $a = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $b = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        // Even with another user's id in the payload, only the session user changes.
        $this->actingAs($a)->patchJson(route('glove-preference.update'), ['mode' => 'fixed', 'x' => 0.8, 'y' => 0.4, 'user_id' => $b->id])->assertOk();
        $this->assertNull($b->fresh()->glove_x);
        $this->assertSame(0.8, $a->fresh()->glovePreference()['x']);
    }

    /** @test */
    public function single_global_scene_markup_is_preserved()
    {
        $content = $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))->get(route('admin.dashboard'))->getContent();
        $this->assertSame(1, substr_count($content, 'id="global-3d-canvas"'));
        $this->assertSame(1, substr_count($content, 'id="global-3d-canvas-fg"'));
        // Foreground layer stays click-through via stylesheet (never blocks UI).
        $this->assertStringContainsString('pointer-events: none', file_get_contents(resource_path('css/app.css')));
    }
}
