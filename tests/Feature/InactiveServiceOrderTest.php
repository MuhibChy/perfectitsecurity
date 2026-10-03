<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Service $activeService;
    private Service $inactiveService;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::create(['name' => 'QA Services', 'slug' => 'qa-services', 'is_active' => true]);
        $this->activeService = Service::create([
            'category_id' => $category->id,
            'name' => 'Active QA Service',
            'slug' => 'active-qa-service',
            'short_description' => 'Orderable service',
            'starting_price' => 500.00,
            'is_active' => true,
        ]);
        $this->inactiveService = Service::create([
            'category_id' => $category->id,
            'name' => 'Retired QA Service',
            'slug' => 'retired-qa-service',
            'short_description' => 'Delisted service',
            'starting_price' => 500.00,
            'is_active' => false,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+447123456789',
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);
    }

    public function test_active_service_order_succeeds(): void
    {
        $this->actingAs($this->customer)->post(route('portal.orders.store'), [
            'service_id' => $this->activeService->id,
            'requirements' => 'Full regression coverage for the release',
        ])->assertRedirect();

        $this->assertDatabaseHas('service_orders', [
            'customer_id' => $this->customer->id,
            'service_id' => $this->activeService->id,
        ]);
    }

    public function test_inactive_service_order_rejected_at_http(): void
    {
        $this->actingAs($this->customer)->post(route('portal.orders.store'), [
            'service_id' => $this->inactiveService->id,
            'requirements' => 'Attempt to order a delisted service directly',
        ])->assertSessionHasErrors('service_id');

        $this->assertDatabaseMissing('service_orders', [
            'customer_id' => $this->customer->id,
            'service_id' => $this->inactiveService->id,
        ]);
    }

    public function test_inactive_service_order_rejected_at_service_layer(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        app(ServiceOrderWorkflowService::class)->createCustomerOrder([
            'service_id' => $this->inactiveService->id,
            'requirements' => 'Direct service-layer attempt with delisted service',
            'negotiate' => true,
        ], $this->customer);

        $this->assertDatabaseCount('service_orders', 0);
    }

    public function test_nonexistent_service_order_rejected(): void
    {
        $this->actingAs($this->customer)->post(route('portal.orders.store'), [
            'service_id' => 999999,
            'requirements' => 'Attempt to order a service that does not exist',
        ])->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('service_orders', 0);
    }

    public function test_other_customer_cannot_exploit_inactive_service(): void
    {
        $other = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+447987654321',
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);

        $this->actingAs($other)->post(route('portal.orders.store'), [
            'service_id' => $this->inactiveService->id,
            'requirements' => 'Cross-customer attempt with delisted service id',
        ])->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('service_orders', 0);
    }
}
