<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\ServiceOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(array $over = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'verification_status' => 'fully_verified',
            'phone' => '+12025550123',
        ], $over));
    }

    private function makeService(): Service
    {
        $cat = ServiceCategory::create(['name' => 'IT', 'slug' => 'it', 'is_active' => true]);
        return Service::create([
            'category_id' => $cat->id, 'name' => 'Support', 'slug' => 'support',
            'base_price' => 100, 'is_active' => true,
        ]);
    }

    public function test_healthz_probe_returns_ok_without_auth(): void
    {
        $this->get('/healthz')->assertOk()->assertJson(['status' => 'ok', 'db' => 'ok']);
    }

    public function test_security_headers_include_csp(): void
    {
        $this->get('/')->assertHeader('Content-Security-Policy');
    }

    public function test_closed_order_rejects_price_proposals(): void
    {
        $customer = $this->makeCustomer();
        $service = $this->makeService();
        $svc = app(ServiceOrderWorkflowService::class);
        $order = $svc->createCustomerOrder([
            'service_id' => $service->id, 'requirements' => 'Need help with servers urgently',
            'proposed_price' => 100,
        ], $customer);
        $order->update(['status' => 'closed']);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $svc->proposePrice($order, ['amount' => 120], $customer);
    }

    public function test_confirmed_order_requires_positive_total(): void
    {
        $customer = $this->makeCustomer();
        $service = $this->makeService();
        $svc = app(ServiceOrderWorkflowService::class);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $svc->createCustomerOrder([
            'service_id' => $service->id, 'requirements' => 'Need help with servers urgently',
            'proposed_price' => 0, 'discount_amount' => 0,
        ], $customer);
    }

    public function test_customer_cannot_reject_draft_quotation(): void
    {
        $customer = $this->makeCustomer();
        $q = Quotation::create([
            'customer_id' => $customer->id, 'quotation_number' => 'Q-1',
            'title' => 'T', 'subtotal' => 10, 'total' => 10, 'status' => 'draft',
        ]);
        $this->actingAs($customer)->post("/portal/quotations/{$q->id}/reject")->assertStatus(422);
    }

    public function test_paid_invoice_cannot_be_edited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = $this->makeCustomer();
        $inv = Invoice::create([
            'customer_id' => $customer->id, 'invoice_number' => 'INV-1',
            'subtotal' => 10, 'total' => 10, 'amount_due' => 0, 'status' => 'paid',
            'due_date' => now()->addWeek(),
        ]);
        $this->actingAs($admin)->put("/admin/invoices/{$inv->id}", [
            'due_date' => now()->addWeek()->toDateString(), 'items' => [],
        ])->assertStatus(422);
    }

    public function test_support_agent_cannot_access_quotations_or_work_order_payments(): void
    {
        $agent = User::factory()->create(['role' => 'support_agent']);
        $customer = $this->makeCustomer();
        $service = $this->makeService();
        $order = ServiceOrder::create([
            'order_number' => 'ORD-2099-000001', 'customer_id' => $customer->id,
            'service_id' => $service->id, 'created_by' => $customer->id,
            'requirements' => 'req', 'status' => 'confirmed', 'total' => 100, 'amount_due' => 100,
        ]);
        $this->actingAs($agent)->get('/admin/quotations')->assertForbidden();
        $this->actingAs($agent)->post("/admin/work-orders/{$order->id}/record-payment", [
            'amount' => 10, 'payment_method' => 'cash',
        ])->assertForbidden();
    }

    public function test_user_update_rejects_invalid_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = $this->makeCustomer();
        $this->actingAs($admin)->put("/admin/users/{$target->id}", [
            'name' => 'X', 'email' => $target->email, 'role' => 'hacker',
        ])->assertSessionHasErrors('role');
    }

    public function test_expense_receipt_requires_auth(): void
    {
        $this->get('/admin/expenses/1/receipt')->assertRedirect('/login');
    }
}
