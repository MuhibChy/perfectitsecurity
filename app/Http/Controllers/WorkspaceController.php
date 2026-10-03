<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * Contractor workspace (freelancer / commission_agent): read-only view of
 * OWN assigned tasks and OWN commissions. These roles pass neither the
 * customer nor the staff boundary, so without this they could authenticate
 * but reach nothing. Nothing here grants finance, admin, or peer access —
 * every row is scoped to the authenticated user id.
 */
class WorkspaceController extends Controller
{
    protected function contractor(): \App\Models\User
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['freelancer', 'commission_agent'], true), 403);

        return $user;
    }

    public function index()
    {
        $user = $this->contractor();
        $tasks = Task::with(['project.customer', 'serviceOrder'])
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereHas('contributors', fn ($qq) => $qq->where('user_id', $user->id));
            })
            ->latest()->paginate(15);
        $earnings = app(\App\Services\CommissionService::class)->getWorkerEarnings($user->id);
        $salaryPaid = (float) $user->salaries()->where('status', 'paid')->sum('net_salary');

        return view('workspace.index', compact('user', 'tasks', 'earnings', 'salaryPaid'));
    }

    public function task(Task $task)
    {
        $user = $this->contractor();
        $task->load(['project.customer', 'serviceOrder.customer', 'customer']);
        $mine = (int) $task->assigned_to === (int) $user->id
            || $task->contributors()->where('user_id', $user->id)->exists();
        abort_unless($mine, 403, 'Task is not assigned to you.');

        return view('workspace.task', compact('user', 'task'));
    }

    public function commissions()
    {
        $user = $this->contractor();
        $commissions = Commission::with(['rule', 'customer', 'project'])
            ->where('worker_id', $user->id)->latest()->paginate(15);
        $payouts = CommissionPayout::where('worker_id', $user->id)->latest()->limit(10)->get();
        $earnings = app(\App\Services\CommissionService::class)->getWorkerEarnings($user->id);

        return view('workspace.commissions', compact('user', 'commissions', 'payouts', 'earnings'));
    }

    /**
     * Contractor self-service commission report (own ledger only).
     * Scope is forced to the authenticated worker; peer/customer filters
     * are ignored. Read-only: never recalculates commissions.
     */
    public function commissionsReport(Request $request)
    {
        $user = $this->contractor();
        $format = strtolower($request->get('format', 'pdf'));
        abort_unless(in_array($format, ['csv', 'pdf', 'xlsx'], true), 422, 'Format must be csv, pdf or xlsx.');
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|max:50',
        ]);
        if (! empty($validated['date_from']) && ! empty($validated['date_to'])) {
            abort_if(\Carbon\Carbon::parse($validated['date_from'])->diffInDays(\Carbon\Carbon::parse($validated['date_to'])) > 366, 422, 'Report date range must not exceed 366 days.');
        }
        $report = app(\App\Services\ReportExportService::class)->build('commission', $validated, $user);
        \App\Models\AuditLog::log('report.exported', 'reports', null, "Commission self-report exported as {$format} by {$user->name} (".count($report['rows']).' rows).');

        return \App\Services\ReportDownloadService::download('my-commissions', $report, $format);
    }

    /**
     * Contractor self-service work report (own assigned tasks only).
     */
    public function workReport(Request $request)
    {
        $user = $this->contractor();
        $format = strtolower($request->get('format', 'pdf'));
        abort_unless(in_array($format, ['csv', 'pdf', 'xlsx'], true), 422, 'Format must be csv, pdf or xlsx.');
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|max:50',
        ]);
        if (! empty($validated['date_from']) && ! empty($validated['date_to'])) {
            abort_if(\Carbon\Carbon::parse($validated['date_from'])->diffInDays(\Carbon\Carbon::parse($validated['date_to'])) > 366, 422, 'Report date range must not exceed 366 days.');
        }
        $validated['employee_id'] = (int) $user->id;
        $report = app(\App\Services\ReportExportService::class)->build('employee-service', $validated, $user);
        \App\Models\AuditLog::log('report.exported', 'reports', null, "Contractor work report exported as {$format} by {$user->name} (".count($report['rows']).' rows).');

        return \App\Services\ReportDownloadService::download('my-work', $report, $format);
    }
}
