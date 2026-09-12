<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $type = 'breach'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticketUrl = route('admin.tickets.show', $this->ticket);
        $isBreach = $this->type === 'breach';
        $subject = $isBreach
            ? "🚨 SLA BREACH: {$this->ticket->ticket_number}"
            : "⚠️ SLA Warning: {$this->ticket->ticket_number}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($isBreach
                ? "A support ticket has exceeded its SLA resolution deadline."
                : "A support ticket is approaching its SLA resolution deadline.");

        $mail->line("**Ticket:** {$this->ticket->ticket_number}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Priority:** " . ucfirst($this->ticket->priority))
            ->line("**Status:** " . ucfirst(str_replace('_', ' ', $this->ticket->status)));

        if ($this->ticket->sla_resolution_deadline) {
            $mail->line("**Deadline:** {$this->ticket->sla_resolution_deadline->format('M d, Y H:i')}");
        }

        if ($this->ticket->assignee) {
            $mail->line("**Assigned to:** {$this->ticket->assignee->name}");
        }

        $mail->action('View Ticket', $ticketUrl);

        if ($isBreach) {
            $mail->line('🚨 This ticket requires immediate attention. Please escalate or resolve as soon as possible.');
        } else {
            $mail->line('⚠️ Please ensure this ticket is resolved before the deadline.');
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $isBreach = $this->type === 'breach';
        return [
            'title' => $isBreach ? 'SLA Breach' : 'SLA Warning',
            'message' => ($isBreach ? 'SLA breached' : 'SLA warning') . " for ticket {$this->ticket->ticket_number}: {$this->ticket->subject}",
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'type' => $this->type,
            'priority' => $this->ticket->priority,
            'url' => route('admin.tickets.show', $this->ticket),
        ];
    }
}
