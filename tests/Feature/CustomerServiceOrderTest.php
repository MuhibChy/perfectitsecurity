<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $verifiedCustomer;
    private User $unverifiedCustomer;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::create(['name' => 'Cloud Support', 'slug' => 'cloud-support', 'is_active' => true]);
        $this->service = Service::create([
            'category_id' => $category->id,
            'name' => 'Cloud Migration Service',
            'slug' => 'cloud-migration-service',
            'short_description' => 'Migrate infrastructure to AWS/GCP',
            'starting_price' => 1000.00,
            'is_active' => true,
        ]);

        $this->verifiedCustomer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+447123456789',
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);

        $this->unverifiedCustomer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => null,
            'phone' => null,
            'phone_verified_at' => null,
            'verification_status' => 'pending',
        ]);
    }

    public function test_unverified_customer_cannot_confirm_fixed_order_immediately(): void
    {
        $service = app(ServiceOrderWorkflowService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $service->createCustomerOrder([
            'service_id' => $this->service->id,
            'requirements' => 'Deploy AWS ECS cluster',
            'negotiate' => false,
        ], $this->unverifiedCustomer);
    }

    public function test_verified_customer_can_place_confirmed_order_generating_financial_and_it_records(): void
    {
        $service = app(ServiceOrderWorkflowService::class);

        $order = $service->createCustomerOrder([
            'service_id' => $this->service->id,
            'requirements' => 'Full cloud migration with terraform scripts',
            'negotiate' => false,
            'urgency' => 'high',
        ], $this->verifiedCustomer);

        $this->assertInstanceOf(ServiceOrder::class, $order);
        $this->assertStringStartsWith('ORD-', $order->order_number);
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals(1000.00, (float) $order->total);
        $this->assertTrue($order->price_locked);

        // Verify connected records
        $this->assertCount(1, $order->invoices);
        $this->assertEquals(1000.00, (float) $order->invoices->first()->total);
        $this->assertCount(1, $order->tickets);
        $this->assertCount(1, $order->tasks);
        $this->assertEquals($order->id, $order->tasks->first()->service_order_id);
    }

    public function test_customer_price_negotiation_workflow(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // 1. Initial price discussion
        $order = $workflow->createCustomerOrder([
            'service_id' => $this->service->id,
            'requirements' => 'Database migration and optimization',
            'negotiate' => true,
            'proposed_price' => 800.00,
        ], $this->verifiedCustomer);

        $this->assertEquals('negotiating', $order->status);
        $this->assertFalse($order->price_locked);

        // 2. Staff counter-offers $900
        $staff = User::factory()->create(['role' => 'employee']);
        $revision = $workflow->proposePrice($order, [
            'amount' => 900.00,
            'terms' => 'Including backup verification',
            'kind' => 'employee_offer',
        ], $staff);

        $this->assertEquals(900.00, (float) $revision->amount);
        $this->assertEquals('proposed', $revision->status);

        // 3. Customer accepts final offer
        $confirmedOrder = $workflow->acceptPrice($order, $this->verifiedCustomer, $revision->id);

        $this->assertEquals('confirmed', $confirmedOrder->status);
        $this->assertTrue($confirmedOrder->price_locked);
        $this->assertEquals(900.00, (float) $confirmedOrder->total);
        $this->assertCount(1, $confirmedOrder->invoices);
        $this->assertEquals(900.00, (float) $confirmedOrder->invoices->first()->total);
    }

    public function test_customer_cannot_access_another_customers_order_idor_check(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);
        $order = $workflow->createCustomerOrder([
            'service_id' => $this->service->id,
            'requirements' => 'Private company server setup',
            'negotiate' => false,
        ], $this->verifiedCustomer);

        $anotherCustomer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($anotherCustomer)->get(route('portal.orders.show', $order->id));
        $response->assertStatus(403);
    }
}
