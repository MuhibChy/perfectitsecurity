<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $report['title'] }}</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
.header { border-bottom: 3px solid #1e40af; padding-bottom: 8px; margin-bottom: 12px; }
.brand { font-size: 18px; font-weight: bold; color: #1e40af; }
.meta { font-size: 9px; color: #555; }
h2 { font-size: 14px; margin: 10px 0 6px; }
table { width: 100%; border-collapse: collapse; margin-top: 6px; }
th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
th { background: #e5e7eb; }
.summary td:first-child { font-weight: bold; width: 220px; }
.footer { margin-top: 14px; font-size: 8px; color: #666; border-top: 1px solid #999; padding-top: 6px; }
.confidential { color: #b91c1c; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
<div class="brand">PerfectITSecurity</div>
<div class="meta">Report generated {{ $generatedAt }} by {{ $generatedBy }} · Period: {{ $report['period'] ?? '—' }}</div>
@if(!empty($report['confidential']))<div class="confidential">CONFIDENTIAL — authorized recipients only</div>@endif
</div>
<h2>{{ $report['title'] }}</h2>
<h2>Summary</h2>
<table class="summary"><tbody>
@foreach($report['summary'] as $k => $v)<tr><td>{{ $k }}</td><td>{{ $v }}</td></tr>@endforeach
</tbody></table>
<h2>Records ({{ count($report['rows']) }})</h2>
<table><thead><tr>@foreach($report['columns'] as $c)<th>{{ $c }}</th>@endforeach</tr></thead>
<tbody>
@forelse($report['rows'] as $row)<tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($report['columns']) }}">No records for the selected filters.</td></tr>@endforelse
</tbody></table>
<div class="footer">PerfectITSecurity · Confidential business report · Generated {{ $generatedAt }} · Totals computed server-side from authoritative records · Page managed by PDF renderer</div>
</body>
</html>
