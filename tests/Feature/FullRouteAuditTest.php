<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FullRouteAuditTest extends TestCase
{
    use RefreshDatabase;
    public function test_public_routes_load()
    {
        $publicRoutes = [
            '/',
            '/about',
            '/pricing',
            '/contact',
            '/services',
            '/blog',
            '/knowledge-base',
            '/useful-links',
            '/login',
            '/register',
            '/password/reset',
        ];

        foreach ($publicRoutes as $uri) {
            $response = $this->get($uri);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Public route {$uri} returned status {$response->status()}"
            );
        }
    }

    public function test_customer_routes_load()
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);

        $customerRoutes = [
            '/portal',
            '/portal/tickets',
            '/portal/tickets/create',
            '/portal/invoices',
            '/portal/projects',
            '/portal/service-request',
            '/portal/quotations',
        ];

        foreach ($customerRoutes as $uri) {
            $response = $this->actingAs($customer)->get($uri);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Customer route {$uri} returned status {$response->status()}"
            );
        }
    }

    public function test_admin_routes_load()
    {
        $admin = User::where('role', 'super_admin')->first() ?? User::factory()->create(['role' => 'super_admin']);

        $adminRoutes = [
            '/admin',
            '/admin/users',
            '/admin/users/create',
            '/admin/companies',
            '/admin/services',
            '/admin/service-categories',
            '/admin/tickets',
            '/admin/projects',
            '/admin/tasks',
            '/admin/invoices',
            '/admin/payments',
            '/admin/quotations',
            '/admin/expenses',
            '/admin/commissions',
            '/admin/commission-rules',
            '/admin/financials',
            '/admin/financials/transactions',
            '/admin/financials/profit-loss',
            '/admin/reports',
            '/admin/reports/financial',
            '/admin/reports/tickets',
            '/admin/reports/employees',
            '/admin/reports/sla',
            '/admin/reports/profitability',
            '/admin/blog',
            '/admin/knowledge-base',
            '/admin/useful-links',
            '/admin/link-submissions',
            '/admin/settings',
            '/admin/audit-logs',
            '/admin/notifications',
            '/admin/ai',
            '/admin/ai/conversations',
            '/admin/ai/knowledge-gaps',
            '/admin/ai/settings',
            '/admin/ai/usage',
            '/admin/ai/questions',
        ];

        foreach ($adminRoutes as $uri) {
            $response = $this->actingAs($admin)->get($uri);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Admin route {$uri} returned status {$response->status()}"
            );
        }
    }
}
