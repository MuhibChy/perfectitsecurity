<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0891b2; padding-bottom: 20px; margin-bottom: 25px; }
        .logo { font-size: 24px; font-weight: bold; color: #0f172a; }
        .quote-title { font-size: 20px; font-weight: bold; color: #0891b2; text-align: right; }
        .meta-table { width: 100%; margin-bottom: 25px; }
        .meta-table td { vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 10px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; }
        .table td { border-bottom: 1px solid #e2e8f0; padding: 10px; }
        .totals { width: 45%; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 6px 10px; }
        .totals .grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid #0f172a; color: #0f172a; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .badge-accepted { background: #dcfce7; color: #166534; }
        .badge-draft { background: #f1f5f9; color: #475569; }
        .badge-sent { background: #e0f2fe; color: #0369a1; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-converted { background: #f3e8ff; color: #6b21a8; }
        .notes-box { margin-top: 25px; background: #f8fafc; border-left: 4px solid #0891b2; padding: 12px 16px; border-radius: 4px; }
        .acceptance-box { margin-top: 35px; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 16px; }
        .footer { margin-top: 45px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    @php
        $symbol = $quotation->currency_symbol ?? '$';
    @endphp

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="logo">TechSupport Solutions</div>
                    <div style="color: #64748b; margin-top: 4px;">Enterprise IT Infrastructure & Cybersecurity</div>
                    <div style="color: #94a3b8; font-size: 11px; margin-top: 2px;">Global Delivery: London &bull; New York &bull; Dhaka</div>
                </td>
                <td style="text-align: right;">
                    <div class="quote-title">SERVICE QUOTATION</div>
                    <div style="font-weight: 600; color: #475569; margin-top: 2px;">#{{ $quotation->quotation_number }}</div>
                    <div style="margin-top: 4px;">Status: 
                        <span class="badge badge-{{ $quotation->status ?? 'draft' }}">
                            {{ strtoupper($quotation->status ?? 'DRAFT') }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Prepared For:</strong><br>
                <strong style="font-size: 14px; color: #0f172a;">{{ $quotation->customer->name ?? 'Valued Customer' }}</strong><br>
                @if(!empty($quotation->customer->company_name))
                    <span style="color: #475569;">{{ $quotation->customer->company_name }}</span><br>
                @elseif(!empty($quotation->company->name))
                    <span style="color: #475569;">{{ $quotation->company->name }}</span><br>
                @endif
                <span style="color: #64748b;">{{ $quotation->customer->email ?? '' }}</span><br>
                <span style="color: #64748b;">{{ $quotation->customer->phone ?? '' }}</span>
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Quotation Date:</strong> {{ $quotation->created_at ? $quotation->created_at->format('M d, Y') : now()->format('M d, Y') }}<br>
                <strong>Valid Until:</strong> 
                @if($quotation->valid_until)
                    <span style="color: #b91c1c; font-weight: 600;">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</span>
                @else
                    <span style="color: #64748b;">30 Days from Issue</span>
                @endif
                <br>
                @if($quotation->sent_at)
                    <strong>Sent At:</strong> {{ \Carbon\Carbon::parse($quotation->sent_at)->format('M d, Y') }}<br>
                @endif
                @if($quotation->accepted_at)
                    <strong>Accepted At:</strong> {{ \Carbon\Carbon::parse($quotation->accepted_at)->format('M d, Y') }}<br>
                @endif
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%;">Service / Scope Description</th>
                <th style="text-align: center; width: 12%;">Qty</th>
                <th style="text-align: right; width: 18%;">Unit Price</th>
                <th style="text-align: right; width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotation->items ?? [] as $item)
            <tr>
                <td>
                    <strong>{{ $item->description }}</strong>
                </td>
                <td style="text-align: center;">{{ $item->quantity ?? 1 }}</td>
                <td style="text-align: right;">{{ $symbol }}{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right;">
                    @php
                        $lineTotal = ($item->quantity * $item->unit_price) - ($item->discount ?? 0);
                    @endphp
                    {{ $symbol }}{{ number_format($lineTotal, 2) }}
                    @if($item->discount > 0)
                        <div style="font-size: 10px; color: #16a34a;">-{{ $symbol }}{{ number_format($item->discount, 2) }} disc.</div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">
                    No line items specified for this quotation.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal:</td>
            <td style="text-align: right;">{{ $symbol }}{{ number_format($quotation->subtotal ?? 0, 2) }}</td>
        </tr>
        @if($quotation->discount_amount > 0)
        <tr>
            <td style="color: #16a34a;">Discount:</td>
            <td style="text-align: right; color: #16a34a;">-{{ $symbol }}{{ number_format($quotation->discount_amount, 2) }}</td>
        </tr>
        @endif
        @if($quotation->tax_rate > 0 || $quotation->tax_amount > 0)
        <tr>
            <td>Tax ({{ number_format($quotation->tax_rate ?? 0, 1) }}%):</td>
            <td style="text-align: right;">{{ $symbol }}{{ number_format($quotation->tax_amount ?? 0, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Estimated Total:</td>
            <td style="text-align: right; color: #0891b2;">{{ $symbol }}{{ number_format($quotation->total ?? 0, 2) }}</td>
        </tr>
    </table>

    @if($quotation->terms || $quotation->notes)
    <div class="notes-box">
        @if($quotation->terms)
            <strong style="font-size: 11px; text-transform: uppercase; color: #0891b2;">Terms & Conditions:</strong>
            <p style="margin: 4px 0 8px 0; color: #475569;">{{ $quotation->terms }}</p>
        @endif
        @if($quotation->notes)
            <strong style="font-size: 11px; text-transform: uppercase; color: #64748b;">Special Notes & Scope:</strong>
            <p style="margin: 4px 0 0 0; color: #475569;">{{ $quotation->notes }}</p>
        @endif
    </div>
    @endif

    <div class="acceptance-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <strong style="color: #0f172a; font-size: 12px;">Quotation Acceptance:</strong>
                    <p style="color: #64748b; font-size: 11px; margin: 4px 0 0 0;">
                        To accept this quotation, please approve it directly within your Customer Portal at {{ url('/portal/quotations') }} or sign below and return via email.
                    </p>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom;">
                    <div style="border-bottom: 1px solid #94a3b8; height: 35px; width: 180px; margin-left: auto;"></div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Authorized Signature & Date</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        TechSupport Solutions &bull; Enterprise IT Infrastructure, Cybersecurity &amp; Managed Services &bull; billing@techsupport.com
    </div>
</body>
</html>
