<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Essential business workflows added/fixed during the production audit:
 * ticket assignment notifies, public intake alerts staff, quotation send
 * notifies, lead conversion, contract renew/expire.
 */
class BusinessWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ticket_assignment_notifies_assignee()
    {
        $manager = User::factory()->create(['role' => 'support_manager', 'is_active' => true]);
        $agent = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::create(['customer_id' => $customer->id, 'subject' => 'Notify me',
            'description' => 'x', 'priority' => 'high']);

        $this->actingAs($manager)->post(route('admin.tickets.assign', $ticket->id), [
            'assigned_to' => $agent->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', ['type' => 'App\\Notifications\\TicketAssignedNotification', 'notifiable_id' => $agent->id]);
    }

    /** @test */
    public function contact_and_quote_forms_alert_staff()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->post(route('contact.submit'), [
            'name' => 'Visitor', 'email' => 'visitor@example.test',
            'subject' => 'Need help', 'message' => 'Please contact me about services.',
        ])->assertRedirect();
        $this->assertDatabaseHas('notifications', ['type' => 'contact_enquiry', 'notifiable_id' => $admin->id]);

        $this->post(route('get-quote.submit'), [
            'name' => 'Buyer', 'email' => 'buyer@example.test',
            'message' => 'Need managed IT support for our office of twenty staff members.',
        ])->assertRedirect();
        $this->assertDatabaseHas('notifications', ['type' => 'quote_request', 'notifiable_id' => $admin->id]);
    }

    /** @test */
    public function quotation_send_notifies_customer()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        $quotation = \App\Models\Quotation::create([
            'customer_id' => $customer->id, 'subtotal' => 100, 'total' => 100,
            'status' => 'draft', 'valid_until' => now()->addDays(14),
        ]);

        $this->actingAs($admin)->post(route('admin.quotations.send', $quotation->id))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['type' => 'quotation_received', 'notifiable_id' => $customer->id]);
    }

    /** @test */
    public function lead_converts_to_customer_with_verification_pending()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $lead = Lead::create(['name' => 'New Prospect', 'email' => 'prospect@example.test', 'status' => 'new']);

        $this->actingAs($admin)->post(route('admin.leads.convert', $lead->id))->assertRedirect();

        $customer = User::where('email', 'prospect@example.test')->firstOrFail();
        $this->assertTrue($customer->isCustomer());
        $this->assertNull($customer->email_verified_at);
        $this->assertEquals('won', $lead->fresh()->status);
        $this->assertEquals($customer->id, $lead->fresh()->customer_id);
    }

    /** @test */
    public function lead_convert_links_existing_customer_and_rejects_staff_email()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $existing = User::factory()->create(['role' => 'customer', 'email' => 'known@example.test']);
        $lead = Lead::create(['name' => 'Known', 'email' => 'known@example.test', 'status' => 'new']);
        $this->actingAs($admin)->post(route('admin.leads.convert', $lead->id))->assertRedirect();
        $this->assertEquals($existing->id, $lead->fresh()->customer_id);

        $staff = User::factory()->create(['role' => 'support_agent', 'email' => 'staffmember@example.test']);
        $lead2 = Lead::create(['name' => 'Staff', 'email' => 'staffmember@example.test', 'status' => 'new']);
        $this->actingAs($admin)->post(route('admin.leads.convert', $lead2->id))->assertStatus(422);
    }

    /** @test */
    public function contract_renew_extends_and_expire_command_closes()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        $contract = Contract::create([
            'customer_id' => $customer->id, 'title' => 'Support 2025',
            'start_date' => now()->subYear(), 'end_date' => now()->addDays(10),
            'status' => 'active', 'value' => 1200,
        ]);

        $this->actingAs($admin)->post(route('admin.contracts.renew', $contract->id), [
            'end_date' => now()->addYear()->format('Y-m-d'),
        ])->assertRedirect();
        $this->assertEquals('active', $contract->fresh()->status);

        $this->actingAs($admin)->post(route('admin.contracts.renew', $contract->id), [
            'end_date' => now()->subDay()->format('Y-m-d'),
        ])->assertStatus(302);
        $this->assertTrue(session()->has('errors'));

        $contract->update(['end_date' => now()->subDay()]);
        $this->artisan('contracts:expire')->assertExitCode(0);
        $this->assertEquals('expired', $contract->fresh()->status);
    }
}
