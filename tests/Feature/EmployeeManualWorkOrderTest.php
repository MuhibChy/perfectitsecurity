<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManualWorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private User $customer;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::create(['name' => 'Network', 'slug' => 'network', 'is_active' => true]);
        $this->service = Service::create([
            'category_id' => $category->id,
            'name' => 'Cisco Firewall Configuration',
            'slug' => 'cisco-firewall-config',
            'starting_price' => 1200.00,
            'is_active' => true,
        ]);

        $this->employee = User::factory()->create(['role' => 'employee']);
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+447000111222',
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);
    }

    public function test_unauthorized_user_cannot_create_manual_work_order(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $workflow->createManualWorkOrder([
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'price' => 1200.00,
            'requirements' => 'Firewall rules setup',
        ], $this->customer);
    }

    public function test_employee_can_create_manual_work_order_with_full_financial_and_it_records(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        $order = $workflow->createManualWorkOrder([
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'price' => 1200.00,
            'discount_amount' => 100.00, // < 10% discount
            'requirements' => 'Office walk-in requested onsite firewall deployment',
            'order_source_label' => 'Office Walk-in',
            'assigned_to' => $this->employee->id,
        ], $this->employee);

        $this->assertInstanceOf(ServiceOrder::class, $order);
        $this->assertEquals('employee_manual', $order->source);
        $this->assertEquals('Office Walk-in', $order->order_source_label);
        $this->assertEquals('confirmed', $order->status);
        $this->assertEquals(1100.00, (float) $order->total);

        // Verification of zero-bypass policy: Invoice, Ticket, and Task are automatically generated
        $this->assertCount(1, $order->invoices);
        $this->assertEquals(1100.00, (float) $order->invoices->first()->total);
        $this->assertCount(1, $order->tickets);
        $this->assertCount(1, $order->tasks);
        $this->assertEquals($this->employee->id, $order->tasks->first()->assigned_to);
    }

    public function test_manual_work_order_with_excessive_discount_requires_manager_approval(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // Employee offers $500 discount on $1000 order (> 20%)
        $order = $workflow->createManualWorkOrder([
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'price' => 1000.00,
            'discount_amount' => 500.00, // 50% discount
            'requirements' => 'Heavy discounted promotional order',
        ], $this->employee);

        $this->assertEquals('pending_approval', $order->status);
        $this->assertFalse($order->price_locked);
    }

    public function test_employee_can_create_customer_profile_on_the_fly(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        $order = $workflow->createManualWorkOrder([
            'customer_name' => 'Jane Walkin',
            'customer_email' => 'jane.walkin@example.com',
            'customer_phone' => '+447999123456',
            'service_id' => $this->service->id,
            'price' => 1200.00,
            'requirements' => 'New walk-in client needs router audit',
        ], $this->employee);

        $this->assertEquals('Jane Walkin', $order->customer->name);
        $this->assertEquals('jane.walkin@example.com', $order->customer->email);
        $this->assertDatabaseHas('users', ['email' => 'jane.walkin@example.com']);
    }
}
