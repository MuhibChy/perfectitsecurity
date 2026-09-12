<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_another_ai_conversation(): void
    {
        $conversation = AiConversation::create(['guest_name' => 'Guest', 'guest_email' => 'guest@example.test']);

        $this->get("/api/ai/conversation/{$conversation->id}/messages")->assertForbidden();
        $this->withHeader('X-Session-ID', $conversation->session_id)
            ->get("/api/ai/conversation/{$conversation->id}/messages")
            ->assertOk();
    }

    public function test_customer_cannot_close_another_customers_ai_conversation(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $conversation = AiConversation::create(['user_id' => $owner->id]);

        $this->actingAs($otherCustomer)
            ->post("/api/ai/conversation/{$conversation->id}/close")
            ->assertForbidden();
    }

    public function test_support_agent_cannot_open_finance_records(): void
    {
        $agent = User::factory()->create(['role' => 'support_agent']);

        $this->actingAs($agent)->get('/admin/payments')->assertForbidden();
        $this->actingAs($agent)->get('/admin/invoices')->assertForbidden();
    }
}
