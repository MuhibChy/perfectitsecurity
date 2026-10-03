<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Support\RoleRegistry;
use Database\Seeders\RoleRegistrySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSystemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registry_definitions_are_complete_and_consistent()
    {
        $defs = RoleRegistry::definitions();
        $this->assertGreaterThanOrEqual(11, count($defs));
        $names = array_column($defs, 'name');
        $this->assertContains('customer', $names);
        $this->assertContains('admin', $names);
        foreach ($defs as $def) {
            foreach ($def['permissions'] as $cap) {
                $this->assertArrayHasKey($cap, RoleRegistry::CAPABILITIES, "Unknown capability {$cap} in {$def['name']}");
            }
            foreach ($def['training_slugs'] ?? [] as $slug) {
                $this->assertNotNull(\App\Support\TrainingCenterLessons::find($slug), "Unknown training slug {$slug} in {$def['name']}");
            }
        }
        // Registration surface: admin excluded, customer self-service.
        $reg = array_column(RoleRegistry::registerable(), 'name');
        $this->assertContains('customer', $reg);
        $this->assertNotContains('admin', $reg);
        $this->assertNotContains('super_admin', $reg);
    }

    /** @test */
    public function registration_policy_splits_production_and_test_modes()
    {
        // Production: privileged requests become pending on a safe account.
        $out = RoleRegistry::registrationOutcome('finance_manager', true);
        $this->assertSame(['assigned' => 'customer', 'requested' => 'finance_manager', 'status' => 'pending'], $out);
        $out = RoleRegistry::registrationOutcome('customer', true);
        $this->assertSame('customer', $out['assigned']);
        // Test/staging: immediate grant for testability.
        $out = RoleRegistry::registrationOutcome('finance_manager', false);
        $this->assertSame(['assigned' => 'finance_manager', 'requested' => 'finance_manager', 'status' => 'approved'], $out);
        // Unknown/disabled roles always fall back safely.
        $out = RoleRegistry::registrationOutcome('super_admin', false);
        $this->assertSame('customer', $out['assigned']);
        $this->assertNull($out['requested']);
    }

    /** @test */
    public function customer_self_registers_and_privileged_requests_are_recorded()
    {
        $this->seed(RoleRegistrySeeder::class);

        $this->post(route('register'), [
            'name' => 'Test Customer', 'email' => 'newcustomer@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'customer',
        ])->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => 'newcustomer@example.com', 'role' => 'customer', 'role_approval_status' => null]);

        // Testing env = test mode: privileged role granted + audited.
        $this->post(route('register'), [
            'name' => 'Test Finance', 'email' => 'newfinance@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'finance_manager',
        ])->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => 'newfinance@example.com', 'role' => 'finance_manager', 'requested_role' => 'finance_manager', 'role_approval_status' => 'approved']);
    }

    /** @test */
    public function role_manipulation_is_neutralized()
    {
        $this->seed(RoleRegistrySeeder::class);
        // super_admin is not selectable: validation rejects it outright.
        $this->post(route('register'), [
            'name' => 'Attacker', 'email' => 'attacker@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'super_admin',
        ])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);
    }

    /** @test */
    public function admin_approves_and_rejects_role_requests_with_audit()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'customer', 'is_active' => true, 'requested_role' => 'support_agent', 'role_approval_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.role-testing.approve', $user))->assertSessionHas('success');
        $user->refresh();
        $this->assertSame('support_agent', $user->role);
        $this->assertSame('approved', $user->role_approval_status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'role.approved', 'auditable_id' => $user->id]);

        $user2 = User::factory()->create(['role' => 'customer', 'is_active' => true, 'requested_role' => 'finance_manager', 'role_approval_status' => 'pending']);
        $this->actingAs($admin)->post(route('admin.role-testing.reject', $user2), ['reason' => 'Not authorized'])->assertSessionHas('success');
        $this->assertSame('customer', $user2->fresh()->role);
        $this->assertSame('rejected', $user2->fresh()->role_approval_status);
    }

    /** @test */
    public function negative_permissions_are_denied_server_side()
    {
        $mk = fn ($role) => User::factory()->create(['role' => $role, 'is_active' => true]);
        // Customer must never reach admin areas.
        $this->actingAs($mk('customer'))->get(route('admin.users.index'))->assertStatus(403);
        // Employee must never reach finance administration.
        $this->actingAs($mk('employee'))->get(route('admin.invoices.index'))->assertStatus(403);
        // Freelancer must never reach system settings.
        $this->actingAs($mk('freelancer'))->get(route('admin.settings.index'))->assertStatus(403);
        // Support agent must never manage users.
        $this->actingAs($mk('support_agent'))->get(route('admin.users.index'))->assertStatus(403);
        // Finance manager must never manage users.
        $this->actingAs($mk('finance_manager'))->get(route('admin.users.index'))->assertStatus(403);
        // Role testing page is admin-only.
        $this->actingAs($mk('employee'))->get(route('admin.role-testing.index'))->assertStatus(403);
        $this->actingAs($mk('admin'))->get(route('admin.role-testing.index'))->assertStatus(200);
    }

    /** @test */
    public function ai_brief_is_role_aware_and_restrictive()
    {
        $brief = RoleRegistry::aiBrief('customer');
        $this->assertStringContainsString('Customer (Client Portal)', $brief);
        $this->assertStringContainsString('not available to their role', $brief);
        $this->assertStringContainsString('/admin/help/problems', $brief);

        $fin = RoleRegistry::aiBrief('finance_manager');
        $this->assertStringContainsString('Finance Manager', $fin);
        $this->assertStringContainsString('payments', $fin);
    }

    /** @test */
    public function training_center_recommends_by_role()
    {
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true]);
        $content = $this->actingAs($finance)->get(route('admin.help.training.index'))->getContent();
        $this->assertStringContainsString('Recommended for your role', $content);
        $this->assertStringContainsString('Finance Manager', $content);
    }

    /** @test */
    public function capabilities_derive_from_registry()
    {
        $this->assertTrue(User::factory()->make(['role' => 'finance_manager'])->hasCapability('finance.manage'));
        $this->assertFalse(User::factory()->make(['role' => 'customer'])->hasCapability('finance.manage'));
        $this->assertTrue(User::factory()->make(['role' => 'customer'])->hasCapability('portal.own'));
    }
}
