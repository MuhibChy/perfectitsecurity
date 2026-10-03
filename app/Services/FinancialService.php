<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Salary;
use Carbon\Carbon;

class FinancialService
{
    public function getRevenue(string $period = 'monthly', Carbon $from = null, Carbon $to = null): float
    {
        $query = FinancialTransaction::income()->where('status', 'completed');
        $refundQuery = FinancialTransaction::where('type', 'refund')->where('status', 'completed');
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
            $refundQuery->whereBetween('created_at', [$from, $to]);
        } else {
            $query->where('created_at', '>=', $this->getPeriodStart($period));
            $refundQuery->where('created_at', '>=', $this->getPeriodStart($period));
        }
        // Net revenue: completed income minus completed refunds.
        return round((float) $query->sum('amount') - (float) $refundQuery->sum('amount'), 2);
    }

    public function getExpenses(string $period = 'monthly', Carbon $from = null, Carbon $to = null): float
    {
        $query = FinancialTransaction::expenses()->where('status', 'completed');
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        } else {
            $query->where('created_at', '>=', $this->getPeriodStart($period));
        }

        return (float) $query->sum('amount');
    }

    public function getCommissionExpenses(Carbon $from = null, Carbon $to = null): float
    {
        $query = Commission::whereIn('status', ['approved', 'payable', 'paid']);
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return (float) $query->sum('commission_amount');
    }

    public function getEmployeeCosts(Carbon $from = null, Carbon $to = null): float
    {
        $query = Salary::where('status', 'paid');
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return (float) $query->sum('net_salary');
    }

    public function getProfitAndLoss(Carbon $from = null, Carbon $to = null): array
    {
        if (! $from) {
            $from = Carbon::now()->startOfMonth();
        }
        if (! $to) {
            $to = Carbon::now()->endOfMonth();
        }

        $revenue = $this->getRevenue('custom', $from, $to);
        $expenses = $this->getExpenses('custom', $from, $to);
        $commissionCosts = $this->getCommissionExpenses($from, $to);
        $employeeCosts = $this->getEmployeeCosts($from, $to);

        $totalExpenses = $expenses + $commissionCosts + $employeeCosts;
        $grossProfit = round($revenue - $totalExpenses, 2);
        $netProfit = round($revenue - $totalExpenses, 2);
        $netLoss = $netProfit < 0 ? abs($netProfit) : 0;

        return [
            'revenue' => $revenue,
            'direct_service_costs' => $expenses,
            'employee_costs' => $employeeCosts,
            'freelancer_costs' => $commissionCosts,
            'commission_costs' => $commissionCosts,
            'operating_expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'gross_profit' => round($revenue - $totalExpenses, 2),
            'net_profit' => $netProfit >= 0 ? $netProfit : 0,
            'net_loss' => $netLoss,
            'period_from' => $from->format('Y-m-d'),
            'period_to' => $to->format('Y-m-d'),
            // Multi-currency: per-code totals. Different currencies are NEVER
            // summed together; convert explicitly via CurrencyService instead.
            'revenue_by_currency' => $this->getRevenueByCurrency($from, $to),
            'expenses_by_currency' => $this->getExpensesByCurrency($from, $to),
        ];
    }

    /**
     * Net revenue grouped by transaction currency (income minus refunds).
     *
     * @return array<string, float>
     */
    public function getRevenueByCurrency(Carbon $from = null, Carbon $to = null): array
    {
        if (! $from) {
            $from = Carbon::now()->startOfMonth();
        }
        if (! $to) {
            $to = Carbon::now()->endOfMonth();
        }
        $income = FinancialTransaction::income()->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('currency, SUM(amount) as total')->groupBy('currency')
            ->pluck('total', 'currency')->all();
        $refunds = FinancialTransaction::where('type', 'refund')->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('currency, SUM(amount) as total')->groupBy('currency')
            ->pluck('total', 'currency')->all();
        $out = [];
        foreach (array_unique(array_merge(array_keys($income), array_keys($refunds))) as $code) {
            $out[strtoupper($code)] = round((float) ($income[$code] ?? 0) - (float) ($refunds[$code] ?? 0), 2);
        }

        return $out;
    }

    /**
     * @return array<string, float>
     */
    public function getExpensesByCurrency(Carbon $from = null, Carbon $to = null): array
    {
        if (! $from) {
            $from = Carbon::now()->startOfMonth();
        }
        if (! $to) {
            $to = Carbon::now()->endOfMonth();
        }

        return FinancialTransaction::expenses()->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('currency, SUM(amount) as total')->groupBy('currency')
            ->pluck('total', 'currency')
            ->mapWithKeys(fn ($v, $k) => [strtoupper($k) => round((float) $v, 2)])->all();
    }

    public function recordIncome(float $amount, string $category, string $description, array $related = []): FinancialTransaction
    {
        return FinancialTransaction::create(array_merge([
            'type' => 'income',
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'status' => 'completed',
            'created_by' => auth()->id(),
        ], $related));
    }

    public function recordRefund(float $amount, string $description, array $related = []): FinancialTransaction
    {
        return FinancialTransaction::create(array_merge([
            'type' => 'refund',
            'category' => 'Customer Refund',
            'description' => $description,
            'amount' => $amount,
            'status' => 'completed',
            'created_by' => auth()->id(),
        ], $related));
    }

    public function recordExpense(float $amount, string $category, string $description, array $related = []): FinancialTransaction
    {
        return FinancialTransaction::create(array_merge([
            'type' => 'expense',
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'status' => 'completed',
            'created_by' => auth()->id(),
        ], $related));
    }

    public function recordCommissionPayment(float $amount, string $description, array $related = []): FinancialTransaction
    {
        return FinancialTransaction::create(array_merge([
            'type' => 'commission',
            'category' => 'Commission Payment',
            'description' => $description,
            'amount' => $amount,
            'status' => 'completed',
            'created_by' => auth()->id(),
        ], $related));
    }

    public function recordSalaryPayment(float $amount, string $description, array $related = []): FinancialTransaction
    {
        return FinancialTransaction::create(array_merge([
            'type' => 'salary',
            'category' => 'Salary Payment',
            'description' => $description,
            'amount' => $amount,
            'status' => 'completed',
            'created_by' => auth()->id(),
        ], $related));
    }

    public function getMonthlyRevenue(): float
    {
        return $this->getRevenue('monthly');
    }

    public function getPendingPayments(): float
    {
        // Outstanding = issued-but-unpaid + overdue + partially paid dues.
        // (Previously partially_paid was excluded, under-reporting pending.)
        return (float) Invoice::whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])->sum('amount_due');
    }

    public function getOutstandingInvoices(): int
    {
        return Invoice::whereIn('status', ['sent', 'viewed', 'partially_paid', 'overdue'])->count();
    }

    public function getMonthlyRecurringRevenue(): float
    {
        return (float) Invoice::where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('total');
    }

    private function getPeriodStart(string $period): Carbon
    {
        return match ($period) {
            'daily' => Carbon::now()->startOfDay(),
            'weekly' => Carbon::now()->startOfWeek(),
            'monthly' => Carbon::now()->startOfMonth(),
            'quarterly' => Carbon::now()->startOfQuarter(),
            'yearly' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
    }
}
