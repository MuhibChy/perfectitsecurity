<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\SlaPolicy;
use App\Models\TicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketingTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $agent;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $this->agent = User::create([
            'name' => 'Test Agent',
            'email' => 'agent@test.com',
            'password' => bcrypt('password'),
            'role' => 'support_agent',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        SlaPolicy::create(['name' => 'Standard', 'response_time_minutes' => 120, 'resolution_time_minutes' => 240, 'priority' => 'medium']);
        $this->category = TicketCategory::create(['name' => 'Test', 'slug' => 'test']);
    }

    public function test_customer_can_create_ticket()
    {
        $response = $this->actingAs($this->customer)->post('/portal/tickets', [
            'subject' => 'Test Issue',
            'description' => 'Description of the issue',
            'category_id' => $this->category->id,
            'priority' => 'medium',
        ]);

        $this->assertDatabaseHas('tickets', ['subject' => 'Test Issue', 'customer_id' => $this->customer->id]);
    }

    public function test_ticket_gets_ticket_number()
    {
        $ticket = Ticket::create([
            'customer_id' => $this->customer->id,
            'subject' => 'Auto Number',
            'description' => 'Test',
            'priority' => 'medium',
            'status' => 'new',
        ]);

        $this->assertNotEmpty($ticket->ticket_number);
        $this->assertStringStartsWith('TK-', $ticket->ticket_number);
    }

    public function test_agent_can_view_assigned_ticket()
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TK-TEST001',
            'customer_id' => $this->customer->id,
            'subject' => 'Assigned ticket',
            'description' => 'Test',
            'priority' => 'medium',
            'status' => 'assigned',
            'assigned_to' => $this->agent->id,
        ]);

        $response = $this->actingAs($this->agent)->get("/admin/tickets/{$ticket->id}");
        $response->assertStatus(200);
    }

    public function test_customer_can_reply_to_ticket()
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TK-REPLY1',
            'customer_id' => $this->customer->id,
            'subject' => 'Reply test',
            'description' => 'Test',
            'priority' => 'medium',
            'status' => 'in_progress',
            'assigned_to' => $this->agent->id,
        ]);

        $response = $this->actingAs($this->customer)->post("/portal/tickets/{$ticket->id}/reply", [
            'message' => 'Customer reply',
        ]);

        $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $ticket->id, 'message' => 'Customer reply']);
    }

    public function test_ticket_status_can_be_updated()
    {
        $ticket = Ticket::create([
            'ticket_number' => 'TK-STATUS1',
            'customer_id' => $this->customer->id,
            'subject' => 'Status test',
            'description' => 'Test',
            'priority' => 'medium',
            'status' => 'in_progress',
            'assigned_to' => $this->agent->id,
        ]);

        $response = $this->actingAs($this->agent)->post("/admin/tickets/{$ticket->id}/status", ['status' => 'resolved']);
        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
    }

    public function test_agent_cannot_view_another_agents_ticket()
    {
        $otherAgent = User::factory()->create(['role' => 'support_agent']);
        $ticket = Ticket::create([
            'customer_id' => $this->customer->id,
            'subject' => 'Private queue item',
            'description' => 'Test',
            'priority' => 'medium',
            'status' => 'assigned',
            'assigned_to' => $otherAgent->id,
        ]);

        $this->actingAs($this->agent)->get("/admin/tickets/{$ticket->id}")->assertForbidden();
    }
}
