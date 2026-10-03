<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\ConfigurationItem;
use App\Models\ItsmChange;
use App\Models\Problem;
use App\Models\RemoteSession;
use App\Models\ServiceAgreement;
use App\Models\ServiceApproval;
use App\Models\SiteVisit;
use App\Models\SlaBreachLog;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\ItsmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItsmExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;

    protected $otherCustomer;

    protected $agent;

    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::create([
            'name' => 'ITSM Customer', 'email' => 'itsm-customer@test.com',
            'password' => bcrypt('password'), 'role' => 'customer',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        $this->otherCustomer = User::create([
            'name' => 'Other Customer', 'email' => 'itsm-other@test.com',
            'password' => bcrypt('password'), 'role' => 'customer',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        $this->agent = User::create([
            'name' => 'ITSM Agent', 'email' => 'itsm-agent@test.com',
            'password' => bcrypt('password'), 'role' => 'support_agent',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        $this->manager = User::create([
            'name' => 'ITSM Manager', 'email' => 'itsm-manager@test.com',
            'password' => bcrypt('password'), 'role' => 'support_manager',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        SlaPolicy::create(['name' => 'ITSM Standard', 'response_time_minutes' => 60, 'resolution_time_minutes' => 240, 'priority' => 'medium']);
        TicketCategory::create(['name' => 'ITSM', 'slug' => 'itsm']);
    }

    public function test_problem_lifecycle_with_linked_incidents()
    {
        $t1 = Ticket::create(['customer_id' => $this->customer->id, 'subject' => 'Outage A', 'description' => 'x', 'priority' => 'high', 'status' => 'open']);
        $t2 = Ticket::create(['customer_id' => $this->customer->id, 'subject' => 'Outage B', 'description' => 'x', 'priority' => 'high', 'status' => 'open']);

        $res = $this->actingAs($this->agent)->post('/admin/problems', [
            'title' => 'Recurring outage', 'description' => 'Root cause hunt',
            'priority' => 'high', 'impact' => 'high', 'ticket_ids' => [$t1->id, $t2->id],
        ]);
        $res->assertRedirect();
        $problem = Problem::where('title', 'Recurring outage')->first();
        $this->assertNotNull($problem);
        $this->assertStringStartsWith('PRB-', $problem->problem_number);
        $this->assertCount(2, $problem->tickets);

        // Invalid transition is rejected (open → resolved skips investigation).
        $this->actingAs($this->agent)->post("/admin/problems/{$problem->id}/transition", ['to' => 'resolved'])->assertStatus(422);

        // Valid lifecycle: open → investigating → known_error → resolved → closed.
        foreach (['investigating', 'known_error', 'resolved', 'closed'] as $to) {
            $this->actingAs($this->agent)->post("/admin/problems/{$problem->id}/transition", [
                'to' => $to, 'root_cause' => 'Faulty switch', 'workaround' => 'Failover link',
            ])->assertRedirect();
        }
        $this->assertEquals('closed', $problem->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'problem.transition', 'auditable_id' => $problem->id]);
    }

    public function test_high_risk_change_requires_approval()
    {
        $change = ItsmChange::create([
            'title' => 'Core switch replacement', 'description' => 'Risky',
            'type' => 'normal', 'risk' => 'high', 'impact' => 'high',
            'requested_by' => $this->manager->id,
        ]);
        $this->assertStringStartsWith('CHG-', $change->change_number);

        // Scheduling without approval is blocked.
        $this->actingAs($this->manager)->post("/admin/changes/{$change->id}/transition", ['to' => 'assessed'])->assertRedirect();
        $this->actingAs($this->manager)->post("/admin/changes/{$change->id}/transition", ['to' => 'approved'])->assertRedirect();
        $this->actingAs($this->manager)->post("/admin/changes/{$change->id}/transition", ['to' => 'scheduled'])->assertStatus(422);

        // Approve, then scheduling works.
        $this->actingAs($this->manager)->post("/admin/changes/{$change->id}/approve", ['decision' => 'approved'])->assertRedirect();
        $this->actingAs($this->manager)->post("/admin/changes/{$change->id}/transition", ['to' => 'scheduled'])->assertRedirect();
        $this->assertEquals('scheduled', $change->fresh()->status);

        // Failure path records lessons learned.
        $this->actingAs($this->manager)->post("/admin/changes/{$change->id}/transition", ['to' => 'implementing'])->assertRedirect();
        $this->actingAs($this->manager)->post("/admin/changes/{$change->id}/transition", [
            'to' => 'failed', 'failure_notes' => 'Rollback executed; faulty firmware.',
        ])->assertRedirect();
        $this->assertEquals('Rollback executed; faulty firmware.', $change->fresh()->failure_notes);
    }

    public function test_asset_ci_relationships_and_customer_isolation()
    {
        $asset = Asset::create(['name' => 'Office server', 'category' => 'Server', 'status' => 'deployed', 'customer_id' => $this->customer->id]);
        $this->assertStringStartsWith('AST-', $asset->asset_tag);

        $app = ConfigurationItem::create(['name' => 'Billing app', 'ci_type' => 'application', 'customer_id' => $this->customer->id, 'environment' => 'production', 'status' => 'active', 'criticality' => 'high']);
        $srv = ConfigurationItem::create(['name' => 'APP-01', 'ci_type' => 'server', 'customer_id' => $this->customer->id, 'asset_id' => $asset->id, 'environment' => 'production', 'status' => 'active', 'criticality' => 'high']);

        $this->actingAs($this->agent)->post("/admin/configuration-items/{$app->id}/relate", [
            'child_ci_id' => $srv->id, 'relationship_type' => 'runs_on',
        ])->assertRedirect();
        $this->assertDatabaseHas('ci_relationships', ['parent_ci_id' => $app->id, 'child_ci_id' => $srv->id]);

        // Self-link rejected.
        $this->actingAs($this->agent)->post("/admin/configuration-items/{$app->id}/relate", [
            'child_ci_id' => $app->id, 'relationship_type' => 'depends_on',
        ])->assertSessionHasErrors();

        // Customer sees own assets only.
        $other = Asset::create(['name' => 'Someone else server', 'status' => 'deployed', 'customer_id' => $this->otherCustomer->id]);
        $res = $this->actingAs($this->customer)->get('/portal/my-assets');
        $res->assertOk()->assertSee($asset->asset_tag)->assertDontSee($other->asset_tag);
        $res = $this->actingAs($this->customer)->get('/portal/my-systems');
        $res->assertOk()->assertSee('Billing app');
    }

    public function test_remote_session_requires_consent_before_active()
    {
        $session = RemoteSession::create(['customer_id' => $this->customer->id, 'provider' => 'support_link']);

        // Technician cannot activate without customer consent.
        $this->actingAs($this->agent)->patch("/admin/remote-sessions/{$session->id}", [
            'technician_id' => $this->agent->id, 'status' => 'active',
        ])->assertSessionHasErrors(['status']);

        // Customer gives consent from portal.
        $this->actingAs($this->customer)->post("/portal/remote-support/{$session->id}/consent")->assertRedirect();
        $this->assertTrue($session->fresh()->consent_given);

        // Now activation works.
        $this->actingAs($this->agent)->patch("/admin/remote-sessions/{$session->id}", [
            'technician_id' => $this->agent->id, 'status' => 'active',
        ])->assertRedirect();
        $this->assertEquals('active', $session->fresh()->status);

        // Other customer cannot consent to this session.
        $s2 = RemoteSession::create(['customer_id' => $this->customer->id, 'provider' => 'anydesk']);
        $this->actingAs($this->otherCustomer)->post("/portal/remote-support/{$s2->id}/consent")->assertStatus(404);
    }

    public function test_site_visit_confirm_and_isolation()
    {
        $visit = SiteVisit::create([
            'customer_id' => $this->customer->id, 'address' => '1 Test Street',
            'status' => 'completed', 'technician_id' => $this->agent->id,
        ]);

        // Cannot confirm a non-completed visit.
        $scheduled = SiteVisit::create(['customer_id' => $this->customer->id, 'address' => '2 Test Street']);
        $this->actingAs($this->customer)->post("/portal/site-visits/{$scheduled->id}/confirm", ['customer_signature_name' => 'X'])->assertSessionHasErrors(['status']);

        $this->actingAs($this->customer)->post("/portal/site-visits/{$visit->id}/confirm", ['customer_signature_name' => 'Jane Doe'])->assertRedirect();
        $this->assertNotNull($visit->fresh()->customer_confirmed_at);

        // Other customer cannot confirm.
        $this->actingAs($this->otherCustomer)->post("/portal/site-visits/{$visit->id}/confirm", ['customer_signature_name' => 'Mallory'])->assertStatus(404);

        $res = $this->actingAs($this->customer)->get('/portal/site-visits');
        $res->assertOk()->assertSee($visit->visit_number);
    }

    public function test_service_agreement_portal_isolation()
    {
        ServiceAgreement::create(['customer_id' => $this->customer->id, 'title' => 'Gold cover', 'status' => 'active']);
        ServiceAgreement::create(['customer_id' => $this->otherCustomer->id, 'title' => 'Other cover', 'status' => 'active']);

        $res = $this->actingAs($this->customer)->get('/portal/my-agreements');
        $res->assertOk()->assertSee('Gold cover')->assertDontSee('Other cover');
    }

    public function test_sla_breach_log_is_idempotent()
    {
        $ticket = Ticket::create([
            'customer_id' => $this->customer->id, 'subject' => 'SLA test', 'description' => 'x',
            'priority' => 'medium', 'status' => 'open',
            'sla_response_deadline' => now()->subHour(), 'sla_resolution_deadline' => now()->subMinutes(10),
        ]);
        $svc = app(ItsmService::class);
        $svc->recordBreach($ticket, 'resolution');
        $svc->recordBreach($ticket, 'resolution'); // retry must not duplicate
        $this->assertEquals(1, SlaBreachLog::where('ticket_id', $ticket->id)->where('breach_type', 'resolution')->count());

        $this->actingAs($this->manager)->post('/admin/sla-breaches/999999/acknowledge')->assertStatus(404); // wrong id guard
        $breach = SlaBreachLog::first();
        $this->actingAs($this->manager)->post("/admin/sla-breaches/{$breach->id}/acknowledge", ['notes' => 'Seen'])->assertRedirect();
        $this->assertNotNull($breach->fresh()->acknowledged_at);
    }

    public function test_generic_approval_decision_is_idempotent()
    {
        $change = ItsmChange::create(['title' => 'Minor DNS', 'description' => 'x', 'type' => 'standard', 'risk' => 'low', 'impact' => 'low']);
        $approval = ServiceApproval::create([
            'approvable_type' => ItsmChange::class, 'approvable_id' => $change->id,
            'requested_by' => $this->agent->id, 'type' => 'change',
        ]);
        $this->assertStringStartsWith('APR-', $approval->approval_number);

        $this->actingAs($this->manager)->post("/admin/approvals/{$approval->id}/decide", ['decision' => 'approved'])->assertRedirect();
        $this->assertEquals('approved', $approval->fresh()->status);
        // Second decision rejected.
        $this->actingAs($this->manager)->post("/admin/approvals/{$approval->id}/decide", ['decision' => 'rejected'])->assertStatus(422);
    }

    public function test_customers_cannot_reach_admin_itsm_routes()
    {
        $this->actingAs($this->customer)->get('/admin/problems')->assertStatus(403);
        $this->actingAs($this->customer)->get('/admin/changes')->assertStatus(403);
        $this->actingAs($this->customer)->get('/admin/assets')->assertStatus(403);
        $this->actingAs($this->customer)->get('/admin/remote-sessions')->assertStatus(403);
        $this->actingAs($this->customer)->get('/admin/site-visits')->assertStatus(403);
        $this->actingAs($this->customer)->get('/admin/service-agreements')->assertStatus(403);
        $this->actingAs($this->customer)->get('/admin/sla-breaches')->assertStatus(403);
        $this->actingAs($this->customer)->get('/admin/approvals')->assertStatus(403);
    }

    public function test_ai_suggestions_do_not_break_ticket_view()
    {
        $ticket = Ticket::create(['customer_id' => $this->customer->id, 'subject' => 'Printer offline', 'description' => 'Printer not responding', 'priority' => 'medium', 'status' => 'open']);
        $this->actingAs($this->manager)->get("/admin/tickets/{$ticket->id}")->assertOk()->assertSee($ticket->ticket_number);
    }
}
