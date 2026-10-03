<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\ManualBankPayment;
use App\Models\PaymentTransaction;
use App\Notifications\PaymentStatusNotification;
use App\Services\CurrencyService;
use App\Services\Money;
use App\Services\PaymentCheckoutService;
use App\Services\PaymentProviderService;
use App\Services\PaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Customer payment checkout: summary → dynamic methods → initiate →
 * provider handoff → server-verified callback. Amounts always come
 * from the invoice row; the browser only chooses the method.
 */
class PaymentCheckoutController extends Controller
{
    /** Checkout summary + available methods for an invoice. */
    public function show(Invoice $invoice, PaymentProviderService $providers)
    {
        $invoice = Invoice::where('id', $invoice->id)->where('customer_id', auth()->id())->firstOrFail();
        abort_unless(in_array($invoice->status, PaymentCheckoutService::PAYABLE, true), 422, 'This invoice is not payable.');
        $outstanding = round((float) $invoice->total - (float) $invoice->amount_paid, 2);
        $methods = $providers->availableFor($invoice->currency ?? 'USD', $outstanding, auth()->user()->country);
        $accounts = BankAccount::active()->forCurrency($invoice->currency ?? 'USD')->ordered()->get();
        if ($accounts->isEmpty()) {
            $accounts = BankAccount::active()->ordered()->get();
        }
        $history = PaymentTransaction::forCustomer(auth()->id())->where('invoice_id', $invoice->id)->latestFirst()->take(10)->get();
        return view('customer.checkout.show', compact('invoice', 'outstanding', 'methods', 'accounts', 'history'));
    }

