<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ManualBankPayment;
use App\Models\PaymentTransaction;
use App\Notifications\PaymentStatusNotification;
use App\Services\PaymentSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Finance verification queue for customer bank-transfer submissions.
 * VERIFY settles through the authoritative funnel (ledger + receipt);
 * REJECT closes with a reason. Receipt uploads are never proof of payment.
 */
class ManualBankPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = ManualBankPayment::with(['customer', 'invoice', 'bankAccount'])->latest('id');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $payments = $query->paginate(20)->withQueryString();
        $pendingCount = ManualBankPayment::where('status', 'pending_verification')->count();

        return view('admin.payments.bank-transfers.index', compact('payments', 'pendingCount'));
    }

    public function show(ManualBankPayment $bankTransfer)
    {
        $bankTransfer->load(['customer', 'invoice', 'serviceOrder', 'bankAccount', 'paymentTransaction']);

        return view('admin.payments.bank-transfers.show', ['transfer' => $bankTransfer]);
    }

    public function receipt(ManualBankPayment $bankTransfer)
    {
        abort_unless($bankTransfer->receipt_path && Storage::disk('private')->exists($bankTransfer->receipt_path), 404);

        return Storage::disk('private')->download($bankTransfer->receipt_path);
    }

    public function verify(Request $request, ManualBankPayment $bankTransfer, PaymentSettlementService $settlement)
    {
        abort_unless($bankTransfer->status === 'pending_verification', 422, 'Already reviewed.');
        $data = $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        return DB::transaction(function () use ($bankTransfer, $data, $settlement) {
            $bankTransfer = ManualBankPayment::lockForUpdate()->findOrFail($bankTransfer->id);
            abort_unless($bankTransfer->status === 'pending_verification', 422, 'Already reviewed.');

            $txn = $bankTransfer->paymentTransaction()->lockForUpdate()->first();
            if (! $txn) {
                // Older submission without a checkout transaction: create the
                // internal record first so settlement stays single-sourced.
                $txn = PaymentTransaction::create([
                    'customer_id' => $bankTransfer->customer_id,
                    'service_order_id' => $bankTransfer->service_order_id,
                    'invoice_id' => $bankTransfer->invoice_id,
                    'provider_key' => 'bank_transfer',
                    'payment_method' => 'bank_transfer',
                    'original_amount' => $bankTransfer->amount,
                    'original_currency' => $bankTransfer->currency,
                    'settlement_currency' => $bankTransfer->currency,
                    'provider_currency' => $bankTransfer->currency,
                    'provider_amount' => $bankTransfer->amount,
                    'gross_amount' => $bankTransfer->amount,
                    'net_amount' => $bankTransfer->amount,
                    'status' => 'pending_verification',
                    'metadata' => ['manual_bank_payment_id' => $bankTransfer->id],
                    'created_by' => auth()->id(),
                ]);
                $bankTransfer->payment_transaction_id = $txn->id;
                $bankTransfer->save();
            }

            // Customer gets 'payment_verified' below; the verifying finance
            // staff member is the actor, so skip the generic receipt pings.
            $result = $settlement->settle($txn->fresh(), auth()->id(), null, false);
            $bankTransfer->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);
            AuditLog::log('bank_transfer.verified', 'manual_bank_payments', $bankTransfer, "Transfer {$bankTransfer->reference} verified; payment {$result['payment']->payment_number}.");
            $bankTransfer->customer->notify(new PaymentStatusNotification('payment_verified', [
                'reference' => $bankTransfer->reference,
                'amount' => $bankTransfer->amount,
                'currency' => $bankTransfer->currency,
            ]));

            return redirect()->route('admin.bank-transfers.show', $bankTransfer)->with('success', 'Transfer verified and payment recorded.');
        });
    }

    public function reject(Request $request, ManualBankPayment $bankTransfer)
    {
        abort_unless($bankTransfer->status === 'pending_verification', 422, 'Already reviewed.');
        $data = $request->validate(['admin_notes' => 'required|string|min:5|max:2000']);
        $bankTransfer->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'admin_notes' => $data['admin_notes'],
        ]);
        if ($bankTransfer->payment_transaction_id) {
            $txn = PaymentTransaction::find($bankTransfer->payment_transaction_id);
            if ($txn && ! $txn->isTerminal()) {
                $txn->failure_reason = 'Bank transfer rejected: '.$data['admin_notes'];
                $txn->save();
                $txn->transitionTo('rejected');
            }
        }
        AuditLog::log('bank_transfer.rejected', 'manual_bank_payments', $bankTransfer, "Transfer {$bankTransfer->reference} rejected.");

        return redirect()->route('admin.bank-transfers.show', $bankTransfer)->with('success', 'Transfer rejected with reason sent to the customer.');
    }
}
