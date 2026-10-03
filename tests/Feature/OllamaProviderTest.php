<?php

namespace Tests\Feature;

use App\Models\AiSetting;
use App\Models\User;
use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\OllamaProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Local Ollama provider: centralized config, response mapping, sanitized
 * errors, health diagnostics, factory default, admin model override.
 * Live-server verification is manual (see docs/AI_AGENT.md); here all
 * HTTP is faked so CI never depends on localhost:11434.
 */
class OllamaProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        AiProviderFactory::reset();
        parent::tearDown();
    }

    /** @test */
    public function chat_maps_ollama_response_with_zero_cost()
    {
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response([
                'message' => ['content' => 'We offer penetration testing.'],
                'prompt_eval_count' => 120,
                'eval_count' => 18,
            ], 200),
        ]);

        $result = (new OllamaProvider())->chat([['role' => 'user', 'content' => 'Hi']]);

        $this->assertEquals('We offer penetration testing.', $result['content']);
        $this->assertEquals(138, $result['tokens_used']);
        $this->assertEquals(120, $result['input_tokens']);
        $this->assertEquals(18, $result['output_tokens']);
        $this->assertEquals(0.0, $result['cost']);
        $this->assertEquals('llama3.2:latest', $result['model']);
    }

    /** @test */
    public function chat_failure_throws_without_leaking_server_body()
    {
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response('model "nope" not found, internal=/data/x', 404),
        ]);

        try {
            (new OllamaProvider())->chat([['role' => 'user', 'content' => 'Hi']]);
            $this->fail('expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringNotContainsString('/data/x', $e->getMessage());
            $this->assertStringNotContainsString('nope', $e->getMessage());
        }
    }

    /** @test */
    public function availability_reflects_tags_endpoint()
    {
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response(['models' => []], 200)]);
        $this->assertTrue((new OllamaProvider())->isAvailable());
    }

    /** @test */
    public function availability_is_false_when_server_errors()
    {
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response(null, 500)]);
        $this->assertFalse((new OllamaProvider())->isAvailable());
    }

    /** @test */
    public function health_check_reports_model_presence()
    {
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response([
            'models' => [['name' => 'llama3.2:latest']],
        ], 200)]);

        $health = (new OllamaProvider())->healthCheck();

        $this->assertEquals('ollama', $health['provider']);
        $this->assertTrue($health['reachable']);
        $this->assertTrue($health['model_available']);
        $this->assertContains('llama3.2:latest', $health['models']);
        $this->assertNull($health['error']);
        $this->assertNotNull($health['checked_at']);
    }

    /** @test */
    public function health_check_flags_missing_model_and_offline_server()
    {
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response(['models' => [['name' => 'other:1']]], 200)]);
        $health = (new OllamaProvider())->healthCheck();
        $this->assertTrue($health['reachable']);
        $this->assertFalse($health['model_available']);
        $this->assertEquals('model_missing', $health['error']);
    }

    /** @test */
    public function health_check_flags_offline_server_without_secrets()
    {
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response(null, 500)]);
        $offline = (new OllamaProvider())->healthCheck();
        $this->assertFalse($offline['reachable']);
        $this->assertEquals('server_error', $offline['error']);
        $this->assertArrayNotHasKey('api_token', $offline);
    }

    /** @test */
    public function factory_defaults_to_ollama_and_honors_admin_override()
    {
        config(['services.ai.default_provider' => 'ollama']);
        AiProviderFactory::reset();
        $this->assertEquals('ollama', AiProviderFactory::make()->getName());

        AiSetting::set('ai_provider', 'openai');
        AiProviderFactory::reset();
        $this->assertEquals('openai', AiProviderFactory::make()->getName());
        AiProviderFactory::reset();
    }

    /** @test */
    public function admin_model_setting_overrides_provider_default()
    {
        AiSetting::set('ai_model', 'qwen2.5-coder:14b');
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response([
            'models' => [['name' => 'qwen2.5-coder:14b']],
        ], 200)]);

        $health = (new OllamaProvider())->healthCheck();
        $this->assertEquals('qwen2.5-coder:14b', $health['model']);
        $this->assertTrue($health['model_available']);
    }

    /** @test */
    public function admin_settings_page_shows_provider_health_panel()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        Http::fake(['http://127.0.0.1:11434/api/tags' => Http::response(['models' => []], 200)]);

        $this->actingAs($admin)
            ->get(route('admin.ai.settings'))
            ->assertStatus(200)
            ->assertSee('Provider Health', false)
            ->assertSee('Ollama (local server)', false);
    }
}