    /** Initiate payment (idempotent; throttle guards double-click floods). */
    public function initiate(Request $request, Invoice $invoice, PaymentCheckoutService $checkout)
    {
        $invoice = Invoice::where('id', $invoice->id)->where('customer_id', auth()->id())->firstOrFail();
        $data = $request->validate([
            'provider' => 'required|string|max:60',
            'amount' => 'nullable|numeric|min:0.01',
            'pay_currency' => 'nullable|string|size:3',
            'idempotency_key' => 'nullable|string|max:80',
        ]);
        if (!empty($data['pay_currency'])) {
            abort_unless(Money::isActive(strtoupper($data['pay_currency'])), 422, 'Unsupported currency.');
        }
        $result = $checkout->initiate($invoice, auth()->id(), $data['provider'], [
            'amount' => $data['amount'] ?? null,
            'pay_currency' => $data['pay_currency'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? ('chk_' . Str::uuid()),
            'return_url' => route('portal.checkout.callback', ['provider' => strtolower($data['provider'])]),
        ]);
        $txn = $result['transaction'];
        auth()->user()->notify(new PaymentStatusNotification('payment_initiated', [
            'reference' => $txn->reference,
            'amount' => $txn->original_amount,
            'currency' => $txn->original_currency,
            'invoice_number' => $invoice->invoice_number,
            'provider' => $txn->provider_key,
            'url' => route('portal.payments.show', $txn->reference),
        ]));

        if (($result['adapter']['redirect_url'] ?? null) && !$result['duplicate']) {
            return redirect()->away($result['adapter']['redirect_url']);
        }
        // Bank-transfer / manual rails land on instructions + submission form.
        if ($txn->provider_key === 'bank_transfer') {
            return redirect()->route('portal.bank-transfer.show', ['transaction' => $txn->reference]);
        }
        return redirect()->route('portal.payments.show', $txn->reference)
            ->with('success', $result['duplicate'] ? 'This payment was already initiated.' : 'Payment initiated. Complete it with the provider.');
    }

    /**
     * Provider return (browser callback). Informational only: re-verifies
     * server-side and NEVER settles on browser word.
     */
    public function callback(Request $request, string $provider, PaymentProviderService $providers, PaymentSettlementService $settlement)
    {
        $reference = $request->query('reference') ?? $request->input('reference');
        $txn = PaymentTransaction::forCustomer(auth()->id())
            ->when($reference, fn ($q) => $q->where(fn ($qq) => $qq->where('reference', $reference)->orWhere('provider_reference', $reference)))
            ->latestFirst()->firstOrFail();
        $adapter = $providers->resolve($txn->provider_key);
        $verdict = $adapter->verifyPayment($txn->provider_reference ?: $txn->reference, $request->all());
        if (($verdict['status'] ?? 'PENDING') === 'SUCCEEDED' && !$txn->payment_id && !$txn->isTerminal()) {
            // Test-mode providers confirm via return; live rails settle via webhook.
            // settle() notifies the customer + finance staff on success.
            if (!$txn->provider || !$txn->provider->isLive()) {
                $settlement->settle($txn->fresh(), auth()->id());
            }
        }
        return redirect()->route('portal.payments.show', $txn->reference)
            ->with('success', 'Payment status refreshed from the provider.');
    }

    /** Bank-transfer instructions + submission form for a transaction. */
    public function bankTransferShow(string $transaction)
    {
        $txn = PaymentTransaction::forCustomer(auth()->id())->where('reference', $transaction)->firstOrFail();
        abort_unless($txn->provider_key === 'bank_transfer', 404);
        $accounts = BankAccount::active()->ordered()->get();
        $submission = ManualBankPayment::where('payment_transaction_id', $txn->id)->latest('id')->first();
        return view('customer.checkout.bank-transfer', compact('txn', 'accounts', 'submission'));
    }

    /** Submit bank-transfer details + optional receipt (never auto-pays). */
    public function bankTransferSubmit(Request $request, string $transaction)
    {
        $txn = PaymentTransaction::forCustomer(auth()->id())->where('reference', $transaction)->firstOrFail();
        abort_unless($txn->provider_key === 'bank_transfer', 404);
        abort_unless($txn->status === 'pending_verification', 422, 'This transfer is already under review.');
        $data = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'sender_name' => 'nullable|string|max:150',
            'sender_bank' => 'nullable|string|max:150',
            'transfer_reference' => 'nullable|string|max:120',
            'provider_transaction_id' => 'nullable|string|max:160',
            'transferred_at' => 'nullable|date|before_or_equal:today',
            'receipt' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
            'idempotency_key' => 'nullable|string|max:80',
        ]);
        abort_if(round((float) $data['amount'], 2) > round((float) $txn->original_amount, 2), 422, 'Amount cannot exceed the transaction amount.');
        $account = BankAccount::active()->findOrFail($data['bank_account_id']);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('bank-transfer-receipts', 'private');
        }
        $submission = ManualBankPayment::firstOrCreate(
            ['idempotency_key' => $data['idempotency_key'] ?? ('mbp_' . Str::uuid())],
            [
                'customer_id' => auth()->id(),
                'invoice_id' => $txn->invoice_id,
                'service_order_id' => $txn->service_order_id,
                'bank_account_id' => $account->id,
                'amount' => round((float) $data['amount'], 2),
                'currency' => $txn->original_currency,
                'sender_name' => $data['sender_name'] ?? null,
                'sender_bank' => $data['sender_bank'] ?? null,
                'transfer_reference' => $data['transfer_reference'] ?? null,
                'provider_transaction_id' => $data['provider_transaction_id'] ?? null,
                'transferred_at' => $data['transferred_at'] ?? null,
                'receipt_path' => $receiptPath,
                'status' => 'pending_verification',
                'payment_transaction_id' => $txn->id,
            ]
        );
        \App\Models\AuditLog::log('bank_transfer.submitted', 'manual_bank_payments', $submission, "Bank transfer {$submission->reference} submitted for {$txn->reference}.");
        auth()->user()->notify(new PaymentStatusNotification('manual_transfer_submitted', [
            'reference' => $submission->reference,
            'amount' => $submission->amount,
            'currency' => $submission->currency,
            'url' => route('portal.payments.show', $txn->reference),
        ]));
        // Ping finance staff with an in-app notification.
        foreach (\App\Models\User::whereIn('role', ['super_admin', 'admin', 'finance_manager'])->where('is_active', true)->limit(20)->get() as $staff) {
            $staff->notify(new PaymentStatusNotification('payment_pending', [
                'reference' => $submission->reference,
                'amount' => $submission->amount,
                'currency' => $submission->currency,
                'url' => route('admin.bank-transfers.show', $submission->id),
            ]));
        }
        return redirect()->route('portal.payments.show', $txn->reference)->with('success', 'Transfer submitted. It will show as paid only after finance verification.');
    }

    /** Customer payment history (own records only). */
    public function history()
    {
        $transactions = PaymentTransaction::forCustomer(auth()->id())->latestFirst()->paginate(20);
        return view('customer.payments.index', compact('transactions'));
    }

    public function historyShow(string $reference)
    {
        $txn = PaymentTransaction::forCustomer(auth()->id())->where('reference', $reference)->firstOrFail();
        $refunds = $txn->refunds()->latest('id')->get();
        return view('customer.payments.show', compact('txn', 'refunds'));
    }

    /** Request a refund (approval + execution stay with finance staff). */
    public function requestRefund(Request $request, string $reference, \App\Services\PaymentRefundService $refunds)
    {
        $txn = PaymentTransaction::forCustomer(auth()->id())->where('reference', $reference)->firstOrFail();
        abort_unless($txn->payment_id && $txn->status === 'paid', 422, 'Only settled payments can be refunded.');
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|min:10|max:1000',
        ]);
        $refund = $refunds->request($txn->payment, (float) $data['amount'], $txn->original_currency, $data['reason'], auth()->id(), $txn->id);
        auth()->user()->notify(new PaymentStatusNotification('refund_requested', [
            'refund_number' => $refund->refund_number,
            'amount' => $refund->amount,
            'currency' => $refund->currency,
        ]));
        return back()->with('success', 'Refund requested. Finance will review it.');
    }
}
