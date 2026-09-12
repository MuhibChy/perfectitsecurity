<?php

namespace Tests\Feature;

use App\Models\Receipt;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderPaymentReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Service $service;
    private ServiceOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $category = ServiceCategory::create(['name' => 'Support', 'slug' => 'support', 'is_active' => true]);
        $this->service = Service::create([
            'category_id' => $category->id,
            'name' => 'Dedicated Sysadmin Support',
            'slug' => 'dedicated-sysadmin-support',
            'starting_price' => 1000.00,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '+447000333444',
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
        ]);

        $workflow = app(ServiceOrderWorkflowService::class);
        $this->order = $workflow->createCustomerOrder([
            'service_id' => $this->service->id,
            'requirements' => 'Monthly server monitoring and maintenance',
            'negotiate' => false,
        ], $this->customer);
    }

    public function test_partial_payment_generates_receipt_and_authorizes_work_when_min_deposit_met(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // Pay $300 (30% minimum deposit on $1000)
        $result = $workflow->recordPayment($this->order, [
            'amount' => 300.00,
            'payment_method' => 'credit_card',
        ], $this->customer);

        $this->order->refresh();
        $this->assertEquals(300.00, (float) $this->order->amount_paid);
        $this->assertEquals(700.00, (float) $this->order->amount_due);
        $this->assertEquals('ready_to_start', $this->order->payment_authorization);

        // Official Receipt verification
        $receipt = $result['receipt'];
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertStringStartsWith('RCP-', $receipt->receipt_number);
        $this->assertEquals(300.00, (float) $receipt->amount);
        $this->assertEquals(700.00, (float) $receipt->remaining_balance);
        $this->assertEquals($this->order->id, $receipt->service_order_id);
    }

    public function test_partial_payment_below_min_deposit_keeps_deposit_required_state(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // Pay $100 (< 30% min deposit on $1000)
        $workflow->recordPayment($this->order, [
            'amount' => 100.00,
        ], $this->customer);

        $this->order->refresh();
        $this->assertEquals('deposit_required', $this->order->payment_authorization);
    }

    public function test_second_payment_completes_balance_and_issues_second_receipt(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        // 1. First payment of $300
        $workflow->recordPayment($this->order, ['amount' => 300.00], $this->customer);

        // 2. Second payment of remaining $700
        $result = $workflow->recordPayment($this->order, ['amount' => 700.00], $this->customer);

        $this->order->refresh();
        $this->assertEquals(1000.00, (float) $this->order->amount_paid);
        $this->assertEquals(0.00, (float) $this->order->amount_due);
        $this->assertEquals('fully_paid', $this->order->payment_authorization);

        $this->assertCount(2, $this->order->receipts);
        $secondReceipt = $result['receipt'];
        $this->assertEquals(700.00, (float) $secondReceipt->amount);
        $this->assertEquals(0.00, (float) $secondReceipt->remaining_balance);
    }

    public function test_cannot_pay_more_than_outstanding_balance(): void
    {
        $workflow = app(ServiceOrderWorkflowService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        // Attempting to pay $1500 on $1000 order
        $workflow->recordPayment($this->order, ['amount' => 1500.00], $this->customer);
    }
}
