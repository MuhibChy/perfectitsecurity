@extends('layouts.app')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Revenue (This Month)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($revenue ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Expenses (This Month)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($expenses ?? 0, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Open Tickets</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $openTickets ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Projects</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $activeProjects ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Second Row Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending Payments</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($pendingPayments ?? 0, 2) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Customers</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalCustomers ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending Tasks</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $pendingTasks ?? 0 }}</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">SLA Compliance</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $slaStats['compliance_rate'] ?? 100 }}%</p>
                </div>
                @if(($breachedTickets ?? 0) > 0)
                <span class="badge badge-danger">{{ $breachedTickets }} breached</span>
                @else
                <span class="badge badge-success">All met</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Charts + Recent Activity --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 glass-card p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Revenue Overview</h3>
            <div class="h-64" x-data="revenueChart()" x-init="init()">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="glass-card p-6">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Recent Transactions</h3>
            <div class="space-y-3">
                @forelse($recentTransactions ?? [] as $txn)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $txn->description }}</p>
                        <p class="text-xs text-gray-500">{{ $txn->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-semibold {{ $txn->type === 'income' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $txn->type === 'income' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No transactions yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Tickets --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white">Recent Tickets</h3>
            <a href="{{ route('admin.tickets.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
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
                        <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="font-mono text-primary-600 hover:underline">{{ $ticket->ticket_number }}</a></td>
                        <td class="font-medium">{{ $ticket->subject }}</td>
                        <td>{{ $ticket->customer->name ?? '-' }}</td>
                        <td>
                            @php $colors = ['low'=>'badge-info','medium'=>'badge-warning','high'=>'badge-danger','urgent'=>'badge-danger','critical'=>'badge-danger']; @endphp
                            <span class="badge {{ $colors[$ticket->priority] ?? 'badge-info' }}">{{ ucfirst($ticket->priority) }}</span>
                        </td>
                        <td><span class="badge badge-purple">{{ str_replace('_', ' ', ucfirst($ticket->status)) }}</span></td>
                        <td class="text-gray-500">{{ $ticket->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-gray-500 py-4">No tickets yet.</td></tr>
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
        init() {
            const data = @json($monthlyRevenue ?? []);
            new Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.label),
                    datasets: [{
                        label: 'Revenue',
                        data: data.map(d => d.value),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    };
}
</script>
@endpush
