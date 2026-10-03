<?php

namespace App\Support;

use App\Models\Role;

/**
 * RoleRegistry — central role + permission + feature catalogue.
 *
 * USER → ROLE → PERMISSIONS → FEATURES / DASHBOARD / NAVIGATION
 *                                ├→ TRAINING, KNOWLEDGE BASE, AI, PROBLEM & SOLUTION
 *
 * The users.role string stays the historical record. This registry only
 * describes roles: adding a row + seeder entry enables a future role
 * without rewriting controllers; deprecating a row blocks new
 * registrations while existing users keep working.
 */
class RoleRegistry
{
    /** Capability keys usable via User::hasCapability(). */
    public const CAPABILITIES = [
        'portal.own' => 'Client portal (own records)',
        'tickets.handle' => 'Handle support tickets',
        'tickets.own' => 'Raise and track own tickets',
        'projects.manage' => 'Manage projects and milestones',
        'tasks.work' => 'Work assigned tasks',
        'finance.manage' => 'Manage payments, invoices, income, expenses',
        'finance.own' => 'View own payments and invoices',
        'commissions.own' => 'View own commission records',
        'crm.manage' => 'Manage leads and customers',
        'services.manage' => 'Manage service catalogue',
        'content.manage' => 'Publish website content and knowledge',
        'users.manage' => 'Manage users and roles',
        'settings.manage' => 'Manage system configuration',
        'reports.view' => 'View reports and audit history',
        'training.manage' => 'Manage Academy training',
        'training.learn' => 'Take Academy training',
        'ai.use' => 'Use the AI assistant',
    ];

