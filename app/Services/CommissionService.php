<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\CommissionPayout;
use App\Models\CommissionPayoutItem;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function calculateCommission($workerId, float $revenueAmount, $ruleId = null, array $related = []): Commission
    {
        $rule = $ruleId ? CommissionRule::findOrFail($ruleId) : CommissionRule::where('is_active', true)->first();
        if (!$rule) {
            throw new \Exception('No active commission rule found');
        }

        $commissionAmount = 0;
        $rate = 0;

        switch ($rule->type) {
            case 'fixed':
                $rate = $rule->rate;
                $commissionAmount = $rule->rate;
                break;
            case 'percentage':
                $rate = $rule->rate;
                $commissionAmount = $revenueAmount * ($rule->rate / 100);
                break;
            case 'tiered':
                $tiers = $rule->tiers ?? [];
                foreach ($tiers as $tier) {
                    if ($revenueAmount >= ($tier['min'] ?? 0) && $revenueAmount <= ($tier['max'] ?? PHP_FLOAT_MAX)) {
                        $rate = $tier['rate'];
                        $commissionAmount = $revenueAmount * ($tier['rate'] / 100);
                        break;
                    }
                }
                break;
            default:
                $rate = $rule->rate;
                $commissionAmount = $revenueAmount * ($rule->rate / 100);
        }

        if ($rule->maximum_payout > 0) {
            $commissionAmount = min($commissionAmount, $rule->maximum_payout);
        }

        return Commission::create(array_merge([
            'worker_id' => $workerId,
            'rule_id' => $rule->id,
            'revenue_amount' => $revenueAmount,
            'commission_type' => $rule->type,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
        ], $related));
    }

    public function approveCommission(Commission $commission, $approvedBy): Commission
    {
        $commission->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        // Note: Financial transaction is recorded when payout is processed, not on approval
        // This prevents double-counting in financial reports

        return $commission;
    }

    public function rejectCommission(Commission $commission, $rejectedBy, string $reason = null): Commission
    {
        $commission->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'notes' => $reason,
        ]);
        return $commission;
    }

    public function processPayout($workerId, array $commissionIds, string $paymentMethod = null): CommissionPayout
    {
        return DB::transaction(function () use ($workerId, $commissionIds, $paymentMethod) {
            $commissions = Commission::whereIn('id', $commissionIds)
                ->where('worker_id', $workerId)
                ->where('status', 'approved')
                ->get();

            $totalAmount = $commissions->sum('commission_amount');

            $payout = CommissionPayout::create([
                'worker_id' => $workerId,
                'amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'processed_by' => auth()->id(),
            ]);

            foreach ($commissions as $commission) {
                CommissionPayoutItem::create([
                    'payout_id' => $payout->id,
                    'commission_id' => $commission->id,
                    'amount' => $commission->commission_amount,
                ]);

                $commission->update(['status' => 'paid', 'paid_at' => now(), 'payment_status' => 'paid']);
            }

            // Record financial transaction
            app(FinancialService::class)->recordCommissionPayment(
                $totalAmount,
                "Commission payout processed for user #{$workerId}",
                ['employee_id' => $workerId]
            );

            return $payout;
        });
    }

    public function getWorkerEarnings($workerId, $startDate = null, $endDate = null): array
    {
        $query = Commission::where('worker_id', $workerId);
        if ($startDate) $query->where('created_at', '>=', $startDate);
        if ($endDate) $query->where('created_at', '<=', $endDate);

        return [
            'total_earned' => (float) $query->sum('commission_amount'),
            'pending' => (float) (clone $query)->where('status', 'pending')->sum('commission_amount'),
            'approved' => (float) (clone $query)->where('status', 'approved')->sum('commission_amount'),
            'paid' => (float) (clone $query)->where('status', 'paid')->sum('commission_amount'),
        ];
    }
}
