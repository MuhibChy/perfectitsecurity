<?php

namespace Tests\Feature;

use App\Models\IdentityDocument;
use App\Models\MemberIdCard;
use App\Models\User;
use App\Services\MemberIdCardService;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Secure member identity: member IDs, avatar isolation, ID verification
 * lifecycle, digital ID + signed QR (incl. revocation), 2FA enrollment /
 * recovery / replay protection, and profile isolation. Synthetic files only.
 */
class MemberIdentityTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => \Illuminate\Support\Str::slug($tag) . '.' . \Illuminate\Support\Str::random(5) . '@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'country' => 'GB',
        ]);
    }

    /** Real 1x1 PNG bytes (no GD extension on this PHP): passes MIME validation. */
    private function fakeImage(string $name = 'id-document.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $path = tempnam(sys_get_temp_dir(), 'tst') . '.png';
        file_put_contents($path, $png);
        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function fakeId(): UploadedFile
    {
        return $this->fakeImage('id-document.png');
    }

    /** @test */
    public function member_numbers_are_unique_and_prefixed()
    {
        $c = $this->person('customer', 'Identity Customer');
        $e = $this->person('employee', 'Identity Employee');
        $a = $this->person('admin', 'Identity Admin');
        $this->assertMatchesRegularExpression('/^CUS-\d{6}$/', $c->fresh()->member_number);
        $this->assertMatchesRegularExpression('/^EMP-\d{6}$/', $e->fresh()->member_number);
        $this->assertMatchesRegularExpression('/^ADM-\d{6}$/', $a->fresh()->member_number);
        $this->assertEquals(3, User::whereIn('id', [$c->id, $e->id, $a->id])->distinct('member_number')->count('member_number'));
    }

    /** @test */
    public function verification_lifecycle_submit_review_approve_card_qr_revoke()
    {
        Storage::fake('private');
        $customer = $this->person('customer', 'Verify Customer');
        $admin = $this->person('admin', 'Verify Admin');

        // Submit → DOCUMENT_SUBMITTED, never auto-verified.
        $this->actingAs($customer)->post(route('identity.store'), [
            'document_type' => 'passport', 'issuing_country' => 'GBR', 'document' => $this->fakeId(),
        ])->assertSessionHasNoErrors();
        $doc = IdentityDocument::where('user_id', $customer->id)->firstOrFail();
        $this->assertEquals('submitted', $doc->status);
        $this->assertEquals('submitted', $customer->fresh()->identity_status);
        $this->assertFalse($customer->fresh()->isIdentityVerified());

        // Ordinary employee cannot review.
        $employee = $this->person('employee', 'Verify Employee');
        $this->actingAs($employee)->post(route('admin.identity.approve', $doc->id))->assertStatus(403);

        // Admin review → approve → VERIFIED.
        $this->actingAs($admin)->post(route('admin.identity.review', $doc->id))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.identity.approve', $doc->id))->assertSessionHasNoErrors();
        $this->assertEquals('verified', $doc->fresh()->status);
        $this->assertTrue($customer->fresh()->isIdentityVerified());

        // Digital ID card + signed QR verifies.
        ['card' => $card, 'token' => $token] = app(MemberIdCardService::class)->issue($customer->fresh());
        $this->assertEquals('active', $card->status);
        $this->get(route('verify.member', $token))->assertStatus(200)->assertSee('Card Valid')->assertSee($customer->member_number);
        // Authorized PDF download; another customer is refused.
        $this->actingAs($customer)->get(route('idcard.pdf'))->assertStatus(200);
        $stranger = $this->person('customer', 'Verify Stranger');
        $this->actingAs($stranger)->get(route('idcard.pdf'))->assertStatus(404);

        // Revoke → QR shows revoked, never valid.
        $this->actingAs($customer)->post(route('idcard.revoke', $card->id), ['reason' => 'Synthetic test revocation.'])
            ->assertSessionHasNoErrors();
        $this->assertEquals('revoked', $card->fresh()->status);
        $this->get(route('verify.member', $token))->assertStatus(200)->assertSee('CARD REVOKED');
    }

    /** @test */
    public function identity_documents_are_idor_protected()
    {
        Storage::fake('private');
        $alpha = $this->person('customer', 'Doc Alpha');
        $beta = $this->person('customer', 'Doc Beta');
        $this->actingAs($alpha)->post(route('identity.store'), [
            'document_type' => 'national_id', 'document' => $this->fakeId(),
        ])->assertSessionHasNoErrors();
        $doc = IdentityDocument::where('user_id', $alpha->id)->firstOrFail();

        // Beta cannot download Alpha's document (404, no oracle).
        $this->actingAs($beta)->get(route('identity.download', $doc->id))->assertStatus(404);
        // Non-admin staff cannot open the admin queue.
        $employee = $this->person('employee', 'Doc Employee');
        $this->actingAs($employee)->get(route('admin.identity.index'))->assertStatus(403);
        $this->actingAs($employee)->get(route('admin.identity.show', $doc->id))->assertStatus(403);
    }

    /** @test */
    public function avatar_delivery_respects_ownership()
    {
        Storage::fake('private');
        $alpha = $this->person('customer', 'Avatar Alpha');
        $beta = $this->person('customer', 'Avatar Beta');
        $path = $this->fakeImage('photo.png')->store('avatars', 'private');
        $alpha->update(['avatar' => $path]);

        // Owner can view; unrelated customer gets 404 (contacts default).
        $this->actingAs($alpha)->get(route('avatar.show', $alpha->id))->assertStatus(200);
        $this->actingAs($beta)->get(route('avatar.show', $alpha->id))->assertStatus(404);
    }

    /** @test */
    public function avatar_is_invisible_to_guests()
    {
        Storage::fake('private');
        $alpha = $this->person('customer', 'Avatar Guest');
        $path = $this->fakeImage('photo.png')->store('avatars', 'private');
        $alpha->update(['avatar' => $path]);
        $this->get(route('avatar.show', $alpha->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function totp_enrollment_encrypts_secret_and_gates_dashboard()
    {
        $totp = app(TotpService::class);
        $customer = $this->person('customer', 'Mfa Customer');

        $this->actingAs($customer)->get(route('mfa.setup'))->assertStatus(200)->assertSee('Two-Factor Setup');
        $secret = session('mfa_setup_secret');
        $code = $totp->at($secret, (int) floor(time() / 30));
        $this->actingAs($customer)->post(route('mfa.enable'), ['code' => $code])->assertRedirect(route('mfa.setup'));
        $customer = $customer->fresh();
        $this->assertTrue($customer->hasMfaEnabled());
        // Secrets encrypted at rest: raw column value must not equal plaintext.
        $this->assertNotEquals($secret, $customer->getAttributes()['two_factor_secret']);
        $this->assertEquals($secret, $customer->two_factor_secret);

        // Challenge gates the dashboard until passed; wrong code fails.
        $this->actingAs($customer)->withSession(['mfa_passed' => null])
            ->get(route('portal.dashboard'))->assertRedirect(route('mfa.challenge'));
        $this->actingAs($customer)->post(route('mfa.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
    }

    /** @test */
    public function recovery_code_login_consumes_single_use_code()
    {
        $totp = app(TotpService::class);
        $user = $this->person('customer', 'Recovery Customer');
        $this->actingAs($user)->get(route('mfa.setup'));
        $secret = session('mfa_setup_secret');
        $this->actingAs($user)->post(route('mfa.enable'), ['code' => $totp->at($secret, (int) floor(time() / 30))]);
        $codes = session('recovery_codes');
        $this->assertCount(8, $codes);
        // Stored hashes only: no plaintext code anywhere in the raw column.
        $raw = $user->fresh()->getAttributes()['two_factor_recovery_codes'];
        foreach ($codes as $c) $this->assertStringNotContainsString($c, $raw);

        // Login with a recovery code succeeds once…
        $this->actingAs($user)->withSession(['mfa_passed' => null])
            ->post(route('mfa.verify'), ['code' => $codes[0]])->assertRedirect();
        // …and the same code is dead afterwards.
        $this->assertFalse($user->fresh()->consumeRecoveryCode($codes[0]));
        $this->assertEquals(7, count($user->fresh()->two_factor_recovery_codes ?? []));
    }

    /** @test */
    public function profile_settings_are_isolated_per_member()
    {
        $alpha = $this->person('customer', 'Settings Alpha');
        $beta = $this->person('customer', 'Settings Beta');
        \App\Models\UserSetting::set($alpha, 'privacy', 'avatar_visibility', 'hidden');
        $this->assertEquals('hidden', \App\Models\UserSetting::get($alpha, 'privacy', 'avatar_visibility'));
        $this->assertNull(\App\Models\UserSetting::get($beta, 'privacy', 'avatar_visibility'));
    }

    /** @test */
    public function security_dashboard_and_id_card_require_auth_and_authorization()
    {
        $customer = $this->person('customer', 'Secure Dash');
        $other = $this->person('customer', 'Secure Other');
        $this->get(route('security.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($customer)->get(route('security.dashboard'))->assertStatus(200)->assertSee($customer->member_number);
        $this->actingAs($customer)->get(route('identity.index'))->assertStatus(200);
        $this->actingAs($customer)->get(route('idcard.show'))->assertStatus(200);
        // Revoking someone else's card is forbidden.
        ['card' => $card] = app(MemberIdCardService::class)->issue($other);
        $this->actingAs($customer)->post(route('idcard.revoke', $card->id), ['reason' => 'x'])->assertStatus(403);
    }
}
