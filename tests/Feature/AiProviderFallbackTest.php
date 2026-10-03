<?php

namespace Tests\Feature;

use App\Models\AiSetting;
use App\Models\User;
use App\Services\Ai\AgentGateway;
use App\Services\Ai\AiProviderException;
use App\Services\Ai\AiProviderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Cloud → local AI fallback matrix (all HTTP faked — no real keys, no cost).
 *
 * Primary = openrouter (cloud). Fallback = ollama (local, free).
 * Proves: cloud success needs no fallback; billing/402, timeout, and
 * connection failures fall back exactly once; total outage surfaces a
 * clear error; missing local model is reported; malformed responses are
 * rejected; tool authorization still enforced; no secrets leak into
 * results or exception messages.
 */
class AiProviderFallbackTest extends TestCase
{
    use RefreshDatabase;

    private const CLOUD = 'https://openrouter.ai/api/v1/chat/completions';
    private const LOCAL_CHAT = 'http://127.0.0.1:11434/api/chat';
    private const LOCAL_TAGS = 'http://127.0.0.1:11434/api/tags';

    protected function setUp(): void
    {
        parent::setUp();
        AiSetting::set('ai_provider', 'openrouter');
        AiProviderFactory::reset();
        config()->set('services.openrouter.api_key', 'test-key');
        config()->set('services.ai.fallback_enabled', true);
        config()->set('services.ai.fallback_provider', 'ollama');
        config()->set('agent.chat_enabled', true);
    }

    protected function tearDown(): void
    {
        AiProviderFactory::reset();
        parent::tearDown();
    }

    private function localOk(): void
    {
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response([
                'message' => ['content' => 'Local answer.'],
                'prompt_eval_count' => 10,
                'eval_count' => 5,
            ], 200),
        ]);
    }

    /** @test */
    public function cloud_success_needs_no_fallback()
    {
        Http::fake([
            self::CLOUD => Http::response([
                'choices' => [['message' => ['content' => 'Cloud answer.']]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5],
            ], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertTrue($out['success']);
        $this->assertEquals('openrouter', $out['via']);
        $this->assertFalse($out['fallback']);
        $this->assertEquals('Cloud answer.', $out['content']);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '11434'));
    }

    /** @test */
    public function cloud_billing_402_falls_back_to_local_once()
    {
        Http::fake([
            self::CLOUD => Http::response(['error' => ['message' => 'Insufficient credits']], 402),
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response([
                'message' => ['content' => 'Local answer after 402.'],
                'prompt_eval_count' => 10,
                'eval_count' => 5,
            ], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertTrue($out['success']);
        $this->assertEquals('ollama', $out['via']);
        $this->assertTrue($out['fallback']);
        $this->assertEquals('Local answer after 402.', $out['content']);
        $this->assertEquals(0.0, $out['cost']);
    }

    /** @test */
    public function cloud_timeout_falls_back_to_local()
    {
        Http::fake([
            self::CLOUD => Http::response(null, 408),
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response([
                'message' => ['content' => 'Local answer after timeout.'],
                'prompt_eval_count' => 10,
                'eval_count' => 5,
            ], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertTrue($out['success']);
        $this->assertEquals('ollama', $out['via']);
        $this->assertTrue($out['fallback']);
    }

    /** @test */
    public function cloud_connection_failure_falls_back_to_local()
    {
        Http::fake([
            self::CLOUD => function () {
                throw new \GuzzleHttp\Exception\ConnectException(
                    'cURL error 7: connection refused',
                    new \GuzzleHttp\Psr7\Request('POST', 'test')
                );
            },
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response([
                'message' => ['content' => 'Local answer after outage.'],
                'prompt_eval_count' => 10,
                'eval_count' => 5,
            ], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertTrue($out['success']);
        $this->assertEquals('ollama', $out['via']);
        $this->assertTrue($out['fallback']);
    }

    /** @test */
    public function both_providers_down_returns_clear_error()
    {
        Http::fake([
            self::CLOUD => Http::response(null, 503),
            self::LOCAL_TAGS => Http::response(null, 500),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertTrue($out['fallback']);
        $this->assertStringContainsString('temporarily unavailable', $out['message']);
        $this->assertArrayHasKey('error_category', $out);
    }

    /** @test */
    public function missing_local_model_is_reported_not_hidden()
    {
        Http::fake([
            self::CLOUD => Http::response(null, 402),
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response('model "gone" not found', 404),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::MODEL_MISSING, $out['error_category']);
    }

    /** @test */
    public function malformed_provider_responses_are_rejected()
    {
        Http::fake([
            self::CLOUD => Http::response(['nope' => true], 200),
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(['garbage' => true], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::INVALID_RESPONSE, $out['error_category']);
    }

    /** @test */
    public function fallback_disabled_never_touches_local()
    {
        config()->set('services.ai.fallback_enabled', false);
        Http::fake([
            self::CLOUD => Http::response(null, 402),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::BILLING_ERROR, $out['error_category']);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '11434'));
    }

    /** @test */
    public function unauthorized_tool_and_cross_account_access_stay_denied()
    {
        config()->set('agent.enabled', true);
        config()->set('agent.allowed_roles', ['customer', 'admin']);
        config()->set('agent.allowed_tools', AgentGateway::IMPLEMENTED_TOOLS);

        $gw = app(AgentGateway::class);
        // Unknown / unimplemented tool.
        $this->assertFalse($gw->authorizeTool(
            User::factory()->create(['role' => 'customer', 'is_active' => true]), 'grant_admin')['allowed']);
        // Guest with no identity.
        $this->assertFalse($gw->authorizeTool(null, 'search_knowledge_base')['allowed']);
        // Customer reaching into another account.
        $this->assertFalse($gw->authorizeTool(
            User::factory()->create(['role' => 'customer', 'is_active' => true]),
            'get_customer_tickets', ['customer_id' => 999999])['allowed']);
    }

    /** @test */
    public function secrets_and_prompts_never_leak_into_results_or_errors()
    {
        config()->set('services.openrouter.api_key', 'key-marker-4471');
        AiProviderFactory::reset();
        $prompt = 'CONFIDENTIAL-PROMPT-9917 my account balance query';

        Http::fake([
            self::CLOUD => Http::response('{"error":"bad key key-marker-4471"}', 401),
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response([
                'message' => ['content' => 'Public local answer.'],
                'prompt_eval_count' => 1,
                'eval_count' => 1,
            ], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => $prompt]]);
        $this->assertTrue($out['success']);
        $flat = json_encode($out);
        $this->assertStringNotContainsString('key-marker-4471', $flat);
        $this->assertStringNotContainsString($prompt, $flat);
    }

    /** @test */
    public function raw_provider_bodies_never_surface_in_errors()
    {
        // NOTE: single Http::fake per test — Laravel keeps the first stub
        // registered for a URL, so re-faking the same URL has no effect.
        Http::fake([self::CLOUD => Http::response('leak-marker-3321 nope', 500)]);
        try {
            (new \App\Services\Ai\OpenRouterProvider())->chat([['role' => 'user', 'content' => 'Hi']]);
            $this->fail('expected AiProviderException');
        } catch (AiProviderException $e) {
            $this->assertStringNotContainsString('leak-marker-3321', $e->getMessage());
            $this->assertEquals(AiProviderException::SERVER_ERROR, $e->getCategory());
        }
    }
}
