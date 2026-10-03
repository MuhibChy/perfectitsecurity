<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ReportExportService;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Reporting hub, service-detail page, new export types, date filters,
 * print entry points and audit-trail immutability.
 */
class ReportSystemTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag) . '.' . Str::random(6) . '@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified',
        ]);
    }

    private function orderFor(User $customer): ServiceOrder
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-rep'], ['name' => 'RPT']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] RPT Service', 'slug' => 't-rep-' . Str::random(6), 'short_description' => 'x', 'starting_price' => 200, 'is_active' => true]);
        $order = ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $service->id, 'created_by' => $customer->id,
            'source' => 'test', 'requirements' => 'Report QA scope.',
            'status' => 'confirmed', 'payment_authorization' => 'not_authorized', 'currency' => 'GBP',
            'original_price' => 200, 'final_price' => 200, 'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => 200, 'amount_paid' => 0, 'amount_due' => 200, 'price_locked' => true, 'customer_accepted_at' => now(),
        ]);
        app(ServiceOrderWorkflowService::class)->generateConnectedRecords($order->fresh(), $customer);
        return $order->fresh();
    }

    /** @test */
    public function hub_and_filtered_report_pages_render()
    {
        $finance = $this->person('finance_manager', 'RPT Finance');
        $this->actingAs($finance)->get(route('admin.reports.index'))->assertStatus(200)->assertSee('Filtered Exports');
        $this->actingAs($finance)->get(route('admin.reports.financial', ['from' => '2026-01-01', 'to' => '2026-12-31']))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.reports.tickets', ['from' => '2026-01-01', 'to' => '2026-12-31']))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.reports.sla'))->assertStatus(200);
        // Dashboard SLA card links to the SLA report.
        $dash = $this->actingAs($finance)->get(route('admin.dashboard'))->assertStatus(200)->getContent();
        $this->assertStringContainsString(route('admin.reports.sla'), $dash);
    }

    /** @test */
    public function service_detail_page_respects_ownership()
    {
        $customer = $this->person('customer', 'RPT Cust');
        $other = $this->person('customer', 'RPT Other');
        $admin = $this->person('admin', 'RPT Admin');
        $order = $this->orderFor($customer);

        $this->actingAs($admin)->get(route('admin.reports.service', $order->id))->assertStatus(200)->assertSee($order->order_number);
        $this->actingAs($customer)->get(route('portal.reports.service', $order->id))->assertStatus(200)->assertSee($order->order_number);
        // Intruder: builder 403 (admin surface) / 404 scoped lookup (portal surface).
        $this->actingAs($other)->get(route('admin.reports.service', $order->id))->assertStatus(403);
        $this->actingAs($other)->get(route('portal.reports.service', $order->id))->assertStatus(404);
    }

    /** @test */
    public function new_export_types_agree_across_formats_and_gates()
    {
        $customer = $this->person('customer', 'RPT Pay');
        $other = $this->person('customer', 'RPT PayOther');
        $finance = $this->person('finance_manager', 'RPT PayFin');
        $order = $this->orderFor($customer);
        app(ServiceOrderWorkflowService::class)->recordPayment($order->fresh(), ['amount' => 200, 'payment_method' => 'card', 'transaction_id' => 'RPT-1'], $finance);

        foreach (['customer-full', 'payment', 'task'] as $type) {
            $params = ['format' => 'csv'] + ($type === 'customer-full' ? ['customer_id' => $customer->id] : []);
            $this->actingAs($finance)->get(route('admin.reports.export', ['type' => $type] + $params))->assertStatus(200);
            $this->actingAs($finance)->get(route('admin.reports.export', ['type' => $type, 'format' => 'pdf'] + ($type === 'customer-full' ? ['customer_id' => $customer->id] : [])))->assertStatus(200);
        }
        // Screen builder agrees with export: paid 200, due 0.
        $full = app(ReportExportService::class)->build('customer-full', ['customer_id' => $customer->id], $finance);
        $this->assertSame(200.0, (float) $full['summary']['Total paid (completed)']);
        // Customer self-service: own payment rows (forcing another id still yields own).
        $own = $this->actingAs($customer)->get(route('portal.reports.mine', ['type' => 'payment', 'format' => 'csv']))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString('RPT-1', $own);
        $spoofed = $this->actingAs($customer)->get(route('portal.reports.mine', ['type' => 'payment', 'customer_id' => $other->id, 'format' => 'csv']))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString('RPT-1', $spoofed);
        $this->actingAs($other)->get(route('portal.reports.mine', ['type' => 'payment', 'format' => 'csv']))->assertStatus(200)->assertDontSee('RPT-1', false);
        $this->assertTrue(AuditLog::where('action', 'report.exported')->exists());
    }

    /** @test */
    public function lifecycle_figures_agree_everywhere()
    {
        $customer = $this->person('customer', 'RPT Lifecycle');
        $finance = $this->person('finance_manager', 'RPT LifecycleFin');
        $svc = app(ServiceOrderWorkflowService::class);
        $order = $this->orderFor($customer);
        $svc->recordPayment($order->fresh(), ['amount' => 120, 'payment_method' => 'card', 'transaction_id' => 'RPT-LC-1'], $finance);
        $svc->recordPayment($order->fresh(), ['amount' => 80, 'payment_method' => 'card', 'transaction_id' => 'RPT-LC-2'], $finance);
        $task = $order->tasks()->firstOrFail();
        $task->update(['status' => 'in_progress']);
        $svc->completeTechnicalTask($task->fresh(), $finance);
        $svc->closeOrder($order->fresh(), $finance);
        $order = $order->fresh();

        // DB truth.
        $this->assertEquals(200.0, (float) $order->amount_paid);
        $this->assertEquals(0.0, (float) $order->amount_due);

        // Profile overview, builders, HTML agree.
        $overview = \App\Services\TraceabilityService::customerOverview($customer);
        $this->assertEquals(200.0, round((float) $overview['paid_total'], 2));
        $this->assertEquals(0.0, round((float) $overview['outstanding'], 2));
        $full = app(ReportExportService::class)->build('customer-full', ['customer_id' => $customer->id], $finance);
        $this->assertEquals(200.0, (float) $full['summary']['Total paid (completed)']);
        $this->assertEquals(200.0, (float) $full['summary']['Total ordered']);
        $service = app(ReportExportService::class)->build('service', ['order_id' => $order->id], $finance);
        $this->assertSame('YES', $service['summary']['Reconciled (TOTAL=PAID+DUE)']);

        // Surfaces render with the same figures.
        $this->actingAs($customer)->get(route('portal.history.index'))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.profile.edit'))->assertStatus(200)->assertSee('Transaction History');
        $html = $this->actingAs($admin = $this->person('admin', 'RPT LifecycleAdm'))->get(route('admin.reports.service', $order->id))->assertStatus(200)->getContent();
        $this->assertStringContainsString('200.00', $html);
        $this->actingAs($finance)->get(route('admin.reports.export', ['type' => 'customer-full', 'customer_id' => $customer->id, 'format' => 'pdf']))->assertStatus(200);
    }

    /** @test */
    public function audit_log_entries_are_immutable()
    {
        $log = AuditLog::log('qa.immutable', 'reports', null, 'Immutability probe.');
        try {
            $log->update(['description' => 'tampered']);
            $this->fail('AuditLog update should throw.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }
        try {
            $log->delete();
            $this->fail('AuditLog delete should throw.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('immutable', $e->getMessage());
        }
        $this->assertSame('Immutability probe.', $log->fresh()->description);
    }
}
