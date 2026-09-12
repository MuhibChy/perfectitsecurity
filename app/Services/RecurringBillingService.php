<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecurringBillingService
{
    public function processDueSubscriptions(): array
    {
        $created = 0;
        $failed = 0;

        Subscription::dueForBilling()->with('customer', 'service')->chunkById(50, function ($subs) use (&$created, &$failed) {
            foreach ($subs as $subscription) {
                try {
                    DB::transaction(function () use ($subscription, &$created) {
                        $this->generateInvoice($subscription);
                        $created++;
                    });
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Recurring billing failed: ' . $e->getMessage(), [
                        'subscription_id' => $subscription->id,
                    ]);
                }
            }
        });

        return compact('created', 'failed');
    }

    public function generateInvoice(Subscription $subscription): Invoice
    {
        $taxRate = (float) $subscription->tax_rate;
        $subtotal = (float) $subscription->amount;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $total = $subtotal + $taxAmount;

        $invoice = Invoice::create([
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'notes' => 'Recurring billing for ' . $subscription->name,
            'terms' => 'Auto-generated subscription invoice.',
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'discount_type' => 'fixed',
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'amount_paid' => 0,
            'amount_due' => $total,
            'currency' => $subscription->currency,
            'status' => 'sent',
            'issued_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'sent_at' => now(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_id' => $subscription->service_id,
            'description' => $subscription->name . ' (' . $subscription->interval . ')',
            'quantity' => 1,
            'unit_price' => $subtotal,
            'discount' => 0,
            'tax_rate' => $taxRate,
            'total' => $total,
        ]);

        $next = $subscription->next_billing_at
            ? $subscription->next_billing_at->copy()
            : now();

        if ($subscription->interval === 'yearly') {
            $next->addYears($subscription->interval_count ?: 1);
        } else {
            $next->addMonths($subscription->interval_count ?: 1);
        }

        $subscription->update([
            'invoice_id' => $invoice->id,
            'next_billing_at' => $next->toDateString(),
        ]);

        return $invoice;
    }
}
