<?php

namespace Tests\Feature;

use App\Models\AiSetting;
use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\OpenRouterProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * OpenRouter provider: OpenAI-compatible mapping, cost accounting,
 * sanitized errors, factory wiring. All HTTP faked — no real key used.
 */
class OpenRouterProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        AiProviderFactory::reset();
        parent::tearDown();
    }

    /** @test */
    public function chat_maps_completion_with_cost()
    {
        Http::fake(['https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'We can help with that.']]],
            'usage' => ['prompt_tokens' => 200, 'completion_tokens' => 20],
        ], 200)]);

        $result = (new OpenRouterProvider())->chat([['role' => 'user', 'content' => 'Hi']]);

        $this->assertEquals('We can help with that.', $result['content']);
        $this->assertEquals(220, $result['tokens_used']);
        $this->assertGreaterThan(0, $result['cost']);
        $this->assertEquals('openrouter', (new OpenRouterProvider())->getName());
    }

    /** @test */
    public function chat_failure_throws_without_leaking_server_body()
    {
        Http::fake(['https://openrouter.ai/api/v1/chat/completions' => Http::response(
            '{"error":{"message":"bad key secret-xyz-123"}}', 401
        )]);

        try {
            (new OpenRouterProvider())->chat([['role' => 'user', 'content' => 'Hi']]);
            $this->fail('expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString('secret-xyz-123', $e->getMessage());
        }
    }

    /** @test */
    public function availability_tracks_key_presence()
    {
        config(['services.openrouter.api_key' => 'test-key']);
        $this->assertTrue((new OpenRouterProvider())->isAvailable());

        config(['services.openrouter.api_key' => '']);
        $this->assertFalse((new OpenRouterProvider())->isAvailable());
    }

    /** @test */
    public function factory_maps_openrouter_and_health_shape()
    {
        AiSetting::set('ai_provider', 'openrouter');
        AiProviderFactory::reset();
        $provider = AiProviderFactory::make();
        $this->assertEquals('openrouter', $provider->getName());

        $health = $provider->healthCheck();
        foreach (['provider', 'reachable', 'model', 'model_available', 'latency_ms', 'checked_at', 'error'] as $key) {
            $this->assertArrayHasKey($key, $health);
        }
        AiProviderFactory::reset();
    }

    /** @test */
    public function admin_settings_page_offers_openrouter()
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin', 'is_active' => true]);
        Http::fake(['https://openrouter.ai/api/v1/*' => Http::response(null, 500)]);
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response(['models' => []], 200)]);

        $this->actingAs($admin)
            ->get(route('admin.ai.settings'))
            ->assertStatus(200)
            ->assertSee('OpenRouter (cloud models)', false);
    }
}
