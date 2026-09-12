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
        if ($request->type) $query->where('type', $request->type);
        if ($request->date_from) $query->where('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
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

    public function export(Request $request)
    {
        $query = FinancialTransaction::with('creator');
        if ($request->type) $query->where('type', $request->type);
        if ($request->date_from) $query->where('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $transactions = $query->get();

        $headers = ['Content-Type' => 'text/csv'];
        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Transaction ID', 'Type', 'Category', 'Description', 'Amount', 'Status', 'Created At']);
            foreach ($transactions as $txn) {
                fputcsv($file, [
                    $txn->transaction_id, $txn->type, $txn->category, $txn->description,
                    $txn->amount, $txn->status, $txn->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
