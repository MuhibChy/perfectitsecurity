<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\User;
use App\Models\Commission;
use App\Services\FinancialService;
use App\Services\SlaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function exportFinancial(Request $request)
    {
        $fs = app(FinancialService::class);
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();

        $transactions = \App\Models\FinancialTransaction::whereBetween('created_at', [$from, $to])
            ->with('creator')
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="financial-report-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, ['Date', 'Type', 'Category', 'Description', 'Amount', 'Status', 'Created By']);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->created_at->format('Y-m-d H:i'),
                    ucfirst($transaction->type),
                    $transaction->category,
                    $transaction->description,
                    number_format($transaction->amount, 2),
                    ucfirst($transaction->status),
                    $transaction->creator->name ?? 'System',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportTickets(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();

        $tickets = Ticket::with('customer', 'assignee', 'category')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tickets-report-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($tickets) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Ticket #', 'Subject', 'Customer', 'Category', 'Priority', 'Status', 'Assigned To', 'Created', 'Resolved', 'Resolution Time (hrs)']);

            foreach ($tickets as $ticket) {
                $resolutionTime = null;
                if ($ticket->resolved_at && $ticket->created_at) {
                    $resolutionTime = round($ticket->created_at->diffInHours($ticket->resolved_at), 1);
                }

                fputcsv($handle, [
                    $ticket->ticket_number,
                    $ticket->subject,
                    $ticket->customer->name ?? 'Unknown',
                    $ticket->category->name ?? 'Uncategorized',
                    ucfirst($ticket->priority),
                    ucfirst(str_replace('_', ' ', $ticket->status)),
                    $ticket->assignee->name ?? 'Unassigned',
                    $ticket->created_at->format('Y-m-d H:i'),
                    $ticket->resolved_at ? $ticket->resolved_at->format('Y-m-d H:i') : '',
                    $resolutionTime,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function financial(Request $request)
    {
        $fs = app(FinancialService::class);
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();

        $data = $fs->getProfitAndLoss($from, $to);
        $data['revenueByCategory'] = \App\Models\FinancialTransaction::income()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->get();

        return view('admin.reports.financial', $data);
    }

    public function tickets(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();

        $resolvedTickets = Ticket::whereNotNull('resolved_at')->whereBetween('created_at', [$from, $to])->get();
        $avgHours = $resolvedTickets->count() > 0 
            ? round($resolvedTickets->avg(function ($t) {
                return $t->created_at && $t->resolved_at ? $t->created_at->diffInHours($t->resolved_at) : 0;
            }), 1)
            : 0;

        $data = [
            'totalTickets' => Ticket::whereBetween('created_at', [$from, $to])->count(),
            'byStatus' => Ticket::whereBetween('created_at', [$from, $to])->selectRaw('status, count(*) as count')->groupBy('status')->get(),
            'byPriority' => Ticket::whereBetween('created_at', [$from, $to])->selectRaw('priority, count(*) as count')->groupBy('priority')->get(),
            'avgResolutionTime' => (object)['avg_hours' => $avgHours],
        ];

        return view('admin.reports.tickets', $data);
    }

    public function employees(Request $request)
    {
        $employees = User::staff()->with('tasks', 'salary')->get();
        return view('admin.reports.employees', compact('employees'));
    }

    public function sla()
    {
        $slaService = app(SlaService::class);
        $data = $slaService->getSlaComplianceStats();
        $data['breached'] = $slaService->getBreachedTickets()->load('customer', 'assignee');
        return view('admin.reports.sla', $data);
    }

    public function profitability(Request $request)
    {
        $projects = Project::with(['invoices' => function ($q) { $q->where('status', 'paid'); }, 'tasks' => function ($q) { $q->with('commissions'); }])->get();

        $projectProfitability = $projects->map(function ($project) {
            $revenue = $project->invoices->sum('total');
            $costs = $project->actual_cost;
            $commissions = $project->tasks->sum('commissions.commission_amount');
            return [
                'project' => $project,
                'revenue' => $revenue,
                'costs' => $costs + $commissions,
                'profit' => $revenue - ($costs + $commissions),
                'margin' => $revenue > 0 ? round((($revenue - ($costs + $commissions)) / $revenue) * 100, 1) : 0,
            ];
        });

        // Service Orders Profitability
        $orders = \App\Models\ServiceOrder::with(['customer', 'service.category', 'assignee', 'expenses'])->get();
        $orderProfitability = $orders->map(function ($order) {
            $revenue = (float) $order->amount_paid;
            $cost = (float) $order->total_cost;
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;
            return [
                'order' => $order,
                'order_value' => (float) $order->total,
                'revenue' => $revenue,
                'outstanding' => (float) $order->amount_due,
                'costs' => $cost,
                'profit' => $profit,
                'margin' => $margin,
            ];
        });

        // Profitability By Service
        $byService = $orders->groupBy('service_id')->map(function ($items) {
            $service = $items->first()->service;
            $revenue = $items->sum('amount_paid');
            $costs = $items->sum('total_cost');
            $profit = $revenue - $costs;
            return [
                'service_name' => $service?->name ?? 'Unknown',
                'category_name' => $service?->category?->name ?? 'General',
                'order_count' => $items->count(),
                'revenue' => $revenue,
                'costs' => $costs,
                'profit' => $profit,
                'margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0,
            ];
        });

        $profitability = $projectProfitability;

        return view('admin.reports.profitability', compact('profitability', 'projectProfitability', 'orderProfitability', 'byService'));
    }
}
