<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AuditLog;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Ai\AiAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AI Customer & Service Management Agent: controlled tools, role gates,
 * isolation, deterministic answers, audit trail.
 */
class AiAgentTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $email = 'agent.a@example.test'): User
    {
        return User::factory()->create(['role' => 'customer', 'is_active' => true,
            'email' => $email, 'email_verified_at' => now(), 'phone_verified_at' => now()]);
    }

    private function agent(): AiAgentService
    {
        return app(AiAgentService::class);
    }

    /** @test */
    public function guest_gets_login_prompt_for_personal_queries()
    {
        $conv = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);
        $answer = app(\App\Services\Ai\AiChatService::class)->answerStatusQuery($conv, 'What is the status of my ticket?');
        $this->assertStringContainsString('log in', strtolower($answer));
    }

    /** @test */
    public function general_howto_questions_fall_through_to_kb()
    {
        $conv = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);
        $this->assertNull(app(\App\Services\Ai\AiChatService::class)->answerStatusQuery($conv, 'How do I create a ticket?'));
        $this->assertNull(app(\App\Services\Ai\AiChatService::class)->answerStatusQuery($conv, 'What is your payment process?'));
    }

    /** @test */
    public function customer_gets_own_ticket_and_order_status_deterministically()
    {
        $customer = $this->customer();
        $ticket = Ticket::create(['customer_id' => $customer->id, 'subject' => 'Agent probe',
            'description' => 'x', 'priority' => 'high']);
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);
        $chat = app(\App\Services\Ai\AiChatService::class);

        $answer = $chat->answerStatusQuery($conv, 'Show my tickets please');
        $this->assertStringContainsString($ticket->ticket_number, $answer);

        $single = $chat->answerStatusQuery($conv, "Status of {$ticket->ticket_number}?");
        $this->assertStringContainsString($ticket->ticket_number, $single);

        // Unknown number → honest miss, not someone else's data.
        $miss = $chat->answerStatusQuery($conv, 'Status of TK-DOESNOTEXIST?');
        $this->assertStringContainsString("couldn't find", $miss);
    }

    /** @test */
    public function agent_tools_reject_cross_customer_access()
    {
        $a = $this->customer('agent.alpha@example.test');
        $b = $this->customer('agent.beta@example.test');
        $ticketB = Ticket::create(['customer_id' => $b->id, 'subject' => 'Beta secret',
            'description' => 'x', 'priority' => 'low']);

        // Direct tool calls are scoped to the passed customer; B's ticket
        // never appears in A's tool output.
        $ticketsA = $this->agent()->getCustomerTickets($a);
        $this->assertEmpty($ticketsA);
        $this->assertNull($this->agent()->getTicketStatus($a, $ticketB->ticket_number));

        // Employee summary only covers the requested customer, and is audited.
        $staff = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $summary = $this->agent()->summarizeCustomerIssues($staff, $b->id);
        $this->assertEquals('Beta secret', $summary['recent_tickets'][0]['subject']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai.customer_summary']);

        // Non-staff cannot use employee tools.
        try {
            $this->agent()->summarizeCustomerIssues($a, $b->id);
            $this->fail('expected 403');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /** @test */
    public function agent_creates_validated_records_with_audit()
    {
        $customer = $this->customer();
        $cat = ServiceCategory::create(['name' => 'Agent Cat', 'slug' => 'agent-cat']);
        $service = Service::create(['category_id' => $cat->id, 'name' => 'Agent Svc',
            'slug' => 'agent-svc', 'short_description' => 'x', 'is_active' => true]);

        $ticket = $this->agent()->createSupportTicket($customer, [
            'subject' => 'Agent-created ticket', 'description' => 'Detailed problem description here.',
        ]);
        $this->assertEquals($customer->id, $ticket->customer_id);
        $this->assertNotNull($ticket->ticket_number);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai.ticket_created']);

        $sr = $this->agent()->createServiceRequest($customer, [
            'service_id' => $service->id, 'requirements' => 'Need help with office network setup details.',
        ]);
        $this->assertEquals($customer->id, $sr->user_id);
        $this->assertEquals('ai-assistant', $sr->lead_source);

        $qr = $this->agent()->createQuoteRequest($customer, [
            'requirements' => 'Quote for fifty seats across two offices please.',
            'budget_range' => '5k-15k',
        ]);
        $this->assertEquals('ai-quote', $qr->lead_source);

        // Validation enforced (no silent bad records).
        try {
            $this->agent()->createSupportTicket($customer, ['subject' => 'x']);
            $this->fail('expected validation');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertTrue(true);
        }

        // Customer reply lands on own open ticket, never as internal note.
        $msg = $this->agent()->addTicketMessage($customer, $ticket->ticket_number, 'Additional info from customer here.');
        $this->assertFalse((bool) $msg->is_internal_note);

        // Other customer's ticket number → 404, never cross-linked.
        $other = $this->customer('agent.other@example.test');
        $otherTicket = Ticket::create(['customer_id' => $other->id, 'subject' => 'Other',
            'description' => 'x', 'priority' => 'low']);
        try {
            $this->agent()->addTicketMessage($customer, $otherTicket->ticket_number, 'Hijack attempt content here.');
            $this->fail('expected 404');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    /** @test */
    public function escalation_records_context_and_audit_without_secrets()
    {
        $customer = $this->customer();
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);
        $conv->messages()->create(['role' => 'user', 'content' => 'Billing dispute about invoice.']);
        $esc = $this->agent()->escalateToEmployee($conv, $customer, 'Billing dispute needs human review');

        $this->assertEquals('pending', $esc->status);
        $this->assertEquals('escalated', $conv->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai.escalated']);
    }

    /** @test */
    public function deterministic_answers_saved_with_sources_and_no_provider()
    {
        $customer = $this->customer();
        Ticket::create(['customer_id' => $customer->id, 'subject' => 'Deterministic probe',
            'description' => 'x', 'priority' => 'medium']);
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);

        // No provider configured in testing → would fall back; deterministic
        // path answers directly without any provider call.
        $result = app(\App\Services\Ai\AiChatService::class)->processMessage($conv, 'Show my tickets please');
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('deterministic', $result);
        $this->assertStringContainsString('Deterministic probe', $result['message']);
    }
}
