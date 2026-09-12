<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public string $action = 'created'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoiceUrl = route('portal.invoices.show', $this->invoice);
        $subject = match($this->action) {
            'sent' => "Invoice Sent: {$this->invoice->invoice_number}",
            'paid' => "Payment Received: {$this->invoice->invoice_number}",
            'overdue' => "Invoice Overdue: {$this->invoice->invoice_number}",
            'refunded' => "Refund Processed: {$this->invoice->invoice_number}",
            default => "New Invoice: {$this->invoice->invoice_number}",
        };

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("**Invoice:** {$this->invoice->invoice_number}")
            ->line("**Amount:** $" . number_format($this->invoice->total, 2))
            ->line("**Status:** " . ucfirst(str_replace('_', ' ', $this->invoice->status)));

        if ($this->invoice->due_date) {
            $mail->line("**Due Date:** {$this->invoice->due_date->format('M d, Y')}");
        }

        $mail->action('View Invoice', $invoiceUrl);

        if ($this->action === 'overdue') {
            $mail->line('This invoice is now overdue. Please make payment as soon as possible to avoid service disruption.');
        } elseif ($this->action === 'paid') {
            $mail->line('Payment has been received. Thank you!');
        } elseif ($this->action === 'refunded') {
            $mail->line('A refund has been processed to your original payment method. Please allow standard processing times.');
        } else {
            $mail->line('Thank you for your business.');
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Invoice ' . ucfirst($this->action),
            'message' => "Invoice {$this->invoice->invoice_number} — $" . number_format($this->invoice->total, 2),
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total,
            'status' => $this->invoice->status,
            'action' => $this->action,
            'url' => route('portal.invoices.show', $this->invoice),
        ];
    }
}
