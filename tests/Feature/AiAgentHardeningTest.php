<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiEscalation;
use App\Models\AiSetting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Ai\AiChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hardening tests for the AI agent confirmation + escalation paths:
 * ticket drafts use the single authorized pipeline (validation, SLA,
 * audit, notification), escalations notify staff, drafts are
 * account-bound, and the admin system prompt is honored.
 */
class AiAgentHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $email = 'harden.a@example.test'): User
    {
        return User::factory()->create(['role' => 'customer', 'is_active' => true,
            'email' => $email, 'email_verified_at' => now(), 'phone_verified_at' => now()]);
    }

    /** @test */
    public function ticket_draft_uses_authorized_pipeline_with_audit_and_notification()
    {
        $customer = $this->customer();
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);

        $result = app(AiChatService::class)->createTicketFromDraft($conv, [
            'subject' => 'Hardened draft ticket',
            'description' => 'Detailed problem description for pipeline check.',
            'category' => 'General Support',
            'priority' => 'high',
        ]);

        $this->assertTrue($result['success']);
        $ticket = $result['ticket'];
        $this->assertEquals($customer->id, $ticket->customer_id);
        $this->assertEquals($ticket->id, $conv->fresh()->related_ticket_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai.ticket_created']);
        $this->assertDatabaseHas('notifications', ['type' => 'ticket_created', 'notifiable_id' => $customer->id]);
    }

    /** @test */
    public function ticket_draft_rejects_cross_account_conversation()
    {
        $a = $this->customer('harden.alpha@example.test');
        $b = $this->customer('harden.beta@example.test');
        // Conversation owned by B, but the resolved user is A (tampered binding).
        $conv = AiConversation::create(['user_id' => $b->id, 'status' => 'active']);
        $conv->setRelation('user', $a);

        try {
            app(AiChatService::class)->createTicketFromDraft($conv, [
                'subject' => 'Hijack', 'description' => 'Cross-account draft attempt content.',
            ]);
            $this->fail('expected 403');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
        $this->assertEquals(0, Ticket::where('customer_id', $a->id)->count());
    }

    /** @test */
    public function controller_escalation_notifies_staff_and_audits()
    {
        $customer = $this->customer();
        $staff = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);
        $conv->messages()->create(['role' => 'user', 'content' => 'Need a human please.']);

        $this->actingAs($customer)
            ->postJson('/api/ai/escalate', ['conversation_id' => $conv->id, 'reason' => 'Wants human review'])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertEquals('escalated', $conv->fresh()->status);
        $this->assertEquals(1, AiEscalation::where('conversation_id', $conv->id)->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'ai.escalated']);
        $this->assertDatabaseHas('notifications', ['type' => 'ai_escalation', 'notifiable_id' => $staff->id]);
    }

    /** @test */
    public function admin_system_prompt_is_honored_in_generated_prompt()
    {
        AiSetting::set('ai_system_prompt', 'Test-company directive: always mention test-directive-xyz.');
        $customer = $this->customer();
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);

        $svc = app(AiChatService::class);
        $ref = new \ReflectionMethod($svc, 'buildSystemPrompt');
        $ref->setAccessible(true);
        $prompt = $ref->invoke($svc, $conv, 'Hello', []);

        $this->assertStringContainsString('test-directive-xyz', $prompt);
    }
}
