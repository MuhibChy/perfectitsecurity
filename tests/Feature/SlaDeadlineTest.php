<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_ticket_is_escalated_once_and_notifies_support_management(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $manager = User::factory()->create(['role' => 'support_manager']);
        $ticket = Ticket::create([
            'customer_id' => $customer->id,
            'subject' => 'Expired incident',
            'description' => 'Test',
            'priority' => 'high',
            'status' => 'in_progress',
            'sla_resolution_deadline' => now()->subMinute(),
        ]);

        $first = app(SlaService::class)->processDeadlines();
        $ticket->refresh();

        $this->assertSame(1, $first['breachCount']);
        $this->assertSame('escalated', $ticket->status);
        $this->assertNotNull($ticket->sla_breached_at);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $manager->id, 'type' => 'sla_breached']);

        $second = app(SlaService::class)->processDeadlines();
        $this->assertSame(0, $second['breachCount']);
        $this->assertSame(1, Notification::where('type', 'sla_breached')->count());
    }
}
