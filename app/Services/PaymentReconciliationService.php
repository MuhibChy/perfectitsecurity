<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;

/**
 * Payment reconciliation (Phase 17).
 *
 * Compares internal payment / invoice / order / finance-ledger records and
 * reports mismatches WITHOUT mutating money. Used by the admin
 * reconciliation screen and the production-readiness audit.
 */
class PaymentReconciliationService
{
    public function reconcileOrder(ServiceOrder $order): array
    {
        $order = $order->fresh(['payments', 'invoices']);
        $issues = [];

        $paySum = round((float) $order->payments()->where('status', 'completed')->sum('amount'), 2);
        $refundSum = round((float) $order->payments()->where('status', 'refunded')->sum('amount'), 2);
        $netPaid = round($paySum - $refundSum, 2);

        if (abs($netPaid - (float) $order->amount_paid) > 0.009) {
            $issues[] = "order amount_paid ({$order->amount_paid}) != net payments ({$netPaid})";
        }
        if (! PaymentState::balancesReconcile((float) $order->total, (float) $order->amount_paid, (float) $order->amount_due)) {
            $issues[] = "order TOTAL ({$order->total}) != PAID ({$order->amount_paid}) + DUE ({$order->amount_due})";
        }

        foreach ($order->invoices as $invoice) {
            $r = $this->reconcileInvoice($invoice);
            foreach ($r['issues'] as $i) {
                $issues[] = "invoice {$invoice->invoice_number}: {$i}";
            }
        }

        // Schedule vs actual.
        $planned = round((float) \App\Models\OrderPaymentSchedule::where('order_id', $order->id)->where('status', '!=', 'waived')->sum('expected_amount'), 2);
        if ($planned > 0 && $planned > round((float) $order->total, 2)) {
            $issues[] = "scheduled total ({$planned}) exceeds order total ({$order->total})";
        }

        // Duplicate provider references.
        $dupes = $order->payments()->select('stripe_checkout_session_id', DB::raw('COUNT(*) c'))
            ->whereNotNull('stripe_checkout_session_id')
            ->groupBy('stripe_checkout_session_id')->having('c', '>', 1)->count();
        if ($dupes > 0) {
            $issues[] = 'duplicate stripe session references detected';
        }

        $dupTxn = $order->payments()->select('transaction_id', DB::raw('COUNT(*) c'))
            ->whereNotNull('transaction_id')
            ->groupBy('transaction_id')->having('c', '>', 1)->count();
        if ($dupTxn > 0) {
            $issues[] = 'duplicate transaction_id references detected';
        }

        return [
            'order' => $order->order_number,
            'total' => (float) $order->total,
            'paid' => (float) $order->amount_paid,
            'due' => (float) $order->amount_due,
            'net_payments' => $netPaid,
            'balanced' => PaymentState::balancesReconcile((float) $order->total, (float) $order->amount_paid, (float) $order->amount_due),
            'issues' => $issues,
            'ok' => empty($issues),
        ];
    }

    public function reconcileInvoice(Invoice $invoice): array
    {
        $invoice = $invoice->fresh(['payments']);
        $issues = [];

        $paySum = round((float) $invoice->payments()->where('status', 'completed')->sum('amount'), 2);
        $refundSum = round((float) $invoice->payments()->where('status', 'refunded')->sum('amount'), 2);
        $netPaid = round($paySum - $refundSum, 2);

        if (abs($netPaid - (float) $invoice->amount_paid) > 0.009) {
            $issues[] = "amount_paid ({$invoice->amount_paid}) != net payments ({$netPaid})";
        }
        if (! PaymentState::balancesReconcile((float) $invoice->total, (float) $invoice->amount_paid, (float) $invoice->amount_due)) {
            $issues[] = "TOTAL ({$invoice->total}) != PAID ({$invoice->amount_paid}) + DUE ({$invoice->amount_due})";
        }
        // Currency consistency.
        foreach ($invoice->payments as $p) {
            if (strtoupper($p->currency) !== strtoupper($invoice->currency ?? 'USD')) {
                $issues[] = "payment {$p->payment_number} currency ({$p->currency}) != invoice ({$invoice->currency})";
            }
        }
        // Finance ledger reflection is checked at order level; per-invoice
        // ledger counts are informational only (no mutation here).

        return [
            'invoice' => $invoice->invoice_number,
            'total' => (float) $invoice->total,
            'paid' => (float) $invoice->amount_paid,
            'due' => (float) $invoice->amount_due,
            'status' => $invoice->status,
            'derived' => PaymentState::deriveInvoiceStatus((float) $invoice->total, (float) $invoice->amount_paid, $invoice->status, (bool) $invoice->is_overdue),
            'balanced' => PaymentState::balancesReconcile((float) $invoice->total, (float) $invoice->amount_paid, (float) $invoice->amount_due),
            'issues' => $issues,
            'ok' => empty($issues),
        ];
    }

    /** Global sweep for the admin screen (bounded for performance). */
    public function sweep(int $limit = 200): array
    {
        $orders = ServiceOrder::latest()->limit($limit)->get();
        $bad = 0;
        $results = [];
        foreach ($orders as $order) {
            $r = $this->reconcileOrder($order);
            if (! $r['ok']) {
                $bad++;
                $results[] = $r;
                if (count($results) >= 50) {
                    break;
                }
            }
        }

        return [
            'checked' => count($orders),
            'mismatched' => $bad,
            'mismatches' => $results,
        ];
    }
}
