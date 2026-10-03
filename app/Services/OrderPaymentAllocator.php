<?php

namespace App\Services;

use App\Models\OrderPaymentSchedule;
use App\Models\Payment;
use App\Models\ServiceOrder;

/**
 * FIFO allocator: every payment posted on ANY rail (manual, Stripe,
 * wallet, workflow) is allocated across the order's open payment
 * schedule rows. Fixes the drift where only the manual work-order path
 * updated paid_amount.
 *
 * Must be called inside the caller's DB transaction with the order locked.
 */
class OrderPaymentAllocator
{
    /**
     * Allocate $amount of $payment across open schedules (oldest first).
     * Returns allocated total. Never exceeds order total.
     */
    public function allocate(ServiceOrder $order, Payment $payment, ?float $amount = null): float
    {
        $amount = round($amount ?? (float) $payment->amount, 2);
        if ($amount <= 0) {
            return 0.0;
        }

        $schedules = OrderPaymentSchedule::where('order_id', $order->id)
            ->where('status', '!=', 'waived')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($schedules->isEmpty()) {
            return 0.0;
        }

        $remaining = $amount;
        $allocated = 0.0;
        $firstTouched = null;

        foreach ($schedules as $schedule) {
            if ($remaining <= 0) {
                break;
            }
            $need = $schedule->remaining();
            if ($need <= 0) {
                continue;
            }
            $take = min($need, $remaining);
            $schedule->paid_amount = round((float) $schedule->paid_amount + $take, 2);
            $schedule->status = $schedule->paid_amount >= (float) $schedule->expected_amount ? 'paid' : 'partial';
            $schedule->save();
            $firstTouched ??= $schedule;
            $remaining = round($remaining - $take, 2);
            $allocated = round($allocated + $take, 2);
        }

        // Link payment to the first schedule it touched (single FK).
        if ($firstTouched && empty($payment->schedule_id)) {
            $payment->schedule_id = $firstTouched->id;
            $payment->save();
        }

        return $allocated;
    }

    /** Schedule-level summary for an order (for portal/admin display). */
    public function summary(ServiceOrder $order): array
    {
        $rows = OrderPaymentSchedule::where('order_id', $order->id)->orderBy('sort_order')->get();

        return [
            'planned' => round((float) $rows->where('status', '!=', 'waived')->sum('expected_amount'), 2),
            'allocated' => round((float) $rows->sum('paid_amount'), 2),
            'rows' => $rows->map(fn ($r) => [
                'id' => $r->id, 'title' => $r->title,
                'expected' => (float) $r->expected_amount,
                'paid' => (float) $r->paid_amount,
                'remaining' => $r->remaining(), 'status' => $r->status,
            ])->all(),
        ];
    }
}
