<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\CommissionPayoutItem;
use App\Models\CommissionRule;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function calculateCommission($workerId, float $revenueAmount, $ruleId = null, array $related = []): Commission
    {
        $rule = $ruleId ? CommissionRule::findOrFail($ruleId) : CommissionRule::where('is_active', true)->first();
        if (! $rule) {
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

    /**
     * Controlled lifecycle (Phase 4), fitted to the existing DB enum
     * (pending|submitted|under_review|approved|payable|paid|rejected):
     * pending(calculated) → submitted → under_review → approved → payable
     * → paid (paid lands ONLY via payout completion against a real
     * reference; the payout row itself carries pending→processing→
     * completed). No arbitrary jumps; every transition audited.
     * Reversals never rewrite history: money moves back via an audited
     * bank transfer while the commission row stays immutable.
     */
    protected function transition(Commission $commission, string $to, int $actorId, string $reason = ''): Commission
    {
        $allowed = [
            'pending' => ['submitted', 'rejected'],
            'submitted' => ['under_review', 'rejected'],
            'under_review' => ['approved', 'rejected'],
            'approved' => ['payable', 'rejected'],
            'payable' => [],
            'paid' => [],
            'rejected' => [],
        ];

        return DB::transaction(function () use ($commission, $to, $actorId, $reason, $allowed) {
            $commission = Commission::lockForUpdate()->findOrFail($commission->id);
            abort_unless(in_array($to, $allowed[$commission->status] ?? [], true), 422, "Commission cannot move from {$commission->status} to {$to}.");
            $from = $commission->status;
            $patch = ['status' => $to];
            if (in_array($to, ['approved', 'rejected'], true)) {
                $patch['approved_by'] = $actorId;
                $patch['approved_at'] = now();
            }
            if ($to === 'paid') {
                $patch['payment_status'] = 'paid';
                $patch['paid_at'] = now();
            }
            $commission->update($patch);
            \App\Models\AuditLog::log('commission.transition', 'commissions', $commission, "Commission {$commission->commission_number}: {$from} → {$to} by user #{$actorId}. Reason: {$reason}");

            return $commission->fresh();
        });
    }

    public function submitCommission(Commission $commission, int $actorId, string $reason = ''): Commission
    {
        return $this->transition($commission, 'submitted', $actorId, $reason);
    }

    public function reviewCommission(Commission $commission, int $actorId, string $reason = ''): Commission
    {
        return $this->transition($commission, 'under_review', $actorId, $reason);
    }

    /** Backward-compatible approval: walks pending|submitted forward through the chain. */
    public function approveCommission(Commission $commission, $approvedBy): Commission
    {
        $commission = $commission->fresh();
        if ($commission->status === 'pending') {
            $commission = $this->transition($commission, 'submitted', (int) $approvedBy, 'auto-advance to approval');
        }
        if ($commission->status === 'submitted') {
            $commission = $this->transition($commission, 'under_review', (int) $approvedBy, 'auto-advance to approval');
        }

        return $this->transition($commission->fresh(), 'approved', (int) $approvedBy, 'approved');
    }

    public function markPayable(Commission $commission, int $actorId, string $reason = ''): Commission
    {
        return $this->transition($commission, 'payable', $actorId, $reason);
    }

    public function cancelCommission(Commission $commission, int $actorId, string $reason = ''): Commission
    {
        // No 'cancelled' value exists in the commissions status enum, so
        // cancellation is recorded as a rejected-with-reason row: history
        // stays queryable, nothing is silently deleted.
        abort_unless(trim($reason) !== '', 422, 'A cancellation reason is required.');

        return $this->transition($commission, 'rejected', $actorId, 'CANCELLED: '.$reason);
    }

    /**
     * Reverse a PAID commission without rewriting history: the commission
     * row stays paid (immutable financial fact) while the money moves back
     * through an audited bank transfer referenced here.
     */
    public function reversePaidCommission(Commission $commission, int $actorId, string $transferReference, string $reason = ''): Commission
    {
        abort_if(trim($transferReference) === '', 422, 'A completed reversal transfer reference is required.');

        return DB::transaction(function () use ($commission, $actorId, $transferReference, $reason) {
            $commission = Commission::lockForUpdate()->findOrFail($commission->id);
            abort_unless($commission->status === 'paid', 422, 'Only paid commissions can be reversed.');
            $transfer = \App\Models\BankTransfer::where('reference', $transferReference)->orWhere('external_reference', $transferReference)->first();
            abort_unless($transfer && $transfer->status === 'completed', 422, 'Reversal requires a completed bank transfer.');
            $commission->update(['notes' => trim(($commission->notes ? $commission->notes."\n" : '')."REVERSED via {$transfer->reference}: {$reason}")]);
            \App\Models\AuditLog::log('commission.reversed', 'commissions', $commission, "Commission {$commission->commission_number} reversed via transfer {$transfer->reference} by user #{$actorId}. Reason: {$reason}");

            return $commission->fresh();
        });
    }

    /**
     * Audited rejection through the controlled transition map (Phase 5).
     * Terminal states (paid, rejected) cannot be re-rejected; the actor,
     * timestamp, previous/new state and reason are recorded in audit.
     */
    public function rejectCommission(Commission $commission, $rejectedBy, string $reason = null): Commission
    {
        $commission = $commission->fresh();
        if ($commission->status === 'rejected') {
            return $commission;
        }
        abort_unless(in_array($commission->status, ['pending', 'submitted', 'under_review', 'approved'], true), 422, "Commission cannot be rejected from status {$commission->status}.");
        $commission = $this->transition($commission, 'rejected', (int) $rejectedBy, $reason ?? 'rejected');
        if ($reason !== null && $reason !== '') {
            $commission->update(['notes' => trim(($commission->notes ? $commission->notes."\n" : '').$reason)]);
            $commission = $commission->fresh();
        }

        return $commission;
    }

    public function processPayout($workerId, array $commissionIds, string $paymentMethod = null): CommissionPayout
    {
        return DB::transaction(function () use ($workerId, $commissionIds, $paymentMethod) {
            $commissions = Commission::whereIn('id', $commissionIds)
                ->where('worker_id', $workerId)
                ->whereIn('status', ['approved', 'payable'])
                ->lockForUpdate()
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

                // Commission stays approved/payable while the payout is
                // pending: PAID lands only on payout completion (below).
                // The payout row itself carries pending→processing→completed.
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

    /**
     * Complete a payout against a REAL provider/bank reference.
     * processPayout() leaves payouts pending; completion is a separate,
     * audited act that requires the external reference (no fabrication).
     */
    public function completePayout(CommissionPayout $payout, string $externalReference): CommissionPayout
    {
        abort_if(trim($externalReference) === '', 422, 'An external payment reference is required to complete a payout.');

        return DB::transaction(function () use ($payout, $externalReference) {
            $payout = \App\Models\CommissionPayout::lockForUpdate()->findOrFail($payout->id);
            abort_unless(in_array($payout->status, ['pending', 'processing'], true), 422, 'Payout cannot be completed from status '.$payout->status.'.');
            $payout->update([
                'status' => 'completed',
                'transaction_reference' => $externalReference,
                'paid_at' => now(),
            ]);
            // Linked commissions settle together with the payout (one event).
            foreach ($payout->items()->with('commission')->get() as $item) {
                if ($item->commission && in_array($item->commission->status, ['approved', 'payable'], true)) {
                    $item->commission->update(['status' => 'paid', 'paid_at' => now(), 'payment_status' => 'paid']);
                }
            }
            \App\Models\AuditLog::log('commission.payout_completed', 'commission_payouts', $payout, "Commission payout {$payout->payout_number} completed. Provider ref: {$externalReference}.");

            return $payout->fresh();
        });
    }

    public function getWorkerEarnings($workerId, $startDate = null, $endDate = null): array
    {
        $query = Commission::where('worker_id', $workerId);
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total_earned' => (float) $query->sum('commission_amount'),
            'pending' => (float) (clone $query)->where('status', 'pending')->sum('commission_amount'),
            'submitted' => (float) (clone $query)->where('status', 'submitted')->sum('commission_amount'),
            'under_review' => (float) (clone $query)->where('status', 'under_review')->sum('commission_amount'),
            'approved' => (float) (clone $query)->where('status', 'approved')->sum('commission_amount'),
            'payable' => (float) (clone $query)->where('status', 'payable')->sum('commission_amount'),
            'paid' => (float) (clone $query)->where('status', 'paid')->sum('commission_amount'),
        ];
    }
}
