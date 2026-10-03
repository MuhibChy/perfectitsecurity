<?php

namespace App\Support;

/**
 * AuthenticatedBackgroundManager — single source of truth for the
 * authenticated background system (visual only; never auth logic).
 *
 * Chain: User Role → Authenticated Layout → Background Manager →
 * Environment Configuration → Unique Background Scene.
 *
 * Route-based overrides take precedence over role defaults so finance,
 * security, AI/knowledge, tickets, projects and settings each get their
 * own room inside one consistent PerfectITSecurity identity. Unknown
 * routes fall back to the role default; guests fall back to 'operations'.
 */
class AuthenticatedBackgroundManager
{
    public const ENVIRONMENTS = [
        'operations'  => 'Global IT Operations Center',
        'command'     => 'Enterprise Command Center',
        'network'     => 'Modern Network Operations Room',
        'soc'         => 'Cybersecurity Security Operations Center',
        'engineering' => 'Digital Engineering Laboratory',
        'fintech'     => 'Enterprise Financial Technology Center',
        'intelligence'=> 'AI Knowledge Intelligence Center',
        'support'     => 'Global Support Operations Center',
        'suite'       => 'Private Digital Infrastructure Suite',
    ];

    /**
     * Legacy 2D-network tint keys consumed by body[data-rolebg] CSS.
     * Kept stable so existing styles/tests keep working.
     */
    public const LEGACY_MAP = [
        'operations'  => 'secure',
        'command'     => 'command',
        'network'     => 'datacenter',
        'soc'         => 'soc',
        'engineering' => 'datacenter',
        'fintech'     => 'fintech',
        'intelligence'=> 'business',
        'support'     => 'soc',
        'suite'       => 'secure',
    ];

    /**
     * Resolve the environment for the current request.
     *
     * @param  string|null  $role       Authenticated user role (or null/guest).
     * @param  string|null  $routeName  Current route name (or null).
     */
    public static function resolve(?string $role, ?string $routeName = null): string
    {
        $route = (string) ($routeName ?? '');

        // 1) Route-based rooms first (page-specific variation).
        if (self::matches($route, ['admin.security', 'admin.security-findings', 'admin.health.errors', 'admin.audit-logs'])) {
            return 'soc';
        }
        if (self::matches($route, ['admin.financials', 'admin.invoices', 'admin.expenses', 'admin.commissions', 'admin.reports', 'admin.proposals', 'admin.contracts', 'admin.subscriptions', 'admin.salaries', 'admin.payments', 'portal.invoices', 'portal.orders', 'portal.quotations'])) {
            return 'fintech';
        }
        if (self::matches($route, ['admin.ai', 'admin.knowledge-base', 'admin.skills', 'portal.knowledge', 'kb.'])) {
            return 'intelligence';
        }
        if (self::matches($route, ['admin.academy', 'admin.training', 'admin.help'])) {
            return 'intelligence';
        }
        if (self::matches($route, ['admin.tickets', 'portal.tickets', 'admin.leads', 'admin.service-requests', 'portal.service-request', 'portal.notifications'])) {
            return 'support';
        }
        if (self::matches($route, ['admin.projects', 'admin.tasks', 'portal.projects', 'portal.documents', 'admin.services', 'portal.services'])) {
            return 'engineering';
        }
        if (self::matches($route, ['admin.settings', 'admin.users', 'admin.companies', 'admin.profile', 'portal.profile', 'portal.verification', 'mfa.', 'profile.'])) {
            return 'suite';
        }
        // Dashboards intentionally resolve via role defaults below so each
        // role keeps its own command room (covered by RoleThemeTest);
        // module routes above take precedence for distinct areas.

        // 2) Role defaults.
        return match (true) {
            in_array($role, ['super_admin', 'admin'], true) => 'command',
            $role === 'finance_manager' => 'fintech',
            in_array($role, ['support_manager', 'support_agent'], true) => 'support',
            in_array($role, ['project_manager', 'employee'], true) => 'engineering',
            in_array($role, ['sales_agent', 'freelancer', 'commission_agent', 'training_manager'], true) => 'intelligence',
            $role === 'customer' => 'operations',
            default => 'operations',
        };
    }

    /**
     * Secondary composition variant so sibling pages in one family differ
     * (e.g. tickets index vs. show) without new heavy scenes.
     */
    public static function variant(?string $routeName = null): string
    {
        $route = (string) ($routeName ?? '');
        if (str_ends_with($route, '.create')) {
            return 'b';
        }
        if (str_ends_with($route, '.edit')) {
            return 'c';
        }
        if (preg_match('/\.(show|pdf|download|receipt)$/', $route)) {
            return 'd';
        }
        if (str_contains($route, '.index') || str_ends_with($route, '.pipeline') || str_contains($route, 'dashboard')) {
            return 'a';
        }

        return 'a';
    }

    public static function legacyKey(string $environment): string
    {
        return self::LEGACY_MAP[$environment] ?? 'secure';
    }

    public static function label(string $environment): string
    {
        return self::ENVIRONMENTS[$environment] ?? self::ENVIRONMENTS['operations'];
    }

    /** @param string[] $prefixes */
    protected static function matches(string $route, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if ($route === $prefix || str_starts_with($route, $prefix.'.') || str_starts_with($route, $prefix.' ')) {
                return true;
            }
        }

        return false;
    }
}
