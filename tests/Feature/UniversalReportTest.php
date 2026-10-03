<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Task;
use App\Models\User;
use App\Services\ReportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Universal reporting authorization: every role × report-type combination
 * is enforced server-side (builder gates + forced self-scope), customer A
 * can never reach customer B, filters cannot escape scope, and every
 * export is audit-logged without duplicating financial records.
 */
class UniversalReportTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role): User
    {
        return User::factory()->create([
            'role' => $role, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    /** @test */
    public function customer_reports_are_self_scoped_and_spoof_proof()
    {
        $a = $this->person('customer');
        $b = $this->person('customer');

        // Own report in every format (xlsx covered in the audit test below).
        foreach (['csv', 'pdf'] as $format) {
            $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'customer', 'format' => $format]))->assertStatus(200);
        }
        $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'customer-full', 'format' => 'pdf']))->assertStatus(200);
        $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'payment', 'format' => 'csv']))->assertStatus(200);
        $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'task', 'format' => 'csv']))->assertStatus(200);

        // Spoofed customer_id is forced back to self (never 403-with-leak, never B's data).
        $spoofBody = $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'customer', 'format' => 'csv', 'customer_id' => $b->id]))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString($a->email, $spoofBody);
        $this->assertStringNotContainsString($b->email, $spoofBody);
    }

    /** @test */
    public function customer_report_rejects_bad_type_format_and_range()
    {
        $a = $this->person('customer');
        // Unknown type coerces safely to the own-customer report (never finance).
        $coerced = $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'financial', 'format' => 'csv']))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString($a->email, $coerced);
        $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'customer', 'format' => 'docx']))->assertStatus(422);
        // Web validation redirects back with errors (never generates a report).
        $this->actingAs($a)->get(route('portal.reports.mine', ['type' => 'customer', 'format' => 'csv', 'date_from' => '2026-02-01', 'date_to' => '2026-01-01']))
            ->assertRedirect()->assertSessionHasErrors('date_to');
    }

    /** @test */
    public function customers_cannot_reach_staff_or_peer_reports()
    {
        $a = $this->person('customer');
        $b = $this->person('customer');
        $order = \App\Models\ServiceOrder::create([
            'customer_id' => $b->id, 'service_id' => $this->service()->id,
            'requirements' => 'Peer isolation probe with detail.', 'status' => 'confirmed',
            'currency' => 'USD', 'total' => 300, 'amount_paid' => 0, 'amount_due' => 300,
        ]);

        // Peer order report is oracle-free (404) or forbidden (403) — never data.
        $this->assertContains($this->actingAs($a)->get(route('portal.reports.service', $order->id))->getStatusCode(), [403, 404]);
        $this->actingAs($a)->get(route('admin.reports.export', ['type' => 'customer', 'customer_id' => $b->id]))->assertStatus(403);
        $this->actingAs($a)->get(route('admin.reports.my-work', ['format' => 'pdf']))->assertStatus(403);
        $this->actingAs($a)->get(route('workspace.commissions.report'))->assertStatus(403);
        $this->post('/logout')->assertRedirect();
        $this->get(route('portal.reports.mine'))->assertRedirect(route('login'));
    }

    /** @test */
    public function employee_self_report_works_but_peers_and_finance_are_denied()
    {
        $employee = $this->person('employee');
        $peer = $this->person('employee');
        $task = Task::create([
            'customer_id' => $this->person('customer')->id, 'assigned_to' => $employee->id,
            'created_by' => $employee->id, 'title' => '[TEST] Own work', 'description' => 'Work performed.',
            'status' => 'completed', 'priority' => 'medium',
        ]);

        $pdf = $this->actingAs($employee)->get(route('admin.reports.my-work', ['format' => 'pdf']))->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->actingAs($employee)->get(route('admin.reports.my-work', ['format' => 'csv']))->assertStatus(200);
        $this->actingAs($employee)->get(route('admin.reports.my-work', ['format' => 'xlsx']))->assertStatus(200);
        // Spoofed employee_id is ignored (forced self).
        $this->actingAs($employee)->get(route('admin.reports.my-work', ['format' => 'csv', 'employee_id' => $peer->id]))->assertStatus(200);

        // Peer finance data stays forbidden at the builder gate.
        $svc = app(ReportExportService::class);
        try {
            $svc->build('employee-finance', ['employee_id' => $peer->id], $employee);
            $this->fail('Employee reached finance report');
        } catch (\Throwable $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        // But the employee-service builder shows own task rows.
        $report = $svc->build('employee-service', ['employee_id' => $employee->id], $employee);
        $this->assertNotEmpty($report['rows']);
    }

    /** @test */
    public function commission_agent_sees_only_own_ledger()
    {
        $agentA = $this->person('commission_agent');
        $agentB = $this->person('commission_agent');
        $rule = CommissionRule::create(['name' => '[TEST] Rule', 'type' => 'percentage', 'rate' => 10, 'status' => 'active']);
        $customer = $this->person('customer');
        Commission::create(['worker_id' => $agentA->id, 'rule_id' => $rule->id, 'customer_id' => $customer->id, 'commission_type' => 'per_sale', 'revenue_amount' => 2000, 'commission_rate' => 10, 'commission_amount' => 200, 'status' => 'approved', 'payment_status' => 'unpaid']);
        Commission::create(['worker_id' => $agentB->id, 'rule_id' => $rule->id, 'customer_id' => $customer->id, 'commission_type' => 'per_sale', 'revenue_amount' => 5000, 'commission_rate' => 10, 'commission_amount' => 500, 'status' => 'approved', 'payment_status' => 'unpaid']);

        $csv = $this->actingAs($agentA)->get(route('workspace.commissions.report', ['format' => 'csv']))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString('200', $csv);
        $this->assertStringNotContainsString('5000', $csv);
        $pdf = $this->actingAs($agentA)->get(route('workspace.commissions.report', ['format' => 'pdf']))->assertStatus(200);
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->actingAs($agentA)->get(route('workspace.work.report', ['format' => 'csv']))->assertStatus(200);

        // Agent B's view contains only B's row.
        $csvB = $this->actingAs($agentB)->get(route('workspace.commissions.report', ['format' => 'csv']))->assertStatus(200)->streamedContent();
        $this->assertStringContainsString('5000', $csvB);
        $this->assertStringNotContainsString('2000', $csvB);

        // Customers and employees cannot use the contractor reports.
        $this->actingAs($customer)->get(route('workspace.commissions.report'))->assertStatus(403);
        $this->actingAs($this->person('employee'))->get(route('workspace.commissions.report'))->assertStatus(403);
    }

    /** @test */
    public function finance_and_admin_keep_global_reporting()
    {
        $finance = $this->person('finance_manager');
        $admin = $this->person('admin');
        $this->actingAs($finance)->get(route('admin.reports.export', ['type' => 'financial', 'format' => 'pdf']))->assertStatus(200);
        $this->actingAs($finance)->get(route('admin.reports.export', ['type' => 'commission', 'format' => 'csv']))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.reports.export', ['type' => 'financial', 'format' => 'csv']))->assertStatus(200);
        // Support agents stay out of finance exports at the route gate.
        $this->actingAs($this->person('support_agent'))->get(route('admin.reports.export', ['type' => 'financial', 'format' => 'csv']))->assertStatus(403);
    }

    /** @test */
    public function report_buttons_render_for_the_right_roles_only()
    {
        $customer = $this->person('customer');
        $orders = $this->actingAs($customer)->get(route('portal.orders.index'))->getContent();
        $this->assertStringContainsString('Generate Report', $orders);
        $history = $this->actingAs($customer)->get(route('portal.history.index'))->getContent();
        $this->assertStringContainsString('Generate Report', $history);

        $employee = $this->person('employee');
        $own = $this->actingAs($employee)->get(route('admin.history.my-work'))->getContent();
        $this->assertStringContainsString('Download My Report', $own);
        // Admin viewing someone else sees no self-report button.
        $admin = $this->person('admin');
        $other = $this->actingAs($admin)->get(route('admin.history.employee', $employee))->getContent();
        $this->assertStringNotContainsString('Download My Report', $other);

        $agent = $this->person('commission_agent');
        $ws = $this->actingAs($agent)->get(route('workspace.index'))->getContent();
        $this->assertStringContainsString('Work Report', $ws);
        $this->assertStringContainsString('Commission Report', $ws);
    }

    /** @test */
    public function exports_are_audited_and_never_duplicate_finance()
    {
        $customer = $this->person('customer');
        $beforePayments = \App\Models\Payment::count();
        $beforeTxns = \App\Models\FinancialTransaction::count();
        $this->actingAs($customer)->get(route('portal.reports.mine', ['type' => 'customer-full', 'format' => 'pdf']))->assertStatus(200);
        $this->actingAs($customer)->get(route('portal.reports.mine', ['type' => 'customer-full', 'format' => 'xlsx']))->assertStatus(200);
        $this->assertSame($beforePayments, \App\Models\Payment::count());
        $this->assertSame($beforeTxns, \App\Models\FinancialTransaction::count());
        $this->assertTrue(\App\Models\AuditLog::where('action', 'report.exported')->exists());
    }

    /** @test */
    public function multi_currency_amounts_are_never_merged()
    {
        $customer = $this->person('customer');
        $svc = app(\App\Services\ServiceOrderWorkflowService::class);
        $finance = $this->person('finance_manager');
        foreach ([['USD', 400], ['EUR', 300]] as [$ccy, $total]) {
            $order = \App\Models\ServiceOrder::create([
                'customer_id' => $customer->id, 'service_id' => $this->service()->id,
                'requirements' => 'Multi-currency probe order detail.', 'status' => 'confirmed',
                'currency' => $ccy, 'total' => $total, 'amount_paid' => 0, 'amount_due' => $total,
            ]);
            $svc->generateConnectedRecords($order->fresh(), $customer);
            $svc->recordPayment($order->fresh(), ['amount' => 100, 'payment_method' => 'card'], $finance);
        }
        $report = app(ReportExportService::class)->build('customer', ['customer_id' => $customer->id], $customer);
        $this->assertStringContainsString('"USD":100', str_replace(' ', '', $report['summary']['Paid (by currency)']));
        $this->assertStringContainsString('"EUR":100', str_replace(' ', '', $report['summary']['Paid (by currency)']));
    }

    private function service(): \App\Models\Service
    {
        $cat = \App\Models\ServiceCategory::firstOrCreate(['slug' => 'rpt-probe'], ['name' => 'Report Probe']);
        return \App\Models\Service::firstOrCreate(['slug' => 'rpt-probe-svc'], [
            'category_id' => $cat->id, 'name' => '[TEST] Report Probe', 'short_description' => 'x', 'is_active' => true,
        ]);
    }
}
