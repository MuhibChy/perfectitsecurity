<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenditureAndProfitabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $tech;
    private User $admin;
    private ServiceOrder $order;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::create(['name' => 'Development', 'slug' => 'development', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Custom API Integration',
            'slug' => 'custom-api-integration',
            'starting_price' => 2000.00,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+447000555666',
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);

        $this->tech = User::factory()->create(['role' => 'employee']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $workflow = app(ServiceOrderWorkflowService::class);
        $this->order = $workflow->createCustomerOrder([
            'service_id' => $service->id,
            'requirements' => 'Stripe and CRM webhooks synchronization',
            'expected_cost' => 600.00,
            'negotiate' => false,
        ], $this->customer);

        $this->task = $this->order->tasks->first();
    }

    public function test_expenditure_and_actual_profit_calculation(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // 1. Customer pays $2000 in full
        $workflow->recordPayment($this->order, ['amount' => 2000.00], $this->customer);

        // 2. Log 10 hours employee labour @ $30/hr ($300)
        $expense1 = $workflow->recordExpense($this->order, [
            'cost_type' => 'labour',
            'hours' => 10,
            'hourly_rate' => 30.00,
            'worker_id' => $this->tech->id,
            'task_id' => $this->task->id,
            'description' => 'API webhook controllers development',
        ], $this->admin);

        $this->assertInstanceOf(Expense::class, $expense1);
        $this->assertEquals(300.00, (float) $expense1->amount);

        // 3. Log $150 server/cloud infrastructure cost
        $expense2 = $workflow->recordExpense($this->order, [
            'cost_type' => 'cloud',
            'amount' => 150.00,
            'vendor' => 'AWS',
            'description' => 'Dedicated ECS staging cluster',
        ], $this->admin);

        $this->assertEquals(150.00, (float) $expense2->amount);

        // Verify profitability figures on Service Order
        $this->order->refresh();
        $this->assertEquals(2000.00, (float) $this->order->amount_paid);
        $this->assertEquals(450.00, (float) $this->order->total_cost); // $300 labour + $150 cloud
        $this->assertEquals(1550.00, (float) $this->order->actual_profit); // $2000 - $450 = $1550
        $this->assertEquals(1400.00, (float) $this->order->expected_profit); // $2000 - $600 estimated = $1400
    }

    public function test_order_closure_enforces_completion_and_zero_balance(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // Attempt to close order while tasks are pending and balance > 0
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $workflow->closeOrder($this->order, $this->admin);
    }

    public function test_order_closes_successfully_when_work_completed_and_paid_in_full(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // 1. Pay in full
        $workflow->recordPayment($this->order, ['amount' => 2000.00], $this->customer);

        // 2. Complete technical task
        $workflow->completeTechnicalTask($this->task, $this->tech, 'All tests passing, integration deployed');

        // 3. Close order
        $closedOrder = $workflow->closeOrder($this->order, $this->admin, 'Client approved UAT test');

        $this->assertEquals('closed', $closedOrder->status);
        $this->assertNotNull($closedOrder->closed_at);
        $this->assertEquals('Client approved UAT test', $closedOrder->closure_notes);
    }
}