    /**
     * Canonical definitions. Permissions mirror the actual RBAC (User::is*
     * methods + requires.role gates) so the matrix documents reality.
     */
    public static function definitions(): array
    {
        return [
            [
                'name' => 'customer', 'display_name' => 'Customer (Client Portal)',
                'description' => 'For customers who purchase or request PerfectITSecurity services. Self-service portal for orders, tickets, invoices, projects and documents.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => false, 'self_registration' => true,
                'dashboard_route' => 'portal.dashboard', 'dashboard_label' => 'Client Portal', 'sort_order' => 10,
                'permissions' => ['portal.own', 'tickets.own', 'finance.own', 'training.learn', 'ai.use'],
                'training_slugs' => ['platform-overview', 'customer-journey', 'services', 'orders', 'payments', 'invoices', 'projects', 'tickets'],
            ],
            [
                'name' => 'support_agent', 'display_name' => 'Support Agent (Ticket Handling)',
                'description' => 'For staff handling customer support tickets and support communication, inside SLA.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Support Dashboard', 'sort_order' => 20,
                'permissions' => ['tickets.handle', 'tasks.work', 'training.learn', 'ai.use'],
                'training_slugs' => ['customer-journey', 'tickets', 'sla', 'customer-communication', 'progress-updates', 'it-services'],
            ],
            [
                'name' => 'project_manager', 'display_name' => 'Project Manager',
                'description' => 'For users responsible for project coordination, milestones and task management.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Project Management Dashboard', 'sort_order' => 30,
                'permissions' => ['projects.manage', 'tasks.work', 'tickets.handle', 'training.learn', 'ai.use'],
                'training_slugs' => ['projects', 'tasks', 'progress-updates', 'customer-communication', 'account-closure'],
            ],
            [
                'name' => 'finance_manager', 'display_name' => 'Finance Manager',
                'description' => 'For authorized users responsible for payments, invoices, income, expenses, commissions and financial reporting.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Finance Dashboard', 'sort_order' => 40,
                'permissions' => ['finance.manage', 'crm.manage', 'reports.view', 'training.learn', 'ai.use'],
                'training_slugs' => ['payments', 'invoices', 'finance', 'income-expenses', 'commission', 'profit-loss', 'account-closure'],
            ],
            [
                'name' => 'employee', 'display_name' => 'Employee / Engineer',
                'description' => 'For employees performing assigned technical and operational work on projects and tasks.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Employee Dashboard', 'sort_order' => 50,
                'permissions' => ['tasks.work', 'tickets.handle', 'training.learn', 'ai.use'],
                'training_slugs' => ['tasks', 'it-services', 'progress-updates', 'tickets', 'projects'],
            ],
            [
                'name' => 'freelancer', 'display_name' => 'Freelancer / Contractor',
                'description' => 'For authorized external or contract technical workers with assigned work, review and commission records.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'workspace.index', 'dashboard_label' => 'Contractor Dashboard', 'sort_order' => 60,
                'permissions' => ['tasks.work', 'commissions.own', 'training.learn', 'ai.use'],
                'training_slugs' => ['tasks', 'progress-updates', 'customer-communication'],
            ],
            [
                'name' => 'admin', 'display_name' => 'System Administrator',
                'description' => 'For authorized users managing the platform, users, configuration and administration. Administrator-created only.',
                'status' => 'active', 'registration_allowed' => false, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Administration Dashboard', 'sort_order' => 70,
                'permissions' => ['users.manage', 'settings.manage', 'services.manage', 'content.manage', 'finance.manage', 'projects.manage', 'tickets.handle', 'crm.manage', 'reports.view', 'training.manage', 'training.learn', 'ai.use'],
                'training_slugs' => ['user-roles', 'website-updates', 'knowledge-base', 'security'],
            ],
            // Existing extended roles (kept, documented, fully supported).
            [
                'name' => 'super_admin', 'display_name' => 'Super Administrator',
                'description' => 'Full platform control, including role management and system settings. Administrator-created only.',
                'status' => 'active', 'registration_allowed' => false, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Administration Dashboard', 'sort_order' => 5,
                'permissions' => ['users.manage', 'settings.manage', 'services.manage', 'content.manage', 'finance.manage', 'projects.manage', 'tickets.handle', 'crm.manage', 'reports.view', 'training.manage', 'training.learn', 'ai.use'],
                'training_slugs' => ['user-roles', 'website-updates', 'knowledge-base', 'security'],
            ],
            [
                'name' => 'support_manager', 'display_name' => 'Support Manager',
                'description' => 'Leads the support function: ticket oversight, SLA, escalation and team coordination.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Support Dashboard', 'sort_order' => 25,
                'permissions' => ['tickets.handle', 'tasks.work', 'reports.view', 'training.learn', 'ai.use'],
                'training_slugs' => ['tickets', 'sla', 'customer-communication', 'progress-updates'],
            ],
            [
                'name' => 'sales_agent', 'display_name' => 'Sales Agent',
                'description' => 'Works the CRM pipeline: leads, quotations, proposals and orders.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.dashboard', 'dashboard_label' => 'Sales Dashboard', 'sort_order' => 35,
                'permissions' => ['crm.manage', 'finance.own', 'training.learn', 'ai.use'],
                'training_slugs' => ['customer-journey', 'crm', 'quotations', 'proposals', 'orders', 'customer-communication'],
            ],
            [
                'name' => 'training_manager', 'display_name' => 'Trainer / Training Manager',
                'description' => 'Runs the internal Academy: courses, assignments, reviews and completion records.',
                'status' => 'active', 'registration_allowed' => false, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'admin.training.dashboard', 'dashboard_label' => 'Training Manager Dashboard', 'sort_order' => 65,
                'permissions' => ['training.manage', 'training.learn', 'reports.view', 'ai.use'],
                'training_slugs' => ['user-roles', 'customer-communication', 'progress-updates'],
            ],
            [
                'name' => 'commission_agent', 'display_name' => 'Commission Agent',
                'description' => 'Refers business and tracks qualifying sales and commission payouts.',
                'status' => 'active', 'registration_allowed' => true, 'approval_required' => true, 'self_registration' => false,
                'dashboard_route' => 'workspace.index', 'dashboard_label' => 'Contractor Dashboard', 'sort_order' => 61,
                'permissions' => ['commissions.own', 'training.learn', 'ai.use'],
                'training_slugs' => ['customer-journey', 'commission'],
            ],
        ];
    }

