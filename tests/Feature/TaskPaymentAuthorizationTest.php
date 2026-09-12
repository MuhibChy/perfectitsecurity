<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $tech;
    private User $manager;
    private ServiceOrder $order;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::create(['name' => 'Security', 'slug' => 'security', 'is_active' => true]);
        $service = Service::create([
            'category_id' => $category->id,
            'name' => 'Penetration Testing Audit',
            'slug' => 'penetration-testing-audit',
            'starting_price' => 1500.00,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+447888999000',
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);

        $this->tech = User::factory()->create(['role' => 'project_manager']);
        $this->manager = User::factory()->create(['role' => 'admin']);

        $workflow = app(ServiceOrderWorkflowService::class);
        $this->order = $workflow->createCustomerOrder([
            'service_id' => $service->id,
            'requirements' => 'Full penetration test on web portal',
            'negotiate' => false,
        ], $this->customer);

        $this->task = $this->order->tasks->first();
    }

    public function test_cannot_start_it_task_when_payment_is_not_authorized(): void
    {
        $this->assertEquals('not_authorized', $this->order->payment_authorization);

        // Attempting to change task status to in_progress via TaskController
        $response = $this->actingAs($this->tech)->put(route('admin.tasks.update', $this->task->id), [
            'title' => $this->task->title,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        // Must be rejected with 422
        $response->assertStatus(422);
    }

    public function test_manager_override_allows_it_task_to_start(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);
        $workflow->managerOverride($this->order, $this->manager, 'Executive client urgent audit approved');

        $this->order->refresh();
        $this->assertEquals('manager_override', $this->order->payment_authorization);
        $this->assertTrue($this->order->canStartWork());

        // Now technician can start work
        $response = $this->actingAs($this->tech)->put(route('admin.tasks.update', $this->task->id), [
            'title' => $this->task->title,
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $response->assertSessionHasNoErrors();
        $this->task->refresh();
        $this->assertEquals('in_progress', $this->task->status);
    }

    public function test_task_completion_transitions_order_to_awaiting_final_payment_when_balance_remains(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // Pay $500 deposit (satisfies > 30% min deposit)
        $workflow->recordPayment($this->order, ['amount' => 500.00], $this->customer);
        $this->order->refresh();

        // Complete the task
        $workflow->completeTechnicalTask($this->task, $this->tech, 'Audit completed, report generated', 240);

        $this->task->refresh();
        $this->order->refresh();

        $this->assertEquals('completed', $this->task->status);
        $this->assertEquals('awaiting_final_payment', $this->order->status);
        $this->assertEquals(1000.00, (float) $this->order->amount_due);

        // Once customer pays remaining balance, transitions to financially_completed
        $workflow->recordPayment($this->order, ['amount' => 1000.00], $this->customer);
        $this->order->refresh();
        $this->assertEquals('financially_completed', $this->order->status);
    }
}
