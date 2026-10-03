@extends('layouts.app')
@section('page-title', $report['title'])
@section('content')
<div class="space-y-6">
    <x-page-header title="{{ $report['title'] }}" subtitle="Full lifecycle: request → quote → order → tasks → charges → payments → completion." sys="REPORT://SERVICE" />
    <div class="flex flex-wrap gap-2 no-print">
        @if(auth()->user()->isStaff())
        <a href="{{ route('admin.reports.export', ['type' => 'service', 'order_id' => $order->id, 'format' => 'pdf']) }}" class="term-btn term-btn-sm">Download PDF</a>
        <a href="{{ route('admin.reports.export', ['type' => 'service', 'order_id' => $order->id, 'format' => 'csv']) }}" class="term-btn term-btn-ghost term-btn-sm">Export CSV</a>
        @else
        <a href="{{ route('portal.reports.mine', ['format' => 'pdf']) }}" class="term-btn term-btn-sm">Download My PDF</a>
        <a href="{{ route('portal.orders.pdf', $order->id) }}" class="term-btn term-btn-ghost term-btn-sm">Order PDF</a>
        @endif
        <button onclick="window.print()" class="term-btn term-btn-ghost term-btn-sm">Print Report</button>
    </div>

    <div class="term-panel p-6 print-panel">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Service Detail</h2>
        <div class="grid sm:grid-cols-2 gap-x-8 gap-y-1 mt-3 text-sm">
            <p><span class="term-hint">Customer:</span> {{ $order->customer->name ?? '—' }} ({{ $order->customer->email ?? '—' }}, {{ $order->customer->phone ?? '—' }})</p>
            <p><span class="term-hint">Service:</span> {{ $order->service->name ?? '—' }}</p>
            <p><span class="term-hint">Order:</span> <span class="font-mono">{{ $order->order_number }}</span> · {{ $order->created_at->format('Y-m-d') }}</p>
            <p><span class="term-hint">Status:</span> {{ $order->status }} · Financial: {{ $order->payment_status ?? $order->payment_authorization }}</p>
            <p><span class="term-hint">Technician:</span> {{ $order->assignee->name ?? $order->tasks->first()?->assignee->name ?? 'Unassigned' }}</p>
            <p><span class="term-hint">Started:</span> {{ $order->tasks->min('start_date')?->format('Y-m-d') ?? '—' }} · <span class="term-hint">Completed:</span> {{ $order->task_completed_at?->format('Y-m-d') ?? $order->closed_at?->format('Y-m-d') ?? '—' }}</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
            <div><p class="term-hint text-xs">TOTAL</p><p class="text-lg font-bold tabular-nums">{{ $order->currency }} {{ number_format($order->total, 2) }}</p></div>
            <div><p class="term-hint text-xs">PAID</p><p class="text-lg font-bold tabular-nums">{{ $order->currency }} {{ number_format($order->amount_paid, 2) }}</p></div>
            <div><p class="term-hint text-xs">OUTSTANDING</p><p class="text-lg font-bold tabular-nums">{{ $order->currency }} {{ number_format($order->amount_due, 2) }}</p></div>
            <div><p class="term-hint text-xs">RECONCILED</p><p class="text-lg font-bold">{{ $report['summary']['Reconciled (TOTAL=PAID+DUE)'] ?? '—' }}</p></div>
        </div>
    </div>

    <div class="term-panel p-6 print-panel">
        <h3 class="font-bold text-slate-900 dark:text-white mb-3">Timeline</h3>
        <x-timeline :timeline="$timeline" />
        @if($statusHistory->isNotEmpty())
        <h4 class="font-bold text-sm mt-4 mb-2">Status audit</h4>
        <ul class="text-sm space-y-1">
            @foreach($statusHistory as $h)
            <li class="font-mono text-xs">{{ $h->created_at->format('Y-m-d H:i') }} · {{ $h->description ?? $h->action }} · by {{ $h->user->name ?? 'system' }}</li>
            @endforeach
        </ul>
        @endif
    </div>

    <div class="term-panel p-6 print-panel">
        <h3 class="font-bold text-slate-900 dark:text-white mb-3">Tasks & Payments ({{ count($report['rows']) }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left font-mono text-[11px] uppercase text-slate-500">@foreach($report['columns'] as $c)<th class="py-2 pr-3">{{ $c }}</th>@endforeach</tr></thead>
                <tbody>
                    @forelse($report['rows'] as $row)<tr class="border-t border-slate-200 dark:border-white/10">@foreach($row as $cell)<td class="py-2 pr-3">{{ $cell }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($report['columns']) }}" class="py-4 term-hint">No task/payment rows yet.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
