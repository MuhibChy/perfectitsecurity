<?php

namespace App\Services\Health;

use App\Models\PageHealth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class PageHealthService
{
    /**
     * Get or register all key application pages for health monitoring.
     */
    public function getMonitoredRoutes(): array
    {
        return [
            // Public Pages
            ['name' => 'home', 'uri' => '/', 'module' => 'Public Website', 'role' => 'guest'],
            ['name' => 'about', 'uri' => '/about', 'module' => 'Public Website', 'role' => 'guest'],
            ['name' => 'pricing', 'uri' => '/pricing', 'module' => 'Public Website', 'role' => 'guest'],
            ['name' => 'contact', 'uri' => '/contact', 'module' => 'Public Website', 'role' => 'guest'],
            ['name' => 'services.index', 'uri' => '/services', 'module' => 'Public Website', 'role' => 'guest'],
            ['name' => 'blog.index', 'uri' => '/blog', 'module' => 'Public Website', 'role' => 'guest'],
            ['name' => 'kb.index', 'uri' => '/knowledge-base', 'module' => 'Knowledge Base', 'role' => 'guest'],
            ['name' => 'useful-links', 'uri' => '/useful-links', 'module' => 'Public Website', 'role' => 'guest'],

            // Auth Pages
            ['name' => 'login', 'uri' => '/login', 'module' => 'Authentication', 'role' => 'guest'],
            ['name' => 'register', 'uri' => '/register', 'module' => 'Authentication', 'role' => 'guest'],
            ['name' => 'password.request', 'uri' => '/password/reset', 'module' => 'Authentication', 'role' => 'guest'],

            // Customer Portal
            ['name' => 'portal.dashboard', 'uri' => '/portal', 'module' => 'Customer Portal', 'role' => 'customer'],
            ['name' => 'portal.tickets.index', 'uri' => '/portal/tickets', 'module' => 'Customer Portal', 'role' => 'customer'],
            ['name' => 'portal.tickets.create', 'uri' => '/portal/tickets/create', 'module' => 'Customer Portal', 'role' => 'customer'],
            ['name' => 'portal.invoices.index', 'uri' => '/portal/invoices', 'module' => 'Customer Portal', 'role' => 'customer'],
            ['name' => 'portal.projects.index', 'uri' => '/portal/projects', 'module' => 'Customer Portal', 'role' => 'customer'],
            ['name' => 'portal.service-request.create', 'uri' => '/portal/service-request', 'module' => 'Customer Portal', 'role' => 'customer'],
            ['name' => 'portal.quotations.index', 'uri' => '/portal/quotations', 'module' => 'Customer Portal', 'role' => 'customer'],

            // Admin Panel
            ['name' => 'admin.dashboard', 'uri' => '/admin', 'module' => 'Admin Dashboard', 'role' => 'admin'],
            ['name' => 'admin.users.index', 'uri' => '/admin/users', 'module' => 'Admin Dashboard', 'role' => 'admin'],
            ['name' => 'admin.users.create', 'uri' => '/admin/users/create', 'module' => 'Admin Dashboard', 'role' => 'admin'],
            ['name' => 'admin.companies.index', 'uri' => '/admin/companies', 'module' => 'Admin Dashboard', 'role' => 'admin'],
            ['name' => 'admin.services.index', 'uri' => '/admin/services', 'module' => 'Admin Dashboard', 'role' => 'admin'],
            ['name' => 'admin.service-categories.index', 'uri' => '/admin/service-categories', 'module' => 'Admin Dashboard', 'role' => 'admin'],
            ['name' => 'admin.tickets.index', 'uri' => '/admin/tickets', 'module' => 'Ticketing System', 'role' => 'admin'],
            ['name' => 'admin.projects.index', 'uri' => '/admin/projects', 'module' => 'Project Management', 'role' => 'admin'],
            ['name' => 'admin.tasks.index', 'uri' => '/admin/tasks', 'module' => 'Task Management', 'role' => 'admin'],
            ['name' => 'admin.invoices.index', 'uri' => '/admin/invoices', 'module' => 'Financial System', 'role' => 'admin'],
            ['name' => 'admin.payments.index', 'uri' => '/admin/payments', 'module' => 'Financial System', 'role' => 'admin'],
            ['name' => 'admin.quotations.index', 'uri' => '/admin/quotations', 'module' => 'Financial System', 'role' => 'admin'],
            ['name' => 'admin.expenses.index', 'uri' => '/admin/expenses', 'module' => 'Financial System', 'role' => 'admin'],
            ['name' => 'admin.commissions.index', 'uri' => '/admin/commissions', 'module' => 'Commission System', 'role' => 'admin'],
            ['name' => 'admin.commission-rules.index', 'uri' => '/admin/commission-rules', 'module' => 'Commission System', 'role' => 'admin'],
            ['name' => 'admin.financials.index', 'uri' => '/admin/financials', 'module' => 'Financial System', 'role' => 'admin'],
            ['name' => 'admin.financials.transactions', 'uri' => '/admin/financials/transactions', 'module' => 'Financial System', 'role' => 'admin'],
            ['name' => 'admin.financials.profit-loss', 'uri' => '/admin/financials/profit-loss', 'module' => 'Financial System', 'role' => 'admin'],
            ['name' => 'admin.reports.index', 'uri' => '/admin/reports', 'module' => 'Reporting', 'role' => 'admin'],
            ['name' => 'admin.reports.financial', 'uri' => '/admin/reports/financial', 'module' => 'Reporting', 'role' => 'admin'],
            ['name' => 'admin.reports.tickets', 'uri' => '/admin/reports/tickets', 'module' => 'Reporting', 'role' => 'admin'],
            ['name' => 'admin.reports.employees', 'uri' => '/admin/reports/employees', 'module' => 'Reporting', 'role' => 'admin'],
            ['name' => 'admin.reports.sla', 'uri' => '/admin/reports/sla', 'module' => 'Reporting', 'role' => 'admin'],
            ['name' => 'admin.reports.profitability', 'uri' => '/admin/reports/profitability', 'module' => 'Reporting', 'role' => 'admin'],
            ['name' => 'admin.blog.index', 'uri' => '/admin/blog', 'module' => 'Content Management', 'role' => 'admin'],
            ['name' => 'admin.knowledge-base.index', 'uri' => '/admin/knowledge-base', 'module' => 'Knowledge Base', 'role' => 'admin'],
            ['name' => 'admin.useful-links.index', 'uri' => '/admin/useful-links', 'module' => 'Content Management', 'role' => 'admin'],
            ['name' => 'admin.link-submissions.index', 'uri' => '/admin/link-submissions', 'module' => 'Content Management', 'role' => 'admin'],
            ['name' => 'admin.settings.index', 'uri' => '/admin/settings', 'module' => 'Settings', 'role' => 'admin'],
            ['name' => 'admin.audit-logs.index', 'uri' => '/admin/audit-logs', 'module' => 'Audit & Security', 'role' => 'admin'],
            ['name' => 'admin.notifications.index', 'uri' => '/admin/notifications', 'module' => 'Email & Notifications', 'role' => 'admin'],
            ['name' => 'admin.ai.index', 'uri' => '/admin/ai', 'module' => 'AI Assistant', 'role' => 'admin'],
            ['name' => 'admin.ai.conversations', 'uri' => '/admin/ai/conversations', 'module' => 'AI Assistant', 'role' => 'admin'],
            ['name' => 'admin.ai.knowledge-gaps', 'uri' => '/admin/ai/knowledge-gaps', 'module' => 'AI Assistant', 'role' => 'admin'],
            ['name' => 'admin.ai.settings', 'uri' => '/admin/ai/settings', 'module' => 'AI Assistant', 'role' => 'admin'],
            ['name' => 'admin.ai.usage', 'uri' => '/admin/ai/usage', 'module' => 'AI Assistant', 'role' => 'admin'],
            ['name' => 'admin.ai.questions', 'uri' => '/admin/ai/questions', 'module' => 'AI Assistant', 'role' => 'admin'],
        ];
    }

    /**
     * Check health of a single route.
     */
    public function checkPage(array $routeInfo): PageHealth
    {
        $start = microtime(true);
        $uri = $routeInfo['uri'];
        $role = $routeInfo['role'] ?? 'guest';
        $routeName = $routeInfo['name'] ?? null;
        $module = $routeInfo['module'] ?? 'General';

        $admin = null;
        $customer = null;

        if ($role === 'admin') {
            $admin = User::whereIn('role', ['super_admin', 'admin'])->first();
        } elseif ($role === 'customer') {
            $customer = User::where('role', 'customer')->first();
        }

        $httpStatus = 500;
        $errorSummary = null;
        $status = 'error';

        try {
            // Internal request dispatching
            $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
            $request = Request::create($uri, 'GET');

            if ($role === 'admin' && $admin) {
                auth()->setUser($admin);
            } elseif ($role === 'customer' && $customer) {
                auth()->setUser($customer);
            } else {
                auth()->logout();
            }

            $response = $kernel->handle($request);
            $httpStatus = $response->getStatusCode();
            $durationMs = (int)((microtime(true) - $start) * 1000);

            if (in_array($httpStatus, [200, 302, 301])) {
                $status = 'healthy';
                if ($durationMs > 1500) {
                    $status = 'warning';
                    $errorSummary = "Slow page response ({$durationMs}ms)";
                }
            } elseif ($httpStatus === 404) {
                $status = 'error';
                $errorSummary = "HTTP 404 Not Found";
            } elseif ($httpStatus >= 500) {
                $status = 'error';
                $errorSummary = "HTTP {$httpStatus} Server Error";
            } else {
                $status = 'warning';
                $errorSummary = "HTTP {$httpStatus}";
            }
        } catch (\Throwable $e) {
            $durationMs = (int)((microtime(true) - $start) * 1000);
            $status = 'error';
            $httpStatus = 500;
            $errorSummary = $e->getMessage();
        }

        $record = PageHealth::updateOrCreate(
            ['uri' => $uri, 'method' => 'GET'],
            [
                'route_name' => $routeName,
                'module' => $module,
                'role_tested' => $role,
                'http_status' => $httpStatus,
                'response_time_ms' => $durationMs,
                'status' => $status,
                'error_summary' => $errorSummary,
                'last_checked_at' => now(),
                'last_success_at' => $status === 'healthy' ? now() : DB::raw('last_success_at'),
                'last_failure_at' => $status === 'error' ? now() : DB::raw('last_failure_at'),
            ]
        );

        return $record;
    }

    /**
     * Check all monitored routes.
     */
    public function checkAllPages(): array
    {
        $routes = $this->getMonitoredRoutes();
        $results = [];

        foreach ($routes as $routeInfo) {
            $results[] = $this->checkPage($routeInfo);
        }

        return $results;
    }
}
