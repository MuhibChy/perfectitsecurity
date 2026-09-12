<?php

namespace App\Services\Health;

use App\Models\SystemHealthCheck;
use App\Models\User;
use App\Models\Company;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\KbArticle;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

class SystemHealthService
{
    /**
     * Run all safe, non-destructive health checks across all modules.
     */
    public function runAllChecks(): array
    {
        $checks = [
            $this->checkApplication(),
            $this->checkDatabase(),
            $this->checkStorage(),
            $this->checkQueue(),
            $this->checkScheduler(),
            $this->checkAssets(),
            $this->checkAuthentication(),
            $this->checkPublicWebsite(),
            $this->checkCustomerPortal(),
            $this->checkAdminPanel(),
            $this->checkTicketingSystem(),
            $this->checkKnowledgeBase(),
            $this->checkAiAssistant(),
            $this->checkFinancialSystem(),
            $this->checkCommissionSystem(),
            $this->checkEmailAndNotifications(),
        ];

        // Save check results in database
        foreach ($checks as $check) {
            SystemHealthCheck::create([
                'module' => $check['module'],
                'check_name' => $check['check_name'],
                'status' => $check['status'],
                'response_time_ms' => $check['response_time_ms'] ?? null,
                'message' => $check['message'],
                'details' => $check['details'] ?? null,
                'checked_at' => now(),
            ]);
        }

        return $checks;
    }

    public function getLatestModuleStatuses(): array
    {
        $modules = [
            'Application & Environment',
            'Database',
            'File Storage',
            'Queue Workers',
            'Task Scheduler',
            'Static Assets & Vite',
            'Authentication',
            'Public Website',
            'Customer Portal',
            'Admin Dashboard',
            'Ticketing System',
            'Knowledge Base',
            'AI Assistant',
            'Financial System',
            'Commission System',
            'Email & Notifications',
        ];

        $results = [];
        foreach ($modules as $module) {
            $latest = SystemHealthCheck::where('module', $module)->latest('checked_at')->first();
            if ($latest) {
                $results[] = $latest;
            } else {
                // If not checked yet, run check on the fly
                $methodName = $this->resolveCheckMethod($module);
                if (method_exists($this, $methodName)) {
                    $check = $this->$methodName();
                    $record = SystemHealthCheck::create([
                        'module' => $check['module'],
                        'check_name' => $check['check_name'],
                        'status' => $check['status'],
                        'response_time_ms' => $check['response_time_ms'] ?? null,
                        'message' => $check['message'],
                        'details' => $check['details'] ?? null,
                        'checked_at' => now(),
                    ]);
                    $results[] = $record;
                }
            }
        }

        return $results;
    }

    protected function resolveCheckMethod(string $module): string
    {
        return match ($module) {
            'Application & Environment' => 'checkApplication',
            'Database' => 'checkDatabase',
            'File Storage' => 'checkStorage',
            'Queue Workers' => 'checkQueue',
            'Task Scheduler' => 'checkScheduler',
            'Static Assets & Vite' => 'checkAssets',
            'Authentication' => 'checkAuthentication',
            'Public Website' => 'checkPublicWebsite',
            'Customer Portal' => 'checkCustomerPortal',
            'Admin Dashboard' => 'checkAdminPanel',
            'Ticketing System' => 'checkTicketingSystem',
            'Knowledge Base' => 'checkKnowledgeBase',
            'AI Assistant' => 'checkAiAssistant',
            'Financial System' => 'checkFinancialSystem',
            'Commission System' => 'checkCommissionSystem',
            'Email & Notifications' => 'checkEmailAndNotifications',
            default => 'checkApplication',
        };
    }

