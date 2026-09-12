<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageHealth;
use App\Models\SystemErrorLog;
use App\Models\SystemHealthCheck;
use App\Services\Health\ErrorLoggingService;
use App\Services\Health\PageHealthService;
use App\Services\Health\SystemHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HealthController extends Controller
{
    protected SystemHealthService $healthService;
    protected PageHealthService $pageService;
    protected ErrorLoggingService $errorService;

    public function __construct(
        SystemHealthService $healthService,
        PageHealthService $pageService,
        ErrorLoggingService $errorService
    ) {
        $this->healthService = $healthService;
        $this->pageService = $pageService;
        $this->errorService = $errorService;
    }

    /**
     * Centralized Website Overview & System Health Dashboard.
     */
    public function index()
    {
        $moduleChecks = $this->healthService->getLatestModuleStatuses();
        
        $totalModules = count($moduleChecks);
        $healthyModules = collect($moduleChecks)->where('status', 'healthy')->count();
        $warningModules = collect($moduleChecks)->where('status', 'warning')->count();
        $failedModules = collect($moduleChecks)->where('status', 'critical')->count();

        // Page Health stats
        $totalPages = PageHealth::count();
        if ($totalPages === 0) {
            $this->pageService->checkAllPages();
            $totalPages = PageHealth::count();
        }

        $healthyPages = PageHealth::where('status', 'healthy')->count();
        $warningPages = PageHealth::where('status', 'warning')->count();
        $errorPages = PageHealth::where('status', 'error')->count();

        // Unresolved errors
        $unresolvedErrorsCount = SystemErrorLog::unresolved()->sum('occurrences');
        $recentErrors = SystemErrorLog::unresolved()->latest('last_seen_at')->limit(5)->get();

        // Overall status badge calculation
        if ($failedModules > 0 || $errorPages > 0) {
            $overallStatus = 'critical';
            $overallTitle = '🔴 Critical Issue Detected';
            $overallBadgeClass = 'bg-red-500/10 border-red-500/30 text-red-600 dark:text-red-400';
        } elseif ($warningModules > 0 || $warningPages > 0 || $unresolvedErrorsCount > 0) {
            $overallStatus = 'warning';
            $overallTitle = '🟡 Attention Required';
            $overallBadgeClass = 'bg-amber-500/10 border-amber-500/30 text-amber-600 dark:text-amber-400';
        } else {
            $overallStatus = 'healthy';
            $overallTitle = '🟢 System Operating Normally';
            $overallBadgeClass = 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400';
        }

        return view('admin.health.index', compact(
            'moduleChecks',
            'totalModules',
            'healthyModules',
            'warningModules',
            'failedModules',
            'totalPages',
            'healthyPages',
            'warningPages',
            'errorPages',
            'unresolvedErrorsCount',
            'recentErrors',
            'overallStatus',
            'overallTitle',
            'overallBadgeClass'
        ));
    }

    /**
     * Run full automated system self-check.
     */
    public function runCheck(Request $request)
    {
        $this->healthService->runAllChecks();
        $this->pageService->checkAllPages();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'System self-check completed successfully.',
            ]);
        }

        return redirect()->route('admin.health.index')->with('success', 'System self-check completed. All modules and pages refreshed.');
    }

    /**
     * Page Health Checker view.
     */
    public function pages(Request $request)
    {
        $query = PageHealth::query();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uri', 'like', "%{$search}%")
                  ->orWhere('route_name', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $pages = $query->orderBy('status', 'desc')->orderBy('uri')->paginate(25)->withQueryString();
        $modules = PageHealth::select('module')->distinct()->pluck('module');

        return view('admin.health.pages', compact('pages', 'modules'));
    }

    /**
     * Re-check single page on-demand.
     */
    public function checkSinglePage($id)
    {
        $page = PageHealth::findOrFail($id);
        $this->pageService->checkPage([
            'name' => $page->route_name,
            'uri' => $page->uri,
            'module' => $page->module,
            'role' => $page->role_tested,
        ]);

        return redirect()->back()->with('success', "Page [{$page->uri}] re-tested.");
    }

    /**
     * Centralized Error Center view.
     */
    public function errors(Request $request)
    {
        $query = SystemErrorLog::query();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'unresolved');
        }

        if ($request->filled('error_type')) {
            $query->where('error_type', $request->error_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('route', 'like', "%{$search}%")
                  ->orWhere('file', 'like', "%{$search}%");
            });
        }

        $errors = $query->latest('last_seen_at')->paginate(20)->withQueryString();
        $modules = SystemErrorLog::select('module')->distinct()->pluck('module');

        return view('admin.health.errors', compact('errors', 'modules'));
    }

    /**
     * Mark error resolved.
     */
    public function resolveError(Request $request, $id)
    {
        $this->errorService->markResolved($id, $request->note);
        return redirect()->back()->with('success', 'Error marked as resolved.');
    }

    /**
     * Add note to error log.
     */
    public function addErrorNote(Request $request, $id)
    {
        $request->validate(['note' => 'required|string|max:1000']);
        $this->errorService->addNote($id, $request->note);
        return redirect()->back()->with('success', 'Note added to error log.');
    }

    /**
     * Status History.
     */
    public function history()
    {
        $history = SystemHealthCheck::latest('checked_at')->paginate(30);
        $totalChecks = SystemHealthCheck::count();
        $criticalCount = SystemHealthCheck::where('status', 'critical')->count();
        $warningCount = SystemHealthCheck::where('status', 'warning')->count();

        return view('admin.health.history', compact('history', 'totalChecks', 'criticalCount', 'warningCount'));
    }

    /**
     * Clean old logs.
     */
    public function cleanupHistory(Request $request)
    {
        $days = (int)($request->days ?? 30);
        $cutoff = now()->subDays($days);

        $deletedChecks = SystemHealthCheck::where('checked_at', '<', $cutoff)->delete();
        $deletedErrors = SystemErrorLog::resolved()->where('resolved_at', '<', $cutoff)->delete();

        return redirect()->back()->with('success', "Cleaned up logs older than {$days} days ({$deletedChecks} health checks, {$deletedErrors} resolved errors removed).");
    }

    /**
     * Safe maintenance actions.
     */
    public function maintenance(Request $request)
    {
        $action = $request->action;

        switch ($action) {
            case 'clear_cache':
                Artisan::call('cache:clear');
                $message = 'Application cache cleared successfully.';
                break;
            case 'clear_views':
                Artisan::call('view:clear');
                $message = 'Compiled Blade views cleared.';
                break;
            case 'clear_routes':
                Artisan::call('route:clear');
                $message = 'Route cache cleared.';
                break;
            case 'optimize':
                Artisan::call('optimize:clear');
                $message = 'Optimized caches cleared.';
                break;
            case 'retry_failed_jobs':
                Artisan::call('queue:retry', ['id' => ['all']]);
                $message = 'Queued retry of all failed jobs.';
                break;
            default:
                return redirect()->back()->with('error', 'Unrecognized maintenance action.');
        }

        return redirect()->back()->with('success', $message);
    }
}
