<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Ai\AiChatService;
use App\Services\Ai\AiProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeSupportProvider implements AiProviderInterface
{
    public static array $lastMessages = [];

    public function chat(array $messages, array $options = []): array
    {
        self::$lastMessages = $messages;
        return [
            'content' => 'We offer penetration testing and vulnerability assessment. **Request a quote** to proceed.',
            'tokens_used' => 42,
            'model' => 'fake-support-1',
            'cost' => 0.0,
        ];
    }

    public function getName(): string
    {
        return 'fake';
    }

    public function isAvailable(): bool
    {
        return true;
    }
}

/**
 * AI customer-support configuration: persona prompt, grounded sources,
 * suggestions, escalation, ticket drafts, KB update reflection.
 */
class AiSupportTest extends TestCase
{
    use RefreshDatabase;

    private function serviceWithFakeProvider(): AiChatService
    {
        $svc = app(AiChatService::class);
        $ref = new \ReflectionProperty(AiChatService::class, 'provider');
        $ref->setAccessible(true);
        $ref->setValue($svc, new FakeSupportProvider());
        FakeSupportProvider::$lastMessages = [];
        return $svc;
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    private function article(string $title, string $content): KbArticle
    {
        $cat = KbCategory::first() ?? KbCategory::create(['name' => 'Support', 'slug' => 'support']);
        $admin = User::factory()->create(['role' => 'admin']);
        return KbArticle::create([
            'category_id' => $cat->id, 'author_id' => $admin->id,
            'title' => $title, 'slug' => \Illuminate\Support\Str::slug($title) . '-' . \Illuminate\Support\Str::random(4),
            'content' => $content, 'visibility' => 'public', 'is_published' => true,
        ]);
    }

    /** @test */
    public function system_prompt_covers_support_persona_process_nav_and_escalation()
    {
        $svc = app(AiChatService::class);
        $method = new \ReflectionMethod(AiChatService::class, 'buildSystemPrompt');
        $method->setAccessible(true);
        $conv = AiConversation::create(['guest_name' => 'T', 'guest_email' => 't@example.test', 'status' => 'active']);
        $prompt = $method->invoke($svc, $conv, 'What services do you provide?');

        foreach ([
            'official', '/get-quote', '/portal/tickets', '/portal/invoices',
            'billing dispute', 'security incident', 'passwords, API keys',
            'never invent', 'UNTRUSTED DATA',
        ] as $needle) {
            $this->assertStringContainsString($needle, $prompt, "missing: {$needle}");
        }
    }

    /** @test */
    public function grounded_answer_appends_authorized_sources_only()
    {
        $customer = $this->user('customer');
        $this->article('Support Pen Testing Guide', 'Our penetration testing covers networks and apps. PENTEST-SRC-1');
        $svc = $this->serviceWithFakeProvider();
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);

        $result = $svc->processMessage($conv, 'Do you provide penetration testing? PENTEST-SRC-1');

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('**Sources:**', $result['message']);
        $this->assertStringContainsString('Support Pen Testing Guide', $result['message']);
        $msg = $conv->messages()->where('role', 'assistant')->latest()->first();
        $this->assertNotEmpty($msg->sources);

        // Prompt sent to provider used the CURRENT question for retrieval.
        $system = collect(FakeSupportProvider::$lastMessages)->firstWhere('role', 'system');
        $this->assertStringContainsString('PENTEST-SRC-1', $system['content']);
    }

    /** @test */
    public function kb_updates_are_reflected_without_reindex()
    {
        $customer = $this->user('customer');
        $article = $this->article('Support Hours Guide', 'Old hours text HOURS-V1-MARKER.');
        $svc = $this->serviceWithFakeProvider();
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);

        $svc->processMessage($conv, 'What are the hours? HOURS-V1-MARKER');
        $first = $conv->fresh()->messages()->where('role', 'assistant')->latest()->first();
        $this->assertStringContainsString('Support Hours Guide', $first->content);

