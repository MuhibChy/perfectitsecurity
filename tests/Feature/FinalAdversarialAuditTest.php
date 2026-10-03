<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ReportExportController;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\CurrencyService;
use App\Services\ServiceOrderWorkflowService;
use App\Support\UserTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Final adversarial regression tests (2026-09-24 audit): each test attacks a
 * gap found by the red-team review and fails unless the fix holds.
 */
class FinalAdversarialAuditTest extends TestCase
{
    use RefreshDatabase;

    private function customer(array $over = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer', 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'password' => bcrypt('secret'),
        ], $over));
    }

    private function staff(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag).'.'.Str::random(6).'@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'password' => bcrypt('secret'),
        ]);
    }

    private function svc(float $price = 500): Service
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-final'], ['name' => 'FINAL']);

        return Service::create([
            'category_id' => $cat->id, 'name' => '[TEST] Final Svc',
            'slug' => 't-final-'.Str::random(6),
            'short_description' => 'x', 'starting_price' => $price, 'is_active' => true,
        ]);
    }

    /** @test */
    public function stale_price_revision_cannot_be_replayed_to_force_old_price()
    {
        $c = $this->customer();
        $svc = $this->svc();
        $wf = app(ServiceOrderWorkflowService::class);
        $order = $wf->createCustomerOrder([
            'service_id' => $svc->id, 'currency' => 'USD', 'requirements' => 'scope here xx',
            'negotiate' => true,
        ], $c);
        $rev = $wf->proposePrice($order->fresh(), ['amount' => 400, 'terms' => 'offer'], $this->staff('finance_manager', 'fin'));
        $rev->update(['status' => 'rejected']);

        try {
            $wf->acceptPrice($order->fresh(), $c, $rev->id);
            $this->fail('Stale rejected revision was accepted.');
        } catch (\Throwable $e) {
            $this->assertContains($e->getStatusCode(), [403, 404, 422]);
        }
        $this->assertNotEquals('accepted', $rev->fresh()->status);
    }

    /** @test */
    public function payment_on_cancelled_order_is_rejected()
    {
        $c = $this->customer();
        $svc = $this->svc();
        $wf = app(ServiceOrderWorkflowService::class);
        $order = $wf->createCustomerOrder(['service_id' => $svc->id, 'requirements' => 'scope here xx'], $c);
        $wf->cancelOrder($order->fresh(), $this->staff('admin', 'adm'), 'no longer needed');

        try {
            $wf->recordPayment($order->fresh(), ['amount' => 10, 'payment_method' => 'card'], $c);
            $this->fail('Payment on cancelled order was accepted.');
        } catch (\Throwable $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
        $this->assertEquals(0, \App\Models\Payment::count());
    }

    /** @test */
    public function manager_override_on_closed_order_is_rejected()
    {
        $c = $this->customer();
        $svc = $this->svc(100);
        $wf = app(ServiceOrderWorkflowService::class);
        $order = $wf->createCustomerOrder(['service_id' => $svc->id, 'requirements' => 'scope here xx'], $c);
        $wf->recordPayment($order->fresh(), ['amount' => 100, 'payment_method' => 'card'], $c);
        // Simulate completed technical work then close.
        $order->fresh()->update(['task_completed_at' => now()]);
        $wf->closeOrder($order->fresh(), $this->staff('admin', 'adm2'));

        try {
            $wf->managerOverride($order->fresh(), $this->staff('admin', 'adm3'), 'let work start please');
            $this->fail('Override on closed order was accepted.');
        } catch (\Throwable $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
    }

    /** @test */
    public function currency_service_fails_closed_without_a_rate()
    {
        $this->expectException(\RuntimeException::class);
        app(CurrencyService::class)->getRate('USD', 'XXX');
    }

    /** @test */
    public function csv_cells_neutralize_formula_injection()
    {
        foreach (['=SUM(A1:A9)', '+cmd', '-2+2', '@evil', '  =HYPERLINK("x")'] as $evil) {
            $cell = ReportExportController::csvCell($evil);
            $this->assertStringStartsWith("'", $cell, "Not neutralized: {$evil}");
        }
        $this->assertSame('Normal text 123', ReportExportController::csvCell('Normal text 123'));
    }

    /** @test */
    public function service_price_type_rejects_arbitrary_strings()
    {
        $admin = $this->staff('admin', 'svc-admin');
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-pt'], ['name' => 'PT']);
        $this->actingAs($admin)->post(route('admin.services.store'), [
            'name' => 'Evil price type', 'category_id' => $cat->id,
            'price_type' => 'free-forever"; DROP TABLE services;--',
        ])->assertSessionHasErrors('price_type');
        $this->assertDatabaseMissing('services', ['name' => 'Evil price type']);
    }

    /** @test */
    public function password_reset_request_does_not_enumerate_accounts()
    {
        $this->post(route('password.email'), ['email' => 'nobody-here@example.test'])
            ->assertSessionHas('status');
        $this->assertGuest();
    }

    /** @test */
    public function non_admin_cannot_download_identity_documents()
    {
        $agent = $this->staff('support_agent', 'agentx');
        $doc = \App\Models\IdentityDocument::create([
            'user_id' => $this->customer()->id, 'document_type' => 'passport',
            'path' => 'identity/x.pdf', 'original_name' => 'x.pdf', 'status' => 'submitted',
        ]);
        $this->actingAs($agent)->get(route('admin.identity.download', $doc))->assertStatus(403);
    }

    /** @test */
    public function mfa_verify_locks_after_repeated_failures()
    {
        $user = $this->staff('admin', 'mfa-lock');
        $user->forceFill([
            'two_factor_secret' => app(\App\Services\TotpService::class)->generateSecret(),
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => now(),
        ])->save();
        $this->actingAs($user);
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('mfa.verify'), ['code' => '000000']);
        }
        $this->post(route('mfa.verify'), ['code' => '000000'])->assertStatus(429);
    }

    /** @test */
    public function export_date_range_over_a_year_is_rejected()
    {
        $admin = $this->staff('admin', 'exp-admin');
        $this->actingAs($admin)->get(route('admin.reports.export', [
            'type' => 'payment', 'format' => 'csv',
            'date_from' => '2020-01-01', 'date_to' => '2022-01-01',
        ]))->assertStatus(422);
    }

    /** @test */
    public function user_time_falls_back_to_utc_and_formats_in_user_timezone()
    {
        $u = $this->customer(['timezone' => 'Asia/Dhaka']);
        $this->assertSame('Asia/Dhaka', UserTime::for($u));
        $this->assertSame('UTC', UserTime::for($this->customer(['timezone' => 'Not/AZone'])));
        // 00:00 UTC = 06:00 Dhaka (no DST in Bangladesh — stable assertion).
        $this->assertSame('2026-01-15 06:00', UserTime::format('2026-01-15 00:00:00', $u));
        // Storage strategy unchanged.
        $this->assertSame('UTC', config('app.timezone'));
    }
}
