<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Generic payment-lifecycle database notification (initiated, successful,
 * failed, pending verification, verified, refund…). Full-payment e-mail
 * continues to flow through InvoiceCreatedNotification('paid').
 */
class PaymentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $event,
        public array $data = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $titles = [
            'payment_initiated' => 'Payment initiated',
            'payment_successful' => 'Payment successful',
            'payment_received' => 'New payment received',
            'payment_failed' => 'Payment failed',
            'payment_pending' => 'Payment pending verification',
            'payment_verified' => 'Bank transfer verified',
            'refund_requested' => 'Refund requested',
            'refund_completed' => 'Refund completed',
            'manual_transfer_submitted' => 'Bank transfer submitted',
            'reconciliation_mismatch' => 'Payment reconciliation mismatch',
        ];

        return array_merge([
            'title' => $titles[$this->event] ?? 'Payment update',
            'event' => $this->event,
        ], $this->data);
    }
}
