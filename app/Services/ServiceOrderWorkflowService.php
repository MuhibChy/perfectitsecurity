<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderPriceRevision;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceOrderWorkflowService
{
    public const MIN_DEPOSIT_PERCENTAGE = 30.0; // 30% minimum deposit

    /**
     * Create order from Customer (either fixed order or price discussion).
     */
    public function createCustomerOrder(array $data, User $customer): ServiceOrder
    {
        return DB::transaction(function () use ($data, $customer) {
            // Only active/orderable catalogue services may be ordered, even if
            // a service ID is submitted directly. Frontend scoping is not a control.
            $service = Service::where('is_active', true)->findOrFail($data['service_id']);
            $negotiate = !empty($data['negotiate']) || !empty($data['custom_quote']);

            // Forged-total guard: a customer-supplied proposed_price that differs
            // from the catalogue price is a price discussion, never an instantly
            // confirmed order. Without this, POSTing proposed_price=1 would mint
            // a confirmed order + invoice at an arbitrary total.
            if (!$negotiate && isset($data['proposed_price']) && is_numeric($data['proposed_price'])) {
                $catalogue = round((float) ($service->base_price ?: 0), 2);
                if (round((float) $data['proposed_price'], 2) !== $catalogue) {
                    $negotiate = true;
                }
            }

            // If customer wants to immediately confirm without negotiation, both email & phone must be verified
            if (!$negotiate) {
                abort_unless($customer->isFullyVerified(), 422, 'You must verify both email and phone before confirming an order.');
            }

            $price = isset($data['proposed_price']) && is_numeric($data['proposed_price'])
                ? (float) $data['proposed_price']
                : (float) ($service->base_price ?: 0);

            $discount = (float) ($data['discount_amount'] ?? 0);
            $taxRate = (float) ($data['tax_rate'] ?? 0);
            $tax = round(max(0, $price - $discount) * ($taxRate / 100), 2);
            $total = round(max(0, $price - $discount) + $tax, 2);

            // Prevent free confirmed orders via manipulated proposed_price/discount.
            if (!$negotiate && $total <= 0) {
                abort(422, 'A confirmed order must have a total greater than zero. Use price discussion for custom quotes.');
            }

            $status = $negotiate ? 'negotiating' : 'confirmed';
            $paymentAuth = 'not_authorized';

            $order = ServiceOrder::create([
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'created_by' => $customer->id,
                'source' => 'customer_portal',
                'order_source_label' => 'Customer Portal',
                'requirements' => $data['requirements'] ?? $service->name,
                'urgency' => $data['urgency'] ?? 'medium',
                'priority' => $data['priority'] ?? $data['urgency'] ?? 'medium',
                'preferred_date' => $data['preferred_date'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'status' => $status,
                'payment_authorization' => $paymentAuth,
                'currency' => $data['currency'] ?? 'USD',
                'original_price' => $price,
                'final_price' => $negotiate ? 0 : $price,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $tax,
                'total' => $negotiate ? 0 : $total,
                'amount_paid' => 0,
                'amount_due' => $negotiate ? 0 : $total,
                'expected_cost' => (float) ($data['expected_cost'] ?? ($service->estimated_cost ?? 0)),
                'price_locked' => !$negotiate,
                'customer_accepted_at' => $negotiate ? null : now(),
                'final_price_accepted_by' => $negotiate ? null : $customer->id,
                'attachments' => $data['attachments'] ?? null,
            ]);

            // Create initial price revision
            ServiceOrderPriceRevision::create([
                'service_order_id' => $order->id,
                'proposed_by' => $customer->id,
                'kind' => $negotiate ? 'customer_proposal' : 'final_offer',
                'amount' => $price,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'terms' => $data['notes'] ?? ($negotiate ? 'Customer initial discussion request' : 'Fixed catalogue order'),
                'status' => $negotiate ? 'proposed' : 'accepted',
            ]);

            AuditLog::log('service_order.created', 'service_orders', $order, "Order {$order->order_number} created by customer.");

            // If confirmed immediately, generate financial record, ticket, task
            if (!$negotiate) {
                $this->generateConnectedRecords($order, $customer);
            }

            return $order->fresh(['customer', 'service', 'priceRevisions', 'invoices', 'tickets', 'tasks']);
        });
    }

    /**
     * Submit a price proposal / offer / counter-offer.
     */
    public function proposePrice(ServiceOrder $order, array $data, User $actor): ServiceOrderPriceRevision
    {
        return DB::transaction(function () use ($order, $data, $actor) {
            $order = ServiceOrder::lockForUpdate()->findOrFail($order->id);

            abort_if(in_array($order->status, ['closed', 'financially_completed', 'cancelled'], true), 422, 'This order is closed and no longer accepts price proposals.');
            // If locked and not admin/manager, cannot change price
            if ($order->price_locked && !$actor->isAdmin() && !$actor->isFinanceManager()) {
                abort(403, 'The agreed price is locked. Only an authorized manager can propose a revision.');
            }

            $amount = (float) $data['amount'];
            abort_if($amount <= 0, 422, 'The proposed price must be greater than zero.');

            $discount = (float) ($data['discount_amount'] ?? 0);
            $taxRate = (float) ($data['tax_rate'] ?? $order->tax_rate ?? 0);
            $kind = $data['kind'] ?? ($actor->isCustomer() ? 'customer_proposal' : 'employee_offer');

            // Staff discount authority check: if discount > 20% and not admin/manager, require approval
            $discountPct = $amount > 0 ? ($discount / $amount) * 100 : 0;
            $status = 'proposed';
            if ($actor->isStaff() && $discountPct > 20 && !$actor->isAdmin() && !$actor->isFinanceManager()) {
                $status = 'pending_approval';
            }

            // Mark previous active proposals as countered
            $order->priceRevisions()->where('status', 'proposed')->update(['status' => 'countered']);

            $revision = ServiceOrderPriceRevision::create([
                'service_order_id' => $order->id,
                'proposed_by' => $actor->id,
                'kind' => $kind,
                'amount' => $amount,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'terms' => $data['terms'] ?? null,
                'status' => $status,
            ]);

            $order->status = 'negotiating';
            $order->price_locked = false;
            $order->save();

            AuditLog::log('service_order.price_proposed', 'service_orders', $order, "Price revision proposed ({$kind}: {$order->currency} {$amount}) by {$actor->name}.");

            return $revision;
        });
    }

    /**
     * Accept final price and confirm order.
     */
    public function acceptPrice(ServiceOrder $order, User $actor, ?int $revisionId = null): ServiceOrder
    {
        return DB::transaction(function () use ($order, $actor, $revisionId) {
            $order = ServiceOrder::lockForUpdate()->findOrFail($order->id);
            $customer = $order->customer;

            abort_if(in_array($order->status, ['closed', 'financially_completed', 'cancelled'], true), 422, 'This order is closed and no longer accepts price decisions.');

            // Strict rule: Customer must have email and phone verified before confirmation
            abort_unless($customer->isFullyVerified(), 422, 'The customer must have both verified email and verified phone before order agreement is confirmed.');

            $revision = $revisionId
                ? $order->priceRevisions()->findOrFail($revisionId)
                : $order->priceRevisions()->whereIn('status', ['proposed', 'pending_approval'])->latest()->firstOrFail();

            // Only live proposals can be accepted — replaying a stale
            // countered/rejected/accepted revision must never re-lock a price.
            abort_unless(in_array($revision->status, ['proposed', 'pending_approval'], true), 422, 'Only pending proposals can be accepted.');

            $subtotal = (float) $revision->amount;
            $discount = (float) $revision->discount_amount;
            $taxRate = (float) $revision->tax_rate;
            $tax = max(0, $subtotal - $discount) * ($taxRate / 100);
            $total = max(0, $subtotal - $discount) + $tax;

            // Lock price
            $revision->status = 'accepted';
            $revision->save();

            $order->update([
                'status' => 'confirmed',
                'original_price' => $order->original_price ?: $subtotal,
                'final_price' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $tax,
                'total' => $total,
                'amount_due' => max(0, $total - (float) $order->amount_paid),
                'price_locked' => true,
                'customer_accepted_at' => now(),
                'employee_approved_at' => now(),
                'final_price_accepted_by' => $actor->id,
            ]);

            AuditLog::log('service_order.price_accepted', 'service_orders', $order, "Final price accepted ({$order->currency} {$total}). Price locked.");

            $this->generateConnectedRecords($order, $actor);

            // Keep the invoice in lockstep: totals/due/status follow the order
            // (extra charges approved after invoicing must not leave a stale invoice).
            if ($invoice = $order->invoices()->first()) {
                $invoice->update([
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $tax,
                    'total' => $total,
                    'amount_due' => max(0, round($total - (float) $order->amount_paid, 2)),
                    'status' => max(0, round($total - (float) $order->amount_paid, 2)) == 0 ? 'paid' : ((float) $order->amount_paid > 0 ? 'partially_paid' : 'sent'),
                ]);
            }

            return $order->fresh(['invoices', 'tickets', 'tasks', 'priceRevisions']);
        });
    }

    /**
     * Employee Manual Work Order creation.
     */
    public function createManualWorkOrder(array $data, User $actor): ServiceOrder
    {
        return DB::transaction(function () use ($data, $actor) {
            abort_unless($actor->isEmployee() || $actor->isStaff(), 403, 'Only authorized employees can create manual work orders.');

            // Customer selection or creation
            if (!empty($data['customer_id'])) {
                $customer = User::findOrFail($data['customer_id']);
            } else {
                // Auto-create customer profile
                $customer = User::create([
                    'name' => $data['customer_name'],
                    'email' => $data['customer_email'],
                    'phone' => $data['customer_phone'] ?? null,
                    'password' => Str::random(16),
                    'role' => 'customer',
                    'verification_status' => 'pending',
                ]);
                AuditLog::log('customer.created_by_staff', 'users', $customer, "Customer profile created manually by {$actor->name}.");
            }

            // Verify customer verification status for confirmed financial activation
            // If customer is unverified, order can be drafted, but if confirming, warn or enforce
            $service = Service::findOrFail($data['service_id']);
            $subtotal = (float) ($data['price'] ?? $service->base_price ?? 0);
            abort_if($subtotal <= 0, 422, 'Please specify a valid service price.');

            $discount = (float) ($data['discount_amount'] ?? 0);
            $taxRate = (float) ($data['tax_rate'] ?? 0);
            $tax = round(max(0, $subtotal - $discount) * ($taxRate / 100), 2);
            $total = round(max(0, $subtotal - $discount) + $tax, 2);

            // Discount permission check: if discount > 20%, require manager/admin
            $discountPct = $subtotal > 0 ? ($discount / $subtotal) * 100 : 0;
            $needsManagerApproval = $discountPct > 20 && !$actor->isAdmin() && !$actor->isFinanceManager();

            $order = ServiceOrder::create([
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'created_by' => $actor->id,
                'assigned_to' => $data['assigned_to'] ?? null,
                'source' => 'employee_manual',
                'order_source_label' => $data['order_source_label'] ?? 'Employee Manual',
                'requirements' => $data['requirements'],
                'urgency' => $data['urgency'] ?? 'medium',
                'priority' => $data['priority'] ?? 'medium',
                'preferred_date' => $data['preferred_date'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'status' => $needsManagerApproval ? 'pending_approval' : 'confirmed',
                'payment_authorization' => 'not_authorized',
                'currency' => $data['currency'] ?? 'USD',
                'original_price' => $subtotal,
                'final_price' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $tax,
                'total' => $total,
                'amount_paid' => 0,
                'amount_due' => $total,
                'expected_cost' => (float) ($data['expected_cost'] ?? $service->estimated_cost ?? 0),
                'price_locked' => !$needsManagerApproval,
                'employee_approved_at' => $needsManagerApproval ? null : now(),
                'customer_accepted_at' => now(),
                'final_price_accepted_by' => $actor->id,
                'attachments' => $data['attachments'] ?? null,
            ]);

            ServiceOrderPriceRevision::create([
                'service_order_id' => $order->id,
                'proposed_by' => $actor->id,
                'kind' => 'final_offer',
                'amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'terms' => $data['terms'] ?? 'Employee manual agreement',
                'status' => $needsManagerApproval ? 'pending_approval' : 'accepted',
            ]);

            AuditLog::log('service_order.manual_created', 'service_orders', $order, "Manual work order {$order->order_number} created by {$actor->name} ({$order->order_source_label}).");

            if (!$needsManagerApproval) {
                $this->generateConnectedRecords($order, $actor);
            }

            return $order->fresh(['customer', 'service', 'invoices', 'tickets', 'tasks', 'priceRevisions']);
        });
    }

    /**
     * Atomically generate Invoice, Ticket, Task, and financial tracking structures.
     */
    public function generateConnectedRecords(ServiceOrder $order, User $actor): void
    {
        // 1. Create or link Invoice
        $invoice = $order->invoices()->first();
        if (!$invoice) {
            $invoice = Invoice::create([
                'customer_id' => $order->customer_id,
                'service_order_id' => $order->id,
                'currency' => $order->currency ?? 'USD',
                'subtotal' => $order->final_price,
                'discount_amount' => $order->discount_amount,
                'tax_rate' => $order->tax_rate,
                'tax_amount' => $order->tax_amount,
                'total' => $order->total,
                'amount_paid' => $order->amount_paid,
                'amount_due' => $order->amount_due,
                'status' => (float) $order->amount_paid >= (float) $order->total ? 'paid' : ((float) $order->amount_paid > 0 ? 'partially_paid' : 'sent'),
                'issued_date' => now(),
                'due_date' => now()->addDays(14),
                'notes' => 'Invoice for Service Order ' . $order->order_number,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $order->service_id,
                'description' => $order->service->name,
                'quantity' => 1,
                'unit_price' => $order->final_price,
                'discount' => $order->discount_amount,
                'tax_rate' => $order->tax_rate,
                'total' => $order->total,
            ]);
        }

        // 2. Create or link IT Ticket
        $ticket = $order->tickets()->first();
        if (!$ticket) {
            $ticket = Ticket::create([
                'customer_id' => $order->customer_id,
                'service_order_id' => $order->id,
                'subject' => $order->service->name . ' — ' . $order->order_number,
                'description' => $order->requirements,
                'priority' => $order->priority ?: 'medium',
                'assigned_to' => $order->assigned_to,
                'status' => 'new',
            ]);
        }

        // 3. Create or link IT Task
        $task = $order->tasks()->first();
        if (!$task) {
            Task::create([
                'service_order_id' => $order->id,
                'ticket_id' => $ticket->id,
                'customer_id' => $order->customer_id,
                'title' => $ticket->subject,
                'description' => $order->requirements,
                'assigned_to' => $order->assigned_to,
                'created_by' => $actor->id,
                'priority' => $order->priority ?: 'medium',
                'sla_priority' => $order->urgency ?: 'medium',
                'status' => 'pending',
                'type' => 'assigned',
                'budget' => $order->total,
            ]);
        }

        AuditLog::log('service_order.records_generated', 'service_orders', $order, "Generated Invoice, IT Ticket ({$ticket->ticket_number}) and IT Task for {$order->order_number}.");
    }

    /**
     * Record payment (full or partial) server-side with strict balance calculation and receipt issuance.
     */
    public function recordPayment(ServiceOrder $order, array $data, ?User $actor = null): array
    {
        return DB::transaction(function () use ($order, $data, $actor) {
            $order = ServiceOrder::lockForUpdate()->findOrFail($order->id);
            $invoice = $order->invoices()->lockForUpdate()->firstOrFail();

            abort_if(in_array($order->status, ['cancelled', 'closed', 'financially_completed'], true), 422, 'This order is closed and no longer accepts payments.');

            $amount = round((float) $data['amount'], 2);
            abort_if($amount <= 0, 422, 'Payment amount must be greater than zero.');

            // Server-side calculation prevents overpaying or browser manipulation
            $outstanding = max(0, round((float) $order->total - (float) $order->amount_paid, 2));
            abort_if($amount > $outstanding, 422, "Payment amount ({$order->currency} {$amount}) cannot exceed the outstanding balance ({$order->currency} {$outstanding}).");

            // Idempotency: a retried callback/record call carrying the same
            // transaction_id reuses the original payment instead of minting
            // money twice. (payments.transaction_id is nullable/non-unique by
            // schema, so this domain guard is the authoritative dedup.)
            $txn = trim((string) ($data['transaction_id'] ?? ''));
            if ($txn !== '') {
                $existing = Payment::where('service_order_id', $order->id)
                    ->where('transaction_id', $txn)->lockForUpdate()->first();
                if ($existing) {
                    $receipt = Receipt::where('payment_id', $existing->id)->first();
                    AuditLog::log('payment.duplicate_ignored', 'service_orders', $order, "Duplicate {$order->currency} {$amount} for {$order->order_number} ignored (transaction {$txn} already recorded as {$existing->payment_number}).");
                    return [
                        'payment' => $existing,
                        'receipt' => $receipt,
                        'order' => $order->fresh(),
                        'duplicate' => true,
                    ];
                }
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $order->customer_id,
                'service_order_id' => $order->id,
                'amount' => $amount,
                'currency' => $order->currency,
                'payment_method' => $data['payment_method'] ?? 'card',
                'transaction_id' => $data['transaction_id'] ?? ('TXN-' . strtoupper(Str::random(10))),
                'status' => 'completed',
                'paid_at' => now(),
                'notes' => $data['notes'] ?? ('Payment for ' . $order->order_number),
            ]);

            // Update order balances
            $newAmountPaid = round((float) $order->amount_paid + $amount, 2);
            $newAmountDue = max(0, round((float) $order->total - $newAmountPaid, 2));

            $order->amount_paid = $newAmountPaid;
            $order->amount_due = $newAmountDue;

            // Update invoice balances
            $invoice->amount_paid = round((float) $invoice->amount_paid + $amount, 2);
            $invoice->amount_due = $newAmountDue;
            $invoice->status = $newAmountDue == 0 ? 'paid' : 'partially_paid';
            if ($newAmountDue == 0) {
                $invoice->paid_at = now();
            }
            $invoice->save();

            // Calculate Payment Authorization state
            $minDeposit = round((float) $order->total * (self::MIN_DEPOSIT_PERCENTAGE / 100), 2);

            if ($order->manager_override_at) {
                $order->payment_authorization = 'manager_override';
            } elseif ($newAmountDue == 0) {
                $order->payment_authorization = 'fully_paid';
            } elseif ($newAmountPaid >= $minDeposit) {
                $order->payment_authorization = 'ready_to_start';
            } else {
                $order->payment_authorization = 'deposit_required';
            }

            // Order status transition
            if ($order->task_completed_at && $newAmountDue == 0) {
                $order->status = 'financially_completed';
            } elseif ($order->payment_authorization === 'ready_to_start' || $order->payment_authorization === 'fully_paid') {
                if ($order->status === 'confirmed' || $order->status === 'awaiting_payment') {
                    $order->status = 'ready_to_start';
                }
            }
            $order->save();

            // Issue official Receipt — idempotent per payment (retry-safe:
            // the unique payment_id key means a retried webhook/record call
            // reuses the same receipt instead of duplicating documents).
            $receipt = Receipt::firstOrCreate(
                ['payment_id' => $payment->id],
                [
                    'invoice_id' => $invoice->id,
                    'customer_id' => $order->customer_id,
                    'service_order_id' => $order->id,
                    'amount' => $amount,
                    'remaining_balance' => $newAmountDue,
                    'currency' => $order->currency,
                    'issued_at' => now(),
                ]
            );

            AuditLog::log('payment.recorded', 'service_orders', $order, "Payment of {$order->currency} {$amount} recorded. Receipt {$receipt->receipt_number} issued. Balance: {$order->currency} {$newAmountDue}.");

            // Allocate across staged payment schedule rows (FIFO). Keeps the
            // milestone/stage view in sync on every rail (manual/Stripe/wallet).
            app(\App\Services\OrderPaymentAllocator::class)->allocate($order->fresh(), $payment->fresh(), $amount);

            // Book finance income once per payment (same rule as the
            // Stripe and finance payment paths; keyed by payment id).
            app(\App\Services\FinancialService::class)->recordIncome(
                (float) $amount,
                'Customer Payment',
                "Payment {$payment->payment_number} for order {$order->order_number}",
                ['invoice_id' => $invoice->id, 'payment_id' => $payment->id, 'customer_id' => $order->customer_id, 'service_order_id' => $order->id]
            );

            if ($newAmountDue == 0 && $invoice->customer) {
                $invoice->customer->notify(new \App\Notifications\InvoiceCreatedNotification($invoice->fresh(), 'paid'));
            }

            return [
                'payment' => $payment,
                'receipt' => $receipt,
                'order' => $order->fresh(),
            ];
        });
    }

    /**
     * Authorize work to begin via Manager Override.
     */
    public function managerOverride(ServiceOrder $order, User $manager, string $reason): ServiceOrder
    {
        abort_unless($manager->isAdmin() || $manager->isSupportManager() || $manager->isProjectManager() || $manager->isFinanceManager(), 403, 'Only managers or administrators can grant work authorization overrides.');
        abort_if(empty(trim($reason)), 422, 'A manager override reason must be provided.');
        abort_if(in_array($order->status, ['cancelled', 'closed', 'financially_completed'], true), 422, 'This order is closed and cannot receive a manager override.');

        $order->update([
            'manager_override_by' => $manager->id,
            'manager_override_at' => now(),
            'manager_override_reason' => $reason,
            'payment_authorization' => 'manager_override',
            'status' => in_array($order->status, ['confirmed', 'awaiting_payment']) ? 'ready_to_start' : $order->status,
        ]);

        AuditLog::log('service_order.manager_override', 'service_orders', $order, "Manager override granted by {$manager->name}. Reason: {$reason}");

        return $order;
    }

    /**
     * Record expenditure (Labour cost, Freelancer, Vendor, Software, etc.) linked to order and task.
     */
    public function recordExpense(ServiceOrder $order, array $data, User $actor): Expense
    {
        return DB::transaction(function () use ($order, $data, $actor) {
            $costType = $data['cost_type'] ?? 'vendor';
            $hours = isset($data['hours']) ? (float) $data['hours'] : null;
            $hourlyRate = isset($data['hourly_rate']) ? (float) $data['hourly_rate'] : null;
            $commissionPct = isset($data['commission_percentage']) ? (float) $data['commission_percentage'] : null;

            // Compute amount based on cost type
            if ($costType === 'labour' && $hours && $hourlyRate) {
                $amount = round($hours * $hourlyRate, 2);
            } elseif ($costType === 'commission' && $commissionPct) {
                $amount = round((float) $order->total * ($commissionPct / 100), 2);
            } else {
                $amount = round((float) $data['amount'], 2);
            }

            abort_if($amount <= 0, 422, 'Expense amount must be greater than zero.');

            $expense = Expense::create([
                'service_order_id' => $order->id,
                'task_id' => $data['task_id'] ?? null,
                'worker_id' => $data['worker_id'] ?? null,
                'category_name' => $data['category_name'] ?? ucfirst($costType),
                'description' => $data['description'] ?? ("{$costType} expense for {$order->order_number}"),
                'amount' => $amount,
                'cost_type' => $costType,
                'hours' => $hours,
                'hourly_rate' => $hourlyRate,
                'commission_percentage' => $commissionPct,
                'commission_amount' => $costType === 'commission' ? $amount : null,
                'date' => $data['date'] ?? now()->toDateString(),
                'vendor' => $data['vendor'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'created_by' => $actor->id,
            ]);

            // Update actual_cost on service order
            $order->actual_cost = $order->expenses()->sum('amount');
            $order->save();

            AuditLog::log('service_order.expense_recorded', 'service_orders', $order, "Expense {$expense->expense_number} ({$costType}: {$order->currency} {$amount}) recorded by {$actor->name}.");

            return $expense;
        });
    }

    /**
     * Mark technical work complete and evaluate financial balance.
     */
    public function completeTechnicalTask(Task $task, User $actor, ?string $technicalNotes = null, ?int $actualMinutes = null): Task
    {
        return DB::transaction(function () use ($task, $actor, $technicalNotes, $actualMinutes) {
            $task->status = 'completed';
            $task->completed_at = now();
            if ($technicalNotes) $task->technical_notes = $technicalNotes;
            if ($actualMinutes) $task->actual_minutes = $actualMinutes;
            $task->save();

            if ($task->service_order_id) {
                $order = ServiceOrder::lockForUpdate()->find($task->service_order_id);
                if ($order) {
                    $order->task_completed_at = now();

                    // Check if all tasks for this order are completed
                    $hasOpenTasks = $order->tasks()->where('status', '!=', 'completed')->exists();

                    if (!$hasOpenTasks) {
                        if ((float) $order->amount_due > 0) {
                            $order->status = 'awaiting_final_payment';
                        } else {
                            $order->status = 'financially_completed';
                        }
                    }
                    $order->save();

                    AuditLog::log('task.completed', 'tasks', $task, "Task {$task->task_number} completed. Order {$order->order_number} status: {$order->status}.");
                }
            }

            return $task;
        });
    }

    /**
     * Create a confirmed order directly from an accepted quotation.
     * Idempotent via the quotation state machine: only `sent` quotes convert,
     * under row lock, so double-clicks/retries can never mint two orders.
     */
    public function createOrderFromQuotation(Quotation $quotation, User $customer): ServiceOrder
    {
        return DB::transaction(function () use ($quotation, $customer) {
            $q = Quotation::lockForUpdate()->findOrFail($quotation->id);
            abort_unless((int) $q->customer_id === (int) $customer->id, 403);
            abort_unless($q->status === 'sent' && (!$q->valid_until || $q->valid_until->isFuture()), 422, 'This quotation is not available for acceptance.');
            abort_unless($customer->isFullyVerified(), 422, 'You must verify both email and phone before confirming an order.');
            abort_if(ServiceOrder::where('quotation_id', $q->id)->exists(), 422, 'An order already exists for this quotation.');

            $serviceId = $q->serviceRequest?->service_id;
            abort_unless($serviceId, 422, 'This quotation has no service linked and cannot become an order.');
            $service = Service::findOrFail($serviceId);

            $subtotal = round((float) $q->subtotal, 2);
            $discount = round((float) $q->discount_amount, 2);
            $taxRate = (float) $q->tax_rate;
            $tax = round((float) $q->tax_amount ?: max(0, $subtotal - $discount) * ($taxRate / 100), 2);
            $total = round((float) $q->total ?: max(0, $subtotal - $discount) + $tax, 2);
            abort_if($total <= 0, 422, 'A confirmed order must have a total greater than zero.');

            $q->update(['status' => 'accepted', 'accepted_at' => now()]);
            $q->serviceRequest?->update(['review_status' => 'accepted', 'status' => 'accepted']);

            $order = ServiceOrder::create([
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'quotation_id' => $q->id,
                'created_by' => $customer->id,
                'source' => 'quotation_accept',
                'order_source_label' => 'Quotation Accept',
                'requirements' => $q->serviceRequest?->requirements ?? $q->notes ?? $service->name,
                'urgency' => 'medium',
                'priority' => 'medium',
                'currency' => $q->currency ?? 'USD',
                'original_price' => $subtotal,
                'final_price' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $tax,
                'total' => $total,
                'amount_paid' => 0,
                'amount_due' => $total,
                'expected_cost' => (float) ($service->estimated_cost ?? 0),
                'status' => 'confirmed',
                'payment_authorization' => 'not_authorized',
                'price_locked' => true,
                'customer_accepted_at' => now(),
                'final_price_accepted_by' => $customer->id,
            ]);

            ServiceOrderPriceRevision::create([
                'service_order_id' => $order->id,
                'proposed_by' => $customer->id,
                'kind' => 'final_offer',
                'amount' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'terms' => 'Accepted quotation ' . $q->quotation_number,
                'status' => 'accepted',
            ]);

            AuditLog::log('service_order.created_from_quotation', 'service_orders', $order, "Order {$order->order_number} created from accepted quotation {$q->quotation_number}.");
            ServiceTrackingService::notify($customer->id, 'order_created', 'Order created', "Your order {$order->order_number} is confirmed. A deposit of 30% unlocks scheduling.");

            $this->generateConnectedRecords($order, $customer);

            return $order->fresh(['customer', 'service', 'invoices', 'tickets', 'tasks', 'priceRevisions']);
        });
    }

    /**
     * Reject a proposed price revision (customer declines, or finance declines).
     */
    public function rejectPrice(ServiceOrder $order, User $actor, ?int $revisionId, string $reason): ServiceOrderPriceRevision
    {
        return DB::transaction(function () use ($order, $actor, $revisionId, $reason) {
            $order = ServiceOrder::lockForUpdate()->findOrFail($order->id);
            abort_if(in_array($order->status, ['closed', 'financially_completed', 'cancelled'], true), 422, 'This order is closed and no longer accepts price decisions.');
            $isOwner = (int) $order->customer_id === (int) $actor->id;
            abort_unless($isOwner || $actor->isAdmin() || $actor->isFinanceManager(), 403);
            $revision = $revisionId
                ? $order->priceRevisions()->findOrFail($revisionId)
                : $order->priceRevisions()->whereIn('status', ['proposed', 'pending_approval'])->latest()->firstOrFail();
            abort_unless(in_array($revision->status, ['proposed', 'pending_approval'], true), 422, 'Only pending proposals can be rejected.');
            $revision->update(['status' => 'rejected']);
            AuditLog::log('service_order.price_rejected', 'service_orders', $order, "Price proposal rejected by {$actor->name}. Reason: {$reason}");
            ServiceTrackingService::notify($order->customer_id, 'price_rejected', 'Price proposal declined', "A price proposal on {$order->order_number} was declined. Reason: {$reason}");
            return $revision->fresh();
        });
    }

    /**
     * Cancel an order with reason/actor/timestamp. Paid balances flag refund_due
     * instead of vanishing; history is preserved (never deleted).
     */
    public function cancelOrder(ServiceOrder $order, User $actor, string $reason): ServiceOrder
    {
        return DB::transaction(function () use ($order, $actor, $reason) {
            $order = ServiceOrder::lockForUpdate()->findOrFail($order->id);
            abort_if(in_array($order->status, ['closed', 'financially_completed', 'cancelled'], true), 422, 'This order can no longer be cancelled.');
            $isOwner = (int) $order->customer_id === (int) $actor->id;
            $isManager = $actor->isAdmin() || $actor->isFinanceManager() || $actor->isSupportManager();
            abort_unless($isOwner || $isManager, 403);
            // Owners may only cancel unpaid work; paid cancellations need finance/admin.
            abort_if($isOwner && !$actor->isAdmin() && !$actor->isFinanceManager() && (float) $order->amount_paid > 0, 403, 'Paid orders can only be cancelled by finance or an administrator.');
            $refundDue = (float) $order->amount_paid > 0;
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'cancelled_by' => $actor->id,
                'refund_due' => $refundDue,
            ]);
            AuditLog::log('service_order.cancelled', 'service_orders', $order, "Order {$order->order_number} cancelled by {$actor->name}. Reason: {$reason}" . ($refundDue ? " Refund due: {$order->currency} {$order->amount_paid}." : ''));
            ServiceTrackingService::notify($order->customer_id, 'order_cancelled', 'Order cancelled', "Order {$order->order_number} was cancelled." . ($refundDue ? ' Our finance team will process your refund.' : ''));
            if ($refundDue) {
                $finance = User::whereIn('role', ['admin', 'super_admin', 'finance_manager'])->where('is_active', true)->get();
                foreach ($finance as $f) {
                    ServiceTrackingService::notify($f->id, 'refund_required', 'Refund required', "Cancelled order {$order->order_number} has a paid balance of {$order->currency} {$order->amount_paid}.");
                }
            }
            return $order;
        });
    }

    /**
     * Close order when technical work is completed and balance is £0.
     */
    public function closeOrder(ServiceOrder $order, User $actor, ?string $closureNotes = null): ServiceOrder
    {
        return DB::transaction(function () use ($order, $actor, $closureNotes) {
            $order = ServiceOrder::lockForUpdate()->findOrFail($order->id);

            // Technical work must be completed
            abort_unless($order->task_completed_at || !$order->tasks()->where('status', '!=', 'completed')->exists(), 422, 'Cannot close order: technical tasks are not yet marked as completed.');

            // Financial balance must be 0
            abort_unless((float) $order->amount_due <= 0, 422, "Cannot close order: outstanding balance of {$order->currency} {$order->amount_due} must be paid in full.");

            $order->update([
                'status' => 'closed',
                'closed_at' => now(),
                'closure_notes' => $closureNotes,
            ]);

            AuditLog::log('service_order.closed', 'service_orders', $order, "Service Order {$order->order_number} closed by {$actor->name}.");

            return $order;
        });
    }
}
