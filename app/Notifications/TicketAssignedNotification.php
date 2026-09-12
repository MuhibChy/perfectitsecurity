<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $assignedByName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticketUrl = route('admin.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Ticket Assigned: {$this->ticket->ticket_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned a new support ticket by {$this->assignedByName}.")
            ->line("**Ticket:** {$this->ticket->ticket_number}")
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Priority:** " . ucfirst($this->ticket->priority))
            ->action('View Ticket', $ticketUrl)
            ->line('Please respond within the SLA timeframe.')
            ->line('Thank you for using TechSupport Solutions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ticket Assigned',
            'message' => "You have been assigned ticket {$this->ticket->ticket_number}: {$this->ticket->subject}",
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'assigned_by' => $this->assignedByName,
            'priority' => $this->ticket->priority,
            'url' => route('admin.tickets.show', $this->ticket),
        ];
    }
}
