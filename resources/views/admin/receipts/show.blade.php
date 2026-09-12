<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt — {{ $receipt->receipt_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans p-6 md:p-12">
    <div class="max-w-2xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-lg border border-gray-200">
        {{-- Header / Brand --}}
        <div class="flex items-start justify-between border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-indigo-600">TechSupport Services</h1>
                <p class="text-xs text-gray-500 mt-0.5">Enterprise IT Support, ITSM & Digital Solutions</p>
                <div class="mt-3 text-xs text-gray-600">
                    <div>support@techsupport.local</div>
                    <div>+1 (800) 555-0199</div>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                    Official Payment Receipt
                </span>
                <div class="mt-2 text-xl font-mono font-bold text-gray-900">{{ $receipt->receipt_number }}</div>
                <div class="text-xs text-gray-500 mt-1">Date: {{ $receipt->issued_at->format('M d, Y H:i:s') }}</div>
            </div>
        </div>

        {{-- Customer & Order Info --}}
        <div class="grid grid-cols-2 gap-6 py-6 border-b border-gray-200 text-xs">
            <div>
                <span class="font-bold uppercase tracking-wider text-gray-400 block mb-1">Customer Details</span>
                <div class="font-bold text-sm text-gray-900">{{ $receipt->customer->name }}</div>
                <div class="text-gray-600">Customer ID: #{{ $receipt->customer->id }}</div>
                <div class="text-gray-600">{{ $receipt->customer->email }}</div>
                <div class="text-gray-600">{{ $receipt->customer->phone ?: 'No phone' }}</div>
            </div>

            <div class="text-right">
                <span class="font-bold uppercase tracking-wider text-gray-400 block mb-1">Associated Records</span>
                <div class="text-gray-700">Service Order: <strong class="font-mono text-gray-900">{{ $receipt->order?->order_number ?? 'N/A' }}</strong></div>
                <div class="text-gray-700">Invoice Reference: <strong class="font-mono text-gray-900">{{ $receipt->invoice->invoice_number }}</strong></div>
                <div class="text-gray-700">Payment ID: <strong class="font-mono text-gray-900">#{{ $receipt->payment_id }}</strong></div>
                <div class="text-gray-700">Transaction Ref: <strong class="font-mono text-gray-900">{{ $receipt->payment?->transaction_id ?? 'N/A' }}</strong></div>
                <div class="text-gray-700">Payment Method: <strong class="capitalize text-gray-900">{{ str_replace('_', ' ', $receipt->payment?->payment_method ?? 'card') }}</strong></div>
            </div>
        </div>

        {{-- Payment Itemization --}}
        <div class="py-6 border-b border-gray-200">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 font-semibold uppercase border-b border-gray-200 pb-2">
                        <th class="py-2">Description</th>
                        <th class="py-2 text-right">Amount Received</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-3">
                            <div class="font-bold text-sm text-gray-900">{{ $receipt->order?->service?->name ?? 'IT Service Payment' }}</div>
                            <div class="text-gray-500 text-[11px]">Payment applied towards invoice {{ $receipt->invoice->invoice_number }}</div>
                        </td>
                        <td class="py-3 text-right font-bold text-base text-gray-900">
                            {{ $receipt->currency }} {{ number_format((float)$receipt->amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Financial Summary Box --}}
        <div class="pt-6 flex justify-end">
            <div class="w-64 space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Amount Received:</span>
                    <span class="font-bold text-emerald-600 text-sm">{{ $receipt->currency }} {{ number_format((float)$receipt->amount, 2) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Remaining Outstanding Balance:</span>
                    <span class="font-bold {{ (float)$receipt->remaining_balance > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                        {{ $receipt->currency }} {{ number_format((float)$receipt->remaining_balance, 2) }}
                    </span>
                </div>
                @if((float)$receipt->remaining_balance == 0)
                <div class="text-center p-2 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-xs mt-2">
                    ✓ Paid in Full
                </div>
                @else
                <div class="text-center p-2 rounded-lg bg-amber-50 text-amber-700 font-semibold text-[11px] mt-2">
                    Partial Payment (Balance Due: {{ $receipt->currency }} {{ number_format((float)$receipt->remaining_balance, 2) }})
                </div>
                @endif
            </div>
        </div>

        {{-- Footer & Print Action --}}
        <div class="mt-8 pt-6 border-t border-gray-200 text-center text-xs text-gray-400 flex items-center justify-between no-print">
            <span>Thank you for your business.</span>
            <button onclick="window.print()" class="px-4 py-2 bg-gray-900 hover:bg-black text-white font-semibold rounded-xl text-xs transition-colors shadow">
                🖨️ Print Receipt
            </button>
        </div>
    </div>
</body>
</html>
