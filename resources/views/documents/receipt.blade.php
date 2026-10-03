<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt #{{ $receipt->receipt_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; }
        .header { border-bottom: 2px solid #16a34a; padding-bottom: 20px; margin-bottom: 25px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; }
        .doc-title { font-size: 20px; font-weight: bold; color: #16a34a; text-align: right; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table td { padding: 7px 4px; border-bottom: 1px solid #e2e8f0; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width:100%;"><tr>
            <td>
                <div class="logo">PerfectITSecurity</div>
                <div style="color:#64748b; font-size:12px;">Enterprise IT Infrastructure & Cybersecurity</div>
                <div style="color:#64748b; font-size:11px; margin-top:4px;">
                    Email: support@perfectitsecurity.com | Web: www.perfectitsecurity.com
                </div>
            </td>
            <td style="text-align:right;">
                <div class="doc-title">PAYMENT RECEIPT</div>
                <div style="font-weight:600; font-size:14px;">#{{ $receipt->receipt_number }}</div>
                <div style="color:#64748b; font-size:12px;">Date: {{ $receipt->issued_at?->format('d M Y') }}</div>
            </td>
        </tr></table>
    </div>
    <table class="table">
        <tr><td><strong>Customer</strong></td><td>{{ $receipt->customer->name ?? 'Valued Customer' }} ({{ $receipt->customer->email ?? '' }})</td></tr>
        @if($receipt->invoice)
        <tr><td><strong>Invoice Number</strong></td><td>{{ $receipt->invoice->invoice_number }}</td></tr>
        @endif
        @if($receipt->order)
        <tr><td><strong>Related Order</strong></td><td>{{ $receipt->order->order_number ?? '' }}</td></tr>
        <tr><td><strong>Service</strong></td><td>{{ $receipt->order->service_snapshot['name'] ?? $receipt->order->service->name ?? 'Managed IT Support' }}</td></tr>
        @endif
        <tr><td><strong>Internal Payment Number</strong></td><td>{{ $receipt->payment->payment_number ?? 'N/A' }}</td></tr>
        <tr><td><strong>Transaction ID / Reference</strong></td><td>{{ $receipt->payment->transaction_id ?? $receipt->payment->payment_number ?? 'N/A' }}</td></tr>
        <tr><td><strong>Payment Method</strong></td><td>{{ ucfirst($receipt->payment->payment_method ?? 'Payment') }}</td></tr>
        <tr><td><strong>Payment Provider</strong></td><td>{{ strtoupper($receipt->payment->gateway ?? $receipt->payment->payment_method ?? 'DIRECT') }}</td></tr>
        <tr><td><strong>Amount Paid</strong></td><td><strong style="color:#16a34a; font-size:14px;">{{ $receipt->currency }} {{ number_format((float) $receipt->amount, 2) }}</strong></td></tr>
        @if($receipt->order)
        <tr><td><strong>Order Total</strong></td><td>{{ $receipt->currency }} {{ number_format((float) ($receipt->order->total ?? 0), 2) }}</td></tr>
        <tr><td><strong>Total Paid to Date</strong></td><td>{{ $receipt->currency }} {{ number_format((float) ($receipt->order->amount_paid ?? $receipt->amount), 2) }}</td></tr>
        @elseif($receipt->invoice)
        <tr><td><strong>Invoice Total</strong></td><td>{{ $receipt->currency }} {{ number_format((float) ($receipt->invoice->total ?? 0), 2) }}</td></tr>
        <tr><td><strong>Total Paid to Date</strong></td><td>{{ $receipt->currency }} {{ number_format((float) ($receipt->invoice->amount_paid ?? $receipt->amount), 2) }}</td></tr>
        @endif
        <tr><td><strong>Remaining Balance</strong></td><td>{{ $receipt->currency }} {{ number_format((float) $receipt->remaining_balance, 2) }}</td></tr>
        <tr><td><strong>Payment Date & Time</strong></td><td>{{ $receipt->payment->paid_at?->format('d M Y, h:i A') ?? $receipt->issued_at?->format('d M Y, h:i A') }}</td></tr>
        <tr><td><strong>Payment Status</strong></td><td><span style="color:#16a34a; font-weight:bold;">COMPLETED / PAID</span></td></tr>
    </table>
    <div class="footer">
        Generated {{ now()->format('d M Y H:i') }} · Receipt {{ $receipt->receipt_number }} acknowledges receipt of payment {{ $receipt->payment->payment_number ?? '' }}. This document is an authoritative payment receipt.
    </div>
</body>
</html>
