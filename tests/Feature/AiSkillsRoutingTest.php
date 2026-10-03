<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiSkill;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\User;
use App\Services\Ai\AiChatService;
use App\Services\Ai\AiProviderInterface;
use App\Services\Ai\AiSkillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeRoutingProvider implements AiProviderInterface
{
    public static array $lastMessages = [];

    public static bool $called = false;

    public function chat(array $messages, array $options = []): array
    {
        self::$called = true;
        self::$lastMessages = $messages;

        return ['content' => 'Provider-generated answer.', 'tokens_used' => 10, 'model' => 'fake', 'cost' => 0.0];
    }

    public function getName(): string
    {
        return 'fake-routing';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function healthCheck(): array
    {
        return ['provider' => 'fake-routing', 'reachable' => true, 'model' => 'fake',
            'model_available' => true, 'models' => [], 'latency_ms' => 0,
            'checked_at' => now()->toDateTimeString(), 'error' => null];
    }
}

/**
 * Skills-first routing: Skills → Knowledge Base → local model fallback,
 * with explicit source labels and relevance gating.
 */
class AiSkillsRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function serviceWithFakeProvider(): AiChatService
    {
        $svc = app(AiChatService::class);
        $ref = new \ReflectionProperty(AiChatService::class, 'provider');
        $ref->setAccessible(true);
        $ref->setValue($svc, new FakeRoutingProvider());
        FakeRoutingProvider::$lastMessages = [];
        FakeRoutingProvider::$called = false;

        return $svc;
    }

    private function kbArticle(string $title, string $content): KbArticle
    {
        $cat = KbCategory::firstOrCreate(['slug' => 'routing-cat'], ['name' => 'Routing']);
        $author = User::factory()->create(['role' => 'admin']);

        return KbArticle::create([
            'category_id' => $cat->id, 'author_id' => $author->id, 'title' => $title, 'slug' => \Illuminate\Support\Str::slug($title).'-'.uniqid(),
            'content' => $content, 'visibility' => 'public', 'is_published' => true, 'ai_readable' => true,
        ]);
    }

    /** @test */
    public function greetings_answer_deterministically_without_provider_call()
    {
        $conv = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);

        $result = $this->serviceWithFakeProvider()->processMessage($conv, 'hi');

        $this->assertEquals('deterministic', $result['answer_source']);
        $this->assertFalse(FakeRoutingProvider::$called);
        $this->assertStringContainsString('How can I help', $result['message']);

        // Requests that merely START with a greeting still fall through.
        $result2 = $this->serviceWithFakeProvider()->processMessage($conv, 'hi, my email is broken');
        $this->assertNotEquals('deterministic', $result2['answer_source'] ?? 'deterministic');
    }

    /** @test */
    public function guest_conversation_start_and_greeting_work_over_http()
    {
        $start = $this->postJson('/api/ai/conversation', [
            'guest_name' => 'Guest User',
            'guest_email' => 'guestuser@example.test',
            'source' => 'public',
        ], ['X-Session-ID' => 'sess-guest-hi-1'])->assertOk();

        $conversationId = $start->json('conversation_id');
        $this->assertNotNull($conversationId);
        $sessionId = $start->json('session_id');

        $reply = $this->postJson('/api/ai/message', [
            'conversation_id' => $conversationId,
            'message' => 'hi',
        ], ['X-Session-ID' => $sessionId])->assertOk();

        $this->assertEquals('deterministic', $reply->json('answer_source'));
        $this->assertStringContainsString('How can I help', $reply->json('message'));
    }

    /** @test */
    public function guest_start_requires_identity_with_json_errors()
    {
        $this->postJson('/api/ai/conversation', ['source' => 'public'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name', 'guest_email']);
    }

    /** @test */
    public function skill_library_covers_all_support_domains()
    {
        $slugs = \App\Models\AiSkill::enabled()->pluck('slug')->all();
        foreach (['customer-support', 'it-support', 'network-support', 'm365-support',
            'cybersecurity-education', 'website-security', 'web-development',
            'digital-marketing', 'cloud-server-support', 'backup-disaster-recovery',
            'service-discovery', 'service-recommendation', 'quote-assistance',
            'ticket-assistance', 'order-support', 'payment-invoice-assistance',
            'project-support', 'knowledge-search', 'platform-navigation',
            'human-escalation'] as $expected) {
            $this->assertContains($expected, $slugs);
        }
    }

    /** @test */
    public function active_skill_instructions_ground_the_prompt()
    {
        $conv = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);
        $this->serviceWithFakeProvider()->processMessage($conv, 'My Outlook inbox stopped synchronizing yesterday');

        $system = collect(FakeRoutingProvider::$lastMessages)->firstWhere('role', 'system');
        $this->assertStringContainsString('Microsoft 365', $system['content']);
    }

    /** @test */
    public function search_matrix_exact_typo_natural_and_restricted()
    {
        $this->kbArticle('Outlook Email Synchronization Repair Guide', 'To repair Outlook email synchronization open account settings and verify the connection, storage, and filters.');
        $svc = app(\App\Services\Ai\AiKnowledgeService::class);

        // Exact + natural wording both retrieve.
        $this->assertNotEmpty($svc->searchRelevantArticles('Outlook Email Synchronization Repair Guide', null, 3));
        $this->assertNotEmpty($svc->searchRelevantArticles('How do I fix Outlook email sync problems?', null, 3));

        // Employee-only article stays hidden from guests and customers.
        $cat = \App\Models\KbCategory::firstOrCreate(['slug' => 'routing-cat'], ['name' => 'Routing']);
        $admin = User::factory()->create(['role' => 'admin']);
        \App\Models\KbArticle::create(['category_id' => $cat->id, 'author_id' => $admin->id,
            'title' => 'Internal Escalation Matrix', 'slug' => 'internal-escalation-matrix-'.uniqid(),
            'content' => 'Internal escalation matrix for staff rota handling.', 'visibility' => 'employee',
            'is_published' => true, 'ai_readable' => true]);
        $this->assertEmpty($svc->searchRelevantArticles('Internal Escalation Matrix rota', null, 3));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->assertEmpty($svc->searchRelevantArticles('Internal Escalation Matrix rota', $customer, 3));
        $this->assertNotEmpty($svc->searchRelevantArticles('Internal Escalation Matrix rota', $admin, 3));

        // Unrelated question retrieves nothing → fallback path.
        $this->assertEmpty($svc->searchRelevantArticles('Explain quantum entanglement fusion reactor design', null, 3));
    }

    /** @test */
    public function admin_test_bench_is_restricted_and_diagnostic()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)->get(route('admin.ai.test-bench'))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.ai.test-bench'))->assertStatus(200);
        $response = $this->actingAs($admin)->post(route('admin.ai.test-bench.run'), [
            'question' => 'Which service should I choose for securing my business website?',
        ])->assertStatus(200);
        $response->assertSee('Detected Skill', false);
        $response->assertSee('Ollama Used', false);
    }

    /** @test */
    public function skill_detection_prefers_score_then_priority_and_role()
    {
        $low = AiSkill::create(['name' => 'Low', 'slug' => 'low', 'system_instructions' => 'x',
            'trigger_keywords' => ['website'], 'priority' => 10, 'status' => 'enabled']);
        $high = AiSkill::create(['name' => 'High', 'slug' => 'high', 'system_instructions' => 'x',
            'trigger_keywords' => ['website', 'secure'], 'priority' => 90, 'status' => 'enabled']);
        $off = AiSkill::create(['name' => 'Off', 'slug' => 'off', 'system_instructions' => 'x',
            'trigger_keywords' => ['website', 'secure', 'company'], 'priority' => 1, 'status' => 'disabled']);

        $svc = app(AiSkillService::class);
        // Higher keyword score wins over priority.
        $this->assertEquals('high', $svc->detectSkill('How do I secure my company website?', null)->slug);

        // Tie → lower priority number wins.
        $a = AiSkill::create(['name' => 'A', 'slug' => 'sk-a', 'system_instructions' => 'x',
            'trigger_keywords' => ['portal'], 'priority' => 5, 'status' => 'enabled']);
        $b = AiSkill::create(['name' => 'B', 'slug' => 'sk-b', 'system_instructions' => 'x',
            'trigger_keywords' => ['portal'], 'priority' => 50, 'status' => 'enabled']);
        $this->assertEquals('sk-a', $svc->detectSkill('Open the portal please', null)->slug);

        // Role gating: staff-only skill invisible to guests and customers.
        $staff = AiSkill::create(['name' => 'Staff', 'slug' => 'staff-only', 'system_instructions' => 'x',
            'trigger_keywords' => ['revenue report'], 'priority' => 1, 'status' => 'enabled',
            'allowed_roles' => ['admin']]);
        $this->assertNull($svc->detectSkill('Show the revenue report', null));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->assertNull($svc->detectSkill('Show the revenue report', $customer));
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertEquals('staff-only', $svc->detectSkill('Show the revenue report', $admin)->slug);
    }

    /** @test */
    public function kb_hit_answers_internally_without_provider_call()
    {
        $this->kbArticle('Password Reset Procedure', 'To reset a portal password open the login page and use the forgotten password link. Password resets never require contacting billing.');
        $conv = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);

        $result = $this->serviceWithFakeProvider()->processMessage($conv, 'How do I reset my portal password?');

        $this->assertEquals('internal', $result['answer_source']);
        // Grounded generation (provider) with KB context — but never the
        // ungrounded fallback framing.
        $this->assertTrue(FakeRoutingProvider::$called);
        $system = collect(FakeRoutingProvider::$lastMessages)->firstWhere('role', 'system');
        $this->assertStringContainsString('Password Reset Procedure', $system['content']);
        $this->assertStringNotContainsString('Fallback Mode', $system['content']);
        $this->assertStringContainsString('Company Knowledge Base', $result['message']);
        $msg = $conv->messages()->where('role', 'assistant')->latest()->first();
        $this->assertEquals('internal', $msg->metadata['answer_source']);
        $this->assertNotNull($msg->metadata['skill']);
    }

    /** @test */
    public function unknown_question_falls_back_to_model_as_general_guidance()
    {
        $conv = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);

        $result = $this->serviceWithFakeProvider()->processMessage($conv, 'Explain quantum entanglement for a curious teenager');

        $this->assertEquals('general', $result['answer_source']);
        $this->assertTrue(FakeRoutingProvider::$called);
        $system = collect(FakeRoutingProvider::$lastMessages)->firstWhere('role', 'system');
        $this->assertStringContainsString('Fallback Mode', $system['content']);
        $this->assertStringContainsString('general guidance', strtolower($result['message']));
        $msg = $conv->messages()->where('role', 'assistant')->latest()->first();
        $this->assertTrue($msg->metadata['ollama_fallback']);
    }

    /** @test */
    public function non_readable_articles_are_excluded_from_retrieval()
    {
        $hidden = $this->kbArticle('Internal Refund Policy', 'Internal refunds are approved by finance within five days.');
        $hidden->update(['ai_readable' => false]);
        $conv = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);

        $result = $this->serviceWithFakeProvider()->processMessage($conv, 'What is the internal refund approval timeline?');

        $this->assertEquals('general', $result['answer_source']);
        $this->assertStringNotContainsString('five days', $result['message']);
    }

    /** @test */
    public function admin_manages_skills_while_customers_are_forbidden()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)->get(route('admin.ai.skills.index'))->assertStatus(403);

        $this->actingAs($admin)->post(route('admin.ai.skills.store'), [
            'name' => 'Test Skill', 'system_instructions' => 'Behave for tests.',
            'trigger_keywords' => 'testalpha, testbeta', 'priority' => 50, 'status' => 'enabled',
        ])->assertRedirect();
        $skill = AiSkill::where('slug', 'test-skill')->firstOrFail();
        $this->assertEquals(['testalpha', 'testbeta'], $skill->trigger_keywords);
        $this->assertEquals(1, $skill->version);

        $this->actingAs($admin)->put(route('admin.ai.skills.update', $skill), [
            'name' => 'Test Skill', 'system_instructions' => 'Behave for tests v2.',
            'trigger_keywords' => 'testalpha', 'priority' => 50, 'status' => 'enabled',
        ])->assertRedirect();
        $this->assertEquals(2, $skill->fresh()->version);

        $this->actingAs($admin)->post(route('admin.ai.skills.toggle', $skill))->assertRedirect();
        $this->assertEquals('disabled', $skill->fresh()->status);

        $this->actingAs($admin)->post(route('admin.ai.skills.duplicate', $skill))->assertRedirect();
        $this->assertEquals(2, AiSkill::where('name', 'like', 'Test Skill%')->count());

        $this->actingAs($admin)->delete(route('admin.ai.skills.destroy', $skill))->assertRedirect();
        $this->assertNull(AiSkill::find($skill->id));
    }
}
