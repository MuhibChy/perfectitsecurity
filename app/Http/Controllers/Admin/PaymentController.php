<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\FinancialService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('invoice', 'customer');
        if ($request->status) $query->where('status', $request->status);
        $payments = $query->latest()->paginate(20);
        return view('admin.payments.index', compact('payments'));
    }

    public function create()
    {
        $invoices = Invoice::whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])->get();
        return view('admin.payments.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
        $invoice = Invoice::lockForUpdate()->findOrFail($validated['invoice_id']);
        abort_unless(in_array($invoice->status, ['sent', 'viewed', 'overdue', 'partially_paid']), 422, 'Payments can only be recorded against an issued invoice.');
        abort_if((float) $validated['amount'] > (float) $invoice->amount_due, 422, 'Payment exceeds the invoice balance.');
        $customerId = $invoice->customer_id;

        $payment = Payment::create([
            'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
            'invoice_id' => $invoice->id,
            'customer_id' => $customerId,
            'amount' => $validated['amount'],
            'status' => 'completed',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'paid_at' => now(),
        ]);

        // Update invoice
        $invoice->amount_paid += $validated['amount'];
        $invoice->amount_due = max(0, $invoice->total - $invoice->amount_paid);
        $invoice->status = $invoice->amount_due <= 0 ? 'paid' : 'partially_paid';
        if ($invoice->status === 'paid') $invoice->paid_at = now();
        $invoice->save();

        // Record financial transaction
        app(FinancialService::class)->recordIncome(
            $validated['amount'],
            'Customer Payment',
            "Payment {$payment->payment_number} received",
            ['payment_id' => $payment->id, 'invoice_id' => $invoice->id, 'customer_id' => $customerId]
        );

        AuditService::log('create', 'payments', $payment, 'Payment recorded');

        // Notify the customer when their invoice is fully paid.
        $invoice->refresh();
        if ($invoice->status === 'paid' && $invoice->customer) {
            $invoice->customer->notify(new \App\Notifications\InvoiceCreatedNotification($invoice, 'paid'));
        }

        return redirect()->route('admin.payments.index')->with('success', 'Payment recorded!');
        });
    }

    /**
     * Record a refund against a completed payment (finance only).
     * Refund can never exceed the unrefunded remainder; balances on the
     * invoice and order move back by the refunded amount; everything is
     * audited. Closed orders are protected from post-closure refunds.
     */
    public function refund(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        return DB::transaction(function () use ($validated, $id) {
            $payment = Payment::lockForUpdate()->findOrFail($id);
            abort_unless($payment->status === 'completed', 422, 'Only completed payments can be refunded.');
            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);
            abort_if(in_array($invoice->status, ['cancelled'], true), 422, 'Refunds cannot be issued against a cancelled invoice.');

            $refundable = round((float) $payment->amount - (float) $payment->refunded_amount, 2);
            abort_if((float) $validated['amount'] > $refundable, 422, "Refund exceeds the refundable remainder ({$refundable}).");

            $amount = round((float) $validated['amount'], 2);
            $refund = Payment::create([
                'payment_number' => 'RFD-' . strtoupper(Str::random(8)),
                'invoice_id' => $invoice->id,
                'customer_id' => $payment->customer_id,
                'service_order_id' => $payment->service_order_id,
                'amount' => $amount,
                'currency' => $payment->currency,
                'status' => 'refunded',
                'payment_method' => $payment->payment_method,
                'transaction_id' => 'REFUND-' . $payment->transaction_id,
                'notes' => "Refund of {$payment->payment_number}: {$validated['reason']}",
                'paid_at' => now(),
            ]);

            $payment->increment('refunded_amount', $amount);

            $invoice->amount_paid = round((float) $invoice->amount_paid - $amount, 2);
            $invoice->amount_due = round((float) $invoice->total - (float) $invoice->amount_paid, 2);
            if ($invoice->status === 'paid') {
                $invoice->status = 'partially_paid';
                $invoice->paid_at = null;
            }
            $invoice->save();

            if ($payment->service_order_id) {
                $order = \App\Models\ServiceOrder::lockForUpdate()->find($payment->service_order_id);
                if ($order) {
                    abort_if($order->status === 'closed', 422, 'Refunds cannot be issued against a closed order.');
                    $order->amount_paid = round((float) $order->amount_paid - $amount, 2);
                    $order->amount_due = round((float) $order->total - (float) $order->amount_paid, 2);
                    if ($order->payment_authorization === 'fully_paid') {
                        $order->payment_authorization = 'deposit_required';
                    }
                    $order->save();
                }
            }

            app(FinancialService::class)->recordRefund(
                $amount,
                "Refund {$refund->payment_number} ({$payment->payment_number})",
                ['payment_id' => $payment->id, 'refund_id' => $refund->id, 'invoice_id' => $invoice->id]
            );

            AuditService::log('refund', 'payments', $refund, "Refund of {$payment->currency} {$amount} against {$payment->payment_number}. Reason: {$validated['reason']}");

            if ($invoice->customer) {
                $invoice->customer->notify(new \App\Notifications\InvoiceCreatedNotification($invoice, 'refunded'));
            }

            return redirect()->back()->with('success', 'Refund recorded.');
        });
    }
}
