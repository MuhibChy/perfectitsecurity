<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Commission;
use App\Models\Expense;
use App\Models\Task;
use App\Services\FinancialService;
use App\Services\SlaService;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $fs = app(FinancialService::class);
        $slaService = app(SlaService::class);

        $data = [
            // Financial
            'revenue' => $fs->getMonthlyRevenue(),
            'expenses' => $fs->getExpenses(),
            'profit' => 0, // Calculated after data array is built
            'pendingPayments' => $fs->getPendingPayments(),
            'outstandingInvoices' => $fs->getOutstandingInvoices(),

            // Operations
            'openTickets' => Ticket::open()->count(),
            'activeProjects' => Project::active()->count(),
            'totalCustomers' => User::customers()->count(),
            'pendingCommissions' => Commission::where('status', 'pending')->sum('commission_amount'),

            // SLA
            'slaStats' => $slaService->getSlaComplianceStats(),
            'breachedTickets' => \App\Models\Ticket::where('status', '!=', 'closed')
                ->where('sla_resolution_deadline', '<', now())
                ->whereNull('resolved_at')
                ->count(),

            // Tasks
            'pendingTasks' => Task::where('status', 'pending')->count(),
            'inProgressTasks' => Task::where('status', 'in_progress')->count(),

            // Recent
            'recentTickets' => Ticket::with('customer', 'assignee')->latest()->limit(5)->get(),
            'recentTransactions' => \App\Models\FinancialTransaction::with('creator')->latest()->limit(10)->get(),

            // Revenue chart data
            'monthlyRevenue' => $this->getMonthlyRevenueChart(),
            'monthlyExpenses' => $this->getMonthlyExpenseChart(),
        ];

        $data['profit'] = ($data['revenue'] ?? 0) - ($data['expenses'] ?? 0);

        return view('admin.dashboard', $data);
    }

    private function monthlySums(string $scope): array
    {
        $driver = config('database.default');
        $expr = $driver === 'mysql'
            ? "DATE_FORMAT(created_at, '%Y-%m') as ym"
            : "strftime('%Y-%m', created_at) as ym";
        return \App\Models\FinancialTransaction::{$scope}()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("{$expr}, SUM(amount) as total")
            ->groupBy('ym')->pluck('total', 'ym')->all();
    }

    private function getMonthlyRevenueChart()
    {
        // Two grouped queries (cached 10 min) instead of 24 per-load aggregates.
        return \Illuminate\Support\Facades\Cache::remember('dashboard:monthly-revenue', 600, function () {
            $sums = $this->monthlySums('income');
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $data[] = ['label' => $month->format('M Y'), 'value' => (float) ($sums[$month->format('Y-m')] ?? 0)];
            }
            return $data;
        });
    }

    private function getMonthlyExpenseChart()
    {
        return \Illuminate\Support\Facades\Cache::remember('dashboard:monthly-expenses', 600, function () {
            $sums = $this->monthlySums('expenses');
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $data[] = ['label' => $month->format('M Y'), 'value' => (float) ($sums[$month->format('Y-m')] ?? 0)];
            }
            return $data;
        });
    }
}
