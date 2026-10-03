<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\CustomerNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Customer sidebar authorization: navigation is built from actual
 * role → capability grants (CustomerNavigation), never from the mere
 * existence of routes. Backend middleware/policies stay the boundary.
 */
class CustomerNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function customer(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer', 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ], $overrides));
    }

    /** @test */
    public function navigation_is_centrally_defined_and_every_route_exists()
    {
        $items = CustomerNavigation::items();
        $this->assertGreaterThanOrEqual(17, count($items));
        foreach ($items as $item) {
            $this->assertNotEmpty($item['key']);
            $this->assertNotEmpty($item['label']);
            $this->assertTrue(Route::has($item['route']), "Unknown route: {$item['route']}");
        }
        // No admin/staff route may ever leak into the customer definition.
        foreach ($items as $item) {
            $this->assertStringStartsWith('portal.', $item['route'], "Non-portal route in customer nav: {$item['route']}");
        }
    }

    /** @test */
    public function customer_sidebar_shows_only_authorized_customer_features()
    {
        $customer = $this->customer();
        $content = $this->actingAs($customer)->get(route('portal.dashboard'))->getContent();

        foreach (['Dashboard', 'My Orders', 'My Tickets', 'Services', 'Request Service', 'Invoices', 'Projects', 'Quotations', 'Documents', 'Service History', 'My Services', 'My Wallet', 'My Account', 'Directory', 'Notifications', 'My Profile', 'User Manual'] as $label) {
            $this->assertStringContainsString('>' . $label . '<', $content, "Missing customer item: {$label}");
        }
        // Fully verified customer: no Verify Account prompt.
        $this->assertStringNotContainsString('>Verify Account<', $content);
        // Never an admin/staff surface.
        foreach (['Training & Knowledge', 'System & Health', 'Sales & Finance', 'data-nav-group=', 'System Health', 'Role Testing', 'Audit Logs'] as $admin) {
            $this->assertStringNotContainsString($admin, $content, "Admin surface leaked to customer: {$admin}");
        }
    }

    /** @test */
    public function verify_account_shows_only_until_fully_verified()
    {
        $unverified = $this->customer(['email_verified_at' => now(), 'phone_verified_at' => null]);
        $this->assertFalse($unverified->isFullyVerified());
        $this->assertTrue(CustomerNavigation::visible($unverified, 'verify'));
        $content = $this->actingAs($unverified)->get(route('portal.dashboard'))->getContent();
        $this->assertStringContainsString('>Verify Account<', $content);

        $verified = $this->customer();
        $this->assertTrue($verified->isFullyVerified());
        $this->assertFalse(CustomerNavigation::visible($verified, 'verify'));
    }

    /** @test */
    public function staff_and_guests_receive_no_customer_navigation()
    {
        $staff = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $this->assertSame([], CustomerNavigation::for($staff));
        $this->assertFalse(CustomerNavigation::visible($staff, 'orders'));
        $this->assertSame([], CustomerNavigation::for(null));
    }

    /** @test */
    public function sidebar_matches_backend_authorization()
    {
        $customer = $this->customer();
        foreach (CustomerNavigation::for($customer) as $item) {
            if (!empty($item['target'])) {
                continue; // External/manual link: no backend gate to probe.
            }
            $response = $this->actingAs($customer)->get(route($item['route']));
            $this->assertNotContains(
                $response->getStatusCode(), [401, 403],
                "Sidebar item unreachable by its owner: {$item['label']} ({$item['route']})"
            );
        }
    }
}
