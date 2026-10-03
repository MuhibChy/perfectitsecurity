<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\InvoiceCreatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * WalletService — authoritative FWallet ledger + ERP posting.
 *
 * Rules: no balance change without a ledger row; every mutation runs
 * inside a DB transaction with row locks; negatives are rejected;
 * provider callbacks are idempotent on provider_event_id.
 */
class WalletService
{
    public static function reference(string $prefix = 'FW'): string
    {
        for ($i = 0; $i < 5; $i++) {
            $ref = $prefix . '-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            if (!WalletTransaction::where('transaction_reference', $ref)->exists()
                && !Wallet::where('wallet_reference', $ref)->exists()) {
                return $ref;
            }
        }
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(Str::random(8)) . time() % 100;
    }

    /** Idempotent wallet provisioning (safe under double-submit/refresh). */
    public function for(User $user, ?string $currency = null): Wallet
    {
        $reason = WalletEligibilityService::ineligibilityReason($user->fresh() ?? $user);
        if ($reason !== null) {
            throw new \RuntimeException('Wallet not eligible: ' . $reason);
        }
        $currency = strtoupper($currency ?? $user->preferred_currency ?? config('app.currency', 'USD') ?? 'USD');
        try {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'currency' => $currency],
                ['wallet_reference' => self::reference('WLT'), 'status' => 'active', 'balance' => 0]
            );
            if ($wallet->wasRecentlyCreated) {
                AuditLog::log('wallet.created', 'wallets', $wallet, "Wallet {$wallet->wallet_reference} created for customer {$user->email}.");
            }
            return $wallet;
        } catch (\Illuminate\Database\QueryException $e) {
            return Wallet::where('user_id', $user->id)->where('currency', $currency)->firstOrFail();
        }
    }

    /**
     * Core ledger writer. Must be called inside a DB transaction with the
     * wallet already locked. Throws on frozen wallet / negative result.
     */
    protected function postLocked(Wallet $wallet, array $data): WalletTransaction
    {
        if (!in_array($data['type'], WalletTransaction::TYPES, true)) {
            throw new \InvalidArgumentException('Unsupported wallet transaction type.');
        }
        if ($wallet->status !== 'active') {
            throw new \RuntimeException('Wallet is not active.');
        }
        $amount = round((float) $data['amount'], 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }
        $before = round((float) $wallet->balance, 2);
        $after = Wallet::isCredit($data['type']) ? round($before + $amount, 2) : round($before - $amount, 2);
        if ($after < 0) {
            throw new \RuntimeException('Insufficient wallet balance.');
        }
        $txn = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'transaction_reference' => $data['reference'] ?? self::reference(),
            'type' => $data['type'],
            'amount' => $amount,
            'currency' => $wallet->currency,
            'balance_before' => $before,
            'balance_after' => $after,
            'status' => $data['status'] ?? 'completed',
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'payment_provider' => $data['provider'] ?? null,
            'provider_transaction_id' => $data['provider_txn'] ?? null,
            'provider_event_id' => $data['provider_event'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'created_by' => $data['by'] ?? null,
        ]);
        if (($data['status'] ?? 'completed') === 'completed') {
            $wallet->balance = $after;
            $wallet->save();
        }
        return $txn;
    }

    /**
     * Credit a verified external deposit. Idempotent: the same provider
     * event always returns the original transaction, never a second one.
     */
    public function creditDeposit(Wallet $wallet, float $amount, string $provider, ?string $providerTxn, string $providerEvent, ?int $by = null, array $meta = []): array
    {
        return DB::transaction(function () use ($wallet, $amount, $provider, $providerTxn, $providerEvent, $by, $meta) {
            $existing = WalletTransaction::where('provider_event_id', $providerEvent)->first();
            if ($existing) {
                return ['transaction' => $existing, 'duplicate' => true];
            }
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            if (!WalletEligibilityService::canReceiveFunds($wallet)) {
                throw new \RuntimeException('Wallet cannot receive funds in its current state.');
            }
            $txn = $this->postLocked($wallet, [
                'type' => 'deposit', 'amount' => $amount, 'provider' => $provider,
                'provider_txn' => $providerTxn, 'provider_event' => $providerEvent,
                'description' => "Wallet deposit via {$provider}", 'metadata' => $meta, 'by' => $by,
            ]);
            app(FinancialService::class)->recordIncome(
                (float) $txn->amount, 'Wallet Deposit',
                "Wallet deposit {$txn->transaction_reference} ({$wallet->wallet_reference})",
                ['wallet_id' => $wallet->id, 'wallet_transaction_id' => $txn->id, 'customer_id' => $wallet->user_id]
            );
            AuditLog::log('wallet.deposit', 'wallets', $txn, "Wallet {$wallet->wallet_reference} credited {$wallet->currency} {$txn->amount} via {$provider}.");
            return ['transaction' => $txn, 'duplicate' => false];
        });
    }

    /** Reserve a pending (unverified) top-up; completion moves the money. */
    public function reserveTopUp(Wallet $wallet, float $amount, string $provider, string $providerEvent, ?int $by = null, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $provider, $providerEvent, $by, $meta) {
            $existing = WalletTransaction::where('provider_event_id', $providerEvent)->first();
            if ($existing) return $existing;
            $amount = round($amount, 2);
            if ($amount <= 0) throw new \InvalidArgumentException('Amount must be positive.');
            if ($wallet->status !== 'active') throw new \RuntimeException('Wallet is not active.');
            if (!WalletEligibilityService::canReceiveFunds($wallet)) {
                throw new \RuntimeException('Wallet cannot receive funds in its current state.');
            }
            return WalletTransaction::create([
                'wallet_id' => $wallet->id, 'transaction_reference' => self::reference(),
                'type' => 'deposit', 'amount' => $amount, 'currency' => $wallet->currency,
                'balance_before' => (float) $wallet->balance, 'balance_after' => (float) $wallet->balance,
                'status' => 'pending', 'payment_provider' => $provider, 'provider_event_id' => $providerEvent,
                'description' => "Pending wallet top-up via {$provider}", 'metadata' => $meta, 'created_by' => $by,
            ]);
        });
    }

    /** Complete a reserved top-up after server-side provider verification. */
    public function completeTopUp(WalletTransaction $pending, ?string $providerTxn = null): array
    {
        return DB::transaction(function () use ($pending, $providerTxn) {
            $pending = WalletTransaction::lockForUpdate()->findOrFail($pending->id);
            if ($pending->status === 'completed') {
                return ['transaction' => $pending, 'duplicate' => true];
            }
            if ($pending->status !== 'pending') {
                throw new \RuntimeException('Top-up cannot be completed from status ' . $pending->status . '.');
            }
            $wallet = Wallet::lockForUpdate()->findOrFail($pending->wallet_id);
            if (!WalletEligibilityService::canReceiveFunds($wallet)) {
                throw new \RuntimeException('Wallet cannot receive funds in its current state.');
            }
            $before = round((float) $wallet->balance, 2);
            $after = round($before + (float) $pending->amount, 2);
            $pending->update([
                'status' => 'completed', 'balance_before' => $before, 'balance_after' => $after,
                'provider_transaction_id' => $providerTxn ?? $pending->provider_transaction_id,
            ]);
            $wallet->balance = $after;
            $wallet->save();
            app(FinancialService::class)->recordIncome(
                (float) $pending->amount, 'Wallet Deposit',
                "Wallet deposit {$pending->transaction_reference} ({$wallet->wallet_reference})",
                ['wallet_id' => $wallet->id, 'wallet_transaction_id' => $pending->id, 'customer_id' => $wallet->user_id]
            );
            AuditLog::log('wallet.deposit', 'wallets', $pending, "Wallet {$wallet->wallet_reference} credited {$wallet->currency} {$pending->amount} via {$pending->payment_provider}.");
            return ['transaction' => $pending->fresh(), 'duplicate' => false];
        });
    }

    /**
     * Pay an invoice from the wallet (partial or full). Mirrors the
     * platform's payment posting rules: Payment + invoice + order +
     * receipt + finance income + audit + notification, atomically.
     */
    public function payInvoice(Wallet $wallet, Invoice $invoice, float $amount, ?int $by = null): array
    {
        return DB::transaction(function () use ($wallet, $invoice, $amount, $by) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            if ((int) $wallet->user_id !== (int) $invoice->customer_id) {
                throw new \RuntimeException('Wallet does not belong to the invoice customer.');
            }
            if (!WalletEligibilityService::canSpend($wallet)) {
                throw new \RuntimeException('Wallet cannot make payments in its current state.');
            }
            if (strtoupper($wallet->currency) !== strtoupper($invoice->currency ?? 'USD')) {
                throw new \RuntimeException('Wallet currency does not match the invoice currency.');
            }
            if (!in_array($invoice->status, ['sent', 'viewed', 'overdue', 'partially_paid'], true)) {
                throw new \RuntimeException('Invoice is not payable in its current status.');
            }
            $due = round((float) $invoice->amount_due, 2);
            $amount = round($amount, 2);
            if ($due <= 0) {
                throw new \RuntimeException('Invoice has no outstanding balance.');
            }
            if ($amount <= 0 || $amount > $due) {
                throw new \InvalidArgumentException('Amount must be between 0 and the outstanding due.');
            }
            if ($amount > round((float) $wallet->balance, 2)) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }

            $txn = $this->postLocked($wallet, [
                'type' => 'invoice_payment', 'amount' => $amount,
                'source_type' => Invoice::class, 'source_id' => $invoice->id,
                'description' => "Wallet payment for invoice {$invoice->invoice_number}",
                'metadata' => ['invoice_id' => $invoice->id], 'by' => $by,
            ]);

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'service_order_id' => $invoice->service_order_id,
                'amount' => $amount,
                'currency' => $invoice->currency ?? 'USD',
                'status' => 'completed',
                'payment_method' => 'wallet',
                'gateway' => 'wallet',
                'transaction_id' => $txn->transaction_reference,
                'paid_at' => now(),
                'notes' => "Paid from wallet {$wallet->wallet_reference}",
            ]);

            $paid = round((float) $invoice->amount_paid + $amount, 2);
            $newDue = max(0, round((float) $invoice->total - $paid, 2));
            $wasPaid = $invoice->status === 'paid';
            $invoice->update([
                'amount_paid' => $paid, 'amount_due' => $newDue,
                'status' => $newDue <= 0 ? 'paid' : 'partially_paid',
                'paid_at' => $newDue <= 0 ? now() : $invoice->paid_at,
            ]);

            if ($invoice->service_order_id) {
                $order = \App\Models\ServiceOrder::lockForUpdate()->find($invoice->service_order_id);
                if ($order) {
                    $newPaid = round((float) $order->amount_paid + $amount, 2);
                    $orderDue = max(0, round((float) $order->total - $newPaid, 2));
                    $order->amount_paid = $newPaid;
                    $order->amount_due = $orderDue;
                    $minDeposit = round((float) $order->total * (\App\Services\ServiceOrderWorkflowService::MIN_DEPOSIT_PERCENTAGE / 100), 2);
                    if ($order->manager_override_at) {
                        $order->payment_authorization = 'manager_override';
                    } elseif ($orderDue == 0) {
                        $order->payment_authorization = 'fully_paid';
                    } elseif ($newPaid >= $minDeposit) {
                        $order->payment_authorization = 'ready_to_start';
                    } else {
                        $order->payment_authorization = 'deposit_required';
                    }
                    if ($order->task_completed_at && $orderDue == 0) {
                        $order->status = 'financially_completed';
                    } elseif (in_array($order->payment_authorization, ['ready_to_start', 'fully_paid'], true)
                        && in_array($order->status, ['confirmed', 'awaiting_payment'], true)) {
                        $order->status = 'ready_to_start';
                    }
                    $order->save();
                    \App\Models\Receipt::create([
                        'payment_id' => $payment->id, 'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->customer_id, 'service_order_id' => $order->id,
                        'amount' => $amount, 'remaining_balance' => $orderDue,
                        'currency' => $payment->currency, 'issued_at' => now(),
                    ]);
                    // Keep staged payment schedule rows in sync (FIFO allocation).
                    app(\App\Services\OrderPaymentAllocator::class)->allocate($order->fresh(), $payment->fresh(), $amount);
                }
            }

            app(FinancialService::class)->recordIncome(
                $amount, 'Customer Payment',
                "Wallet payment {$payment->payment_number} for invoice {$invoice->invoice_number}",
                ['invoice_id' => $invoice->id, 'payment_id' => $payment->id, 'customer_id' => $invoice->customer_id, 'wallet_transaction_id' => $txn->id]
            );
            AuditLog::log('wallet.payment', 'wallets', $txn, "Wallet {$wallet->wallet_reference} paid {$invoice->currency} {$amount} to invoice {$invoice->invoice_number}.");

            if (!$wasPaid && $newDue <= 0 && $invoice->customer) {
                $invoice->customer->notify(new InvoiceCreatedNotification($invoice->fresh(), 'paid'));
            }

            return ['transaction' => $txn, 'payment' => $payment, 'invoice' => $invoice->fresh()];
        });
    }

    /** Refund a wallet invoice-payment back into the wallet (linked, once). */
    public function refundWalletPayment(WalletTransaction $original, ?int $by = null, string $reason = ''): array
    {
        return DB::transaction(function () use ($original, $by, $reason) {
            $original = WalletTransaction::lockForUpdate()->findOrFail($original->id);
            if ($original->type !== 'invoice_payment' || $original->status !== 'completed') {
                throw new \RuntimeException('Only completed wallet payments can be refunded.');
            }
            if (WalletTransaction::where('type', 'refund')->where('source_type', WalletTransaction::class)->where('source_id', $original->id)->where('status', 'completed')->exists()) {
                throw new \RuntimeException('This wallet payment has already been refunded.');
            }
            $wallet = Wallet::lockForUpdate()->findOrFail($original->wallet_id);
            $payment = Payment::where('transaction_id', $original->transaction_reference)->where('status', 'completed')->lockForUpdate()->first();
            if (!$payment) {
                throw new \RuntimeException('Linked payment not found.');
            }
            $amount = round((float) $original->amount, 2);
            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);

            $refundTxn = $this->postLocked($wallet, [
                'type' => 'refund', 'amount' => $amount,
                'source_type' => WalletTransaction::class, 'source_id' => $original->id,
                'description' => "Refund of {$original->transaction_reference}. {$reason}",
                'metadata' => ['original_transaction_id' => $original->id, 'reason' => $reason], 'by' => $by,
            ]);
            $refund = Payment::create([
                'payment_number' => 'RFD-' . strtoupper(Str::random(8)),
                'invoice_id' => $invoice->id, 'customer_id' => $payment->customer_id,
                'service_order_id' => $payment->service_order_id, 'amount' => $amount,
                'currency' => $payment->currency, 'status' => 'refunded',
                'payment_method' => 'wallet', 'gateway' => 'wallet',
                'transaction_id' => $refundTxn->transaction_reference,
                'notes' => "Wallet refund for {$payment->payment_number}. {$reason}", 'paid_at' => now(),
            ]);
            $invoice->amount_paid = round((float) $invoice->amount_paid - $amount, 2);
            $invoice->amount_due = round((float) $invoice->total - (float) $invoice->amount_paid, 2);
            if ($invoice->status === 'paid') {
                $invoice->status = 'partially_paid';
                $invoice->paid_at = null;
            }
            $invoice->save();
            if ($payment->service_order_id && ($order = \App\Models\ServiceOrder::lockForUpdate()->find($payment->service_order_id))) {
                $order->amount_paid = round((float) $order->amount_paid - $amount, 2);
                $order->amount_due = round((float) $order->total - (float) $order->amount_paid, 2);
                if ($order->payment_authorization === 'fully_paid') {
                    $order->payment_authorization = 'deposit_required';
                }
                $order->save();
            }
            app(FinancialService::class)->recordRefund(
                $amount, "Wallet refund {$refund->payment_number} ({$payment->payment_number})",
                ['payment_id' => $payment->id, 'refund_id' => $refund->id, 'invoice_id' => $invoice->id]
            );
            AuditLog::log('wallet.refund', 'wallets', $refundTxn, "Wallet {$wallet->wallet_reference} refunded {$wallet->currency} {$amount} for {$original->transaction_reference}. Reason: {$reason}");

            return ['transaction' => $refundTxn, 'refund' => $refund, 'invoice' => $invoice->fresh()];
        });
    }

    /** Controlled manual adjustment (mandatory reason, audited). */
    public function adjust(Wallet $wallet, string $direction, float $amount, string $reason, ?int $by = null): WalletTransaction
    {
        if (!in_array($direction, ['credit', 'debit'], true)) {
            throw new \InvalidArgumentException('Direction must be credit or debit.');
        }
        if (trim($reason) === '') {
            throw new \InvalidArgumentException('A reason is mandatory for adjustments.');
        }
        return DB::transaction(function () use ($wallet, $direction, $amount, $reason, $by) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            $txn = $this->postLocked($wallet, [
                'type' => $direction === 'credit' ? 'credit_adjustment' : 'debit_adjustment',
                'amount' => $amount, 'description' => "Manual adjustment: {$reason}",
                'metadata' => ['reason' => $reason], 'by' => $by,
            ]);
            AuditLog::log('wallet.adjustment', 'wallets', $txn, "Wallet {$wallet->wallet_reference} {$direction} {$wallet->currency} {$txn->amount}. Reason: {$reason}");
            return $txn;
        });
    }

    public function freeze(Wallet $wallet, ?int $by = null, string $reason = ''): Wallet
    {
        return DB::transaction(function () use ($wallet, $by, $reason) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            $wallet->update(['status' => 'frozen']);
            AuditLog::log('wallet.freeze', 'wallets', $wallet, "Wallet {$wallet->wallet_reference} frozen. Reason: {$reason}");
            return $wallet->fresh();
        });
    }

    public function unfreeze(Wallet $wallet, ?int $by = null): Wallet
    {
        return DB::transaction(function () use ($wallet, $by) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            $wallet->update(['status' => 'active']);
            AuditLog::log('wallet.unfreeze', 'wallets', $wallet, "Wallet {$wallet->wallet_reference} reactivated.");
            return $wallet->fresh();
        });
    }

    /**
     * Administrative ownership correction (exceptional). Requires an
     * eligible customer target and a mandatory reason. The ledger is
     * untouched: every historical row keeps pointing at the same wallet.
     */
    public function correctOwner(Wallet $wallet, User $newOwner, string $reason, ?int $by = null): Wallet
    {
        if (trim($reason) === '') {
            throw new \InvalidArgumentException('A reason is mandatory for ownership correction.');
        }
        $eligible = WalletEligibilityService::ineligibilityReason($newOwner->fresh() ?? $newOwner);
        if ($eligible !== null) {
            throw new \RuntimeException('New owner not eligible: ' . $eligible);
        }
        return DB::transaction(function () use ($wallet, $newOwner, $reason, $by) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);
            $oldOwnerId = (int) $wallet->user_id;
            if ($oldOwnerId === (int) $newOwner->id) {
                throw new \RuntimeException('Wallet already belongs to this customer.');
            }
            $wallet->update(['user_id' => $newOwner->id]);
            AuditLog::log('wallet.owner_corrected', 'wallets', $wallet,
                "Wallet {$wallet->wallet_reference} ownership corrected from user #{$oldOwnerId} to user #{$newOwner->id} ({$newOwner->email}). Reason: {$reason}. Ledger preserved.",
                ['user_id' => $oldOwnerId], ['user_id' => (int) $newOwner->id]);
            return $wallet->fresh();
        });
    }
}
