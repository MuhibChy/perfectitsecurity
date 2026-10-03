<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Authorized report exports (Phase 2). Every export re-runs the same
 * filter-bound builder as the screen view, then renders PDF or CSV.
 * Authorization lives in the service (per-type gates); downloads are
 * route-model/ownership checked, never predictable public URLs.
 */
class ReportExportController extends Controller
{
    public function __construct(private ReportExportService $reports) {}

    public function export(Request $request, string $type)
    {
        $format = strtolower($request->get('format', 'csv'));
        abort_unless(in_array($format, ['csv', 'pdf', 'xlsx'], true), 422, 'Format must be csv, pdf or xlsx.');
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'customer_id' => 'nullable|integer',
            'employee_id' => 'nullable|integer',
            'agent_id' => 'nullable|integer',
            'worker_id' => 'nullable|integer',
            'franchise_id' => 'nullable|integer',
            'service_id' => 'nullable|integer',
            'order_id' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'status' => 'nullable|string|max:50',
            'payment_status' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'branch' => 'nullable|string|max:100',
        ]);
        // Bound export windows: an unbounded range can dump full tables.
        if (!empty($validated['date_from']) && !empty($validated['date_to'])) {
            abort_if(\Carbon\Carbon::parse($validated['date_from'])->diffInDays(\Carbon\Carbon::parse($validated['date_to'])) > 366, 422, 'Export date range must not exceed 366 days.');
        }
        $report = $this->reports->build($type, $validated, auth()->user());

        AuditLog::log('report.exported', 'reports', null, "Report '{$type}' exported as {$format} by " . auth()->user()->name . ' with filters: ' . json_encode($request->except(['_token'])) . " (" . count($report['rows']) . ' rows).');

        return match ($format) {
            'pdf' => $this->pdf($type, $report),
            'xlsx' => $this->xlsx($type, $report),
            default => $this->csv($type, $report),
        };
    }

    protected function xlsx(string $type, array $report)
    {
        $path = app(\App\Services\XlsxExportService::class)->build(
            $report['title'], $report['period'] ?? '—', $report['summary'], $report['columns'], $report['rows']
        );
        return response()->download($path, "perfectit-{$type}-" . now()->format('Ymd-His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    protected function csv(string $type, array $report): StreamedResponse
    {
        $filename = "perfectit-{$type}-" . now()->format('Ymd-His') . '.csv';
        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, [self::csvCell($report['title'])]);
            fputcsv($out, ['Generated', now()->format('Y-m-d H:i'), 'Period', self::csvCell($report['period'] ?? '—')]);
            foreach ($report['summary'] as $k => $v) fputcsv($out, [self::csvCell($k), self::csvCell($v)]);
            fputcsv($out, []);
            fputcsv($out, array_map([self::class, 'csvCell'], $report['columns']));
            foreach ($report['rows'] as $row) fputcsv($out, array_map(fn ($v) => self::csvCell($v === null ? '' : (string) $v), $row));
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Neutralize spreadsheet formula injection: values starting with
     * = + - @ (after optional whitespace) are prefixed so Excel/Sheets
     * treat them as text, never as formulas.
     */
    public static function csvCell(mixed $value): string
    {
        $s = (string) $value;
        if (preg_match('/^[\s]*[=+\-@]/', $s)) {
            return "'" . $s;
        }
        return $s;
    }

    protected function pdf(string $type, array $report)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.report', [
            'report' => $report,
            'generatedBy' => auth()->user()->name,
            'generatedAt' => now()->format('Y-m-d H:i'),
        ])->setPaper('a4', 'landscape');
        return $pdf->download("perfectit-{$type}-" . now()->format('Ymd-His') . '.pdf');
    }
}