    public static function for(?string $role): ?array
    {
        foreach (self::definitions() as $def) {
            if ($def['name'] === $role) return $def;
        }
        return null;
    }

    public static function displayName(?string $role): string
    {
        return self::for($role)['display_name'] ?? ucfirst(str_replace('_', ' ', (string) $role));
    }

    public static function capabilitiesOf(?string $role): array
    {
        return self::for($role)['permissions'] ?? [];
    }

    public static function trainingFor(?string $role): array
    {
        return self::for($role)['training_slugs'] ?? [];
    }

    /** Roles shown in the registration dropdown (DB status respected). */
    public static function registerable(): array
    {
        try {
            $rows = Role::registerable()->get();
            if ($rows->isNotEmpty()) {
                return $rows->map(fn ($r) => [
                    'name' => $r->name,
                    'display_name' => $r->display_name,
                    'description' => $r->description,
                    'approval_required' => (bool) $r->approval_required,
                    'self_registration' => (bool) $r->self_registration,
                ])->all();
            }
        } catch (\Throwable $e) {
            // Table not yet migrated (e.g. early install): fall back to definitions.
        }
        return array_values(array_filter(array_map(function ($d) {
            if (empty($d['registration_allowed'])) return null;
            return [
                'name' => $d['name'], 'display_name' => $d['display_name'],
                'description' => $d['description'], 'approval_required' => $d['approval_required'],
                'self_registration' => $d['self_registration'],
            ];
        }, self::definitions())));
    }

    /**
     * Registration policy outcome.
     * Production: only self-registration roles are assigned directly;
     * everything else becomes a pending request on a safe customer account.
     * Non-production: requested active roles are granted immediately so the
     * team and trainers can test every role.
     *
     * @return array{assigned: string, requested: ?string, status: ?string}
     */
    public static function registrationOutcome(string $requested, bool $isProduction): array
    {
        $def = self::for($requested);
        if (!$def || ($def['status'] ?? 'active') !== 'active' || empty($def['registration_allowed'])) {
            return ['assigned' => 'customer', 'requested' => null, 'status' => null];
        }
        if (!empty($def['self_registration'])) {
            return ['assigned' => $requested, 'requested' => null, 'status' => null];
        }
        if ($isProduction) {
            return ['assigned' => 'customer', 'requested' => $requested, 'status' => 'pending'];
        }
        return ['assigned' => $requested, 'requested' => $requested, 'status' => 'approved'];
    }

    /** AI-facing role brief: capabilities + restricted topics + deep links. */
    public static function aiBrief(?string $role): string
    {
        $def = self::for($role);
        $caps = self::capabilitiesOf($role);
        $names = array_map(fn ($c) => self::CAPABILITIES[$c] ?? $c, $caps);
        $brief = 'Current user role: ' . self::displayName($role) . " (internal key: {$role}).\n";
        $brief .= 'Allowed areas: ' . ($names ? implode('; ', $names) : 'standard customer access') . ".\n";
        $brief .= "Rules: explain only workflows inside the user's allowed areas. "
            . "If asked about a restricted area (e.g. a customer asking about expense management, finance administration, user management or other staff procedures), "
            . "state plainly that it is not available to their role, never reveal restricted procedures, and redirect to what they CAN do "
            . "(customer: orders /portal/orders, invoices /portal/invoices, payments, tickets /portal/tickets, projects /portal/projects). "
            . "Link relevant help: Training Center /admin/help/training and Problem & Solution Center /admin/help/problems.\n";
        if ($def && !empty($def['training_slugs'])) {
            $brief .= 'Recommended training for this role: /admin/help/training pages: ' . implode(', ', $def['training_slugs']) . ".\n";
        }
        return $brief;
    }
}
