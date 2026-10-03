<?php

namespace Tests\Unit;

use App\Support\AuthenticatedBackgroundManager;
use PHPUnit\Framework\TestCase;

/**
 * AuthenticatedBackgroundManager: every authenticated area resolves to a
 * distinct environment; dashboards fall back to role defaults; legacy
 * data-rolebg keys stay stable for existing CSS/tests.
 */
class AuthenticatedBackgroundManagerTest extends TestCase
{
    /** @test */
    public function nine_unique_environments_exist()
    {
        $this->assertCount(9, AuthenticatedBackgroundManager::ENVIRONMENTS);
        $this->assertCount(
            9,
            array_unique(array_keys(AuthenticatedBackgroundManager::ENVIRONMENTS))
        );
    }

    /** @test */
    public function route_modules_resolve_to_distinct_rooms()
    {
        $this->assertSame('fintech', AuthenticatedBackgroundManager::resolve('admin', 'admin.invoices.index'));
        $this->assertSame('soc', AuthenticatedBackgroundManager::resolve('admin', 'admin.security-findings.index'));
        $this->assertSame('intelligence', AuthenticatedBackgroundManager::resolve('admin', 'admin.ai.index'));
        $this->assertSame('support', AuthenticatedBackgroundManager::resolve('customer', 'portal.tickets.index'));
        $this->assertSame('engineering', AuthenticatedBackgroundManager::resolve('customer', 'portal.projects.show'));
        $this->assertSame('suite', AuthenticatedBackgroundManager::resolve('customer', 'portal.profile.edit'));
        $this->assertSame('operations', AuthenticatedBackgroundManager::resolve('customer', 'portal.dashboard'));
    }

    /** @test */
    public function dashboards_fall_back_to_role_defaults()
    {
        $this->assertSame('command', AuthenticatedBackgroundManager::resolve('admin', 'admin.dashboard'));
        $this->assertSame('fintech', AuthenticatedBackgroundManager::resolve('finance_manager', 'admin.dashboard'));
        $this->assertSame('support', AuthenticatedBackgroundManager::resolve('support_agent', 'admin.dashboard'));
        $this->assertSame('engineering', AuthenticatedBackgroundManager::resolve('project_manager', 'admin.dashboard'));
        $this->assertSame('intelligence', AuthenticatedBackgroundManager::resolve('sales_agent', 'admin.dashboard'));
        $this->assertSame('operations', AuthenticatedBackgroundManager::resolve('customer', 'portal.dashboard'));
        $this->assertSame('operations', AuthenticatedBackgroundManager::resolve(null, null));
    }

    /** @test */
    public function legacy_keys_match_existing_role_themes()
    {
        // Must stay aligned with RoleThemeTest expectations.
        $this->assertSame('command', AuthenticatedBackgroundManager::legacyKey('command'));
        $this->assertSame('fintech', AuthenticatedBackgroundManager::legacyKey('fintech'));
        $this->assertSame('soc', AuthenticatedBackgroundManager::legacyKey('support'));
        $this->assertSame('datacenter', AuthenticatedBackgroundManager::legacyKey('engineering'));
        $this->assertSame('business', AuthenticatedBackgroundManager::legacyKey('intelligence'));
        $this->assertSame('secure', AuthenticatedBackgroundManager::legacyKey('operations'));
    }

    /** @test */
    public function variants_shift_sibling_pages()
    {
        $this->assertSame('a', AuthenticatedBackgroundManager::variant('portal.tickets.index'));
        $this->assertSame('b', AuthenticatedBackgroundManager::variant('portal.tickets.create'));
        $this->assertSame('c', AuthenticatedBackgroundManager::variant('admin.tasks.edit'));
        $this->assertSame('d', AuthenticatedBackgroundManager::variant('portal.orders.show'));
    }
}
