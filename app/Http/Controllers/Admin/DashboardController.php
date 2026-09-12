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
            'breachedTickets' => $slaService->getBreachedTickets()->count(),

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

    private function getMonthlyRevenueChart()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data[] = [
                'label' => $month->format('M Y'),
                'value' => \App\Models\FinancialTransaction::income()
                    ->where('status', 'completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount'),
            ];
        }
        return $data;
    }

    private function getMonthlyExpenseChart()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $data[] = [
                'label' => $month->format('M Y'),
                'value' => \App\Models\FinancialTransaction::expenses()
                    ->where('status', 'completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount'),
            ];
        }
        return $data;
    }
}
