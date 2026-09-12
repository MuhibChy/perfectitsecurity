<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\InvoiceCreatedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SendOverdueInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-overdue-reminders';
    protected $description = 'Send reminders for overdue invoices and mark them as overdue';

    public function handle(): int
    {
        $today = now()->startOfDay();

        // Find invoices that are past due but not yet marked overdue
        $pastDueInvoices = Invoice::whereIn('status', ['sent', 'viewed', 'partially_paid'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->whereNull('paid_at')
            ->get();

        $count = 0;

        foreach ($pastDueInvoices as $invoice) {
            DB::transaction(function () use ($invoice, &$count) {
                // Update status to overdue
                if ($invoice->status !== 'overdue') {
                    $invoice->update(['status' => 'overdue']);
                }

                // Send notification to customer
                if ($invoice->customer) {
                    $invoice->customer->notify(
                        new InvoiceCreatedNotification($invoice, 'overdue')
                    );
                }

                // Create in-app notification for admins
                $adminUsers = User::whereIn('role', ['super_admin', 'admin', 'finance_manager'])
                    ->where('is_active', true)
                    ->get();

                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'id' => (string) Str::uuid(),
                        'type' => 'invoice_overdue',
                        'notifiable_type' => User::class,
                        'notifiable_id' => $admin->id,
                        'data' => [
                            'title' => 'Invoice Overdue',
                            'message' => 'Invoice ' . $invoice->invoice_number . ' ($' . number_format($invoice->total, 2) . ') is overdue. Customer: ' . ($invoice->customer->name ?? 'Unknown'),
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'amount' => $invoice->total,
                            'url' => route('admin.invoices.edit', $invoice),
                        ],
                    ]);
                }

                $count++;
            });
        }

        $this->info("Processed {$count} overdue invoice(s).");
        return Command::SUCCESS;
    }
}
