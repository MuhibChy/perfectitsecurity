<?php

namespace App\Support;

/**
 * AdminNavigation — central staff-sidebar definition.
 *
 * Dashboard + 5 collapsible groups. Each item declares its route, active
 * route patterns, icon, badge and gate (a User method). Adding a future
 * menu item means adding one array entry — no Blade surgery.
 *
 * Gates mirror the previous inline sidebar exactly; no permission is
 * widened or narrowed by this reorganization.
 */
class AdminNavigation
{
    public static function groups(): array
    {
        return [
            [
                'key' => 'training',
                'label' => 'Training & Knowledge',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'items' => [
                    ['label' => 'User Manual', 'route' => 'admin.manual', 'active' => ['admin.manual'], 'target' => '_blank', 'gate' => 'isAdmin', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['label' => 'Academy', 'route' => 'admin.academy.index', 'active' => ['admin.academy.*'], 'badge' => 'Learn', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'],
                    ['label' => 'Training Manager', 'route' => 'admin.training.dashboard', 'active' => ['admin.training.*'], 'gate' => 'isTrainer', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['label' => 'Training', 'route' => 'admin.help.training.index', 'active' => ['admin.help.training.*'], 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'iconClass' => 'text-cyan-500'],
                    ['label' => 'Problem & Solution', 'route' => 'admin.help.problems.index', 'active' => ['admin.help.problems.*'], 'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', 'iconClass' => 'text-cyan-500'],
                    ['label' => 'Knowledge Base', 'route' => 'admin.knowledge-base.index', 'active' => ['admin.knowledge-base.*'], 'gate' => 'isAdmin', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['label' => 'AI Assistant', 'route' => 'admin.ai.index', 'active' => ['admin.ai.*'], 'gate' => 'isAdmin', 'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ],
            ],
            [
                'key' => 'work',
                'label' => 'Work & Operations',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'items' => [
                    ['label' => 'My Work History', 'route' => 'admin.history.my-work', 'active' => ['admin.history.my-work'], 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'My Earnings', 'route' => 'admin.earnings.show', 'active' => ['admin.earnings.*'], 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Directory', 'route' => 'admin.directory.index', 'active' => ['admin.directory.*'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['label' => 'Team Assignments', 'route' => 'admin.ecosystem.assignments', 'active' => ['admin.ecosystem.assignments*'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['label' => 'Call Logs', 'route' => 'admin.ecosystem.calls', 'active' => ['admin.ecosystem.calls*'], 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                    ['label' => 'Operations', 'route' => 'admin.operations.index', 'active' => ['admin.operations.*', 'admin.service.show'], 'gate' => 'isProjectManager', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ['label' => 'History Search', 'route' => 'admin.search.index', 'active' => ['admin.search.*'], 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
                    ['label' => 'Work Orders', 'route' => 'admin.work-orders.index', 'active' => ['admin.work-orders.*'], 'badge' => 'New', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'iconClass' => 'text-primary-500'],
                    ['label' => 'Leads', 'route' => 'admin.leads.index', 'active' => ['admin.leads.*'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['label' => 'Tickets', 'route' => 'admin.tickets.index', 'active' => ['admin.tickets.*'], 'gate' => 'isSupport', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                    ['label' => 'Projects', 'route' => 'admin.projects.index', 'active' => ['admin.projects.*'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ['label' => 'Tasks', 'route' => 'admin.tasks.index', 'active' => ['admin.tasks.*'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ],
            ],
            [
                'key' => 'sales',
                'label' => 'Sales & Finance',
                'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'items' => [
                    ['label' => 'Sales Pipeline', 'route' => 'admin.service-requests.pipeline', 'active' => ['admin.service-requests.*'], 'badge' => 'CRM', 'badgeClass' => 'cyan', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'iconClass' => 'text-cyan-500'],
                    ['label' => 'Wallets', 'route' => 'admin.wallets.index', 'active' => ['admin.wallets.*'], 'gate' => 'isFinanceManager', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z'],
                    ['label' => 'Proposals', 'route' => 'admin.proposals.index', 'active' => ['admin.proposals.*'], 'gate' => 'isFinanceManager', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['label' => 'Contracts', 'route' => 'admin.contracts.index', 'active' => ['admin.contracts.*'], 'gate' => 'isFinanceManager', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['label' => 'Subscriptions', 'route' => 'admin.subscriptions.index', 'active' => ['admin.subscriptions.*'], 'gate' => 'isFinanceManager', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H2m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                    ['label' => 'Invoices', 'route' => 'admin.invoices.index', 'active' => ['admin.invoices.*'], 'gate' => 'isFinanceManager', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                    ['label' => 'Financials', 'route' => 'admin.financials.index', 'active' => ['admin.financials.*'], 'gate' => 'isFinanceManager', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Payments Overview', 'route' => 'admin.payments.overview', 'active' => ['admin.payments.*'], 'gate' => 'isFinanceManager', 'badge' => 'New', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z'],
                    ['label' => 'Payment Providers', 'route' => 'admin.payment-providers.index', 'active' => ['admin.payment-providers.*'], 'gate' => 'isFinanceManager', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                    ['label' => 'Bank Accounts', 'route' => 'admin.bank-accounts.index', 'active' => ['admin.bank-accounts.*'], 'gate' => 'isFinanceManager', 'icon' => 'M3 21h18M3 10h18M5 6l7-3 7 3M4 10v10M20 10v10M8 14v3m4-3v3m4-3v3'],
                    ['label' => 'Bank Transfers', 'route' => 'admin.bank-transfers.index', 'active' => ['admin.bank-transfers.*'], 'gate' => 'isFinanceManager', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                    ['label' => 'Refunds', 'route' => 'admin.refunds.index', 'active' => ['admin.refunds.*'], 'gate' => 'isFinanceManager', 'icon' => 'M16 15v-1a3 3 0 00-3-3H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                    ['label' => 'Expenses', 'route' => 'admin.expenses.index', 'active' => ['admin.expenses.*'], 'gate' => 'isFinanceManager', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['label' => 'Commissions', 'route' => 'admin.commissions.index', 'active' => ['admin.commissions.*'], 'gate' => 'isFinanceManager', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'],
                    ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*'], 'gate' => 'isFinanceManager', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ],
            ],
            [
                'key' => 'services',
                'label' => 'Services & Content',
                'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                'items' => [
                    ['label' => 'Services Catalogue', 'route' => 'services.index', 'active' => [], 'target' => '_blank', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                    ['label' => 'Services', 'route' => 'admin.services.index', 'active' => ['admin.services.*', 'admin.service-categories.*'], 'gate' => 'isAdmin', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                    ['label' => 'Companies', 'route' => 'admin.companies.index', 'active' => ['admin.companies.*'], 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['label' => 'Blog', 'route' => 'admin.blog.index', 'active' => ['admin.blog.*'], 'gate' => 'isAdmin', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                    ['label' => 'Case Studies', 'route' => 'admin.content.case-studies', 'active' => ['admin.content.case-studies*'], 'gate' => 'isAdmin', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ['label' => 'Careers', 'route' => 'admin.content.careers', 'active' => ['admin.content.careers*'], 'gate' => 'isAdmin', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['label' => 'Portfolio', 'route' => 'admin.content.portfolio', 'active' => ['admin.content.portfolio*'], 'gate' => 'isAdmin', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['label' => 'Useful Links', 'route' => 'admin.useful-links.index', 'active' => ['admin.useful-links.*'], 'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                    ['label' => 'Link Submissions', 'route' => 'admin.link-submissions.index', 'active' => ['admin.link-submissions.*'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ],
            ],
            [
                'key' => 'system',
                'label' => 'System & Health',
                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'items' => [
                    ['label' => 'System Health', 'route' => 'admin.health.index', 'active' => ['admin.health.*'], 'gate' => 'isAdmin', 'pulse' => true, 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'iconClass' => 'text-emerald-500'],
                    ['label' => 'Users', 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'gate' => 'isAdmin', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                    ['label' => 'Role Testing', 'route' => 'admin.role-testing.index', 'active' => ['admin.role-testing.*'], 'gate' => 'isAdmin', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['label' => 'Audit History', 'route' => 'admin.history.audit', 'active' => ['admin.history.audit', 'admin.history.consistency'], 'gate' => 'isAdmin', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Settings', 'route' => 'admin.settings.index', 'active' => ['admin.settings.*'], 'gate' => 'isAdmin', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'active' => ['admin.audit-logs.*'], 'gate' => 'isAdmin', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Security Findings', 'route' => 'admin.security-findings.index', 'active' => ['admin.security-findings.*'], 'gate' => 'isAdmin', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['label' => 'Security Dashboard', 'route' => 'admin.security.dashboard', 'active' => ['admin.security.dashboard'], 'gate' => 'isAdmin', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ],
            ],
        ];
    }

    protected static function gateOpen($user, ?string $gate): bool
    {
        if (! $gate) {
            return true;
        }
        if (! $user) {
            return false;
        }
        if ($gate === 'isSupport') {
            return $user->isSupportAgent() || $user->isSupportManager();
        }
        if (method_exists($user, $gate)) {
            return (bool) $user->$gate();
        }

        return $user->role === $gate;
    }

    /**
     * Groups filtered for $user with active flags resolved.
     * Groups with zero visible items are omitted entirely.
     */
    public static function for($user): array
    {
        $out = [];
        foreach (self::groups() as $group) {
            $items = [];
            $groupActive = false;
            foreach ($group['items'] as $item) {
                if (! self::gateOpen($user, $item['gate'] ?? null)) {
                    continue;
                }
                $active = false;
                foreach ($item['active'] ?? [] as $pattern) {
                    if (request()->routeIs($pattern)) {
                        $active = true;
                        break;
                    }
                }
                if ($active) {
                    $groupActive = true;
                }
                $items[] = $item + ['isActive' => $active];
            }
            if (empty($items)) {
                continue;
            }
            $group['items'] = $items;
            $group['isActive'] = $groupActive;
            $out[] = $group;
        }

        return $out;
    }
}
