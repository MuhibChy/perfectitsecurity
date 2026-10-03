<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cash Memo #{{ $memo->cash_memo_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; }
        .header { border-bottom: 2px solid #b45309; padding-bottom: 20px; margin-bottom: 25px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; }
        .doc-title { font-size: 20px; font-weight: bold; color: #b45309; text-align: right; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table td { padding: 7px 4px; border-bottom: 1px solid #e2e8f0; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width:100%;"><tr>
            <td><div class="logo">PerfectITSecurity</div><div style="color:#64748b;">Enterprise IT Infrastructure & Cybersecurity</div></td>
            <td style="text-align:right;"><div class="doc-title">CASH MEMO</div><div style="font-weight:600;">#{{ $memo->cash_memo_number }}</div><div>{{ $memo->issued_at?->format('d M Y') }}</div></td>
        </tr></table>
    </div>
    <p><strong>This cash memo documents the same cash payment recorded as {{ $memo->payment->payment_number }}. It is not a separate financial transaction.</strong></p>
    <table class="table">
        <tr><td><strong>Customer</strong></td><td>{{ $memo->payment->customer->name ?? '' }}</td></tr>
        <tr><td><strong>Order</strong></td><td>{{ $memo->payment->serviceOrder->order_number ?? '' }}</td></tr>
        <tr><td><strong>Cash Received</strong></td><td>{{ $memo->payment->currency }} {{ number_format((float) $memo->payment->amount, 2) }}</td></tr>
        <tr><td><strong>Payment Method</strong></td><td>Cash</td></tr>
        <tr><td><strong>Received By</strong></td><td>{{ $memo->issuer->name ?? 'Authorized staff' }}</td></tr>
        <tr><td><strong>Date</strong></td><td>{{ $memo->payment->paid_at?->format('d M Y') }}</td></tr>
    </table>
    <div class="footer">Generated {{ now()->format('d M Y H:i') }} · Cash memo {{ $memo->cash_memo_number }} ↔ payment {{ $memo->payment->payment_number }}.</div>
</body>
</html>
