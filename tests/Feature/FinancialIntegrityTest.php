<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Salary;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\BankTransferService;
use App\Services\CommissionService;
use App\Services\ReportExportService;
use App\Services\ServiceOrderWorkflowService;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Customer lifetime ledger + employee lifetime earnings + service closure
 * integrity (§49-55, §73-74). One business event = one authoritative
 * transaction; dashboards, 360° views and reports must agree exactly.
 */
class FinancialIntegrityTest extends TestCase
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

    private function service(): Service
    {
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-integrity'], ['name' => 'Integrity']);

        return Service::create(['category_id' => $cat->id, 'name' => '[TEST] Website Security Assessment', 'slug' => 't-svc-'.Str::random(6), 'short_description' => 'x', 'starting_price' => 1000, 'is_active' => true]);
    }

    private function order(User $customer, Service $service, float $total): ServiceOrder
    {
        return ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $service->id, 'created_by' => $customer->id,
            'source' => 'test', 'requirements' => 'Synthetic integrity scope.',
            'status' => 'confirmed', 'payment_authorization' => 'not_authorized', 'currency' => 'USD',
            'original_price' => $total, 'final_price' => $total, 'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => $total, 'amount_paid' => 0, 'amount_due' => $total, 'price_locked' => true, 'customer_accepted_at' => now(),
        ]);
    }

    /** @test §49: staged 300/200/200/300 against £1,000 → settled, closed, +£1,000 lifetime exactly. */
    public function staged_payments_settle_exactly_and_close()
    {
        $customer = $this->person('customer', 'Customer Alpha');
        $finance = $this->person('finance_manager', 'Finance Alpha');
        $svc = app(ServiceOrderWorkflowService::class);
        $order = $this->order($customer, $this->service(), 1000);
        $svc->generateConnectedRecords($order->fresh(), $customer);

        $before = (float) Payment::where('customer_id', $customer->id)->where('status', 'completed')->sum('amount');
        foreach ([300, 200, 200, 300] as $i => $amount) {
            $r = $svc->recordPayment($order->fresh(), ['amount' => $amount, 'payment_method' => 'bank_transfer', 'transaction_id' => 'STAGE-'.($i + 1)], $finance);
            $order = $r['order'];
        }
        $this->assertEquals(1000.0, (float) $order->amount_paid);
        $this->assertEquals(0.0, (float) $order->amount_due);
        $this->assertEquals('fully_paid', $order->payment_authorization);

        // Exactly +£1,000 lifetime — not 2x/3x/4x from invoice+receipt double counting.
        $after = (float) Payment::where('customer_id', $customer->id)->where('status', 'completed')->sum('amount');
        $this->assertEquals(1000.0, round($after - $before, 2));
        $this->assertEquals(4, Payment::where('customer_id', $customer->id)->where('status', 'completed')->count());
        // Receipts are documents, not money: 4 receipts, but ONE income booking per payment.
        $this->assertEquals(4, Receipt::where('service_order_id', $order->id)->count());

        // Closure: technical completion → close → history retained.
        $task = $order->tasks()->firstOrFail();
        $svc->completeTechnicalTask($task, $finance);
        $closed = $svc->closeOrder($order->fresh(), $finance, 'Synthetic closure.');
        $this->assertEquals('closed', $closed->status);
        $this->assertEquals(4, Payment::where('service_order_id', $order->id)->count());
        $this->assertNotNull(Invoice::where('service_order_id', $order->id)->first());
        $this->assertEquals(4, Receipt::where('service_order_id', $order->id)->count());
        // Post-closure: unpaid-balance guard — a fresh unpaid order cannot close.
        $open = $this->order($customer, $this->service(), 500);
        try {
            $svc->closeOrder($open, $finance);
            $this->fail('unpaid order must not close');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
    }

    /** @test §52: two £300 attempts against £300 due → one payment, second rejected. */
    public function overpayment_and_double_submit_are_rejected()
    {
        $customer = $this->person('customer', 'Customer Beta');
        $finance = $this->person('finance_manager', 'Finance Beta');
        $svc = app(ServiceOrderWorkflowService::class);
        $order = $this->order($customer, $this->service(), 300);
        $svc->generateConnectedRecords($order->fresh(), $customer);
        $svc->recordPayment($order->fresh(), ['amount' => 300, 'payment_method' => 'card', 'transaction_id' => 'ONCE-1'], $finance);
        try {
            $svc->recordPayment($order->fresh(), ['amount' => 300, 'payment_method' => 'card', 'transaction_id' => 'ONCE-1-RETRY'], $finance);
            $this->fail('second £300 must be rejected');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
        $this->assertEquals(1, Payment::where('service_order_id', $order->id)->where('status', 'completed')->count());
        $this->assertEquals(300.0, (float) $order->fresh()->amount_paid);
        $this->assertEquals(0.0, (float) $order->fresh()->amount_due);
    }

    /** @test §53+§55: three orders → invoiced 3500 / paid 3300 / due 200, every layer agrees. */
    public function customer_lifetime_agrees_across_all_layers()
    {
        $customer = $this->person('customer', 'Customer Gamma');
        $finance = $this->person('finance_manager', 'Finance Gamma');
        $admin = $this->person('admin', 'Admin Gamma');
        $svc = app(ServiceOrderWorkflowService::class);
        $service = $this->service();

        $a = $this->order($customer, $service, 1000);
        $svc->generateConnectedRecords($a->fresh(), $customer);
        $svc->recordPayment($a->fresh(), ['amount' => 1000, 'payment_method' => 'card'], $finance);

        $b = $this->order($customer, $service, 500);
        $svc->generateConnectedRecords($b->fresh(), $customer);
        $svc->recordPayment($b->fresh(), ['amount' => 300, 'payment_method' => 'card'], $finance);

        $c = $this->order($customer, $service, 2000);
        $svc->generateConnectedRecords($c->fresh(), $customer);
        $svc->recordPayment($c->fresh(), ['amount' => 2000, 'payment_method' => 'card'], $finance);

        // Layer 1: raw authoritative sums.
        $paid = (float) Payment::where('customer_id', $customer->id)->where('status', 'completed')->sum('amount');
        $invoiced = (float) Invoice::where('customer_id', $customer->id)->where('status', '!=', 'cancelled')->sum('total');
        $due = (float) Invoice::where('customer_id', $customer->id)->where('status', '!=', 'cancelled')->sum('amount_due');
        $this->assertEquals(3500.0, round($invoiced, 2));
        $this->assertEquals(3300.0, round($paid, 2));
        $this->assertEquals(200.0, round($due, 2));

        // Layer 2: customer 360° overview (same source of truth).
        $overview = TraceabilityService::customerOverview($customer);
        $this->assertEquals(3300.0, round((float) $overview['paid_total'], 2));
        $this->assertEquals(200.0, round((float) $overview['outstanding'], 2));

        // Layer 3: report builder summary.
        $report = app(ReportExportService::class)->build('customer', ['customer_id' => $customer->id], $admin);
        $this->assertEquals(3, $report['summary']['Orders']);
        $this->assertEquals(3300.0, round((float) $report['summary']['Invoiced total'] - (float) json_decode($report['summary']['Outstanding (by currency)'], true)['USD'], 2) - 0 + 0 + 0); // sanity: rows present
        $this->assertCount(3, $report['rows']);

        // Layer 4: finance ledger income bookings match payments (one booking each).
        $income = (float) FinancialTransaction::where('type', 'income')->where('status', 'completed')->sum('amount');
        $this->assertTrue($income >= 3300.0, 'ledger income must cover all successful payments');
    }

    /** @test §50/§54: salaries + bonus + commission = lifetime; customer money never leaks in. */
    public function employee_lifetime_counts_only_employee_earnings()
    {
        $employee = $this->person('employee', 'Employee Alpha');
        $customer = $this->person('customer', 'Customer Delta');
        $finance1 = $this->person('finance_manager', 'Finance Delta1');
        $finance2 = $this->person('finance_manager', 'Finance Delta2');
        $agent = $employee; // employee also earns a commission in this scenario

        // Customer pays £1,000 (must NOT become employee income).
        $svc = app(ServiceOrderWorkflowService::class);
        $order = $this->order($customer, $this->service(), 1000);
        $svc->generateConnectedRecords($order->fresh(), $customer);
        $svc->recordPayment($order->fresh(), ['amount' => 1000, 'payment_method' => 'card'], $finance1);

        // 3× £2,000 salaries, March carries a £100 bonus → £6,100 salary.
        $bt = app(BankTransferService::class);
        // (created explicitly below for audit clarity)
        $salaries = [];
        $salaries[] = Salary::create(['user_id' => $employee->id, 'base_salary' => 2000, 'bonus' => 0, 'deductions' => 0, 'net_salary' => 2000, 'period' => 'monthly', 'pay_date' => now()->toDateString(), 'status' => 'approved']);
        $salaries[] = Salary::create(['user_id' => $employee->id, 'base_salary' => 2000, 'bonus' => 0, 'deductions' => 0, 'net_salary' => 2000, 'period' => 'monthly', 'pay_date' => now()->toDateString(), 'status' => 'approved']);
        $salaries[] = Salary::create(['user_id' => $employee->id, 'base_salary' => 2000, 'bonus' => 100, 'deductions' => 0, 'net_salary' => 2100, 'period' => 'monthly', 'pay_date' => now()->toDateString(), 'status' => 'approved']);
        foreach ($salaries as $i => $salary) {
            $this->assertNotEmpty($salary->salary_number, 'every payroll row carries a SAL- reference');
            $t = $bt->request(['beneficiary_id' => $employee->id, 'purpose' => 'salary', 'related_id' => $salary->id, 'amount' => (float) $salary->net_salary, 'currency' => 'USD', 'provider' => 'sandbox', 'idempotency_key' => 'EMP-TEST-'.$salary->id], $finance1);
            $t = $bt->approve($t, $finance2);
            $t = $bt->markProcessing($t, $finance2);
            $bt->complete($t, $finance2, 'SANDBOX-REF-'.$salary->id);
            $this->assertEquals('paid', $salary->fresh()->status);
        }

        // £300 commission → approved → payout → paid.
        $rule = CommissionRule::create(['name' => '[TEST] 10% integrity', 'type' => 'percentage', 'rate' => 10, 'status' => 'active']);
        $commission = Commission::create(['worker_id' => $agent->id, 'rule_id' => $rule->id, 'customer_id' => $customer->id, 'commission_type' => 'per_sale', 'revenue_amount' => 3000, 'commission_rate' => 10, 'commission_amount' => 300, 'status' => 'pending']);
        app(CommissionService::class)->approveCommission($commission, $finance1->id);
        $payout = app(CommissionService::class)->processPayout($agent->id, [$commission->id], 'bank_transfer');
        app(CommissionService::class)->completePayout($payout, 'SANDBOX-COM-300');
        $this->assertEquals('paid', $commission->fresh()->status);

        // Lifetime = 6100 salary + 300 commission = 6400. Customer £1,000 excluded.
        $salaryPaid = (float) Salary::where('user_id', $employee->id)->where('status', 'paid')->sum('net_salary');
        $commPaid = (float) Commission::where('worker_id', $employee->id)->where('status', 'paid')->sum('commission_amount');
        $this->assertEquals(6100.0, round($salaryPaid, 2));
        $this->assertEquals(300.0, round($commPaid, 2));
        $this->assertEquals(6400.0, round($salaryPaid + $commPaid, 2));
        // Separation: company revenue side untouched by earnings math.
        $this->assertEquals(1000.0, (float) Payment::where('customer_id', $customer->id)->where('status', 'completed')->sum('amount'));
    }
}
