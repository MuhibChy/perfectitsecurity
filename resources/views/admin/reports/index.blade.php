@extends('layouts.app')
@section('page-title', 'Reports')
@section('content')

<x-page-header title="Reports" subtitle="Traceable, filterable and printable reports across the full service lifecycle." sys="REPORT://CENTER" />
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="term-panel p-5">
            <h3 class="font-bold text-slate-900 dark:text-white">Financial</h3>
            <p class="term-hint mt-1">Revenue, expenses, collections for a period.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.financial') }}" class="term-btn term-btn-sm">Open</a>
                <a href="{{ route('admin.reports.export', ['type' => 'financial', 'format' => 'pdf']) }}" class="term-btn term-btn-ghost term-btn-sm">PDF</a>
                <a href="{{ route('admin.reports.export', ['type' => 'financial', 'format' => 'csv']) }}" class="term-btn term-btn-ghost term-btn-sm">CSV</a>
            </div>
        </div>
        <div class="term-panel p-5">
            <h3 class="font-bold text-slate-900 dark:text-white">Tickets</h3>
            <p class="term-hint mt-1">Volume, status mix and resolution time.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.tickets') }}" class="term-btn term-btn-sm">Open</a>
                <a href="{{ route('admin.reports.tickets.export', request()->query()) }}" class="term-btn term-btn-ghost term-btn-sm">CSV</a>
            </div>
        </div>
        <div class="term-panel p-5">
            <h3 class="font-bold text-slate-900 dark:text-white">Employees</h3>
            <p class="term-hint mt-1">Workload and service throughput per staff member.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.employees') }}" class="term-btn term-btn-sm">Open</a>
            </div>
        </div>
        <div class="term-panel p-5">
            <h3 class="font-bold text-slate-900 dark:text-white">SLA Compliance</h3>
            <p class="term-hint mt-1">Breaches and compliance rate.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.sla') }}" class="term-btn term-btn-sm">Open</a>
            </div>
        </div>
        <div class="term-panel p-5">
            <h3 class="font-bold text-slate-900 dark:text-white">Profitability</h3>
            <p class="term-hint mt-1">Project and order margins by service.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.profitability') }}" class="term-btn term-btn-sm">Open</a>
            </div>
        </div>
        <div class="term-panel p-5">
            <h3 class="font-bold text-slate-900 dark:text-white">Service Detail</h3>
            <p class="term-hint mt-1">Full lifecycle of one order: timeline, charges, payments.</p>
            <form method="GET" action="{{ route('admin.reports.service.form') }}" class="mt-3 flex gap-2">
                <input name="order_number" maxlength="50" placeholder="ORD-…" aria-label="Order number" class="term-input !w-40" required>
                <button class="term-btn term-btn-sm">Open</button>
            </form>
        </div>
    </div>

    <div class="term-panel p-5">
        <h3 class="font-bold text-slate-900 dark:text-white">Filtered Exports (PDF / CSV / XLSX)</h3>
        <p class="term-hint mt-1">Date presets apply to every export. Screen, CSV and PDF always agree — same builder.</p>
        <form method="GET" action="{{ route('admin.reports.export', ['type' => 'financial']) }}" class="mt-3 flex flex-wrap items-end gap-2" id="export-form">
            <div>
                <label class="term-field-label">Report</label>
                <select name="_type" id="export-type" class="term-input">
                    @foreach(['financial' => 'Financial', 'customer' => 'Customer 360°', 'service' => 'Service detail', 'payment' => 'Payments', 'task' => 'Tasks', 'employee-service' => 'Employee service', 'commission' => 'Commissions', 'franchise' => 'Franchise'] as $v => $l)
                    <option value="{{ $v }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="term-field-label">Preset</label>
                <select id="export-preset" class="term-input">
                    <option value="">Custom</option>
                    <option value="today">Today</option>
                    <option value="week">This week</option>
                    <option value="month">This month</option>
                    <option value="last-month">Last month</option>
                    <option value="year">This year</option>
                </select>
            </div>
            <div>
                <label class="term-field-label">From</label>
                <input type="date" name="date_from" id="export-from" class="term-input">
            </div>
            <div>
                <label class="term-field-label">To</label>
                <input type="date" name="date_to" id="export-to" class="term-input">
            </div>
            <div>
                <label class="term-field-label">Format</label>
                <select name="format" class="term-input">
                    <option value="pdf">PDF</option>
                    <option value="csv">CSV</option>
                    <option value="xlsx">XLSX</option>
                </select>
            </div>
            <button class="term-btn term-btn-sm">Generate Report</button>
        </form>
        <script>
        (function () {
            var form = document.getElementById('export-form');
            var type = document.getElementById('export-type');
            var preset = document.getElementById('export-preset');
            var from = document.getElementById('export-from');
            var to = document.getElementById('export-to');
            function dates(p) {
                var now = new Date(), fns = function (d) { return d.toISOString().slice(0, 10); };
                var first, last;
                if (p === 'today') { first = last = now; }
                else if (p === 'week') { first = new Date(now); first.setDate(now.getDate() - now.getDay()); last = new Date(first); last.setDate(first.getDate() + 6); }
                else if (p === 'month') { first = new Date(now.getFullYear(), now.getMonth(), 1); last = new Date(now.getFullYear(), now.getMonth() + 1, 0); }
                else if (p === 'last-month') { first = new Date(now.getFullYear(), now.getMonth() - 1, 1); last = new Date(now.getFullYear(), now.getMonth(), 0); }
                else if (p === 'year') { first = new Date(now.getFullYear(), 0, 1); last = new Date(now.getFullYear(), 11, 31); }
                else return;
                from.value = fns(first); to.value = fns(last);
            }
            preset.addEventListener('change', function () { dates(preset.value); });
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var url = '{{ url('/admin/reports/export') }}/' + encodeURIComponent(type.value) + '?' + new URLSearchParams(new FormData(form)).toString();
                window.location.href = url;
            });
        })();
        </script>
    </div>

    <div class="term-panel p-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.history.audit') }}" class="term-btn term-btn-ghost term-btn-sm">Audit Trail</a>
        <a href="{{ route('admin.history.consistency') }}" class="term-btn term-btn-ghost term-btn-sm">Consistency Check</a>
        <button onclick="window.print()" class="term-btn term-btn-ghost term-btn-sm no-print">Print Report</button>
    </div>
</div>
@endsection