        $article->update(['content' => 'New hours text HOURS-V2-MARKER.']);
        $conv2 = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);
        $result = $svc->processMessage($conv2, 'What are the hours? HOURS-V2-MARKER');
        $this->assertStringContainsString('Support Hours Guide', $result['message']);
    }

    /** @test */
    public function ticket_draft_requires_login_and_confirms_for_customers()
    {
        $svc = app(AiChatService::class);

        // Guest is guided to log in, no ticket created.
        $guest = AiConversation::create(['guest_name' => 'G', 'guest_email' => 'g@example.test', 'status' => 'active']);
        $result = $svc->processMessage($guest, 'Please create a ticket for my issue');
        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('ticket', $result);
        $this->assertStringContainsString('log in', strtolower($result['message']));

        // Customer gets a draft requiring explicit confirmation.
        $customer = $this->user('customer');
        $cc = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);
        $draft = $svc->processMessage($cc, 'I want to create a ticket about email outage');
        $this->assertTrue($draft['ticket_draft']);
        $this->assertEquals(0, Ticket::where('customer_id', $customer->id)->count());

        // Confirm endpoint rejects guests.
        $this->postJson(route('ai.ticket.confirm'), [
            'conversation_id' => $cc->id, 'subject' => 'x', 'description' => 'y',
        ])->assertStatus(401);
    }

    /** @test */
    public function escalation_returns_human_handoff_without_provider()
    {
        $customer = $this->user('customer');
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);
        $result = app(AiChatService::class)->processMessage($conv, 'I want to speak to a human about a refund decision');

        $this->assertTrue($result['escalated']);
        $this->assertStringContainsString('human support agent', $result['message']);
        $this->assertEquals('escalated', $conv->fresh()->status);
    }

    /** @test */
    public function suggested_questions_match_spec_and_role()
    {
        $svc = app(AiChatService::class);
        $guest = $svc->getSuggestedQuestions(null);
        foreach (['What cybersecurity services do you provide?', 'How can I request a quote?', 'How can I contact support?'] as $q) {
            $this->assertContains($q, $guest);
        }
        $this->assertNotContains('Where can I see my orders?', $guest);

        $customerQs = $svc->getSuggestedQuestions($this->user('customer'));
        $this->assertContains('Where can I see my orders?', $customerQs);
    }

    /** @test */
    public function home_page_serves_working_widget_to_guests()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('function aiChat', false);
        $response->assertSee('AI Support Assistant', false);
    }

    /** @test */
    public function authenticated_layout_serves_widget_to_staff_and_customers()
    {
        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertStatus(200)->assertSee('function aiChat', false);

        $customer = $this->user('customer');
        $this->actingAs($customer)->get(route('portal.dashboard'))
            ->assertStatus(200)->assertSee('function aiChat', false);
    }

    /** @test */
    public function guest_journey_ask_then_identity_required_for_requests()
    {
        // 1. Guest starts a conversation with contact details (no account needed).
        $start = $this->postJson(route('ai.conversation.start'), [
            'guest_name' => 'Guest User', 'guest_email' => 'guest-journey@example.test',
        ])->assertStatus(200);
        $conversationId = $start->json('conversation_id');
        $this->assertNotEmpty($conversationId);
        $this->assertStringContainsString('AI Support Assistant', $start->json('welcome_message'));
        // Guest session binding (mirrors the widget's X-Session-ID header).
        $guestHeaders = ['X-Session-ID' => $start->json('session_id')];

        // 2. Guest asks a general service question (works, graceful fallback offline).
        $ask = $this->withHeaders($guestHeaders)->postJson(route('ai.message.send'), [
            'conversation_id' => $conversationId, 'message' => 'What cybersecurity services do you provide?',
        ])->assertStatus(200);
        $this->assertTrue($ask->json('success'));

        // 3. Guest requests a ticket draft, but confirmation demands login.
        $this->withHeaders($guestHeaders)->postJson(route('ai.message.send'), [
            'conversation_id' => $conversationId, 'message' => 'Please create a ticket for my outage',
        ])->assertStatus(200);
        $this->withHeaders($guestHeaders)->postJson(route('ai.ticket.confirm'), [
            'conversation_id' => $conversationId, 'subject' => 'Outage', 'description' => 'Down',
        ])->assertStatus(401);
        $this->assertEquals(0, Ticket::count());
    }

    /** @test */
    public function customer_confirms_request_with_verified_identity_only()
    {
        $customer = $this->user('customer');
        $other = $this->user('customer');
        $conv = AiConversation::create(['user_id' => $customer->id, 'status' => 'active']);

        // Draft carries an explicit confirmation summary, creates nothing.
        $draft = app(AiChatService::class)->processMessage($conv, 'create a ticket: VPN down for office');
        $this->assertTrue($draft['ticket_draft']);
        $this->assertArrayHasKey('subject', $draft['draft_data']);
        $this->assertEquals(0, Ticket::where('customer_id', $customer->id)->count());

        // Foreign customer cannot confirm on this conversation.
        $this->actingAs($other)->postJson(route('ai.ticket.confirm'), [
            'conversation_id' => $conv->id, 'subject' => 'Hijack', 'description' => 'x',
        ])->assertStatus(403);

        // Owner confirms: ticket is owned by the SESSION identity, not input.
        $this->actingAs($customer)->postJson(route('ai.ticket.confirm'), [
            'conversation_id' => $conv->id, 'subject' => 'VPN down', 'description' => 'Office VPN down',
        ])->assertStatus(200);
        $ticket = Ticket::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals('VPN down', $ticket->subject);
        $this->assertNotNull($ticket->ticket_number);
    }
}
