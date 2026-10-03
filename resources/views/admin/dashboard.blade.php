@extends('layouts.app')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Admin Dashboard"
        subtitle="Operational command overview: revenue, tickets, projects, tasks and SLA health."
        sys="ADMIN://DASHBOARD"
    />

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Revenue (This Month)"
            :value="'$' . number_format($revenue ?? 0, 2)"
            subtitle="THIS MONTH"
            color="emerald"
            :href="route('admin.invoices.index')"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'
        />
        <x-stat-card
            title="Expenses (This Month)"
            :value="'$' . number_format($expenses ?? 0, 2)"
            subtitle="THIS MONTH"
            color="rose"
            :href="route('admin.expenses.index')"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
        />
        <x-stat-card
            title="Open Tickets"
            :value="$openTickets ?? 0"
            subtitle="AWAITING ACTION"
            color="amber"
            :href="route('admin.tickets.index')"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>'
        />
        <x-stat-card
            title="Active Projects"
            :value="$activeProjects ?? 0"
            subtitle="IN DELIVERY"
            color="blue"
            :href="route('admin.projects.index')"
            icon='<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
        />
    </div>

    {{-- Second Row Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Pending Payments"
            :value="'$' . number_format($pendingPayments ?? 0, 2)"
            subtitle="AWAITING SETTLEMENT"
            color="amber"
            :href="route('admin.payments.index')"
        />
        <x-stat-card
            title="Total Customers"
            :value="$totalCustomers ?? 0"
            subtitle="ALL ACCOUNTS"
            color="cyan"
            :href="route('admin.users.index')"
        />
        <x-stat-card
            title="Pending Tasks"
            :value="$pendingTasks ?? 0"
            subtitle="OPEN BACKLOG"
            color="purple"
            :href="route('admin.tasks.index')"
        />
        <a href="{{ route('admin.reports.sla') }}" class="term-panel p-5 block transition-all hover:border-accent/40">
            <div class="font-mono text-[10px] uppercase tracking-[0.22em] text-slate-500 dark:text-term-700">SLA Compliance</div>
            <div class="flex items-center justify-between gap-3 mt-1.5">
                <h3 class="text-2xl lg:text-3xl font-bold font-display text-slate-900 dark:text-white tracking-tight tabular-nums">{{ $slaStats['compliance_rate'] ?? 100 }}%</h3>
                @if(($breachedTickets ?? 0) > 0)
                <x-status-badge status="overdue" :label="$breachedTickets . ' breached'" />
                @else
                <x-status-badge status="resolved" label="All met" />
                @endif
            </div>
        </a>
    </div>

    {{-- Charts + Recent Activity --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 term-panel p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white">Revenue Overview</h3>
                <span class="term-tag term-tag-accent">LIVE FEED</span>
            </div>
            <div class="h-64" x-data="revenueChart()" x-init="init()">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="term-panel p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Recent Transactions</h3>
            <div class="space-y-3">
                @forelse($recentTransactions ?? [] as $txn)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-white/5 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $txn->description }}</p>
                        <p class="text-xs text-slate-600 dark:text-term-800">{{ $txn->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-semibold tabular-nums {{ $txn->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $txn->type === 'income' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-slate-600 dark:text-term-800 text-center py-4">No transactions yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Tickets --}}
    <div class="term-panel p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900 dark:text-white">Recent Tickets</h3>
            <a href="{{ route('admin.tickets.index') }}" class="term-btn term-btn-sm term-btn-ghost">View All</a>
        </div>
        <div class="term-table-wrap">
            <table class="data-table term-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Customer</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets ?? [] as $ticket)
                    <tr>
                        <td data-label="ID"><a href="{{ route('admin.tickets.show', $ticket) }}" class="font-mono mono-id text-primary-600 hover:underline">{{ $ticket->ticket_number }}</a></td>
                        <td class="font-medium" data-label="Subject">{{ $ticket->subject }}</td>
                        <td data-label="Customer">{{ $ticket->customer->name ?? '-' }}</td>
                        <td data-label="Priority"><x-status-badge :status="$ticket->priority" /></td>
                        <td data-label="Status"><x-status-badge :status="$ticket->status" /></td>
                        <td class="text-slate-600 dark:text-term-800" data-label="Created">{{ $ticket->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-slate-600 dark:text-term-800 py-4">No tickets yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.js"></script>
<script>
function revenueChart() {
    return {
        chart: null,
        init() {
            const data = @json($monthlyRevenue ?? []);
            const themeColors = () => {
                const dark = document.documentElement.classList.contains('dark');
                return {
                    tick: dark ? '#9db0a4' : '#475569',
                    grid: dark ? 'rgba(255,255,255,0.07)' : 'rgba(15,23,42,0.08)',
                    bar: dark ? 'rgba(0, 230, 122, 0.75)' : 'rgba(5, 150, 105, 0.8)',
                };
            };
            const applyTheme = () => {
                if (!this.chart) return;
                const c = themeColors();
                this.chart.data.datasets[0].backgroundColor = c.bar;
                this.chart.options.scales.y.ticks.color = c.tick;
                this.chart.options.scales.y.grid.color = c.grid;
                this.chart.options.scales.x.ticks.color = c.tick;
                this.chart.update();
            };
            const c = themeColors();
            this.chart = new Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.label),
                    datasets: [{
                        label: 'Revenue',
                        data: data.map(d => d.value),
                        backgroundColor: c.bar,
                        borderRadius: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { color: c.tick }, grid: { color: c.grid } },
                        x: { ticks: { color: c.tick }, grid: { display: false } }
                    }
                }
            });
            window.addEventListener('theme-changed', applyTheme);
        }
    };
}
</script>
@endpush
