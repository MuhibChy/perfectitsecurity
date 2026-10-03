<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ReportDownloadService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;

/**
 * Staff self-service reports. Every request is forced to the
 * authenticated staff member's own scope — an employee can generate a
 * report of their own authorized work, never a peer's and never finance.
 */
class StaffReportController extends Controller
{
    public function __construct(private ReportExportService $reports)
    {
    }

    public function myWork(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isStaff(), 403);

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
        // Forced self-scope: spoofed employee_id values are ignored.
        $validated['employee_id'] = (int) $user->id;

        $report = $this->reports->build('employee-service', $validated, $user);
        AuditLog::log('report.exported', 'reports', null, "Self work report exported as {$format} by {$user->name} (".count($report['rows']).' rows).');

        return ReportDownloadService::download('my-work', $report, $format);
    }
}
