<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ReportExportService;
use Illuminate\Http\Request;

/**
 * Customer self-service exports: own 360° report only.
 * customer_id is forced to the authenticated user — never request-supplied.
 */
class ReportController extends Controller
{
    public function __construct(private ReportExportService $reports)
    {
    }

    /** Owner-scoped service-detail HTML report (404-first lookup, then builder gate). */
    public function service(int $id)
    {
        $order = \App\Models\ServiceOrder::with(['customer', 'service', 'tasks.assignee', 'payments', 'invoices'])
            ->where('customer_id', auth()->id())->findOrFail($id);
        $report = $this->reports->build('service', ['order_id' => $id], auth()->user());
        $timeline = \App\Services\ServiceTrackingService::serviceTimeline($order->id, null, true)->map(fn ($e) => [
            'at' => $e->created_at,
            'label' => ucfirst(str_replace('_', ' ', $e->action)),
            'detail' => trim(($e->comment ?? '').($e->actor ? ' — by '.$e->actor->name : '')) ?: ucfirst(str_replace('_', ' ', $e->action)),
            'url' => null,
        ]);
        $statusHistory = collect();

        return view('reports.service', compact('report', 'order', 'timeline', 'statusHistory'));
    }

    public function mine(Request $request)
    {
        $format = strtolower($request->get('format', 'csv'));
        abort_unless(in_array($format, ['csv', 'pdf', 'xlsx'], true), 422);
        // Self-service types only; customer_id is always forced to self.
        $type = in_array($request->get('type'), ['customer', 'customer-full', 'payment', 'task'], true)
            ? $request->get('type') : 'customer';
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|max:50',
            'service_id' => 'nullable|integer',
            'order_id' => 'nullable|integer',
            'currency' => 'nullable|string|size:3',
        ]);
        $filters = array_merge($request->all(), ['customer_id' => auth()->id()]);
        if ($format === 'xlsx') {
            $report = $this->reports->build($type, $filters, auth()->user());
            AuditLog::log('report.exported', 'reports', null, 'Own customer report exported as xlsx by '.auth()->user()->name.'.');
            $path = app(\App\Services\XlsxExportService::class)->build(
                $report['title'], $report['period'] ?? '—', $report['summary'], $report['columns'], $report['rows']
            );

            return response()->download($path, 'my-report-'.now()->format('Ymd-His').'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }
        $report = $this->reports->build($type, $filters, auth()->user());
        AuditLog::log('report.exported', 'reports', null, "Own {$type} report exported as {$format} by ".auth()->user()->name.'.');
        if ($format === 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.report', [
                'report' => $report, 'generatedBy' => auth()->user()->name, 'generatedAt' => now()->format('Y-m-d H:i'),
            ])->setPaper('a4', 'landscape')->download('my-report-'.now()->format('Ymd-His').'.pdf');
        }
        $filename = 'my-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [$report['title']]);
            foreach ($report['summary'] as $k => $v) {
                fputcsv($out, [$k, $v]);
            }
            fputcsv($out, []);
            fputcsv($out, $report['columns']);
            foreach ($report['rows'] as $row) {
                fputcsv($out, array_map(fn ($v) => $v === null ? '' : (string) $v, $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
