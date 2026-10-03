<?php

namespace App\Support;

/**
 * CustomerNavigation — central customer-sidebar definition.
 *
 * USER → ROLE → PERMISSIONS → NAVIGATION → ROUTE → POLICY/DATA.
 *
 * Every customer menu item declares the route plus the capability gate
 * required to see it. The Blade layout renders ONLY items whose gate
 * passes for the authenticated user; route/policy middleware remains the
 * security boundary (the sidebar never grants access by itself).
 *
 * Gates mirror the portal's actual authorization (customer middleware +
 * portal.own capability). Adding a future item means adding one array
 * entry — no Blade surgery.
 */
class CustomerNavigation
{
    /**
     * Canonical customer items in display order. Labels/routes match the
     * approved customer portal; do not add admin/staff routes here.
     *
     * Special keys:
     *  - capability: RoleRegistry capability required (default portal.own).
     *  - unverified_only: shown only while the account is not fully verified.
     *  - target: link target (e.g. _blank for the manual).
     */
    public static function items(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'portal.dashboard', 'active' => ['portal.dashboard'], 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['key' => 'orders', 'label' => 'My Orders', 'route' => 'portal.orders.index', 'active' => ['portal.orders.*'], 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
            ['key' => 'tickets', 'label' => 'My Tickets', 'route' => 'portal.tickets.index', 'active' => ['portal.tickets.*'], 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
            ['key' => 'services', 'label' => 'Services', 'route' => 'portal.services.index', 'active' => ['portal.services.*'], 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
            ['key' => 'service-request', 'label' => 'Request Service', 'route' => 'portal.service-request.create', 'active' => ['portal.service-request.*'], 'icon' => 'M12 4v16m8-8H4'],
            ['key' => 'invoices', 'label' => 'Invoices', 'route' => 'portal.invoices.index', 'active' => ['portal.invoices.*', 'portal.checkout.*', 'portal.payments.*', 'portal.bank-transfer.*'], 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
            ['key' => 'projects', 'label' => 'Projects', 'route' => 'portal.projects.index', 'active' => ['portal.projects.*'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['key' => 'quotations', 'label' => 'Quotations', 'route' => 'portal.quotations.index', 'active' => ['portal.quotations.*'], 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['key' => 'documents', 'label' => 'Documents', 'route' => 'portal.documents.index', 'active' => ['portal.documents.*', 'portal.orders.pdf', 'portal.receipts.pdf', 'portal.cash-memos.pdf', 'portal.tracking.report-pdf'], 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
            ['key' => 'history', 'label' => 'Service History', 'route' => 'portal.history.index', 'active' => ['portal.history.*', 'portal.reports.*'], 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['key' => 'tracking', 'label' => 'My Services', 'route' => 'portal.tracking.index', 'active' => ['portal.tracking.*', 'portal.orders.change-request'], 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['key' => 'wallet', 'label' => 'My Wallet', 'route' => 'portal.wallet.index', 'active' => ['portal.wallet.*'], 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z'],
            ['key' => 'account', 'label' => 'My Account', 'route' => 'portal.account.summary', 'active' => ['portal.account.*', 'portal.search.*'], 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['key' => 'directory', 'label' => 'Directory', 'route' => 'portal.directory.index', 'active' => ['portal.directory.*'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['key' => 'notifications', 'label' => 'Notifications', 'route' => 'portal.notifications.index', 'active' => ['portal.notifications.*'], 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['key' => 'profile', 'label' => 'My Profile', 'route' => 'portal.profile.edit', 'active' => ['portal.profile.*'], 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['key' => 'verify', 'label' => 'Verify Account', 'route' => 'portal.verification.phone', 'active' => ['portal.verification.*'], 'unverified_only' => true, 'highlight' => true, 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['key' => 'manual', 'label' => 'User Manual', 'route' => 'portal.manual', 'active' => ['portal.manual'], 'target' => '_blank', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ];
    }

    /** Whether $user may see the item identified by $key. */
    public static function visible($user, string $key): bool
    {
        if (!$user || !method_exists($user, 'isCustomer') || !$user->isCustomer()) {
            return false;
        }
        foreach (self::items() as $item) {
            if ($item['key'] !== $key) {
                continue;
            }
            if (!$user->hasCapability($item['capability'] ?? 'portal.own')) {
                return false;
            }
            if (!empty($item['unverified_only']) && $user->isFullyVerified()) {
                return false;
            }
            return \Illuminate\Support\Facades\Route::has($item['route']);
        }
        return false;
    }

    /**
     * Items filtered for $user with active flags resolved.
     * Non-customers (staff/guests) receive an empty list — staff navigation
     * is owned exclusively by AdminNavigation.
     */
    public static function for($user): array
    {
        if (!$user || !method_exists($user, 'isCustomer') || !$user->isCustomer()) {
            return [];
        }
        $out = [];
        foreach (self::items() as $item) {
            if (!$user->hasCapability($item['capability'] ?? 'portal.own')) {
                continue;
            }
            if (!empty($item['unverified_only']) && $user->isFullyVerified()) {
                continue;
            }
            if (!\Illuminate\Support\Facades\Route::has($item['route'])) {
                continue;
            }
            $active = false;
            foreach ($item['active'] ?? [] as $pattern) {
                if (request()->routeIs($pattern)) {
                    $active = true;
                    break;
                }
            }
            $out[] = $item + ['isActive' => $active];
        }
        return $out;
    }
}
