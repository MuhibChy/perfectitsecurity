<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService
{
    public function isConfigured(): bool
    {
        return !empty(config('services.stripe.secret'));
    }

    public function configure(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Currencies Stripe can actually charge (verified against Stripe docs).
     * BDT is deliberately absent: Stripe does not support it — those
     * invoices stay on manual/bank-transfer rails. Never add a code here
     * without verifying provider support first.
     */
    public const STRIPE_SUPPORTED = ['AED', 'SAR', 'QAR', 'KWD', 'BHD', 'OMR', 'JOD', 'EUR', 'GBP', 'USD'];

    public static function supportsCurrency(string $code): bool
    {
        return in_array(strtoupper($code), self::STRIPE_SUPPORTED, true);
    }

    /**
     * Create a Stripe Checkout session for an invoice balance.
     */
    public function createInvoiceCheckout(Invoice $invoice, string $successUrl, string $cancelUrl): ?Session
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Honest capability gate: unsupported currencies (e.g. BDT) never
        // mint a session; callers degrade to manual payment rails.
        if (!self::supportsCurrency($invoice->currency ?? 'USD')) {
            Log::info('Stripe checkout skipped: currency unsupported', ['invoice' => $invoice->id, 'currency' => $invoice->currency]);
            return null;
        }

        $this->configure();
        $amountDue = (float) $invoice->amount_due;
        if ($amountDue <= 0) {
            throw new \RuntimeException('Invoice has no outstanding balance.');
        }

        $currency = strtolower($invoice->currency ?? 'usd');
        $customer = $invoice->customer;

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $invoice->id,
            'customer_email' => $customer?->email,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
            ],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => (int) round($amountDue * 100),
                    'product_data' => [
                        'name' => 'Invoice ' . $invoice->invoice_number,
                        'description' => 'Payment for invoice ' . $invoice->invoice_number,
                    ],
                ],
            ]],
        ]);

        $invoice->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return $session;
    }

    public function handleWebhook(string $payload, ?string $signature): array
    {
        $secret = config('services.stripe.webhook_secret');
        if ($secret) {
            $event = Webhook::constructEvent($payload, $signature ?? '', $secret);
        } else {
            // Fail closed in production: unsigned webhooks must never move money.
            abort_if(app()->isProduction(), 500, 'Stripe webhook secret is not configured.');
            Log::warning('Stripe webhook without signature verification (non-production only).');
            // Local/dev/test fallback when webhook secret is unset — still parse JSON safely.
            $event = json_decode($payload);
            if (!$event || empty($event->type)) {
                throw new \InvalidArgumentException('Invalid Stripe payload.');
            }
        }

        $type = is_array($event) ? ($event['type'] ?? null) : ($event->type ?? null);
        $data = is_array($event) ? ($event['data']['object'] ?? []) : ($event->data->object ?? null);

        if ($type === 'checkout.session.completed') {
            // Wallet top-ups carry purpose=wallet_topup metadata and settle
            // through the wallet ledger (idempotent), never the invoice path.
            $metaProbe = is_array($data) ? ($data['metadata'] ?? []) : ($data->metadata ?? null);
            $purpose = is_object($metaProbe) ? ($metaProbe->purpose ?? null) : ($metaProbe['purpose'] ?? null);
            if ($purpose === 'wallet_topup') {
                return $this->markWalletTopUpFromSession($data);
            }
            return $this->markInvoicePaidFromSession($data);
        }

        if ($type === 'charge.refunded') {
            return $this->syncExternalRefund($data);
        }

        return ['handled' => false, 'type' => $type];
    }

    /**
     * Settle a wallet top-up Checkout session. Verified fields only
     * (paid status, wallet ownership, currency); idempotent on the Stripe
     * session id so duplicate deliveries credit exactly once.
     */
    protected function markWalletTopUpFromSession($session): array
    {
        $get = fn ($k) => is_object($session) ? ($session->$k ?? null) : ($session[$k] ?? null);
        $meta = $get('metadata');
        $metaGet = fn ($k) => is_object($meta) ? ($meta->$k ?? null) : (is_array($meta) ? ($meta[$k] ?? null) : null);
        if (($get('payment_status') ?? null) !== 'paid') {
            Log::warning('Stripe webhook: wallet session not paid', ['session' => $get('id')]);
            return ['handled' => false, 'reason' => 'session_not_paid'];
        }
        $wallet = \App\Models\Wallet::find($metaGet('wallet_id'));
        if (!$wallet) {
            Log::warning('Stripe webhook: wallet not found', ['session' => $get('id')]);
            return ['handled' => false, 'reason' => 'wallet_not_found'];
        }
        if ((int) $metaGet('user_id') !== (int) $wallet->user_id) {
            Log::warning('Stripe webhook: wallet owner mismatch', ['wallet' => $wallet->id]);
            return ['handled' => false, 'reason' => 'owner_mismatch'];
        }
        $currency = strtoupper($get('currency') ?? 'usd');
        if ($currency !== strtoupper($wallet->currency)) {
            Log::warning('Stripe webhook: wallet currency mismatch', ['wallet' => $wallet->id]);
            return ['handled' => false, 'reason' => 'currency_mismatch'];
        }
        $amount = round((($get('amount_total') ?? 0) / 100), 2);
        if ($amount <= 0) {
            return ['handled' => false, 'reason' => 'invalid_amount'];
        }
        $intent = $get('payment_intent');
        $result = app(\App\Services\WalletService::class)->creditDeposit(
            $wallet, $amount, 'stripe',
            is_string($intent) ? $intent : null,
            'stripe-session:' . $get('id'),
            null,
            ['stripe_session' => $get('id')]
        );
        return ['handled' => true, 'wallet_id' => $wallet->id, 'transaction_id' => $result['transaction']->id, 'duplicate' => $result['duplicate']];
    }

    protected function markInvoicePaidFromSession($session): array
    {
        $sessionId = is_object($session) ? ($session->id ?? null) : ($session['id'] ?? null);
        $metadata = is_object($session) ? ($session->metadata ?? null) : ($session['metadata'] ?? []);
        $invoiceId = is_object($metadata) ? ($metadata->invoice_id ?? null) : ($metadata['invoice_id'] ?? null);
        $metaCustomerId = is_object($metadata) ? ($metadata->customer_id ?? null) : ($metadata['customer_id'] ?? null);
        $paymentIntent = is_object($session) ? ($session->payment_intent ?? null) : ($session['payment_intent'] ?? null);
        $paymentStatus = is_object($session) ? ($session->payment_status ?? null) : ($session['payment_status'] ?? null);
        $customerEmail = is_object($session) ? ($session->customer_email ?? $session->customer_details->email ?? null) : ($session['customer_email'] ?? $session['customer_details']['email'] ?? null);
        $amountTotal = is_object($session) ? (($session->amount_total ?? 0) / 100) : (($session['amount_total'] ?? 0) / 100);
        $currency = strtoupper(is_object($session) ? ($session->currency ?? 'usd') : ($session['currency'] ?? 'usd'));

        // Only completed, paid sessions move money. Real Stripe events always
        // carry payment_status; its absence (or any non-paid value) is forged
        // or premature — acknowledge without any state change.
        if ($paymentStatus !== 'paid') {
            Log::warning('Stripe webhook: session not paid', ['session' => $sessionId, 'payment_status' => $paymentStatus]);
            return ['handled' => false, 'reason' => 'session_not_paid'];
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($sessionId, $invoiceId, $metaCustomerId, $paymentIntent, $amountTotal, $currency, $customerEmail) {
            $invoice = $invoiceId
                ? Invoice::lockForUpdate()->find($invoiceId)
                : ($sessionId ? Invoice::lockForUpdate()->where('stripe_checkout_session_id', $sessionId)->first() : null);

            if (!$invoice) {
                Log::warning('Stripe webhook: invoice not found', ['session' => $sessionId]);
                return ['handled' => false, 'reason' => 'invoice_not_found'];
            }

            // Idempotency: Stripe retries the same event; never record the same
            // checkout session twice (guards partial-payment double counting).
            $existing = $sessionId
                ? Payment::where('stripe_checkout_session_id', (string) $sessionId)->first()
                : null;
            if ($existing) {
                Log::info('Stripe webhook: duplicate session ignored', ['session' => $sessionId, 'payment_id' => $existing->id]);
                return ['handled' => true, 'reason' => 'duplicate_session', 'invoice_id' => $invoice->id, 'payment_id' => $existing->id];
            }

            if ($invoice->status === 'paid') {
                return ['handled' => true, 'reason' => 'already_paid', 'invoice_id' => $invoice->id];
            }

            // Only issued invoices accept money — never drafts, cancelled,
            // or refunded ones (mirrors the manual finance path).
            if (!in_array($invoice->status, ['sent', 'viewed', 'overdue', 'partially_paid'], true)) {
                Log::warning('Stripe webhook: invoice not payable', ['invoice' => $invoice->id, 'status' => $invoice->status]);
                return ['handled' => false, 'reason' => 'invoice_not_payable'];
            }

            // Cross-checks against the authoritative invoice. Any mismatch is
            // acknowledged without state change (no retry storm, no money moved).
            if ($metaCustomerId && (int) $metaCustomerId !== (int) $invoice->customer_id) {
                Log::warning('Stripe webhook: customer mismatch', ['invoice' => $invoice->id]);
                return ['handled' => false, 'reason' => 'customer_mismatch'];
            }
            if ($customerEmail && $invoice->customer && strcasecmp(trim($customerEmail), trim($invoice->customer->email)) !== 0) {
                Log::warning('Stripe webhook: customer email mismatch', ['invoice' => $invoice->id]);
                return ['handled' => false, 'reason' => 'customer_mismatch'];
            }
            if (strtoupper($invoice->currency ?? 'USD') !== $currency) {
                Log::warning('Stripe webhook: currency mismatch', ['invoice' => $invoice->id, 'currency' => $currency]);
                return ['handled' => false, 'reason' => 'currency_mismatch'];
            }
            if (abs(round($amountTotal, 2) - round((float) $invoice->amount_due, 2)) > 0.009) {
                Log::warning('Stripe webhook: amount mismatch', ['invoice' => $invoice->id, 'amount' => $amountTotal, 'due' => $invoice->amount_due]);
                return ['handled' => false, 'reason' => 'amount_mismatch'];
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'service_order_id' => $invoice->service_order_id,
                'amount' => round((float) $invoice->amount_due, 2),
                'currency' => $invoice->currency ?? 'USD',
                'status' => 'completed',
                'payment_method' => 'card',
                'gateway' => 'stripe',
                'transaction_id' => is_string($paymentIntent) ? $paymentIntent : (string) $paymentIntent,
                'stripe_checkout_session_id' => $sessionId,
                'stripe_payment_intent_id' => is_string($paymentIntent) ? $paymentIntent : null,
                'paid_at' => now(),
                'notes' => 'Paid via Stripe Checkout',
            ]);

            $paid = round((float) $invoice->amount_paid + (float) $payment->amount, 2);
            $due = max(0, round((float) $invoice->total - $paid, 2));
            $wasPaid = $invoice->status === 'paid';
            $invoice->update([
                'amount_paid' => $paid,
                'amount_due' => $due,
                'status' => $due <= 0 ? 'paid' : 'partially_paid',
                'paid_at' => $due <= 0 ? now() : $invoice->paid_at,
                'stripe_payment_intent_id' => is_string($paymentIntent) ? $paymentIntent : $invoice->stripe_payment_intent_id,
                'stripe_checkout_session_id' => $due <= 0 ? null : $invoice->stripe_checkout_session_id,
            ]);

            // Keep the service order ledger in sync (same rules as manual pay).
            if ($invoice->service_order_id) {
                $order = \App\Models\ServiceOrder::lockForUpdate()->find($invoice->service_order_id);
                if ($order) {
                    $newPaid = round((float) $order->amount_paid + (float) $payment->amount, 2);
                    $newDue = max(0, round((float) $order->total - $newPaid, 2));
                    $order->amount_paid = $newPaid;
                    $order->amount_due = $newDue;
                    $minDeposit = round((float) $order->total * (\App\Services\ServiceOrderWorkflowService::MIN_DEPOSIT_PERCENTAGE / 100), 2);
                    if ($order->manager_override_at) {
                        $order->payment_authorization = 'manager_override';
                    } elseif ($newDue == 0) {
                        $order->payment_authorization = 'fully_paid';
                    } elseif ($newPaid >= $minDeposit) {
                        $order->payment_authorization = 'ready_to_start';
                    } else {
                        $order->payment_authorization = 'deposit_required';
                    }
                    if ($order->task_completed_at && $newDue == 0) {
                        $order->status = 'financially_completed';
                    } elseif (in_array($order->payment_authorization, ['ready_to_start', 'fully_paid'], true)
                        && in_array($order->status, ['confirmed', 'awaiting_payment'], true)) {
                        $order->status = 'ready_to_start';
                    }
                    $order->save();

                    \App\Models\Receipt::create([
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->customer_id,
                        'service_order_id' => $order->id,
                        'amount' => $payment->amount,
                        'remaining_balance' => $newDue,
                        'currency' => $payment->currency,
                        'issued_at' => now(),
                    ]);

                    // Keep staged payment schedule rows in sync (FIFO allocation).
                    app(\App\Services\OrderPaymentAllocator::class)->allocate($order->fresh(), $payment->fresh(), (float) $payment->amount);
                }
            }

            // Ledger + audit, like the manual finance path.
            app(\App\Services\FinancialService::class)->recordIncome(
                (float) $payment->amount,
                'Customer Payment',
                "Stripe payment {$payment->payment_number} for invoice {$invoice->invoice_number}",
                ['invoice_id' => $invoice->id, 'payment_id' => $payment->id, 'customer_id' => $invoice->customer_id]
            );
            \App\Services\AuditService::log('create', 'payments', $payment, "Stripe webhook payment {$payment->payment_number} confirmed.");

            if (!$wasPaid && $due <= 0 && $invoice->customer) {
                $invoice->customer->notify(new \App\Notifications\InvoiceCreatedNotification($invoice->fresh(), 'paid'));
            }

            return ['handled' => true, 'invoice_id' => $invoice->id, 'payment_id' => $payment->id];
        });
    }

    /**
     * Mirror a refund performed in the Stripe Dashboard into the local
     * ledger. Only the unrecorded remainder is booked, so double delivery
     * of the event (or a prior in-app refund) can never double-count.
     */
    protected function syncExternalRefund($charge): array
    {
        $intentId = is_object($charge) ? ($charge->payment_intent ?? null) : ($charge['payment_intent'] ?? null);
        $refundedTotal = is_object($charge) ? (($charge->amount_refunded ?? 0) / 100) : (($charge['amount_refunded'] ?? 0) / 100);
        if (!$intentId || $refundedTotal <= 0) {
            return ['handled' => false, 'reason' => 'not_a_refund'];
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($intentId, $refundedTotal) {
            $payment = Payment::lockForUpdate()
                ->where('stripe_payment_intent_id', (string) $intentId)
                ->where('status', 'completed')
                ->first();
            if (!$payment) {
                return ['handled' => false, 'reason' => 'payment_not_found'];
            }
            $unrecorded = round($refundedTotal - (float) $payment->refunded_amount, 2);
            if ($unrecorded <= 0) {
                return ['handled' => true, 'reason' => 'already_recorded', 'payment_id' => $payment->id];
            }
            $unrecorded = min($unrecorded, round((float) $payment->amount - (float) $payment->refunded_amount, 2));

            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);
            $refund = Payment::create([
                'payment_number' => 'RFD-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'invoice_id' => $invoice->id,
                'customer_id' => $payment->customer_id,
                'service_order_id' => $payment->service_order_id,
                'amount' => $unrecorded,
                'currency' => $payment->currency,
                'status' => 'refunded',
                'payment_method' => $payment->payment_method,
                'gateway' => 'stripe',
                'transaction_id' => 'STRIPE-DASHBOARD-REFUND-' . $payment->id,
                'notes' => "External Stripe reversal for {$payment->payment_number}",
                'paid_at' => now(),
            ]);
            $payment->increment('refunded_amount', $unrecorded);
            $invoice->amount_paid = round((float) $invoice->amount_paid - $unrecorded, 2);
            $invoice->amount_due = round((float) $invoice->total - (float) $invoice->amount_paid, 2);
            if ($invoice->status === 'paid') {
                $invoice->status = 'partially_paid';
                $invoice->paid_at = null;
            }
            $invoice->save();
            if ($payment->service_order_id && ($order = \App\Models\ServiceOrder::lockForUpdate()->find($payment->service_order_id))) {
                $order->amount_paid = round((float) $order->amount_paid - $unrecorded, 2);
                $order->amount_due = round((float) $order->total - (float) $order->amount_paid, 2);
                if ($order->payment_authorization === 'fully_paid') {
                    $order->payment_authorization = 'deposit_required';
                }
                $order->save();
            }
            app(\App\Services\FinancialService::class)->recordRefund(
                $unrecorded,
                "External Stripe reversal {$refund->payment_number} ({$payment->payment_number})",
                ['payment_id' => $payment->id, 'refund_id' => $refund->id, 'invoice_id' => $invoice->id]
            );
            \App\Services\AuditService::log('refund', 'payments', $refund, "External Stripe reversal of {$payment->currency} {$unrecorded} against {$payment->payment_number}.");

            return ['handled' => true, 'reason' => 'external_refund_synced', 'payment_id' => $payment->id, 'refund_id' => $refund->id];
        });
    }
}
