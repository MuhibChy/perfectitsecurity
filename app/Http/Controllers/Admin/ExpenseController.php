<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\FinancialService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category', 'creator', 'approver');
        if ($request->status) $query->where('status', $request->status);
        if ($request->category_id) $query->where('category_id', $request->category_id);
        $expenses = $query->latest()->paginate(20);

        $categories = ExpenseCategory::where('is_active', true)->get();
        return view('admin.expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['expense_number'] = 'EXP-' . strtoupper(\Illuminate\Support\Str::random(6));
        $validated['status'] = 'pending';

        if ($request->hasFile('receipt')) {
            // Expense receipts may contain sensitive vendor data — keep private, serve via authorized download.
            $validated['receipt_path'] = $request->file('receipt')->store('expense-receipts', 'local');
        }

        $expense = Expense::create($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded!');
    }

    public function show($id)
    {
        $expense = Expense::with('category', 'creator', 'approver')->findOrFail($id);
        return view('admin.expenses.show', compact('expense'));
    }

    public function downloadReceipt($id)
    {
        $expense = Expense::findOrFail($id);
        abort_unless($expense->receipt_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($expense->receipt_path), 404);
        // Finance-tier access is enforced by the requires.role:isFinanceManager
        // route middleware; record every download for the audit trail since
        // receipts may contain sensitive vendor data.
        \App\Services\AuditService::log('download_receipt', 'expenses', $expense, 'Expense receipt downloaded');
        return \Illuminate\Support\Facades\Storage::disk('local')->download($expense->receipt_path);
    }

    public function approve($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(FinancialService::class)->recordExpense(
            $expense->amount,
            $expense->category?->name ?? 'Expense',
            $expense->description,
            ['expense_id' => $expense->id, 'project_id' => $expense->project_id]
        );

        return redirect()->back()->with('success', 'Expense approved!');
    }

    public function reject($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return redirect()->back()->with('info', 'Expense rejected.');
    }
}
