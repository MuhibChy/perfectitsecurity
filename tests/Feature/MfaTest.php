<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Two-factor authentication: enrollment, challenge enforcement, and lockout
 * of MFA-enabled staff until verification. Uses real TOTP codes.
 */
class MfaTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        return User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
    }

    /** @test */
    public function guest_is_redirected_from_mfa_pages()
    {
        $this->get(route('mfa.setup'))->assertRedirect(route('login'));
        $this->get(route('mfa.challenge'))->assertRedirect(route('login'));
    }

    /** @test */
    public function customer_cannot_open_mfa_setup()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($customer)->get(route('mfa.setup'))->assertStatus(403);
    }

    /** @test */
    public function staff_can_view_mfa_setup_with_qr()
    {
        $this->actingAs($this->staff())->get(route('mfa.setup'))
            ->assertStatus(200)
            ->assertSee('Two-Factor Setup');
    }

    /** @test */
    public function staff_can_complete_full_totp_enrollment_and_challenge()
    {
        $totp = app(TotpService::class);
        $user = $this->staff();

        // 1. Open setup (stores pending secret in session).
        $this->actingAs($user)->get(route('mfa.setup'))->assertStatus(200);

        // 2. Enabling with a wrong code fails.
        $this->actingAs($user)->post(route('mfa.enable'), ['code' => '000000'])
            ->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasMfaEnabled());

        // 3. Enabling with the correct TOTP code succeeds.
        $secret = session('mfa_setup_secret');
        $this->assertNotEmpty($secret);
        $code = $totp->at($secret, (int) floor(time() / 30));
        $this->actingAs($user)->post(route('mfa.enable'), ['code' => $code])
            ->assertRedirect(route('mfa.setup'));
        $this->assertTrue($user->fresh()->hasMfaEnabled());

        // 4. A fresh session without mfa_passed is redirected to the challenge.
        $user->fresh();
        $this->actingAs($user)->withSession(['mfa_passed' => null])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('mfa.challenge'));

        // 5. Challenge page renders; wrong code rejected, correct code passes.
        $this->actingAs($user)->get(route('mfa.challenge'))->assertStatus(200);
        $this->actingAs($user)->post(route('mfa.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $userSecret = $user->fresh()->two_factor_secret;
        $valid = $totp->at($userSecret, (int) floor(time() / 30));
        $this->actingAs($user)->post(route('mfa.verify'), ['code' => $valid])
            ->assertRedirect();

        // 6. Disable with a valid code turns MFA off.
        $off = $totp->at($userSecret, (int) floor(time() / 30));
        $this->actingAs($user)->post(route('mfa.disable'), ['code' => $off])
            ->assertRedirect(route('mfa.setup'));
        $this->assertFalse($user->fresh()->hasMfaEnabled());
    }

    /** @test */
    public function staff_without_mfa_is_not_interrupted()
    {
        $this->actingAs($this->staff())->get(route('admin.dashboard'))->assertStatus(200);
    }
}
