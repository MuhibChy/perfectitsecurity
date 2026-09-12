<?php

namespace Tests\Feature;

use App\Models\SecurityFinding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vulnerability tracker + SBOM + security dashboard: CRUD, validation,
 * KEV workflow, admin-only authorization, audit trail.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'title' => 'Outdated dompdf with SVG file-read',
            'description' => 'Vendor advisory GHSA-j8qw-6jw8-r297.',
            'cve' => 'CVE-2026-59943',
            'severity' => 'high',
            'cvss_score' => 7.5,
            'cvss_vector' => 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:N/A:N',
            'cvss_version' => '3.1',
            'epss_score' => 0.12,
            'affected_asset' => 'composer: dompdf/dompdf',
            'affected_version' => '<3.1.6',
            'mitre_technique' => 'T1595',
            'status' => 'open',
            'due_date' => now()->addDays(14)->toDateString(),
            'evidence' => 'composer audit output attached.',
            'remediation' => 'Upgrade to >= 3.1.6 with framework upgrade.',
            'discovered_source' => 'composer audit',
        ], $over);
    }

    /** @test */
    public function admin_full_finding_lifecycle_with_audit()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.security-findings.store'), $this->payload())
            ->assertRedirect();
        $finding = SecurityFinding::firstOrFail();
        $this->assertStringStartsWith('SEC-', $finding->finding_number);
        $this->assertDatabaseHas('audit_logs', ['action' => 'create', 'module' => 'security_findings']);

        // CVSS band helper + KEV filter.
        $this->assertEquals('high', $finding->cvssBand());
        $this->actingAs($admin)->get(route('admin.security-findings.index', ['kev' => 1]))
            ->assertStatus(200)->assertDontSee($finding->finding_number);

        // Mark KEV + resolve → resolved_at set + audit.
        $this->actingAs($admin)->put(route('admin.security-findings.update', $finding), $this->payload([
            'status' => 'resolved', 'is_known_exploited' => '1',
        ]))->assertRedirect();
        $finding->refresh();
        $this->assertTrue((bool) $finding->is_known_exploited);
        $this->assertNotNull($finding->resolved_at);
        $this->actingAs($admin)->get(route('admin.security-findings.index', ['kev' => 1]))
            ->assertStatus(200)->assertSee($finding->finding_number);

        // Verify stamps verified_at.
        $this->actingAs($admin)->put(route('admin.security-findings.update', $finding), $this->payload(['status' => 'verified']))
            ->assertRedirect();
        $this->assertNotNull($finding->fresh()->verified_at);

        // Destroy archives (soft delete) with audit.
        $this->actingAs($admin)->delete(route('admin.security-findings.destroy', $finding))->assertRedirect();
        $this->assertSoftDeleted('security_findings', ['id' => $finding->id]);
    }

    /** @test */
    public function finding_validation_rejects_bad_scores_and_vectors()
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.security-findings.store'), $this->payload(['cvss_score' => 11]))
            ->assertSessionHasErrors('cvss_score');
        $this->actingAs($admin)->post(route('admin.security-findings.store'), $this->payload(['epss_score' => 2]))
            ->assertSessionHasErrors('epss_score');
        $this->actingAs($admin)->post(route('admin.security-findings.store'), $this->payload(['cve' => 'not-a-cve']))
            ->assertSessionHasErrors('cve');
        $this->actingAs($admin)->post(route('admin.security-findings.store'), $this->payload(['mitre_technique' => 'XYZ']))
            ->assertSessionHasErrors('mitre_technique');
        $this->assertEquals(0, SecurityFinding::count());
    }

    /** @test */
    public function non_admins_cannot_touch_findings()
    {
        $finding = SecurityFinding::create([
            'title' => 'Guarded', 'severity' => 'medium', 'status' => 'open',
        ]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $agent = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);

        foreach ([$customer, $agent] as $user) {
            $this->actingAs($user)->get(route('admin.security-findings.index'))->assertStatus(403);
            $this->actingAs($user)->get(route('admin.security-findings.show', $finding))->assertStatus(403);
            $this->actingAs($user)->post(route('admin.security-findings.store'), $this->payload())->assertStatus(403);
        }
        $this->post(route('logout'));
        $this->get(route('admin.security-findings.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function sbom_and_dashboard_are_admin_only_and_render_inventory()
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.sbom'))->assertStatus(200)->assertSee('laravel/framework');
        $this->actingAs($admin)->get(route('admin.security.dashboard'))->assertStatus(200)->assertSee('Security Posture');

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($customer)->get(route('admin.sbom'))->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.security.dashboard'))->assertStatus(403);
    }
}
