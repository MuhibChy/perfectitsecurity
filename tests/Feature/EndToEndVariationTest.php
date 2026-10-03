<?php

namespace Tests\Feature;

use App\Models\CashMemo;
use App\Models\Receipt;
use App\Models\Task;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ten synthetic end-to-end variations across countries, currencies,
 * payment stages and roles. Only application-supported stages are
 * executed; sandbox paths only; no duplicate financial records.
 */
class EndToEndVariationTest extends TestCase
{
    use RefreshDatabase;

    private function customer(?string $country = null, ?string $code = null): User
    {
        return User::factory()->create([
            'name' => '[TEST] Variation Customer', 'role' => 'customer', 'is_active' => true,
            'is_demo' => true, 'email_verified_at' => now(),
            'phone' => '+447700900501', 'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
            'country' => $country, 'country_code' => $code,
        ]);
    }

    private function service(): \App\Models\Service
    {
        $cat = \App\Models\ServiceCategory::firstOrCreate(['slug' => 'e2e-vary'], ['name' => 'E2E Variations']);

        return \App\Models\Service::firstOrCreate(['slug' => 'e2e-vary-svc'], [
            'category_id' => $cat->id, 'name' => '[TEST] Variation Service', 'short_description' => 'x', 'is_active' => true,
        ]);
    }

