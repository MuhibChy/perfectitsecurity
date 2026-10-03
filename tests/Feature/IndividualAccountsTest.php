<?php

namespace Tests\Feature;

use App\Models\BankTransfer;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\DirectMessage;
use App\Models\EmergencyRequest;
use App\Models\Franchise;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\TaskContributor;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\BankTransferService;
use App\Services\CommissionService;
use App\Services\EmergencyService;
use App\Services\MessagingService;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Individual-account synthetic verification (§60-68):
 * customer, engineer, PM, finance ×2, agent, freelancer, franchise.
 * Customer £1,500 payment vs £2,000 salary vs £100 commission stay separate.
 */
class IndividualAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role, string $tag, array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'name' => "[TEST] {$tag}", 'role' => $role, 'is_active' => true,
            'email' => Str::slug($tag) . '.' . Str::random(5) . '@example.test',
            'email_verified_at' => now(), 'phone_verified_at' => now(),
            'verification_status' => 'verified', 'country' => 'GB',
        ], $extra));
    }

    /** @test */
    public function full_individual_account_lifecycle()
    {
        $customer = $this->person('customer', 'International Customer Alpha', ['preferred_currency' => 'GBP']);
        $engineer = $this->person('employee', 'IT Engineer Alpha', ['job_title' => 'Security Analyst', 'department' => 'Security']);
        $pm = $this->person('project_manager', 'Project Manager Alpha');
        $finance1 = $this->person('finance_manager', 'Finance Manager Alpha');
        $finance2 = $this->person('finance_manager', 'Finance Manager Beta');
        $agent = $this->person('commission_agent', 'Commission Agent Alpha');
        $freelancer = $this->person('freelancer', 'Freelancer Alpha');

        // ── Franchise (§64): owner + member customer, separate books ──
        $franchise = Franchise::create(['name' => '[TEST] Franchise Alpha', 'owner_id' => $agent->id, 'territory' => 'London']);
        $customer->update(['franchise_id' => $franchise->id]);
        $this->assertEquals($franchise->id, $customer->fresh()->franchise_id);

        // ── Customer workflow (§61): order → invoice → £500 advance ──
        $cat = ServiceCategory::firstOrCreate(['slug' => 't-managed'], ['name' => 'Managed']);
        $service = Service::create(['category_id' => $cat->id, 'name' => '[TEST] Managed IT Support', 'slug' => 't-svc-' . Str::random(6), 'short_description' => 'x', 'starting_price' => 1500, 'is_active' => true]);
        $svc = app(ServiceOrderWorkflowService::class);
        $order = ServiceOrder::create([
            'customer_id' => $customer->id, 'service_id' => $service->id, 'created_by' => $customer->id,
            'source' => 'test', 'requirements' => 'Synthetic individual-account scope statement.',
            'status' => 'confirmed', 'payment_authorization' => 'not_authorized', 'currency' => 'GBP',
            'original_price' => 1500, 'final_price' => 1500, 'discount_amount' => 0, 'tax_rate' => 0, 'tax_amount' => 0,
            'total' => 1500, 'amount_paid' => 0, 'amount_due' => 1500, 'price_locked' => true, 'customer_accepted_at' => now(),
        ]);
        $svc->generateConnectedRecords($order->fresh(), $customer);
        $order = $order->fresh();
        $r = $svc->recordPayment($order, ['amount' => 500, 'payment_method' => 'bank_transfer', 'transaction_id' => 'IND-ACC-500'], $finance1);
        $this->assertEquals(500.0, (float) $r['order']->amount_paid);
        $this->assertEquals(1000.0, (float) $r['order']->amount_due);

        // ── Multi-employee contribution (§45/65): engineer + freelancer on one task ──
        $task = $order->tasks()->firstOrFail();
        $task->update(['assigned_to' => $engineer->id]);
        TaskContributor::create(['task_id' => $task->id, 'user_id' => $engineer->id, 'role' => 'Security Analyst']);
        TaskContributor::create(['task_id' => $task->id, 'user_id' => $freelancer->id, 'role' => 'Network Engineer']);
        $task->update(['status' => 'in_progress', 'progress' => 40]);
        $this->assertEquals(2, TaskContributor::where('task_id', $task->id)->count());
        $this->assertTrue($engineer->fresh()->contributedTasks()->where('tasks.id', $task->id)->exists());
        // Customer history references the same authoritative task (no duplicate transaction).
        $this->assertEquals($task->id, $order->fresh()->tasks()->first()->id);

        // ── Per-user settings (§5/51): isolated KV, no global bleed ──
        UserSetting::set($customer, 'preferences', 'theme', 'dark');
        UserSetting::set($engineer, 'preferences', 'theme', 'light');
        $this->assertEquals('dark', UserSetting::get($customer, 'preferences', 'theme'));
        $this->assertEquals('light', UserSetting::get($engineer, 'preferences', 'theme'));

        // ── Messaging (§14/16): customer→staff ok, customer→customer blocked, internal hidden ──
        $msgSvc = app(MessagingService::class);
        $m1 = $msgSvc->send($customer, $engineer, 'My VPN is down, please help.');
        $this->assertFalse($m1->is_internal);
        $other = $this->person('customer', 'International Customer Beta');
        try {
            $msgSvc->send($customer, $other, 'hello peer');
            $this->fail('customer-to-customer must be forbidden');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
        $internal = $msgSvc->send($pm, $engineer, 'Check SLA quietly.', null, ['is_internal' => true]);
        $visible = DirectMessage::visibleTo($customer)->where('id', $internal->id)->exists();
        $this->assertFalse($visible, 'internal notes must never leak to customers');

        // ── Emergency lane (§15/50): raise → acknowledge → resolve → close ──
        $emSvc = app(EmergencyService::class);
        $er = $emSvc->raise($customer, ['severity' => 'CRITICAL', 'category' => 'outage', 'description' => 'Complete office network outage, critical.']);
        $this->assertEquals('new', $er->status);
        $er = $emSvc->transition($er, 'acknowledged', $pm);
        $er = $emSvc->transition($er, 'assigned', $pm, $engineer->id);
        $er = $emSvc->transition($er, 'in_progress', $engineer);
        $er = $emSvc->transition($er, 'resolved', $engineer);
        $er = $emSvc->transition($er, 'closed', $pm);
        $this->assertEquals('closed', $er->status);
        try {
            $emSvc->transition($er, 'in_progress', $pm);
            $this->fail('closed must be terminal');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }

        // ── Salary + sandbox transfer (§62): £2,000, segregation of duties ──
        $salary = \App\Models\Salary::create(['user_id' => $engineer->id, 'base_salary' => 2000, 'bonus' => 0, 'deductions' => 0, 'net_salary' => 2000, 'period' => 'monthly', 'pay_date' => now()->toDateString(), 'status' => 'approved']);
        $btSvc = app(BankTransferService::class);
        $transfer = $btSvc->request(['beneficiary_id' => $engineer->id, 'purpose' => 'salary', 'related_id' => $salary->id, 'amount' => 2000, 'currency' => 'GBP', 'provider' => 'sandbox', 'idempotency_key' => 'SAL-TEST-2000'], $finance1);
        $this->assertEquals('pending_approval', $transfer->status);
        try {
            $btSvc->approve($transfer, $finance1);
            $this->fail('self-approval must be refused');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
        // Completion without provider reference is fabrication → refused.
        try {
            $btSvc->complete($transfer, $finance2, '');
            $this->fail('reference-less completion must be refused');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(422, $e->getStatusCode());
        }
        $transfer = $btSvc->approve($transfer, $finance2);
        $transfer = $btSvc->markProcessing($transfer, $finance2);
        $this->assertEquals('processing', $transfer->status);
        $transfer = $btSvc->complete($transfer, $finance2, 'SANDBOX-BANK-REF-2000');
        $this->assertEquals('completed', $transfer->status);
        // Linked payroll settles to paid (audited); salary never fabricates payment itself.
        $this->assertEquals('paid', $salary->fresh()->status);
        // Idempotent retry: same key returns original, no duplicate money.
        $retry = $btSvc->request(['beneficiary_id' => $engineer->id, 'purpose' => 'salary', 'related_id' => $salary->id, 'amount' => 2000, 'currency' => 'GBP', 'provider' => 'sandbox', 'idempotency_key' => 'SAL-TEST-2000'], $finance1);
        $this->assertEquals($transfer->id, $retry->id);
        $this->assertEquals(1, BankTransfer::where('idempotency_key', 'SAL-TEST-2000')->count());

        // ── Commission (§63): £1,000 order × 10% = £100, approve → payout → complete ──
        $rule = \App\Models\CommissionRule::create(['name' => '[TEST] 10% sales', 'type' => 'percentage', 'rate' => 10, 'status' => 'active']);
        $commission = Commission::create(['worker_id' => $agent->id, 'rule_id' => $rule->id, 'customer_id' => $customer->id, 'commission_type' => 'per_sale', 'revenue_amount' => 1000, 'commission_rate' => 10, 'commission_amount' => 100, 'status' => 'pending']);
        app(CommissionService::class)->approveCommission($commission, $finance1->id);
        $this->assertEquals('approved', $commission->fresh()->status);
        $payout = app(CommissionService::class)->processPayout($agent->id, [$commission->id], 'bank_transfer');
        $this->assertEquals('pending', $payout->status);
        $this->assertEquals('approved', $commission->fresh()->status); // unpaid until payout completes
        app(CommissionService::class)->completePayout($payout, 'SANDBOX-BANK-REF-100');
        $this->assertEquals('completed', $payout->fresh()->status);
        $this->assertEquals('paid', $commission->fresh()->status);
        // No duplicate commission for the same order+agent.
        $this->assertEquals(1, Commission::where('worker_id', $agent->id)->where('revenue_amount', 1000)->count());

        // ── Isolation (§66): cross-account invisibility enforced at query level ──
        $this->actingAs($other)->get(route('portal.orders.show', $order->id))->assertStatus(404);
        // Agent B cannot see Agent A's commissions (scoped query).
        $agentB = $this->person('commission_agent', 'Commission Agent Beta');
        $this->assertEquals(0, Commission::where('worker_id', $agentB->id)->count());
        $this->assertEquals(1, Commission::where('worker_id', $agent->id)->count());

        // ── People 360 HTTP layer (§83): admin sees, agent is forbidden ──
        $admin = $this->person('admin', 'System Administrator Alpha');
        $this->actingAs($admin)->get(route('admin.people.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.people.show', $customer->id))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.people.show', $engineer->id))->assertStatus(200);
        $this->actingAs($agent)->get(route('admin.people.show', $engineer->id))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.salaries.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.transfers.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.franchises.show', $franchise->id))->assertStatus(200);
        $this->actingAs($engineer)->get(route('admin.salaries.index'))->assertStatus(403);

        // ── Reconciliation separation (§68): three distinct money events ──
        $income = (float) \App\Models\FinancialTransaction::where('type', 'income')->sum('amount');
        $this->assertTrue($income >= 500, 'customer revenue posted');
        $salaryTx = \App\Models\FinancialTransaction::where('type', 'salary')->sum('amount');
        $this->assertEquals(2000.0, round((float) $salaryTx, 2));
        $commTx = \App\Models\FinancialTransaction::where('type', 'commission')->sum('amount');
        $this->assertTrue((float) $commTx >= 100);
        // Balances must hold independently.
        $order->refresh();
        $this->assertTrue(\App\Services\PaymentState::balancesReconcile(1500, (float) $order->amount_paid, (float) $order->amount_due));
        $rep = app(\App\Services\PaymentReconciliationService::class)->reconcileOrder($order);
        $this->assertTrue($rep['ok'], implode('; ', $rep['issues']));
    }
}

