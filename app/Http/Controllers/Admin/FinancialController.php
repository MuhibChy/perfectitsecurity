<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialController extends Controller
{
    public function index()
    {
        $fs = app(FinancialService::class);
        $data = [
            'revenue' => $fs->getRevenue(),
            'expenses' => $fs->getExpenses(),
            'commissionCosts' => $fs->getCommissionExpenses(),
            'employeeCosts' => $fs->getEmployeeCosts(),
            'pendingPayments' => $fs->getPendingPayments(),
            'outstandingInvoices' => $fs->getOutstandingInvoices(),
            'monthlyRecurring' => $fs->getMonthlyRecurringRevenue(),
            'recentTransactions' => FinancialTransaction::with('creator')->latest()->limit(20)->get(),
        ];

        return view('admin.financials.index', $data);
    }

    public function transactions(Request $request)
    {
        $query = FinancialTransaction::with('creator');
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to.' 23:59:59');
        }
        $transactions = $query->latest()->paginate(30);

        return view('admin.financials.transactions', compact('transactions'));
    }

    public function profitLoss(Request $request)
    {
        $fs = app(FinancialService::class);
        $from = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to) : now()->endOfMonth();

        $data = $fs->getProfitAndLoss($from, $to);

        return view('admin.financials.profit-loss', $data);
    }

    /**
     * Payment reconciliation screen (Phase 17): compares internal payment /
     * invoice / order / ledger records. Read-only — never mutates money.
     */
    public function reconciliation(Request $request)
    {
        $svc = app(\App\Services\PaymentReconciliationService::class);
        $result = $svc->sweep((int) ($request->get('limit', 200)));
        $order = null;
        if ($request->filled('order_id') && ($found = \App\Models\ServiceOrder::find($request->get('order_id')))) {
            $order = $svc->reconcileOrder($found);
        }

        return view('admin.financials.reconciliation', array_merge($result, ['order' => $order]));
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|max:30',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        if ($request->filled('date_from') && $request->filled('date_to')) {
            abort_if(Carbon::parse($request->date_from)->diffInDays(Carbon::parse($request->date_to)) > 366, 422, 'Export date range must not exceed 366 days.');
        }
        $query = FinancialTransaction::with('creator');
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->date_from) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('created_at', '<=', $request->date_to.' 23:59:59');
        }

        $transactions = $query->latest()->limit(5000)->get();

        $headers = ['Content-Type' => 'text/csv'];
        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Transaction ID', 'Type', 'Category', 'Description', 'Amount', 'Status', 'Created At']);
            foreach ($transactions as $txn) {
                fputcsv($file, [
                    ReportExportController::csvCell($txn->transaction_id), ReportExportController::csvCell($txn->type), ReportExportController::csvCell($txn->category), ReportExportController::csvCell(mb_substr((string) $txn->description, 0, 200)),
                    $txn->amount, $txn->status, $txn->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        \App\Models\AuditLog::log('financial.exported', 'financial_transactions', null, 'Financial transactions exported as CSV ('.$transactions->count().' rows) by '.auth()->user()->name.'.');

        return response()->stream($callback, 200, $headers);
    }
}
