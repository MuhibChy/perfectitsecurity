<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ReportDownloadService — single renderer for title/summary/columns/rows
 * report arrays (screen HTML is separate per controller). Every download
 * reuses the same builder output, so CSV/XLSX/PDF always agree.
 * Rendering never creates financial records.
 */
class ReportDownloadService
{
    public static function filename(string $type, string $format): string
    {
        return 'report-' . preg_replace('/[^a-z0-9-]/', '', strtolower($type)) . '-' . now()->format('Ymd-His') . '.' . $format;
    }

    public static function download(string $type, array $report, string $format)
    {
        return match ($format) {
            'pdf' => self::pdf($report),
            'xlsx' => self::xlsx($report),
            default => self::csv($report),
        };
    }

    public static function csv(array $report): StreamedResponse
    {
        $filename = self::filename('export', 'csv');
        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [$report['title'] ?? 'Report', $report['period'] ?? '']);
            foreach ($report['summary'] ?? [] as $k => $v) {
                fputcsv($out, [$k, is_scalar($v) ? $v : json_encode($v)]);
            }
            fputcsv($out, []);
            fputcsv($out, $report['columns'] ?? []);
            foreach ($report['rows'] ?? [] as $row) {
                fputcsv($out, array_map(fn ($c) => self::csvCell($c), (array) $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Formula-injection guard (mirrors admin export contract). */
    public static function csvCell($value): string
    {
        $s = (string) $value;
        return preg_match('/^[=+\-@]/', $s) ? "'" . $s : $s;
    }

    public static function xlsx(array $report)
    {
        $path = app(XlsxExportService::class)->build(
            $report['title'] ?? 'Report', $report['period'] ?? '—',
            $report['summary'] ?? [], $report['columns'] ?? [], $report['rows'] ?? []
        );
        return response()->download($path, self::filename('export', 'xlsx'))->deleteFileAfterSend(true);
    }

    public static function pdf(array $report)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.report', [
            'report' => $report,
            'generatedBy' => auth()->user()->name ?? 'System',
            'generatedAt' => now()->format('Y-m-d H:i'),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', false);
        return $pdf->download(self::filename('export', 'pdf'));
    }
}
