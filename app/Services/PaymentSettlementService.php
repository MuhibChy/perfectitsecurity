<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Receipt;
use App\Notifications\InvoiceCreatedNotification;
use Illuminate\Support\Facades\DB;

/**
 * Authoritative settlement funnel for the provider layer.
 *
 * Money moves ONLY here (or the pre-existing rails): server-side invoice
 * balances, idempotent on the transaction, ledger via the same
 * FinancialService::recordIncome every other rail uses. Order-linked
 * invoices delegate to ServiceOrderWorkflowService::recordPayment so
 * authorization states, schedules, receipts and commission-visible
 * revenue stay identical across rails.
 */
class PaymentSettlementService
{
    // Bank-transfer human verification settles directly from
    // pending_verification: the review itself is the authorization.
    public const SETTLEABLE = ['pending', 'processing', 'authorized', 'verified', 'pending_verification', 'requires_verification'];
    public const PAYABLE = ['sent', 'viewed', 'overdue', 'partially_paid'];

    /**
     * Settle a provider transaction into Payment + invoice/order + ledger.
     * Returns ['payment'=>..., 'receipt'=>..., 'duplicate'=>bool].
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (422)
     */
    public function settle(PaymentTransaction $txn, ?int $actorId = null, ?string $providerEventId = null, bool $notify = true): array
    {
        return DB::transaction(function () use ($txn, $actorId, $providerEventId, $notify) {
            $txn = PaymentTransaction::lockForUpdate()->findOrFail($txn->id);

            if ($txn->payment_id) {
                return ['payment' => $txn->payment, 'receipt' => null, 'duplicate' => true];
            }
            abort_unless(in_array($txn->status, self::SETTLEABLE, true), 422, 'Transaction is not settleable.');

            if ($providerEventId && PaymentTransaction::where('provider_event_id', $providerEventId)->where('id', '!=', $txn->id)->exists()) {
                abort(422, 'Duplicate provider event.');
            }

            $invoice = Invoice::lockForUpdate()->findOrFail($txn->invoice_id);
            abort_unless(in_array($invoice->status, array_merge(self::PAYABLE, ['paid']), true), 422, 'Invoice is not payable.');
            abort_unless(strtoupper($txn->original_currency) === strtoupper($invoice->currency ?? ''), 422, 'Currency mismatch.');

            $outstanding = round((float) $invoice->total - (float) $invoice->amount_paid, 2);
            abort_if($outstanding <= 0, 422, 'Invoice already paid in full.');
            $amount = min(round((float) $txn->original_amount, 2), $outstanding);
            abort_if($amount <= 0, 422, 'Nothing left to settle.');

            if ($providerEventId) {
                $txn->provider_event_id = $providerEventId;
                $txn->save();
            }

            if ($invoice->service_order_id) {
                $order = \App\Models\ServiceOrder::findOrFail($invoice->service_order_id);
                $actor = $actorId ? \App\Models\User::find($actorId) : $invoice->customer;
                $result = app(ServiceOrderWorkflowService::class)->recordPayment($order, [
                    'amount' => $amount,
                    'payment_method' => $txn->payment_method,
                    'transaction_id' => $txn->reference,
                    'notes' => "Provider {$txn->provider_key} payment {$txn->reference} for {$invoice->invoice_number}",
                ], $actor);
                $payment = $result['payment'];
                $payment->update(['gateway' => $txn->provider_key]);
                $receipt = $result['receipt'];
            } else {
                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $amount,
                    'currency' => strtoupper($invoice->currency ?? 'USD'),
                    'payment_method' => $txn->payment_method,
                    'gateway' => $txn->provider_key,
                    'transaction_id' => $txn->reference,
                    'status' => 'completed',
                    'paid_at' => now(),
                    'notes' => "Provider {$txn->provider_key} payment {$txn->reference} for {$invoice->invoice_number}",
                ]);
                $invoice->amount_paid = round((float) $invoice->amount_paid + $amount, 2);
                $invoice->amount_due = max(0, round((float) $invoice->total - (float) $invoice->amount_paid, 2));
                $invoice->status = $invoice->amount_due == 0 ? 'paid' : 'partially_paid';
                if ($invoice->amount_due == 0) $invoice->paid_at = now();
                $invoice->save();

                $receipt = Receipt::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $amount,
                    'remaining_balance' => $invoice->amount_due,
                    'currency' => $payment->currency,
                    'issued_at' => now(),
                ]);

                app(FinancialService::class)->recordIncome(
                    $amount, 'Customer Payment',
                    "Payment {$payment->payment_number} for invoice {$invoice->invoice_number} via {$txn->provider_key}",
                    ['invoice_id' => $invoice->id, 'payment_id' => $payment->id, 'customer_id' => $invoice->customer_id, 'currency' => $payment->currency]
                );

                if ($invoice->amount_due == 0 && $invoice->customer) {
                    $invoice->customer->notify(new InvoiceCreatedNotification($invoice->fresh(), 'paid'));
                }
            }

            $txn->payment_id = $payment->id;
            $txn->transitionTo('paid');
            if ($actorId) {
                $txn->updated_by = $actorId;
                $txn->save();
            }

            \App\Models\AuditLog::log('payment.settled', 'payment_transactions', $txn, "Settled {$payment->currency} {$amount} via {$txn->provider_key}; payment {$payment->payment_number}.");

            if ($notify) {
                $customer = $txn->customer;
                $invoiceNumber = $invoice->invoice_number;
                $reference = $txn->reference;
                $settledAmount = $amount;
                $settledCurrency = $payment->currency;
                $providerKey = $txn->provider_key;
                DB::afterCommit(function () use ($customer, $invoiceNumber, $reference, $settledAmount, $settledCurrency, $providerKey) {
                    if ($customer) {
                        $customer->notify(new \App\Notifications\PaymentStatusNotification('payment_successful', [
                            'reference' => $reference,
                            'amount' => $settledAmount,
                            'currency' => $settledCurrency,
                            'invoice_number' => $invoiceNumber,
                            'url' => route('portal.payments.show', $reference),
                        ]));
                    }
                    foreach (\App\Models\User::whereIn('role', ['super_admin', 'admin', 'finance_manager'])->where('is_active', true)->limit(20)->get() as $staff) {
                        $staff->notify(new \App\Notifications\PaymentStatusNotification('payment_received', [
                            'reference' => $reference,
                            'amount' => $settledAmount,
                            'currency' => $settledCurrency,
                            'invoice_number' => $invoiceNumber,
                            'provider' => $providerKey,
                            'url' => route('admin.payments.overview'),
                        ]));
                    }
                });
            }

            return ['payment' => $payment->fresh(), 'receipt' => $receipt ?? null, 'duplicate' => false];
        });
    }

    public function markFailed(PaymentTransaction $txn, string $reason): void
    {
        DB::transaction(function () use ($txn, $reason) {
            $txn = PaymentTransaction::lockForUpdate()->findOrFail($txn->id);
            if ($txn->isTerminal()) return;
            $txn->failure_reason = mb_substr($reason, 0, 1000);
            $txn->save();
            $txn->transitionTo('failed');
            \App\Models\AuditLog::log('payment.failed', 'payment_transactions', $txn, "Marked failed: {$reason}");
        });
    }
}
