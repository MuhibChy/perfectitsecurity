@extends('layouts.app')
@section('page-title', 'Payment Reconciliation')
@section('content')

    <x-page-header title="Payment Reconciliation" sys="FINANCE://FINANCE" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Payment Reconciliation</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Read-only integrity check: internal payments vs invoices vs orders. Checked {{ $checked }} recent orders; {{ $mismatched }} mismatched.</p>
    @if($order)
        <div class="mb-4 p-4 rounded border {{ $order['ok'] ? 'border-green-300' : 'border-red-300' }}">
            <div class="font-semibold">Order {{ $order['order'] }} — Total {{ $order['total'] }}, Paid {{ $order['paid'] }}, Due {{ $order['due'] }}</div>
            <div class="text-sm">Balanced (TOTAL = PAID + DUE): {{ $order['balanced'] ? 'YES' : 'NO' }}</div>
            @foreach($order['issues'] as $issue)
                <div class="text-sm text-red-600">{{ $issue }}</div>
            @endforeach
            @if(empty($order['issues']))<div class="text-sm text-green-600">No issues.</div>@endif
        </div>
    @endif
    @if(empty($mismatches))
        <p class="text-green-600 font-semibold">All checked orders reconcile. TOTAL = PAID + DUE holds everywhere.</p>
    @else
        <table class="data-table min-w-full text-sm term-table">
            <thead><tr><th class="text-left p-2">Order</th><th class="text-right p-2">Total</th><th class="text-right p-2">Paid</th><th class="text-right p-2">Due</th><th class="text-left p-2">Issues</th></tr></thead>
            <tbody>
            @foreach($mismatches as $m)
                <tr class="border-t">
                    <td class="p-2 font-mono" data-label="Order">{{ $m['order'] }}</td>
                    <td class="p-2 text-right" data-label="Total">{{ $m['total'] }}</td>
                    <td class="p-2 text-right" data-label="Paid">{{ $m['paid'] }}</td>
                    <td class="p-2 text-right" data-label="Due">{{ $m['due'] }}</td>
                    <td class="p-2 text-red-600" data-label="Issues">{{ implode('; ', $m['issues']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
