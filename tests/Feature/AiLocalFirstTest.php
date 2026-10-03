<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Ai\AgentGateway;
use App\Services\Ai\AiProviderException;
use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\OllamaProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Local-first AI policy matrix (all HTTP faked — no live cloud calls).
 *
 * Default posture: Ollama primary, cloud fallback DISABLED. A stored API
 * key alone must never trigger a cloud request. Approved fallback
 * (explicit flag + named provider) is exercised in dedicated tests only.
 */
class AiLocalFirstTest extends TestCase
{
    use RefreshDatabase;

    private const LOCAL_CHAT = 'http://127.0.0.1:11434/api/chat';

    private const LOCAL_TAGS = 'http://127.0.0.1:11434/api/tags';

    private const CLOUD = 'https://openrouter.ai/api/v1/chat/completions';

    protected function setUp(): void
    {
        parent::setUp();
        AiProviderFactory::reset();
        // Hermetic local-first defaults (mirror shipped config defaults).
        config()->set('services.ai.default_provider', 'ollama');
        config()->set('services.ai.fallback_enabled', false);
        config()->set('services.ai.fallback_provider', '');
        config()->set('services.openrouter.api_key', 'dormant-key-marker-8821');
        config()->set('agent.chat_enabled', true);
        config()->set('agent.max_tokens', 512);
    }

    protected function tearDown(): void
    {
        AiProviderFactory::reset();
        parent::tearDown();
    }