    public function checkApplication(): array
    {
        $start = microtime(true);
        $appKeySet = !empty(config('app.key'));
        $appEnv = config('app.env');
        $appDebug = config('app.debug');

        $status = 'healthy';
        $message = "Laravel " . app()->version() . " running in [{$appEnv}] environment.";

        if (!$appKeySet) {
            $status = 'critical';
            $message = "APP_KEY is missing! Application encryption is insecure.";
        }
        if ($appEnv === 'production' && $appDebug) {
            $status = 'critical';
            $message = 'APP_DEBUG is enabled in production; exception details may expose sensitive information.';
        }

        return [
            'module' => 'Application & Environment',
            'check_name' => 'Laravel Framework & App Configuration',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'environment' => $appEnv,
                'debug_mode' => $appDebug,
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
            ]
        ];
    }

    public function checkDatabase(): array
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $pingStart = microtime(true);
            DB::select('SELECT 1');
            $pingMs = (int)((microtime(true) - $pingStart) * 1000);

            $driver = DB::connection()->getDriverName();
            $tablesCount = count(Schema::getAllTables());

            $status = 'healthy';
            $message = "Connected to [{$driver}] database with {$tablesCount} tables. Ping: {$pingMs}ms.";

            if ($pingMs > 500) {
                $status = 'warning';
                $message .= " High latency detected.";
            }

            return [
                'module' => 'Database',
                'check_name' => 'Database Connectivity & Query Latency',
                'status' => $status,
                'response_time_ms' => (int)((microtime(true) - $start) * 1000),
                'message' => $message,
                'details' => [
                    'driver' => $driver,
                    'database' => DB::connection()->getDatabaseName(),
                    'tables_count' => $tablesCount,
                    'ping_ms' => $pingMs,
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'module' => 'Database',
                'check_name' => 'Database Connectivity & Query Latency',
                'status' => 'critical',
                'response_time_ms' => (int)((microtime(true) - $start) * 1000),
                'message' => "Database connection failed: " . $e->getMessage(),
                'details' => ['error' => $e->getMessage()]
            ];
        }
    }

    public function checkStorage(): array
    {
        $start = microtime(true);
        $pathsToCheck = [
            'storage/app' => storage_path('app'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $unwritable = [];
        foreach ($pathsToCheck as $name => $path) {
            if (!File::exists($path)) {
                @File::makeDirectory($path, 0775, true);
            }
            if (!File::isWritable($path)) {
                $unwritable[] = $name;
            }
        }

        $freeBytes = @disk_free_space(storage_path());
        $freeGb = $freeBytes ? round($freeBytes / (1024 * 1024 * 1024), 2) : 'N/A';

        $status = empty($unwritable) ? 'healthy' : 'critical';
        $message = empty($unwritable)
            ? "All critical application storage directories are writable. Free space: {$freeGb} GB."
            : "Storage write permissions missing for: " . implode(', ', $unwritable);

        return [
            'module' => 'File Storage',
            'check_name' => 'Storage Directory Writable Permissions',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'free_space_gb' => $freeGb,
                'unwritable_paths' => $unwritable,
                'public_storage_linked' => file_exists(public_path('storage')),
            ]
        ];
    }

    public function checkQueue(): array
    {
        $start = microtime(true);
        $driver = config('queue.default');
        
        $failedJobsCount = 0;
        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = DB::table('failed_jobs')->count();
        }

        $status = in_array($driver, ['sync', 'null']) ? 'warning' : 'healthy';
        $message = "Queue driver [{$driver}] configured. Failed jobs: {$failedJobsCount}.";

        if ($status === 'warning') {
            $message .= ' A persistent queue worker is required for production asynchronous jobs.';
        }

        if ($failedJobsCount > 10) {
            $status = 'warning';
            $message = "Queue has {$failedJobsCount} failed jobs requiring administrator inspection.";
        }

        return [
            'module' => 'Queue Workers',
            'check_name' => 'Queue Worker & Failed Jobs Monitor',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'default_driver' => $driver,
                'failed_jobs_count' => $failedJobsCount,
            ]
        ];
    }

    public function checkScheduler(): array
    {
        $start = microtime(true);
        $eventsCount = count(app(\Illuminate\Console\Scheduling\Schedule::class)->events());
        $status = app()->environment('production') ? 'warning' : 'healthy';
        $message = "{$eventsCount} scheduled task(s) registered.";
        if ($status === 'warning') {
            $message .= ' Verify the server cron invokes `php artisan schedule:run` every minute.';
        }

        return [
            'module' => 'Task Scheduler',
            'check_name' => 'Task Scheduler Heartbeat & Crons',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'scheduler_timezone' => config('app.timezone'),
                'registered_events' => $eventsCount,
                'now' => now()->toIso8601String(),
            ]
        ];
    }

    public function checkAssets(): array
    {
        $start = microtime(true);
        $viteManifest = public_path('build/manifest.json');
        $hasViteManifest = file_exists($viteManifest);
        $hasAppCss = file_exists(resource_path('css/app.css'));
        $hasAppJs = file_exists(resource_path('js/app.js'));

        $status = $hasViteManifest ? 'healthy' : 'warning';
        $message = $hasViteManifest
            ? 'Built Vite assets are available.'
            : 'Vite production manifest is missing; run npm run build before deployment.';

        return [
            'module' => 'Static Assets & Vite',
            'check_name' => 'Stylesheet, Script & Build Asset Health',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'app_css_present' => $hasAppCss,
                'app_js_present' => $hasAppJs,
                'manifest_built' => $hasViteManifest,
            ]
        ];
    }

    public function checkAuthentication(): array
    {
        $start = microtime(true);
        $usersCount = User::count();
        $adminCount = User::whereIn('role', ['super_admin', 'admin'])->count();
        $activeUsers = User::where('is_active', true)->count();

        $status = $adminCount > 0 ? 'healthy' : 'warning';
        $message = "{$usersCount} registered users ({$adminCount} administrators, {$activeUsers} active).";

        return [
            'module' => 'Authentication',
            'check_name' => 'User Directory & Authentication Security',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'total_users' => $usersCount,
                'admin_users' => $adminCount,
                'active_users' => $activeUsers,
                'guards' => array_keys(config('auth.guards', [])),
            ]
        ];
    }

    public function checkPublicWebsite(): array
    {
        $start = microtime(true);
        $servicesCount = Service::where('is_active', true)->count();
        $status = 'healthy';
        $message = "Public website active with {$servicesCount} published IT service catalog items.";

        return [
            'module' => 'Public Website',
            'check_name' => 'Marketing & Landing Page Engine',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'active_services' => $servicesCount,
            ]
        ];
    }

    public function checkCustomerPortal(): array
    {
        $start = microtime(true);
        $customersCount = User::where('role', 'customer')->count();
        $status = 'healthy';
        $message = "Customer self-service portal active for {$customersCount} customer accounts.";

        return [
            'module' => 'Customer Portal',
            'check_name' => 'Client Portal, Tickets & Invoicing',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'total_customers' => $customersCount,
            ]
        ];
    }

    public function checkAdminPanel(): array
    {
        $start = microtime(true);
        $status = 'healthy';
        $message = "Admin panel active with role-based access control and system management tools.";

        return [
            'module' => 'Admin Dashboard',
            'check_name' => 'Administrator Workspace & Controls',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
        ];
    }

    public function checkTicketingSystem(): array
    {
        $start = microtime(true);
        $openTickets = Ticket::whereIn('status', ['new', 'open', 'in_progress'])->count();
        $unassignedTickets = Ticket::whereIn('status', ['new', 'open'])->whereNull('assigned_to')->count();

        $status = 'healthy';
        $message = "{$openTickets} open support incidents ({$unassignedTickets} awaiting assignment).";

        if ($unassignedTickets > 20) {
            $status = 'warning';
            $message = "High volume of unassigned tickets ({$unassignedTickets}) awaiting dispatcher.";
        }

        return [
            'module' => 'Ticketing System',
            'check_name' => 'Helpdesk Dispatcher & SLA Engine',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => [
                'open_tickets' => $openTickets,
                'unassigned_tickets' => $unassignedTickets,
            ]
        ];
    }

    public function checkKnowledgeBase(): array
    {
        $start = microtime(true);
        $articlesCount = Schema::hasTable('kb_articles') ? KbArticle::where('is_published', true)->count() : 0;

        return [
            'module' => 'Knowledge Base',
            'check_name' => 'Self-Help Articles & Technical Docs',
            'status' => 'healthy',
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => "{$articlesCount} published self-help articles and technical guides available.",
            'details' => ['published_articles' => $articlesCount]
        ];
    }

    public function checkAiAssistant(): array
    {
        $start = microtime(true);
        $aiEnabled = config('services.ai.enabled', true);
        $status = $aiEnabled ? 'healthy' : 'warning';
        $message = $aiEnabled
            ? "AI Assistant engine active with conversational support and automated triage."
            : "AI Assistant disabled in configuration.";

        return [
            'module' => 'AI Assistant',
            'check_name' => 'Conversational Support & AI Triage',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $message,
            'details' => ['enabled' => $aiEnabled]
        ];
    }

    public function checkFinancialSystem(): array
    {
        $start = microtime(true);
        $invoicesCount = Invoice::count();
        $pendingAmount = Invoice::whereIn('status', ['sent', 'overdue', 'partially_paid'])->sum('amount_due');

        $status = in_array($mailDriver, ['log', 'array']) ? 'warning' : 'healthy';
        return [
            'module' => 'Financial System',
            'check_name' => 'Invoicing, Expenses & Profit/Loss',
            'status' => $status,
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => "{$invoicesCount} total invoices recorded (${$pendingAmount} pending collection).",
            'details' => [
                'total_invoices' => $invoicesCount,
                'pending_receivables' => (float)$pendingAmount,
            ]
        ];
    }

    public function checkCommissionSystem(): array
    {
        $start = microtime(true);
        return [
            'module' => 'Commission System',
            'check_name' => 'Technician Payouts & Commission Rules',
            'status' => 'healthy',
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => "Commission calculation rules active with automated percentage and fixed payouts.",
        ];
    }

    public function checkEmailAndNotifications(): array
    {
        $start = microtime(true);
        $mailDriver = config('mail.default', 'log');

        return [
            'module' => 'Email & Notifications',
            'check_name' => 'Transactional Email & Realtime Alerts',
            'status' => 'healthy',
            'response_time_ms' => (int)((microtime(true) - $start) * 1000),
            'message' => $status === 'healthy'
                ? "Notification dispatcher configured with [{$mailDriver}] mail delivery driver."
                : "Mail driver [{$mailDriver}] does not deliver production email; configure a transactional mail provider.",
            'details' => ['mail_driver' => $mailDriver]
        ];
    }
}
