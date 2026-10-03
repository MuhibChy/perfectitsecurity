<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Services\PaymentRefundService;
use Illuminate\Http\Request;

/**
 * Controlled refunds. Customers request (portal); finance approves and
 * executes. Ordinary customers can never execute directly (no route).
 */
class PaymentRefundController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentRefund::with(['customer', 'payment', 'requester', 'approver'])->latest('id');
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $refunds = $query->paginate(20)->withQueryString();
        return view('admin.payments.refunds.index', compact('refunds'));
    }

    public function show(PaymentRefund $refund)
    {
        $refund->load(['customer', 'payment', 'paymentTransaction', 'requester', 'approver']);
        return view('admin.payments.refunds.show', compact('refund'));
    }

    public function store(Request $request, PaymentRefundService $service)
    {
        $data = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|min:10|max:1000',
        ]);
        $payment = Payment::findOrFail($data['payment_id']);
        $txnId = $payment->customer_id ? optional(
            \App\Models\PaymentTransaction::where('payment_id', $payment->id)->first()
        )->id : null;
        $refund = $service->request($payment, (float) $data['amount'], $payment->currency, $data['reason'], auth()->id(), $txnId);
        return redirect()->route('admin.refunds.show', $refund)->with('success', 'Refund requested.');
    }

    public function approve(PaymentRefund $refund, PaymentRefundService $service)
    {
        $service->approve($refund, auth()->id());
        return back()->with('success', 'Refund approved. Execute it to move money.');
    }

    public function reject(Request $request, PaymentRefund $refund, PaymentRefundService $service)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000']);
        $service->reject($refund, auth()->id(), $data['reason']);
        return back()->with('success', 'Refund rejected.');
    }

    public function execute(PaymentRefund $refund, PaymentRefundService $service)
    {
        $service->execute($refund, auth()->id());
        return redirect()->route('admin.refunds.show', $refund)->with('success', 'Refund executed and ledger updated.');
    }
}
