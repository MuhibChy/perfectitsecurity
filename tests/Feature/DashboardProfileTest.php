<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Logged-in dashboard profile card: name / email / contact / picture,
 * completion states, edit persistence, and owner isolation.
 */
class DashboardProfileTest extends TestCase
{
    use RefreshDatabase;

    private function customer(array $over = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer', 'is_active' => true,
            'phone' => '+447700900111',
        ], $over));
    }

    /** @test */
    public function dashboard_shows_authenticated_owner_profile()
    {
        $user = $this->customer(['name' => 'Dana Profile', 'phone' => '+447700900112', 'avatar' => 'avatars/dana.png']);
        $content = $this->actingAs($user)->get(route('portal.dashboard'))
            ->assertStatus(200)->getContent();
        $this->assertStringContainsString('Dana Profile', $content);
        $this->assertStringContainsString($user->email, $content);
        $this->assertStringContainsString('+447700900112', $content);
        $this->assertStringContainsString(route('avatar.show', $user->id), $content);
    }

    /** @test */
    public function dashboard_handles_missing_contact_and_missing_picture()
    {
        $noPhone = $this->customer(['phone' => null]);
        $content = $this->actingAs($noPhone)->get(route('portal.dashboard'))
            ->assertStatus(200)->getContent();
        $this->assertStringContainsString('Not provided', $content);
        $this->assertStringContainsString('Profile Status: Incomplete', $content);

        $noAvatar = $this->customer(['avatar' => null]);
        $content2 = $this->actingAs($noAvatar)->get(route('portal.dashboard'))
            ->assertStatus(200)->getContent();
        // Professional default avatar, never a broken image.
        $this->assertStringContainsString('ui-avatars.com', $content2);
    }

    /** @test */
    public function user_a_never_sees_user_b_profile()
    {
        $a = $this->customer(['name' => 'User Alpha']);
        $b = $this->customer(['name' => 'User Beta']);
        $content = $this->actingAs($a)->get(route('portal.dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('User Alpha', $content);
        $this->assertStringNotContainsString('User Beta', $content);
        $this->assertStringNotContainsString($b->email, $content);
    }

    /** @test */
    public function profile_update_reflects_on_dashboard_and_survives_relogin()
    {
        $user = $this->customer(['phone' => null]);
        $this->actingAs($user)->put(route('portal.profile.update'), [
            'name' => 'Dana Updated',
            'email' => $user->email,
            'phone' => '+447700900199',
        ])->assertRedirect(route('portal.profile.edit'));

        $user->refresh();
        $this->assertSame('Dana Updated', $user->name);
        // Phone change resets phone verification (takeover protection).
        $this->assertNull($user->phone_verified_at);

        $content = $this->actingAs($user)->get(route('portal.dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Dana Updated', $content);
        $this->assertStringContainsString('+447700900199', $content);

        // Re-login: data persists (DB-backed, not session-cached).
        auth()->logout();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();
        $content2 = $this->get(route('portal.dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Dana Updated', $content2);
    }

    private function realPng(string $name): UploadedFile
    {
        // 1x1 transparent PNG bytes (no GD required for fake()->image()).
        $path = tempnam(sys_get_temp_dir(), 'ava').'.png';
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /** @test */
    public function avatar_upload_replace_and_delete_old()
    {
        $user = $this->customer();
        $this->actingAs($user)->put(route('portal.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $this->realPng('me.png'),
        ])->assertRedirect();
        $first = $user->fresh()->avatar;
        $this->assertNotEmpty($first);
        $this->assertTrue(Storage::disk('private')->exists($first));

        $this->actingAs($user)->put(route('portal.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $this->realPng('me2.png'),
        ])->assertRedirect();
        $second = $user->fresh()->avatar;
        $this->assertNotSame($first, $second);
        $this->assertFalse(Storage::disk('private')->exists($first), 'old avatar removed');
        $this->assertTrue(Storage::disk('private')->exists($second));

        // Malicious / non-image rejected.
        $this->actingAs($user)->put(route('portal.profile.update'), [
            'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
            'avatar' => UploadedFile::fake()->create('evil.php', 100, 'application/x-php'),
        ])->assertSessionHasErrors('avatar');

        Storage::disk('private')->delete([$first, $second]);
    }

    /** @test */
    public function dropdown_exposes_profile_settings_for_both_roles()
    {
        $customer = $this->customer();
        $c = $this->actingAs($customer)->get(route('portal.dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Profile Settings', $c);
        $this->assertStringContainsString(route('portal.profile.edit'), $c);

        $staff = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $s = $this->actingAs($staff)->get(route('admin.dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Profile Settings', $s);
        $this->assertStringContainsString(route('admin.my-profile.edit'), $s);
        // Existing items intact.
        $this->assertStringContainsString('Two-Factor Auth', $s);
    }

    /** @test */
    public function customer_password_change_cycles_login()
    {
        $user = $this->customer();
        // Wrong current password rejected.
        $this->actingAs($user)->put(route('portal.profile.password.update'), [
            'current_password' => 'wrong-pass',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->put(route('portal.profile.password.update'), [
            'current_password' => 'password',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ])->assertRedirect();

        auth()->logout();
        // Old password dead, new password works.
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->post(route('login'), ['email' => $user->email, 'password' => 'NewSecret123!'])
            ->assertRedirect();
    }

    /** @test */
    public function staff_password_change_cycles_login()
    {
        $staff = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $this->actingAs($staff)->post(route('admin.my-profile.password'), [
            'current_password' => 'password',
            'password' => 'StaffNew123!',
            'password_confirmation' => 'StaffNew123!',
        ])->assertRedirect();

        auth()->logout();
        $this->post(route('login'), ['email' => $staff->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->post(route('login'), ['email' => $staff->email, 'password' => 'StaffNew123!'])
            ->assertRedirect();
    }

    /** @test */
    public function guests_cannot_reach_dashboard_or_profile_update()
    {
        $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
        $this->put(route('portal.profile.update'), ['name' => 'X', 'email' => 'x@y.zz'])
            ->assertRedirect(route('login'));
    }
}
