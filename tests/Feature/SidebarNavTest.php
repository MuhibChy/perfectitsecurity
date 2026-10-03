<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavTest extends TestCase
{
    use RefreshDatabase;

    protected function staff(string $role = 'employee'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    /** @test */
    public function admin_sees_five_groups_and_every_legacy_item()
    {
        $content = $this->actingAs($this->staff('admin'))->get(route('admin.dashboard'))->getContent();

        foreach (['Training &amp; Knowledge', 'Work &amp; Operations', 'Sales &amp; Finance', 'Services &amp; Content', 'System &amp; Health'] as $group) {
            $this->assertStringContainsString($group, $content);
        }
        $this->assertSame(5, substr_count($content, 'data-nav-group='));

        $labels = ['User Manual', 'Dashboard', 'Academy', 'Training Manager', 'Training',
            'Problem &amp; Solution', 'My Work History', 'History Search', 'Work Orders',
            'Sales Pipeline', 'Leads', 'Tickets', 'Projects', 'Tasks', 'Invoices',
            'Financials', 'Expenses', 'Commissions', 'Reports', 'Proposals', 'Contracts',
            'Subscriptions', 'Services Catalogue', 'System Health', 'Users', 'Role Testing',
            'Audit History', 'Services', 'Companies', 'Blog', 'Knowledge Base',
            'Case Studies', 'Careers', 'Portfolio', 'Settings', 'Useful Links',
            'Link Submissions', 'AI Assistant', 'Audit Logs', 'Security Findings', 'Security Dashboard'];
        foreach ($labels as $label) {
            $this->assertStringContainsString('>' . $label . '<', $content, "Missing sidebar item: {$label}");
        }
    }

    /** @test */
    public function permissions_hide_unauthorized_items_and_empty_groups()
    {
        // Employee: no finance block, no admin block.
        $employee = $this->actingAs($this->staff('employee'))->get(route('admin.dashboard'))->getContent();
        foreach (['Invoices', 'Financials', 'Users', 'Security Dashboard', 'AI Assistant', 'Knowledge Base', 'Settings'] as $hidden) {
            $this->assertStringNotContainsString('>' . $hidden . '<', $employee);
        }
        $this->assertStringNotContainsString('System & Health', $employee);
        // ...but keeps its own work items.
        foreach (['Work Orders', 'Projects', 'Tasks', 'Leads', 'My Work History'] as $visible) {
            $this->assertStringContainsString('>' . $visible . '<', $employee);
        }

        // Finance manager: finance items, no admin items.
        $finance = $this->actingAs($this->staff('finance_manager'))->get(route('admin.dashboard'))->getContent();
        $this->assertStringContainsString('>Invoices<', $finance);
        $this->assertStringContainsString('>Financials<', $finance);
        $this->assertStringNotContainsString('>Users<', $finance);
        $this->assertStringNotContainsString('System & Health', $finance);

        // Support agent: tickets visible, finance hidden.
        $support = $this->actingAs($this->staff('support_agent'))->get(route('admin.dashboard'))->getContent();
        $this->assertStringContainsString('>Tickets<', $support);
        $this->assertStringNotContainsString('>Invoices<', $support);
    }

    /** @test */
    public function active_route_expands_its_parent_and_highlights_child()
    {
        $finance = $this->staff('finance_manager');
        $content = $this->actingAs($finance)->get(route('admin.invoices.index'))->getContent();
        // Sales & Finance group forced open server-side.
        $this->assertStringContainsString('data-nav-group="sales"', $content);
        $this->assertStringContainsString('data-nav-open="1"', $content);

        $admin = $this->staff('admin');
        $kb = $this->actingAs($admin)->get(route('admin.knowledge-base.index'))->getContent();
        $this->assertStringContainsString('data-nav-group="training"', $kb);

        $sec = $this->actingAs($admin)->get(route('admin.security.dashboard'))->getContent();
        $this->assertStringContainsString('data-nav-group="system"', $sec);
    }

    /** @test */
    public function navigation_is_centrally_defined_and_accessible()
    {
        $groups = AdminNavigation::groups();
        $this->assertCount(5, $groups);
        $total = 0;
        foreach ($groups as $group) {
            $this->assertNotEmpty($group['label']);
            $this->assertNotEmpty($group['items']);
            foreach ($group['items'] as $item) {
                $total++;
                $this->assertTrue(\Illuminate\Support\Facades\Route::has($item['route']), "Unknown route: {$item['route']}");
            }
        }
        $this->assertGreaterThanOrEqual(40, $total);

        // Component contract: dropdown buttons, aria, persistence.
        $content = $this->actingAs($this->staff('admin'))->get(route('admin.dashboard'))->getContent();
        $this->assertStringContainsString('aria-expanded', $content);
        $this->assertStringContainsString('aria-controls="nav-sub-', $content);
        $this->assertStringContainsString('pits-admin-nav', $content);
        $this->assertStringContainsString('x-cloak', $content);
    }

    /** @test */
    public function customer_portal_navigation_is_untouched()
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $content = $this->actingAs($customer)->get(route('portal.dashboard'))->getContent();
        $this->assertStringContainsString('My Orders', $content);
        $this->assertStringNotContainsString('Training & Knowledge', $content);
        $this->assertStringNotContainsString('data-nav-group=', $content);
    }
}
