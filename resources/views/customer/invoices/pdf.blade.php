<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 25px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; }
        .invoice-title { font-size: 20px; font-weight: bold; color: #2563eb; text-align: right; }
        .meta-table { width: 100%; margin-bottom: 25px; }
        .meta-table td { vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 10px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; }
        .table td { border-bottom: 1px solid #e2e8f0; padding: 10px; }
        .totals { width: 40%; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 6px 10px; }
        .totals .grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid #1e293b; color: #1e293b; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-overdue { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 50px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="logo">TechSupport Solutions</div>
                    <div style="color: #64748b; margin-top: 4px;">Enterprise IT Infrastructure & Cybersecurity</div>
                </td>
                <td style="text-align: right;">
                    <div class="invoice-title">INVOICE</div>
                    <div style="font-weight: 600; color: #475569;">#{{ $invoice->invoice_number }}</div>
                    <div>Status: <span class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : ($invoice->status === 'overdue' ? 'badge-overdue' : 'badge-pending') }}">{{ strtoupper($invoice->status) }}</span></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Billed To:</strong><br>
                <strong>{{ $invoice->customer->name ?? 'Customer' }}</strong><br>
                {{ $invoice->customer->company_name ?? '' }}<br>
                {{ $invoice->customer->email ?? '' }}<br>
                {{ $invoice->customer->phone ?? '' }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Invoice Date:</strong> {{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : ($invoice->created_at ? $invoice->created_at->format('M d, Y') : 'N/A') }}<br>
                <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}<br>
                @if($invoice->paid_at)
                    <strong>Paid Date:</strong> {{ $invoice->paid_at->format('M d, Y') }}<br>
                @endif
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%;">Item & Description</th>
                <th style="text-align: center; width: 15%;">Quantity</th>
                <th style="text-align: right; width: 15%;">Unit Price</th>
                <th style="text-align: right; width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items ?? [] as $item)
            <tr>
                <td>
                    <strong>{{ $item->description ?? $item->name }}</strong>
                </td>
                <td style="text-align: center;">{{ $item->quantity ?? 1 }}</td>
                <td style="text-align: right;">${{ number_format($item->unit_price ?? $item->price ?? 0, 2) }}</td>
                <td style="text-align: right;">${{ number_format($item->total ?? (($item->quantity ?? 1) * ($item->unit_price ?? 0)), 2) }}</td>
            </tr>
            @empty
            <tr>
                <td><strong>IT Professional Services</strong></td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;">${{ number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2) }}</td>
                <td style="text-align: right;">${{ number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2) }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal:</td>
            <td style="text-align: right;">${{ number_format($invoice->subtotal ?? $invoice->total_amount ?? $invoice->amount ?? 0, 2) }}</td>
        </tr>
        @if(isset($invoice->tax_amount) && $invoice->tax_amount > 0)
        <tr>
            <td>Tax:</td>
            <td style="text-align: right;">${{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        @endif
        @if(isset($invoice->discount_amount) && $invoice->discount_amount > 0)
        <tr>
            <td>Discount:</td>
            <td style="text-align: right;">-${{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Total:</td>
            <td style="text-align: right;">${{ number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2) }}</td>
        </tr>
    </table>

    @if($invoice->notes)
    <div style="margin-top: 30px; background: #f8fafc; padding: 12px; border-radius: 6px;">
        <strong style="font-size: 11px; text-transform: uppercase; color: #64748b;">Notes / Payment Terms:</strong>
        <p style="margin: 4px 0 0 0; color: #475569;">{{ $invoice->notes }}</p>
    </div>
    @endif

    <div class="footer">
        Thank you for choosing TechSupport Solutions. For billing inquiries, contact billing@techsupport.com
    </div>
</body>
</html>
