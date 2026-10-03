<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Controlled refunds: requested → approved → processing → refunded|failed.
 * Customers may REQUEST; only finance roles approve/execute (enforced in
 * controllers). Execution mirrors Admin\PaymentController@refund step for
 * step (provider reversal first, then RFD row + invoice/order rollback +
 * recordRefund) so the ledger stays single-sourced.
 */
class PaymentRefundService
{
    public function request(Payment $payment, float $amount, string $currency, string $reason, int $requestedBy, ?int $txnId = null): PaymentRefund
    {
        abort_unless($payment->status === 'completed', 422, 'Only completed payments can be refunded.');
        $amount = round($amount, 2);
        abort_if($amount <= 0, 422, 'Refund amount must be greater than zero.');
        $refundable = round((float) $payment->amount - (float) $payment->refunded_amount, 2);
        abort_if($amount > $refundable, 422, 'Refund exceeds refundable balance.');
        abort_unless(strtoupper($currency) === strtoupper($payment->currency ?? ''), 422, 'Refund currency must match payment currency.');

        return DB::transaction(function () use ($payment, $amount, $currency, $reason, $requestedBy, $txnId) {
            $refund = PaymentRefund::create([
                'payment_transaction_id' => $txnId,
                'payment_id' => $payment->id,
                'customer_id' => $payment->customer_id,
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'reason' => $reason,
                'requested_by' => $requestedBy,
                'status' => 'requested',
            ]);
            \App\Models\AuditLog::log('refund.requested', 'payment_refunds', $refund, "Refund {$refund->refund_number} requested for payment {$payment->payment_number}.");
            $refundNumber = $refund->refund_number;
            $paymentNumber = $payment->payment_number;
            DB::afterCommit(function () use ($refundNumber, $paymentNumber, $amount, $refund) {
                foreach (\App\Models\User::whereIn('role', ['super_admin', 'admin', 'finance_manager'])->where('is_active', true)->limit(20)->get() as $staff) {
                    $staff->notify(new \App\Notifications\PaymentStatusNotification('refund_requested', [
                        'refund_number' => $refundNumber,
                        'payment_number' => $paymentNumber,
                        'amount' => $amount,
                        'currency' => $refund->currency,
                        'url' => route('admin.refunds.index'),
                    ]));
                }
            });

            return $refund;
        });
    }

    public function approve(PaymentRefund $refund, int $approvedBy): PaymentRefund
    {
        abort_unless($refund->status === 'requested', 422, 'Only requested refunds can be approved.');
        $refund->update(['status' => 'approved', 'approved_by' => $approvedBy]);
        \App\Models\AuditLog::log('refund.approved', 'payment_refunds', $refund, "Refund {$refund->refund_number} approved.");

        return $refund->fresh();
    }

    public function reject(PaymentRefund $refund, int $approvedBy, string $reason = ''): PaymentRefund
    {
        abort_unless($refund->status === 'requested', 422, 'Only requested refunds can be rejected.');
        $refund->update(['status' => 'rejected', 'approved_by' => $approvedBy]);
        \App\Models\AuditLog::log('refund.rejected', 'payment_refunds', $refund, "Refund {$refund->refund_number} rejected. {$reason}");

        return $refund->fresh();
    }

