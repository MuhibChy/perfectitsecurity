<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Salary;
use App\Models\User;
use App\Services\BankTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Payroll (finance-gated). Salary rows are COMPANY→PERSON liabilities;
 * actual movement happens only via BankTransferService (sandbox rail in
 * tests; external reference mandatory for completion — never fabricated).
 */
class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = Salary::with('user')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        $salaries = $query->paginate(20)->withQueryString();
        return view('admin.salaries.index', compact('salaries'));
    }

    public function create()
    {
        $employees = User::where('is_active', true)->orderBy('name')->limit(100)->get();
        return view('admin.salaries.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'period' => 'required|in:weekly,biweekly,monthly,yearly',
            'pay_date' => 'nullable|date',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string|max:1000',
        ]);
        $net = round((float) $data['base_salary'] + (float) ($data['bonus'] ?? 0) - (float) ($data['deductions'] ?? 0), 2);
        abort_if($net < 0, 422, 'Net salary cannot be negative.');
        $salary = Salary::create([
            'user_id' => $data['user_id'], 'base_salary' => $data['base_salary'],
            'bonus' => $data['bonus'] ?? 0, 'deductions' => $data['deductions'] ?? 0,
            'net_salary' => $net, 'period' => $data['period'],
            'pay_date' => $data['pay_date'] ?? now()->toDateString(),
            'effective_from' => $data['effective_from'] ?? ($data['pay_date'] ?? now()->toDateString()),
            'effective_to' => $data['effective_to'] ?? null,
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'status' => 'pending', 'notes' => $data['notes'] ?? null,
        ]);
        AuditLog::log('salary.created', 'salaries', $salary, "Salary record created for user #{$salary->user_id} (net {$net}).");
        return redirect()->route('admin.salaries.show', $salary->id)->with('success', 'Payroll record created (pending approval).');
    }

    public function show(Salary $salary)
    {
        $salary->load('user');
        $transfers = \App\Models\BankTransfer::where('purpose', 'salary')->where('related_id', $salary->id)->latest()->get();
        return view('admin.salaries.show', compact('salary', 'transfers'));
    }

    public function approve(Salary $salary)
    {
        abort_unless($salary->status === 'pending', 422, 'Only pending payroll can be approved.');
        // Versioning: approval stamps actor/time and defaults the effective
        // date; history rows are never overwritten (changes = new records).
        $salary->update([
            'status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now(),
            'effective_from' => $salary->effective_from ?? $salary->pay_date,
        ]);
        AuditLog::log('salary.approved', 'salaries', $salary, "Salary #{$salary->id} approved by " . auth()->user()->name . '.');
        return back()->with('success', 'Salary approved. Initiate a bank transfer to pay.');
    }

    /** Initiate sandbox bank transfer for an approved salary. */
    public function pay(Salary $salary, Request $request, BankTransferService $transfers)
    {
        abort_unless($salary->status === 'approved', 422, 'Only approved payroll can be paid.');
        $data = $request->validate(['destination' => 'nullable|string|max:100', 'currency' => 'nullable|string|size:3']);
        $transfer = DB::transaction(function () use ($salary, $data, $transfers) {
            $t = $transfers->request([
                'beneficiary_id' => $salary->user_id, 'purpose' => 'salary',
                'related_id' => $salary->id, 'amount' => (float) $salary->net_salary,
                'currency' => $data['currency'] ?? 'USD', 'provider' => BankTransferService::PROVIDER_SANDBOX,
                'destination' => $data['destination'] ?? null,
                'idempotency_key' => 'SAL-' . $salary->id . '-' . $salary->updated_at->timestamp,
            ], auth()->user());
            // Same-actor segregation: approval of the transfer needs a second
            // finance manager — enforced inside approve(). Auto-advance is NOT done.
            return $t;
        });
        return redirect()->route('admin.transfers.show', $transfer->id)->with('success', "Transfer {$transfer->reference} created (pending approval by a second finance manager).");
    }

    public function payslip(Salary $salary)
    {
        $salary->load('user');
        abort_unless(auth()->user()->isFinanceManager() || (int) $salary->user_id === (int) auth()->id(), 403);
        return view('admin.salaries.payslip', compact('salary'));
    }
}
