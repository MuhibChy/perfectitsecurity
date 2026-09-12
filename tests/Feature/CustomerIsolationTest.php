<?php

namespace Tests\Feature;

use App\Models\CustomerDocument;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\ServiceOrder;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Customer data isolation: Customer A must never access Customer B's records,
 * customers must never reach staff areas, and guests must be redirected.
 * Covers IDOR via URL/ID manipulation across the portal.
 */
class CustomerIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer', 'is_active' => true]);
    }

    /** @test */
    public function customer_a_cannot_view_customer_b_invoice()
    {
        $a = $this->customer();
        $b = $this->customer();
        $invoice = Invoice::create(['customer_id' => $b->id, 'status' => 'sent', 'subtotal' => 100, 'total' => 100]);

        $this->actingAs($a)->get(route('portal.invoices.show', $invoice->id))->assertStatus(404);
        $this->actingAs($a)->get(route('portal.invoices.pdf', $invoice->id))->assertStatus(404);
        // Owner can still view.
        $this->actingAs($b)->get(route('portal.invoices.show', $invoice->id))->assertStatus(200);
    }

    /** @test */
    public function customer_a_cannot_view_or_reply_to_customer_b_ticket()
    {
        $a = $this->customer();
        $b = $this->customer();
        $ticket = Ticket::create([
            'customer_id' => $b->id,
            'subject' => 'Isolation probe',
            'description' => 'Do not access',
            'priority' => 'medium',
        ]);

        $this->actingAs($a)->get(route('portal.tickets.show', $ticket->id))->assertStatus(404);
        $this->actingAs($a)->post(route('portal.tickets.reply', $ticket->id), ['message' => 'hijack'])
            ->assertStatus(404);
        $this->actingAs($b)->get(route('portal.tickets.show', $ticket->id))->assertStatus(200);
    }

    /** @test */
    public function customer_a_cannot_view_customer_b_project()
    {
        $a = $this->customer();
        $b = $this->customer();
        $project = Project::create(['customer_id' => $b->id, 'project_number' => 'PRJ-ISO-1', 'slug' => 'secret-project', 'name' => 'Secret project', 'status' => 'in_progress']);

        $this->actingAs($a)->get(route('portal.projects.show', $project->id))->assertStatus(404);
        $this->actingAs($b)->get(route('portal.projects.show', $project->id))->assertStatus(200);
    }

    /** @test */
    public function customer_a_cannot_view_customer_b_quotation()
    {
        $a = $this->customer();
        $b = $this->customer();
        $quotation = Quotation::create([
            'customer_id' => $b->id, 'status' => 'sent',
            'valid_until' => now()->addDays(7), 'subtotal' => 50, 'total' => 50,
        ]);

        $this->actingAs($a)->get(route('portal.quotations.show', $quotation->id))->assertStatus(404);
        $this->actingAs($a)->get(route('portal.quotations.pdf', $quotation->id))->assertStatus(404);
        $this->actingAs($b)->get(route('portal.quotations.show', $quotation->id))->assertStatus(200);
    }

    /** @test */
    public function customer_a_cannot_download_or_delete_customer_b_document()
    {
        $a = $this->customer();
        $b = $this->customer();
        $doc = CustomerDocument::create([
            'user_id' => $b->id, 'name' => 'secret', 'original_name' => 'secret.pdf',
            'mime_type' => 'application/pdf', 'size' => 10, 'path' => 'documents/secret.pdf',
            'category' => 'general',
        ]);

        $this->actingAs($a)->get(route('portal.documents.download', $doc))->assertStatus(403);
        $this->actingAs($a)->delete(route('portal.documents.destroy', $doc))->assertStatus(403);
        $this->assertDatabaseHas('customer_documents', ['id' => $doc->id]);
    }

    /** @test */
    public function customer_a_cannot_view_customer_b_order()
    {
        $a = $this->customer();
        $b = $this->customer();
        $category = \App\Models\ServiceCategory::create(['name' => 'Iso Cat', 'slug' => 'iso-cat']);
        $service = \App\Models\Service::create([
            'category_id' => $category->id, 'name' => 'Iso Service', 'slug' => 'iso-service',
            'short_description' => 'Isolation probe service', 'is_active' => true,
        ]);
        $order = ServiceOrder::create(['customer_id' => $b->id, 'service_id' => $service->id, 'requirements' => 'Isolation probe order', 'status' => 'pending']);

        $this->actingAs($a)->get(route('portal.orders.show', $order->id))->assertStatus(403);
        $this->actingAs($b)->get(route('portal.orders.show', $order->id))->assertStatus(200);
    }

    /** @test */
    public function customer_cannot_access_admin_area()
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.invoices.index'))->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.tickets.index'))->assertStatus(403);
    }

    /** @test */
    public function staff_cannot_access_customer_portal()
    {
        $agent = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);

        $this->actingAs($agent)->get(route('portal.dashboard'))->assertStatus(403);
        $this->actingAs($agent)->get(route('portal.invoices.index'))->assertStatus(403);
    }

    /** @test */
    public function guests_are_redirected_to_login()
    {
        $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    /** @test */
    public function support_agent_cannot_open_finance_or_admin_user_records()
    {
        $agent = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);

        $this->actingAs($agent)->get(route('admin.payments.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('admin.invoices.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('admin.users.index'))->assertForbidden();
    }
}