    /** Execute an approved refund. Idempotent per refund row. */
    public function execute(PaymentRefund $refund, int $actorId): PaymentRefund
    {
        return DB::transaction(function () use ($refund) {
            $refund = PaymentRefund::lockForUpdate()->findOrFail($refund->id);
            abort_unless(in_array($refund->status, ['approved'], true), 422, 'Refund must be approved before execution.');
            $payment = Payment::lockForUpdate()->findOrFail($refund->payment_id);
            abort_unless($payment->status === 'completed', 422, 'Payment is no longer refundable.');

            $refund->update(['status' => 'processing']);

            // 1. Provider reversal first (Stripe live only; failure aborts).
            $providerRefundId = null;
            if ($payment->gateway === 'stripe' && $payment->stripe_payment_intent_id && app(StripePaymentService::class)->isConfigured()) {
                try {
                    $reversal = \Stripe\Refund::create([
                        'payment_intent' => $payment->stripe_payment_intent_id,
                        'amount' => (int) round($refund->amount * 100),
                        'metadata' => ['refund_number' => $refund->refund_number],
                    ]);
                    $providerRefundId = $reversal->id ?? null;
                } catch (\Throwable $e) {
                    $refund->update(['status' => 'failed']);
                    throw $e;
                }
            }

            // 2. RFD payment row (history preserved, never deleted).
            $rfd = Payment::create([
                'invoice_id' => $payment->invoice_id,
                'customer_id' => $payment->customer_id,
                'service_order_id' => $payment->service_order_id,
                'amount' => $refund->amount,
                'currency' => $payment->currency,
                'payment_method' => $payment->payment_method,
                'gateway' => $payment->gateway,
                'transaction_id' => $refund->refund_number,
                'status' => 'refunded',
                'paid_at' => now(),
                'notes' => "Refund {$refund->refund_number} for {$payment->payment_number}: {$refund->reason}",
            ]);
            $payment->increment('refunded_amount', $refund->amount);

            // 3. Roll back invoice + order balances.
            $invoice = $payment->invoice()->lockForUpdate()->first();
            if ($invoice && $invoice->status !== 'cancelled') {
                $invoice->amount_paid = max(0, round((float) $invoice->amount_paid - $refund->amount, 2));
                $invoice->amount_due = max(0, round((float) $invoice->total - (float) $invoice->amount_paid, 2));
                if ($invoice->status === 'paid') {
                    $invoice->status = 'partially_paid';
                }
                $invoice->save();
                if ($invoice->service_order_id) {
                    $order = \App\Models\ServiceOrder::lockForUpdate()->find($invoice->service_order_id);
                    if ($order && $order->status !== 'closed') {
                        $order->amount_paid = max(0, round((float) $order->amount_paid - $refund->amount, 2));
                        $order->amount_due = max(0, round((float) $order->total - (float) $order->amount_paid, 2));
                        if ($order->payment_authorization === 'fully_paid') {
                            $order->payment_authorization = 'deposit_required';
                        }
                        $order->save();
                    }
                }
            }

            app(FinancialService::class)->recordRefund(
                (float) $refund->amount,
                "Refund {$refund->refund_number} for payment {$payment->payment_number}",
                ['invoice_id' => $payment->invoice_id, 'payment_id' => $rfd->id, 'customer_id' => $payment->customer_id, 'currency' => $payment->currency]
            );

            // 4. Mirror onto the provider transaction (idempotent refund math).
            if ($refund->payment_transaction_id) {
                $txn = PaymentTransaction::lockForUpdate()->find($refund->payment_transaction_id);
                if ($txn) {
                    $txn->refunded_amount = round((float) $txn->refunded_amount + $refund->amount, 2);
                    $txn->save();
                    $full = round((float) $txn->gross_amount - (float) $txn->refunded_amount, 2) <= 0;
                    $txn->transitionTo($full ? 'refunded' : 'partially_refunded');
                }
            }

            $refund->update(['status' => 'refunded', 'provider_refund_id' => $providerRefundId, 'processed_at' => now()]);
            \App\Models\AuditLog::log('refund.executed', 'payment_refunds', $refund, "Refund {$refund->refund_number} executed ({$payment->currency} {$refund->amount}).");

            if ($payment->customer) {
                $payment->customer->notify(new \App\Notifications\PaymentStatusNotification('refund_completed', [
                    'refund_number' => $refund->refund_number,
                    'amount' => $refund->amount,
                    'currency' => $refund->currency,
                    'payment_number' => $payment->payment_number,
                ]));
            }

            return $refund->fresh();
        });
    }
}
