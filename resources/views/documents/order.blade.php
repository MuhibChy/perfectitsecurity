<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Order #{{ $order->order_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 25px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; }
        .doc-title { font-size: 20px; font-weight: bold; color: #2563eb; text-align: right; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 9px; text-align: left; font-size: 12px; text-transform: uppercase; color: #64748b; }
        .table td { border-bottom: 1px solid #e2e8f0; padding: 9px; }
        .totals { width: 45%; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 6px 10px; }
        .grand-total { font-size: 15px; font-weight: bold; border-top: 2px solid #1e293b; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width:100%;"><tr>
            <td><div class="logo">PerfectITSecurity</div><div style="color:#64748b;">Enterprise IT Infrastructure & Cybersecurity</div></td>
            <td style="text-align:right;"><div class="doc-title">SERVICE ORDER</div><div style="font-weight:600;">#{{ $order->order_number }}</div><div>Order Date: {{ $order->created_at->format('d M Y') }}</div><div>Status: {{ strtoupper($order->status) }}</div></td>
        </tr></table>
    </div>
    <table style="width:100%;margin-bottom:20px;"><tr>
        <td><strong>Customer</strong><br>{{ $order->customer->name ?? '' }}<br>{{ $order->customer->email ?? '' }}<br>{{ $order->customer->phone ?? '' }}</td>
        <td><strong>Service</strong><br>{{ $order->service_snapshot['name'] ?? $order->service->name ?? '' }}<br>{{ $order->service_snapshot['category'] ?? '' }}<br>Requirements: {{ \Illuminate\Support\Str::limit($order->requirements ?? '', 200) }}</td>
    </tr></table>
    <table class="table">
        <thead><tr><th>Description</th><th>Amount ({{ $order->currency }})</th></tr></thead>
        <tbody>
            <tr><td>Agreed service price</td><td>{{ number_format((float) $order->final_price ?: (float) $order->original_price, 2) }}</td></tr>
            @if((float) $order->discount_amount > 0)<tr><td>Discount</td><td>-{{ number_format((float) $order->discount_amount, 2) }}</td></tr>@endif
            <tr><td>Tax ({{ $order->tax_rate }}%)</td><td>{{ number_format((float) $order->tax_amount, 2) }}</td></tr>
        </tbody>
    </table>
    <table class="totals">
        <tr><td>Total</td><td style="text-align:right;">{{ number_format((float) $order->total, 2) }}</td></tr>
        <tr><td>Paid</td><td style="text-align:right;">{{ number_format((float) $order->amount_paid, 2) }}</td></tr>
        <tr class="grand-total"><td>Remaining Due</td><td style="text-align:right;">{{ number_format((float) $order->amount_due, 2) }}</td></tr>
    </table>
    <p>Payment Terms: advance as agreed · Currency {{ $order->currency }} · Price locked: {{ $order->price_locked ? 'Yes' : 'No' }}</p>
    <div class="footer">Generated {{ now()->format('d M Y H:i') }} · PerfectITSecurity · This document references order {{ $order->order_number }}.</div>
</body>
</html>
