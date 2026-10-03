<?php

namespace Tests\Feature;

use App\Models\PaymentProvider;
use App\Models\User;
use App\Services\PaymentReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Go-live readiness hardening (additive).
 * Proves: readiness states, LIVE gate blocks on missing config, LIVE opens
 * when complete + writes audit, secrets never leak, finance RBAC holds.
 * Settlement/ledger behaviour is untouched (covered by existing suites).
 */
class PaymentGoLiveReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function staff(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    protected function provider(string $key = 'bkash', array $over = []): PaymentProvider
    {
        return PaymentProvider::create(array_merge([
            'key' => $key, 'name' => ucfirst($key), 'type' => 'mobile_wallet',
            'country' => 'Bangladesh', 'currencies' => ['BDT'],
            'environment' => 'test', 'status' => 'enabled', 'priority' => 10,
            'is_active' => true,
        ], $over));
    }

    /** @test */
    public function missing_provider_reports_not_configured_with_presence_only_checks()
    {
        $detail = app(PaymentReadinessService::class)->forProvider('nagad');
        $this->assertSame('NOT_CONFIGURED', $detail['state']);
        $this->assertFalse($detail['row_exists']);
        $json = json_encode($detail);
        $this->assertStringNotContainsString('shh-secret', $json);
        $this->assertArrayHasKey('credentials_configured', $detail['checks']);
        $this->assertArrayHasKey('webhook_secret_configured', $detail['checks']);
    }

    /** @test */
    public function live_activation_is_blocked_until_gates_pass()
    {
        $finance = $this->staff('finance_manager');
        $this->provider('bkash'); // enabled test row, no credentials/limits/webhook secret
        $response = $this->actingAs($finance)->post(route('admin.payments.readiness.activate', 'bkash'), [
            'confirmation' => PaymentReadinessService::CONFIRMATION_PHRASE,
            'environment' => 'live',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('payment_providers', ['key' => 'bkash', 'environment' => 'test']);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'provider.live_activated']);
    }

    /** @test */
    public function live_activation_requires_exact_confirmation_phrase()
    {
        $finance = $this->staff('finance_manager');
        $p = $this->provider('paypal', ['type' => 'gateway', 'country' => 'International', 'currencies' => ['USD'], 'min_amount' => 1, 'max_amount' => 10000]);
        $p->credentials = ['client_id' => 'x', 'client_secret' => 'y'];
        $p->webhook_secret = 'whsec-ok';
        $p->save();
        $this->actingAs($finance)->post(route('admin.payments.readiness.activate', 'paypal'), [
            'confirmation' => 'yes please activate',
            'environment' => 'live',
        ])->assertSessionHasErrors('confirmation');
        $this->assertSame('test', $p->fresh()->environment);
    }

    /** @test */
    public function live_activation_succeeds_when_complete_and_audits_without_secrets()
    {
        $finance = $this->staff('finance_manager');
        \App\Models\ExchangeRate::updateOrCreate(
            ['base_currency' => 'USD', 'target_currency' => 'EUR'],
            ['rate' => 0.92, 'fetched_at' => now()]
        );
        $p = $this->provider('paypal', ['type' => 'gateway', 'country' => 'International', 'currencies' => ['USD'], 'min_amount' => 1, 'max_amount' => 10000]);
        $p->credentials = ['client_id' => 'live-id', 'client_secret' => 'live-topsecret'];
        $p->webhook_secret = 'whsec-live';
        $p->save();

        $this->actingAs($finance)->post(route('admin.payments.readiness.activate', 'paypal'), [
            'confirmation' => PaymentReadinessService::CONFIRMATION_PHRASE,
            'environment' => 'live',
        ])->assertRedirect();

        $fresh = $p->fresh();
        $this->assertSame('live', $fresh->environment);
        $this->assertSame('live', $fresh->status);
        $audit = \App\Models\AuditLog::where('action', 'provider.live_activated')->latest('id')->first();
        $this->assertNotNull($audit);
        $blob = json_encode([$audit->description, $audit->old_values, $audit->new_values]);
        $this->assertStringNotContainsString('live-topsecret', $blob);
        $this->assertStringNotContainsString('whsec-live', $blob);
        $this->assertSame('LIVE', app(PaymentReadinessService::class)->forProvider('paypal')['state']);
    }

    /** @test */
    public function readiness_pages_are_finance_gated_and_leak_no_secrets()
    {
        $p = $this->provider('bkash');
        $p->credentials = ['api_key' => 'ultra-secret-xyz'];
        $p->webhook_secret = 'whsec-hidden-xyz';
        $p->save();

        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $this->actingAs($customer)->get(route('admin.payments.readiness'))->assertStatus(403);

        $finance = $this->staff('finance_manager');
        $this->actingAs($finance)->get(route('admin.payments.readiness'))->assertStatus(200)->assertDontSee('ultra-secret-xyz', false);
        $this->actingAs($finance)->get(route('admin.payments.readiness.show', 'bkash'))->assertStatus(200)->assertDontSee('whsec-hidden-xyz', false);
        $this->actingAs($finance)->get(route('admin.payments.health'))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.payments.config-check'))->assertStatus(200)->assertDontSee('ultra-secret-xyz', false);
        $this->actingAs($finance)->get(route('admin.payments.checklist'))->assertStatus(200);
    }

    /** @test */
    public function maintenance_state_and_bank_transfer_bank_account_gate()
    {
        $svc = app(PaymentReadinessService::class);
        $p = $this->provider('bank_transfer', ['type' => 'bank', 'currencies' => ['BDT'], 'min_amount' => 1, 'max_amount' => 5000, 'status' => 'maintenance']);
        $this->assertSame('MAINTENANCE', $svc->forProvider('bank_transfer')['state']);
        $p->update(['status' => 'enabled']);
        // No bank account seeded in fresh DB → live gate must flag it.
        $gate = $svc->canActivateLive('bank_transfer', $p->fresh());
        $this->assertFalse($gate['ok']);
        $this->assertTrue(collect($gate['blockers'])->contains(fn ($b) => str_contains($b, 'bank account')));
    }
}