    private function orderFor(User $customer, float $total, string $ccy = 'USD'): \App\Models\ServiceOrder
    {
        $order = \App\Models\ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $this->service()->id,
            'created_by' => $customer->id, 'requirements' => 'Variation order with full detail.',
            'status' => 'confirmed', 'currency' => $ccy, 'total' => $total,
            'amount_paid' => 0, 'amount_due' => $total,
        ]);
        app(ServiceOrderWorkflowService::class)->generateConnectedRecords($order->fresh(), $customer);

        return $order->fresh();
    }

    private function pay(\App\Models\ServiceOrder $order, float $amount, string $method = 'card'): array
    {
        $finance = User::factory()->create(['role' => 'finance_manager', 'is_active' => true, 'email_verified_at' => now()]);

        return app(ServiceOrderWorkflowService::class)->recordPayment($order->fresh(), ['amount' => $amount, 'payment_method' => $method], $finance);
    }

    /** @test */
    public function variation_01_fully_paid_single_stage()
    {
        $customer = $this->customer('United States', 'US');
        $this->assertSame(['USD'], $customer->availableCurrencies());
        $order = $this->orderFor($customer, 600);
        $this->pay($order, 600);
        $order = $order->fresh();
        $this->assertEquals(0, (float) $order->amount_due);
        $this->assertSame(1, Receipt::where('service_order_id', $order->id)->count());
        $this->actingAs($customer)->get(route('portal.invoices.show', $order->invoices()->first()->id))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.reports.service', $order->id))->assertStatus(200);
    }

    /** @test */
    public function variation_02_advance_plus_final_gbp()
    {
        $customer = $this->customer('United Kingdom', 'UK');
        $this->assertSame(['GBP', 'USD'], $customer->availableCurrencies());
        $order = $this->orderFor($customer, 1500, 'GBP');
        $this->pay($order, 500);
        $this->assertEquals(1000, (float) $order->fresh()->amount_due);
        $this->pay($order, 1000);
        $this->assertEquals(0, (float) $order->fresh()->amount_due);
        $this->assertSame(2, Receipt::where('service_order_id', $order->id)->count());
        $invoice = $order->invoices()->first();
        $this->assertSame('GBP', $invoice->fresh()->currency); // history preserved
        $pdf = $this->actingAs($customer)->get(route('portal.invoices.pdf', $invoice->id))->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    /** @test */
    public function variation_03_multi_stage_eur()
    {
        $customer = $this->customer('Germany', 'DE');
        $order = $this->orderFor($customer, 1500, 'EUR');
        foreach ([500, 300, 300, 400] as $stage) {
            $this->pay($order, $stage);
        }
        $this->assertSame(4, \App\Models\Payment::where('service_order_id', $order->id)->count());
        $this->assertSame(4, Receipt::where('service_order_id', $order->id)->count());
        $this->assertEquals(0, (float) $order->fresh()->amount_due);
        $csv = $this->actingAs($customer)->get(route('portal.reports.mine', ['type' => 'payment', 'format' => 'csv']))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString('EUR', $csv);
    }

    /** @test */
    public function variation_04_outstanding_balance_bdt()
    {
        $customer = $this->customer('Bangladesh', 'BD');
        $this->assertSame(['BDT', 'USD'], $customer->availableCurrencies());
        $order = $this->orderFor($customer, 1000, 'BDT');
        $this->pay($order, 300);
        $order = $order->fresh();
        $this->assertEquals(700, (float) $order->amount_due);
        $this->assertSame('partially_paid', $order->invoices()->first()->status);
        $html = $this->actingAs($customer)->get(route('portal.orders.show', $order->id))->assertStatus(200)->getContent();
        $this->assertStringContainsString('BDT', $html);
    }

    /** @test */
    public function variation_05_employee_task_report()
    {
        $employee = User::factory()->create(['role' => 'employee', 'is_active' => true, 'email_verified_at' => now(), 'phone_verified_at' => now()]);
        $customer = $this->customer();
        foreach (['completed', 'in_progress', 'pending'] as $i => $status) {
            Task::create([
                'customer_id' => $customer->id, 'assigned_to' => $employee->id, 'created_by' => $employee->id,
                'title' => "[TEST] Employee task {$i}", 'description' => 'Work performed.',
                'status' => $status, 'priority' => 'medium',
            ]);
        }
        $report = app(\App\Services\ReportExportService::class)->build('employee-service', ['employee_id' => $employee->id], $employee);
        $this->assertSame(3, $report['summary']['Assigned tasks']);
        $this->assertSame(1, $report['summary']['Completed']);
        $pdf = $this->actingAs($employee)->get(route('admin.reports.my-work', ['format' => 'pdf']))->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $html = $this->actingAs($employee)->get(route('admin.history.my-work'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Download My Report', $html);
    }

    /** @test */
    public function variation_06_commission_agent_report()
    {
        $agent = User::factory()->create(['role' => 'commission_agent', 'is_active' => true, 'email_verified_at' => now(), 'phone_verified_at' => now()]);
        $rule = \App\Models\CommissionRule::create(['name' => '[TEST] Agent 10%', 'type' => 'percentage', 'rate' => 10, 'status' => 'active']);
        \App\Models\Commission::create(['worker_id' => $agent->id, 'rule_id' => $rule->id, 'customer_id' => $this->customer()->id, 'commission_type' => 'per_sale', 'revenue_amount' => 3000, 'commission_rate' => 10, 'commission_amount' => 300, 'status' => 'approved', 'payment_status' => 'unpaid']);
        $csv = $this->actingAs($agent)->get(route('workspace.commissions.report', ['format' => 'csv']))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString('300', $csv);
        $work = $this->actingAs($agent)->get(route('workspace.work.report', ['format' => 'csv']))->assertStatus(200);
        $this->assertSame(200, $work->getStatusCode());
        // Ledger untouched by reporting.
        $this->assertSame(1, \App\Models\Commission::where('worker_id', $agent->id)->count());
    }

    /** @test */
    public function variation_07_customer_service_report_with_print()
    {
        $customer = $this->customer('France', 'FR');
        $order = $this->orderFor($customer, 900, 'EUR');
        $this->pay($order, 900);
        $html = $this->actingAs($customer)->get(route('portal.reports.service', $order->id))->assertStatus(200)->getContent();
        $this->assertStringContainsString($order->order_number, $html);
        $this->assertStringContainsString('window.print', $html); // print action present
        $this->assertStringContainsString('no-print', $html); // chrome hidden on print
        $pdf = $this->actingAs($customer)->get(route('portal.reports.mine', ['type' => 'customer-full', 'format' => 'pdf']))->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    /** @test */
    public function variation_08_different_currency_aed()
    {
        $customer = $this->customer('United Arab Emirates', 'AE');
        $this->assertSame(['AED', 'USD'], $customer->availableCurrencies());
        $order = $this->orderFor($customer, 5250, 'AED');
        $this->pay($order, 5250);
        $this->assertSame('AED', $order->invoices()->first()->currency);
        $pdf = $this->actingAs($customer)->get(route('portal.receipts.pdf', Receipt::where('service_order_id', $order->id)->first()->id))->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    /** @test */
    public function variation_09_unsupported_country_usd_only()
    {
        $customer = $this->customer('Atlantis', null);
        $this->assertSame(['USD'], $customer->availableCurrencies());
        $order = $this->orderFor($customer, 250, 'USD');
        $this->pay($order, 100);
        $this->assertEquals(150, (float) $order->fresh()->amount_due);
        $this->actingAs($customer)->get('/currency/USD')->assertRedirect();
        $this->actingAs($customer)->get('/currency/GBP')->assertStatus(422);
    }

    /** @test */
    public function variation_10_multiple_tasks_one_order_cash_memo()
    {
        $customer = $this->customer('Qatar', 'QA');
        $order = $this->orderFor($customer, 2000, 'QAR');
        $employee = User::factory()->create(['role' => 'employee', 'is_active' => true, 'email_verified_at' => now()]);
        foreach (['Survey', 'Implementation', 'Handover'] as $title) {
            Task::create([
                'service_order_id' => $order->id, 'customer_id' => $customer->id,
                'assigned_to' => $employee->id, 'created_by' => $employee->id,
                'title' => "[TEST] {$title}", 'description' => 'Work performed.',
                'status' => 'completed', 'priority' => 'medium',
            ]);
        }
        // One order → one invoice despite three tasks.
        $this->assertSame(1, $order->invoices()->count());
        $result = $this->pay($order, 2000, 'cash');
        CashMemo::firstOrCreate(['payment_id' => $result['payment']->id], ['issued_by' => $result['payment']->customer_id, 'issued_at' => now()]);
        $this->assertSame(1, CashMemo::where('payment_id', $result['payment']->id)->count());
        $report = app(\App\Services\ReportExportService::class)->build('service', ['order_id' => $order->id], $customer);
        $taskRows = array_values(array_filter($report['rows'], fn ($r) => $r[0] === 'task'));
        // Auto-generated order task + the three explicit tasks.
        $this->assertCount(4, $taskRows);
        $this->assertSame(1, \App\Models\Payment::where('service_order_id', $order->id)->count());
    }
}
