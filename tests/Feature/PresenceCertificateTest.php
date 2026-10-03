<?php

namespace Tests\Feature;

use App\Http\Controllers\VerifyCertificateController;
use App\Models\TrainingCertificate;
use App\Models\TrainingCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Presence lifecycle (§18) + certificate verification (§27):
 * explicit state, automatic expiry, roster privacy, signed public
 * verification, revocation enforced, learner isolation intact.
 */
class PresenceCertificateTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag).'.'.Str::random(6).'@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified',
        ]);
    }

    /** @test */
    public function presence_sets_expires_and_respects_privacy()
    {
        $user = $this->person('employee', 'Presence User');
        $other = $this->person('employee', 'Presence Other');

        // Heartbeat sets explicit state.
        $this->actingAs($user)->postJson(route('presence.update'), ['presence' => 'online'])
            ->assertStatus(200)->assertJson(['presence' => 'online', 'label' => 'Available']);
        // Invalid states rejected.
        $this->actingAs($user)->postJson(route('presence.update'), ['presence' => 'invisible'])->assertStatus(422);

        // Stale activity expires to offline (never a ghost "online").
        $user->forceFill(['presence' => 'online', 'last_activity_at' => now()->subMinutes(30)])->save();
        $this->assertEquals('offline', $user->fresh()->effectivePresence());

        // Roster honors hidden visibility for non-privileged viewers.
        $other->forceFill(['presence' => 'online', 'presence_visible' => false, 'last_activity_at' => now()])->save();
        $resp = $this->actingAs($user)->postJson(route('presence.roster'), ['ids' => [$other->id]]);
        $resp->assertStatus(200);
        $this->assertEquals('offline', $resp->json((string) $other->id.'.presence'));
        // Admins still resolve true state where required.
        $admin = $this->person('admin', 'Presence Admin');
        $resp = $this->actingAs($admin)->postJson(route('presence.roster'), ['ids' => [$other->id]]);
        $this->assertEquals('online', $resp->json((string) $other->id.'.presence'));
    }

    /** @test */
    public function certificate_verifies_publicly_and_revokes()
    {
        $learner = $this->person('customer', 'Cert Learner');
        $trainer = $this->person('training_manager', 'Cert Trainer');
        $course = TrainingCourse::create([
            'title' => '[TEST] Cybersecurity Fundamentals', 'slug' => 't-cert-'.Str::random(6),
            'is_published' => true,
        ]);
        $certificate = TrainingCertificate::create([
            'course_id' => $course->id, 'user_id' => $learner->id, 'score' => 92,
            'certificate_no' => 'CERT-'.strtoupper(Str::random(8)), 'completed_at' => now(), 'status' => 'active',
        ]);
        $token = VerifyCertificateController::tokenFor($certificate);
        $this->get(route('verify.certificate', $token))->assertStatus(200)
            ->assertSee('Certificate Valid')->assertSee($certificate->certificate_no);
        // Forged token is not valid.
        $this->get(route('verify.certificate', $certificate->id.'.forged'))->assertStatus(200)->assertSee('Not Valid');

        // Trainer revokes with reason → verifies as not valid, row preserved.
        $this->actingAs($trainer)->post(route('admin.training.certificates.revoke', $certificate->id), ['reason' => 'Synthetic test revocation.'])
            ->assertSessionHasNoErrors();
        $this->assertEquals('revoked', $certificate->fresh()->status);
        $this->get(route('verify.certificate', $token))->assertStatus(200)->assertSee('Not Valid');

        // Learner cannot revoke (trainers/admins only).
        $certificate->update(['status' => 'active', 'revoked_at' => null]);
        $this->actingAs($learner)->post(route('admin.training.certificates.revoke', $certificate->id), ['reason' => 'x'])->assertStatus(403);
    }
}
