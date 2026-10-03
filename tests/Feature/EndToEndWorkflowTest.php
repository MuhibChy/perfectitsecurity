<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\FinancialTransaction;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\TaskContributor;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\ReportExportService;
use App\Services\ServiceOrderWorkflowService;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Complete synthetic business workflow (§53):
 * £1,500 service → £500 advance → work → £300 + £300 milestones →
 * £400 final → closure → £150 commission → reconciliation.
 * Every amount verified, no duplicates, history preserved.
 */
class EndToEndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag): User
    {
        return User::factory()->create([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag).'.'.Str::random(6).'@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'country' => 'GB',
        ]);
    }

    /** @test */
    public function full_staged_service_workflow_reconciles()
    {
        $customer = $this->person('customer', 'E2E Customer');
        $engineer = $this->person('employee', 'E2E Engineer');
        $pm = $this->person('project_manager', 'E2E PM');
        $finance = $this->person('finance_manager', 'E2E Finance');
        $agent = $this->person('commission_agent', 'E2E Agent');
        $admin = $this->person('admin', 'E2E Admin');
        $svc = app(ServiceOrderWorkflowService::class);

        // Order £1,500 with connected invoice/ticket/task.
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-e2e'], ['name' => 'E2E']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] E2E Managed Service', 'slug' => 't-e2e-'.Str::random(6), 'short_description' => 'x', 'starting_price' => 1500, 'is_active' => true]);
        $order = ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $service->id, 'created_by' => $customer->id,
            'source' => 'test', 'requirements' => 'E2E synthetic scope.',
            'status' => 'confirmed', 'payment_authorization' => 'not_authorized', 'currency' => 'GBP',
            'original_price' => 1500, 'final_price' => 1500, 'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => 1500, 'amount_paid' => 0, 'amount_due' => 1500, 'price_locked' => true, 'customer_accepted_at' => now(),
        ]);
        $svc->generateConnectedRecords($order->fresh(), $customer);

        // £500 advance → £1,000 due.
        $r = $svc->recordPayment($order->fresh(), ['amount' => 500, 'payment_method' => 'bank_transfer', 'transaction_id' => 'E2E-ADV'], $finance);
        $this->assertEquals(500.0, (float) $r['order']->amount_paid);
        $this->assertEquals(1000.0, (float) $r['order']->amount_due);

        // Assignment + progress visible to customer history (same task row).
        $task = $order->tasks()->firstOrFail();
        $task->update(['assigned_to' => $engineer->id]);
        TaskContributor::create(['task_id' => $task->id, 'user_id' => $engineer->id, 'role' => 'Engineer']);
        $task->update(['status' => 'in_progress', 'progress' => 50]);

        // Milestones £300 + £300, final £400 → settled exactly.
        $paid = 500.0;
        foreach ([300, 300, 400] as $i => $amount) {
            $r = $svc->recordPayment($order->fresh(), ['amount' => $amount, 'payment_method' => 'bank_transfer', 'transaction_id' => 'E2E-'.($i + 1)], $finance);
            $paid += $amount;
            $this->assertEquals($paid, (float) $r['order']->amount_paid);
            $this->assertEquals(round(1500 - $paid, 2), (float) $r['order']->amount_due);
        }
        $order = $order->fresh();
        $this->assertEquals(1500.0, (float) $order->amount_paid);
        $this->assertEquals(0.0, (float) $order->amount_due);

        // Completion + audited closure; history retained.
        $svc->completeTechnicalTask($task->fresh(), $engineer, 'All stages delivered.');
        $closed = $svc->closeOrder($order->fresh(), $pm, 'E2E closure.');
        $this->assertEquals('closed', $closed->status);
        $this->assertEquals(4, Payment::where('service_order_id', $order->id)->where('status', 'completed')->count());
        $this->assertEquals(4, Receipt::where('service_order_id', $order->id)->count());
        $this->assertTrue(AuditLog::where('action', 'service_order.closed')->where('auditable_id', $order->id)->exists());

        // Commission £150 (10% of £1,500) → approved → paid, once.
        $rule = CommissionRule::create(['name' => '[TEST] E2E 10%', 'type' => 'percentage', 'rate' => 10, 'status' => 'active']);
        $commission = Commission::create(['worker_id' => $agent->id, 'rule_id' => $rule->id, 'customer_id' => $customer->id, 'commission_type' => 'per_sale', 'revenue_amount' => 1500, 'commission_rate' => 10, 'commission_amount' => 150, 'status' => 'pending']);
        app(CommissionService::class)->approveCommission($commission, $finance->id);
        $payout = app(CommissionService::class)->processPayout($agent->id, [$commission->id], 'bank_transfer');
        app(CommissionService::class)->completePayout($payout, 'SANDBOX-E2E-150');
        $this->assertEquals('paid', $commission->fresh()->status);

        // Reconciliation across every layer.
        $overview = TraceabilityService::customerOverview($customer);
        $this->assertEquals(1500.0, round((float) $overview['paid_total'], 2));
        $this->assertEquals(0.0, round((float) $overview['outstanding'], 2));
        $report = app(ReportExportService::class)->build('customer', ['customer_id' => $customer->id], $admin);
        $this->assertEquals(1, $report['summary']['Orders']);
        $this->assertCount(1, $report['rows']);
        $income = (float) FinancialTransaction::where('type', 'income')->where('status', 'completed')->sum('amount');
        $this->assertTrue($income >= 1500.0);
        $links = TraceabilityService::employeeCustomerLinks($engineer);
        $this->assertTrue($links->isNotEmpty(), 'engineer work links to the customer');
        // Employee earnings show commission only — never the £1,500 customer payment.
        $commPaid = (float) Commission::where('worker_id', $engineer->id)->where('status', 'paid')->sum('commission_amount');
        $this->assertEquals(0.0, $commPaid);
    }
}
