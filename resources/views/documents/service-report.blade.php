<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Report — {{ $project->project_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 25px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; }
        .doc-title { font-size: 20px; font-weight: bold; color: #2563eb; text-align: right; }
        h3 { color: #1e293b; margin-bottom: 6px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 8px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; }
        .table td { border-bottom: 1px solid #e2e8f0; padding: 8px; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width:100%;"><tr>
            <td><div class="logo">PerfectITSecurity</div><div style="color:#64748b;">Enterprise IT Infrastructure & Cybersecurity</div></td>
            <td style="text-align:right;"><div class="doc-title">SERVICE REPORT</div><div style="font-weight:600;">{{ $project->project_number }}</div><div>Status: {{ strtoupper(str_replace('_', ' ', $project->status)) }}</div></td>
        </tr></table>
    </div>
    <h3>Service & Customer</h3>
    <p><strong>Service:</strong> {{ $project->service->name ?? '' }}<br>
    <strong>Customer:</strong> {{ $project->customer->name ?? '' }}<br>
    <strong>Assigned Team:</strong> {{ $project->projectManager->name ?? 'Assigned team' }}<br>
    <strong>Progress:</strong> {{ $progress['percent'] }}% ({{ $progress['basis'] }})<br>
    <strong>Period:</strong> {{ $project->start_date?->format('d M Y') ?? '—' }} → {{ $project->completed_at?->format('d M Y') ?? ($project->deadline?->format('d M Y') ?? 'ongoing') }}</p>

    <h3>Stages & Work Completed</h3>
    <table class="table">
        <thead><tr><th>Stage / Task</th><th>Status</th><th>Completed</th></tr></thead>
        <tbody>
            @foreach($project->milestones as $ms)<tr><td>{{ $ms->name }} (milestone)</td><td>{{ $ms->is_completed ? 'Completed' : 'Pending' }}</td><td>{{ $ms->completed_at?->format('d M Y') ?? '—' }}</td></tr>@endforeach
            @foreach($project->tasks as $task)<tr><td>{{ $task->title }}</td><td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td><td>{{ $task->completed_at?->format('d M Y') ?? '—' }}</td></tr>@endforeach
        </tbody>
    </table>

    <h3>Service Timeline (customer-visible events)</h3>
    <table class="table">
        <thead><tr><th>Date</th><th>Event</th></tr></thead>
        <tbody>
            @foreach($timeline->where('customer_visible', true)->take(30) as $e)
            <tr><td>{{ $e->created_at->format('d M Y H:i') }}</td><td>{{ $e->comment ?? ucfirst(str_replace('_', ' ', $e->action)) }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generated {{ now()->format('d M Y H:i') }} · Report references project {{ $project->project_number }}. Internal notes are excluded from customer reports.</div>
</body>
</html>
