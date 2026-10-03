<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;

/**
 * Staff-facing operational history. Customer data stays inside the staff
 * boundary; employee detail is own-record or admin-only.
 */
class TraceabilityController extends Controller
{
    public function customer(User $user)
    {
        abort_unless($user->isCustomer(), 404);
        $overview = TraceabilityService::customerOverview($user);
        $timeline = TraceabilityService::customerTimeline($user, 'admin', 100);
        return view('admin.traceability.customer', compact('user', 'overview', 'timeline'));
    }

    public function employee(User $user)
    {
        abort_unless($user->isStaff(), 404);
        abort_unless(auth()->id() === $user->id || auth()->user()->isAdmin(), 403);
        $overview = TraceabilityService::employeeOverview($user);
        $timeline = TraceabilityService::employeeTimeline($user, 100);
        $links = TraceabilityService::employeeCustomerLinks($user);
        return view('admin.traceability.employee', compact('user', 'overview', 'timeline', 'links'));
    }

    public function myWork()
    {
        $user = auth()->user();
        $overview = TraceabilityService::employeeOverview($user);
        $timeline = TraceabilityService::employeeTimeline($user, 100);
        $links = TraceabilityService::employeeCustomerLinks($user);
        return view('admin.traceability.employee', compact('user', 'overview', 'timeline', 'links'));
    }

    public function search(Request $request)
    {
        $q = (string) $request->input('q', '');
        $results = $q !== '' ? TraceabilityService::globalSearch($q) : null;
        return view('admin.traceability.search', compact('q', 'results'));
    }

    public function auditDashboard(Request $request)
    {
        $query = AuditLog::with('user')->latest();
        if ($request->filled('module')) $query->where('module', $request->input('module'));
        if ($request->filled('action')) $query->where('action', 'like', '%' . $request->input('action') . '%');
        if ($request->filled('date')) $query->whereDate('created_at', $request->input('date'));
        $logs = $query->paginate(50)->withQueryString();
        $today = AuditLog::whereDate('created_at', today())->count();
        $byModule = AuditLog::whereDate('created_at', today())->selectRaw('module, COUNT(*) as c')->groupBy('module')->pluck('c', 'module');
        $failed = AuditLog::where('action', 'like', '%fail%')->orWhere('action', 'like', '%breach%')->latest()->take(10)->get();
        $modules = AuditLog::select('module')->distinct()->pluck('module');
        return view('admin.traceability.audit', compact('logs', 'today', 'byModule', 'failed', 'modules'));
    }

    public function consistency()
    {
        $issues = TraceabilityService::consistencyCheck();
        return view('admin.traceability.consistency', compact('issues'));
    }
}
