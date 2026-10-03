<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\Wallet;

/**
 * Read-only account aggregates (§5-6, §31). Salary + commission figures come
 * from the authoritative rows (salaries / commissions / payouts); project
 * earnings from the project_members terms + project-linked commissions.
 * Customer spending from invoices / payments / wallet ledger. Never writes.
 */
class AccountEarningsService
{
    public function __construct(private CommissionService $commissions) {}

    public function forEmployee(User $employee): array
    {
        $id = $employee->id;
        $salaryEarned = (float) $employee->salaries()->whereIn('status', ['approved', 'paid'])->sum('net_salary');
        $salaryPaid = (float) $employee->salaries()->where('status', 'paid')->sum('net_salary');
        $c = $this->commissions->getWorkerEarnings($id);
        $commissionPaid = (float) ($c['paid'] ?? 0);
        $commissionOutstanding = (float) (($c['pending'] ?? 0) + ($c['submitted'] ?? 0) + ($c['under_review'] ?? 0) + ($c['approved'] ?? 0) + ($c['payable'] ?? 0));
        $projectCommission = (float) Commission::where('worker_id', $id)->whereNotNull('project_id')->where('status', 'paid')->sum('commission_amount');
        $projectAgreed = (float) \DB::table('project_members')->where('user_id', $id)->sum('agreed_amount');
        $projectPaid = (float) \DB::table('project_members')->where('user_id', $id)->sum('paid_amount');
        $bonus = (float) $employee->salaries()->where('status', 'paid')->sum('bonus');
        $deductions = (float) $employee->salaries()->where('status', 'paid')->sum('deductions');

        $totalEarned = $salaryEarned + (float) ($c['total_earned'] ?? 0) + $projectAgreed;
        $totalPaid = $salaryPaid + $commissionPaid + $projectPaid;

        return [
            'salary_earned' => round($salaryEarned, 2),
            'salary_paid' => round($salaryPaid, 2),
            'commission_earned' => round((float) ($c['total_earned'] ?? 0), 2),
            'commission_paid' => round($commissionPaid, 2),
            'commission_outstanding' => round($commissionOutstanding, 2),
            'project_agreed' => round($projectAgreed, 2),
            'project_paid' => round($projectPaid, 2),
            'project_commission_paid' => round($projectCommission, 2),
            'bonuses' => round($bonus, 2),
            'deductions' => round($deductions, 2),
            'total_earned' => round($totalEarned, 2),
            'total_paid' => round($totalPaid, 2),
            'outstanding' => round(max($totalEarned - $totalPaid, 0), 2),
            'compensation' => $employee->compensation?->modelLabel() ?? 'Not configured',
        ];
    }

    public function forCustomer(User $customer): array
    {
        $id = $customer->id;
        $spent = (float) Payment::where('customer_id', $id)->where('status', 'completed')->sum('amount');
        $refunded = (float) Payment::where('customer_id', $id)->sum('refunded_amount');
        $invoiceTotal = (float) Invoice::where('customer_id', $id)->whereNotIn('status', ['cancelled'])->sum('total');
        $invoicePaid = (float) Invoice::where('customer_id', $id)->sum('amount_paid');
        $invoiceDue = (float) Invoice::where('customer_id', $id)->whereNotIn('status', ['cancelled'])->sum('amount_due');
        $walletBalance = (float) Wallet::where('user_id', $id)->where('status', 'active')->sum('balance');
        $ordersActive = $customer->serviceOrders()->whereNotIn('status', ['completed', 'cancelled'])->count();
        $ordersCompleted = $customer->serviceOrders()->where('status', 'completed')->count();

        return [
            'total_spent' => round($spent, 2),
            'total_refunded' => round($refunded, 2),
            'invoiced' => round($invoiceTotal, 2),
            'invoice_paid' => round($invoicePaid, 2),
            'outstanding' => round($invoiceDue, 2),
            'wallet_balance' => round($walletBalance, 2),
            'orders_active' => $ordersActive,
            'orders_completed' => $ordersCompleted,
        ];
    }
}