    private function localChatOk(string $content = 'Local answer.'): void
    {
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response([
                'message' => ['content' => $content],
                'prompt_eval_count' => 10,
                'eval_count' => 5,
            ], 200),
        ]);
    }

    private function assertCloudUntouched(): void
    {
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'openrouter'));
    }

    private function sentCountTo(string $needle): int
    {
        return collect(Http::recorded())
            ->filter(fn ($pair) => str_contains($pair[0]->url(), $needle))
            ->count();
    }

    /** @test */
    public function local_success_returns_local_answer_without_cloud()
    {
        $this->localChatOk();

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertTrue($out['success']);
        $this->assertEquals('ollama', $out['via']);
        $this->assertFalse($out['fallback']);
        $this->assertEquals('Local answer.', $out['content']);
        $this->assertCloudUntouched();
    }

    /** @test */
    public function local_timeout_fails_safe_without_cloud_when_fallback_disabled()
    {
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(null, 408),
            self::CLOUD => Http::response(['choices' => [['message' => ['content' => 'Cloud lured.']]]], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::TIMEOUT, $out['error_category']);
        $this->assertTrue($out['escalation_available']);
        $this->assertCloudUntouched();
    }

    /** @test */
    public function local_connection_refusal_fails_safe_without_cloud()
    {
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => function () {
                throw new \GuzzleHttp\Exception\ConnectException(
                    'cURL error 7: connection refused',
                    new \GuzzleHttp\Psr7\Request('POST', 'test')
                );
            },
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::CONNECTION_ERROR, $out['error_category']);
        $this->assertCloudUntouched();
    }

    /** @test */
    public function missing_local_model_reports_sanitized_error()
    {
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'other:1']]], 200),
            self::LOCAL_CHAT => Http::response('model gone not found', 404),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::MODEL_MISSING, $out['error_category']);
        $this->assertStringNotContainsString('gone', json_encode($out));
        $this->assertCloudUntouched();
    }

    /** @test */
    public function stored_api_key_alone_never_triggers_cloud_use()
    {
        // Key configured (setUp), fallback disabled, cloud would succeed.
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(null, 500),
            self::CLOUD => Http::response([
                'choices' => [['message' => ['content' => 'Cloud answer.']]],
                'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
            ], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::SERVER_ERROR, $out['error_category']);
        $this->assertCloudUntouched();
    }

    /** @test */
    public function approved_cloud_fallback_succeeds_after_local_failure()
    {
        config()->set('services.ai.fallback_enabled', true);
        config()->set('services.ai.fallback_provider', 'openrouter');
        AiProviderFactory::reset();
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(null, 500),
            self::CLOUD => Http::response([
                'choices' => [['message' => ['content' => 'Approved cloud answer.']]],
                'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 5],
            ], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertTrue($out['success']);
        $this->assertEquals('openrouter', $out['via']);
        $this->assertTrue($out['fallback']);
    }

    /** @test */
    public function approved_cloud_billing_failure_is_categorized_without_loops()
    {
        config()->set('services.ai.fallback_enabled', true);
        config()->set('services.ai.fallback_provider', 'openrouter');
        AiProviderFactory::reset();
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(null, 500),
            self::CLOUD => Http::response(['error' => ['message' => 'quota gone']], 402),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::BILLING_ERROR, $out['error_category']);
        $this->assertEquals(1, $this->sentCountTo('openrouter'));
    }

    /** @test */
    public function approved_cloud_rate_limit_is_categorized()
    {
        // NOTE: one Http::fake per test — Laravel keeps the first stub
        // registered for a URL, so re-faking the same URL has no effect.
        config()->set('services.ai.fallback_enabled', true);
        config()->set('services.ai.fallback_provider', 'openrouter');
        AiProviderFactory::reset();
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(null, 500),
            self::CLOUD => Http::response('nope', 429),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::RATE_LIMITED, $out['error_category']);
        $this->assertEquals(1, $this->sentCountTo('openrouter'));
    }

    /** @test */
    public function approved_cloud_auth_failure_is_categorized()
    {
        config()->set('services.ai.fallback_enabled', true);
        config()->set('services.ai.fallback_provider', 'openrouter');
        AiProviderFactory::reset();
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(null, 500),
            self::CLOUD => Http::response('nope', 401),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::AUTH_ERROR, $out['error_category']);
        $this->assertEquals(1, $this->sentCountTo('openrouter'));
    }

    /** @test */
    public function both_providers_failing_preserves_escalation_path()
    {
        config()->set('services.ai.fallback_enabled', true);
        config()->set('services.ai.fallback_provider', 'openrouter');
        AiProviderFactory::reset();
        Http::fake([
            self::LOCAL_TAGS => Http::response(null, 500),
            self::LOCAL_CHAT => Http::response(null, 500),
            self::CLOUD => Http::response(null, 503),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertTrue($out['fallback']);
        $this->assertTrue($out['escalation_available']);
        $this->assertStringContainsString('temporarily unavailable', $out['message']);
    }

    /** @test */
    public function malformed_provider_responses_are_rejected_safely()
    {
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(['unexpected' => 'shape'], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(AiProviderException::INVALID_RESPONSE, $out['error_category']);
        $this->assertCloudUntouched();
    }

    /** @test */
    public function oversized_prompts_are_rejected_before_any_provider_contact()
    {
        config()->set('services.ai.max_prompt_chars', 50);
        Http::fake([
            self::LOCAL_CHAT => Http::response(['message' => ['content' => 'x']], 200),
        ]);

        $out = app(AgentGateway::class)->chat(null, [
            ['role' => 'user', 'content' => str_repeat('a', 5000)],
        ]);

        $this->assertFalse($out['success']);
        $this->assertEquals('prompt_too_large', $out['error_category']);
        Http::assertNothingSent();
    }

    /** @test */
    public function output_size_is_bounded_by_configured_max_tokens()
    {
        $this->localChatOk();

        app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']], ['max_tokens' => 100000]);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $sent = $payload['options']['num_predict'] ?? $payload['max_tokens'] ?? null;

            return $sent !== null && (int) $sent <= 512;
        });
    }

    /** @test */
    public function retry_budget_is_one_attempt_per_provider()
    {
        config()->set('services.ai.fallback_enabled', true);
        config()->set('services.ai.fallback_provider', 'openrouter');
        AiProviderFactory::reset();
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response(null, 500),
            self::CLOUD => Http::response(null, 500),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'Hi']]);

        $this->assertFalse($out['success']);
        $this->assertEquals(1, $this->sentCountTo('11434/api/chat'));
        $this->assertEquals(1, $this->sentCountTo('openrouter'));
    }

    /** @test */
    public function guest_tool_and_knowledge_access_stays_denied_regardless_of_provider()
    {
        config()->set('agent.enabled', true);
        config()->set('agent.allowed_roles', ['customer', 'admin']);
        config()->set('agent.allowed_tools', AgentGateway::IMPLEMENTED_TOOLS);
        $gw = app(AgentGateway::class);

        $this->assertFalse($gw->authorizeTool(null, 'search_knowledge_base')['allowed']);
        $this->assertFalse($gw->authorizeTool(null, 'escalate_to_employee')['allowed']);
    }

    /** @test */
    public function customer_data_isolation_survives_provider_changes()
    {
        config()->set('agent.enabled', true);
        config()->set('agent.allowed_roles', ['customer']);
        config()->set('agent.allowed_tools', AgentGateway::IMPLEMENTED_TOOLS);
        $gw = app(AgentGateway::class);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $auth = $gw->authorizeTool($customer, 'get_customer_tickets', ['customer_id' => $customer->id + 999]);
        $this->assertFalse($auth['allowed']);
    }

    /** @test */
    public function local_error_bodies_and_keys_stay_out_of_results()
    {
        config()->set('services.openrouter.api_key', 'dormant-key-marker-8821');
        Http::fake([
            self::LOCAL_TAGS => Http::response(['models' => [['name' => 'llama3.2:latest']]], 200),
            self::LOCAL_CHAT => Http::response('secret-body-marker-5519 exploded', 500),
        ]);

        $out = app(AgentGateway::class)->chat(null, [['role' => 'user', 'content' => 'balance query secret-prompt-7741']]);

        $this->assertFalse($out['success']);
        $flat = json_encode($out);
        $this->assertStringNotContainsString('secret-body-marker-5519', $flat);
        $this->assertStringNotContainsString('dormant-key-marker-8821', $flat);
        $this->assertStringNotContainsString('secret-prompt-7741', $flat);
        $this->assertCloudUntouched();
    }

    /** @test */
    public function ollama_provider_surfaces_categorized_errors_without_bodies()
    {
        Http::fake([
            self::LOCAL_CHAT => Http::response('disk-path-marker-3091 leak', 500),
        ]);

        try {
            (new OllamaProvider())->chat([['role' => 'user', 'content' => 'Hi']]);
            $this->fail('expected AiProviderException');
        } catch (AiProviderException $e) {
            $this->assertStringNotContainsString('disk-path-marker-3091', $e->getMessage());
            $this->assertEquals(AiProviderException::SERVER_ERROR, $e->getCategory());
        }
    }
}
