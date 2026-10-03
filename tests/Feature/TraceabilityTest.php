<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TraceabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraceabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function lifecycle(): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true, 'company_name' => 'NorthBridge Retail Ltd']);
        $agent = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $pm = User::factory()->create(['role' => 'project_manager', 'is_active' => true]);
        $engineer = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $category = \App\Models\ServiceCategory::create(['name' => 'Trace Test Cat', 'slug' => 'trace-test-cat']);
        $service = \App\Models\Service::create(['category_id' => $category->id, 'name' => 'Trace Test Service', 'slug' => 'trace-test-service']);

        $order = ServiceOrder::create(['order_number' => 'ORD-2026-000001', 'customer_id' => $customer->id, 'service_id' => $service->id, 'requirements' => 'Managed IT Support', 'status' => 'confirmed', 'total' => 1000, 'amount_paid' => 400, 'amount_due' => 600]);
        $invoice = Invoice::create(['invoice_number' => 'INV-2026-000001', 'customer_id' => $customer->id, 'subtotal' => 1000, 'total' => 1000, 'amount_paid' => 400, 'amount_due' => 600, 'status' => 'partially_paid', 'due_date' => now()->addDays(14)]);
        $payment = Payment::create(['payment_number' => 'PAY-2026-000001', 'invoice_id' => $invoice->id, 'customer_id' => $customer->id, 'amount' => 400, 'status' => 'completed', 'payment_method' => 'bank_transfer', 'paid_at' => now()]);
        $project = Project::create(['project_number' => 'PRJ-2026-000001', 'name' => 'NorthBridge Support', 'slug' => 'nb-support', 'customer_id' => $customer->id, 'project_manager_id' => $pm->id, 'status' => 'in_progress']);
        $task = Task::create(['task_number' => 'TSK-2026-000001', 'project_id' => $project->id, 'title' => 'M365 Configuration', 'assigned_to' => $engineer->id, 'created_by' => $pm->id, 'status' => 'completed']);
        $ticket = Ticket::create(['ticket_number' => 'TK-2026-000001', 'customer_id' => $customer->id, 'subject' => 'Email issue', 'description' => 'Sync failure', 'status' => 'resolved', 'assigned_to' => $agent->id]);

        return compact('customer', 'agent', 'pm', 'engineer', 'order', 'invoice', 'payment', 'project', 'task', 'ticket');
    }

    /** @test */
    public function status_transitions_are_audited_with_actor_and_values()
    {
        $this->lifecycle();
        $task = Task::where('task_number', 'TSK-2026-000001')->first();
        $engineer = User::where('role', 'employee')->first();
        $this->actingAs($engineer);

        $task->update(['status' => 'in_progress']);

        $log = AuditLog::where('action', 'status.changed')->where('auditable_type', Task::class)->where('auditable_id', $task->id)->first();
        $this->assertNotNull($log);
        $this->assertSame('completed', $log->old_values['status']);
        $this->assertSame('in_progress', $log->new_values['status']);
        $this->assertEquals($engineer->id, $log->user_id);
    }

    /** @test */
    public function customer_360_aggregates_real_records_only()
    {
        $fx = $this->lifecycle();
        $overview = TraceabilityService::customerOverview($fx['customer']);

        $this->assertSame(1, $overview['orders']);
        $this->assertSame(1000.0, $overview['orders_total']);
        $this->assertSame(1, $overview['invoices']);
        $this->assertSame(400.0, $overview['paid_total']);
        $this->assertSame(600.0, $overview['outstanding']);
        $this->assertSame(1, $overview['projects']);
        $this->assertSame(1, $overview['tickets']);

        $timeline = TraceabilityService::customerTimeline($fx['customer'], 'admin');
        $labels = $timeline->pluck('label')->all();
        foreach (['Customer account created', 'Order created', 'Payment received', 'Invoice issued', 'Project created', 'Ticket opened'] as $expected) {
            $this->assertContains($expected, $labels);
        }
    }

    /** @test */
    public function employee_history_links_work_to_customers()
    {
        $fx = $this->lifecycle();
        $overview = TraceabilityService::employeeOverview($fx['engineer']);
        $this->assertSame(1, $overview['tasks_assigned']);
        $this->assertSame(1, $overview['tasks_completed']);

        $links = TraceabilityService::employeeCustomerLinks($fx['engineer']);
        $this->assertTrue($links->contains(fn ($l) => $l['customer']->id === $fx['customer']->id));
    }

    /** @test */
    public function history_endpoints_enforce_idor_boundaries()
    {
        $fx = $this->lifecycle();
        $other = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Portal history is own-scoped: another customer's data never appears.
        $content = $this->actingAs($other)->get(route('portal.history.index'))->assertStatus(200)->getContent();
        $this->assertStringNotContainsString('ORD-2026-000001', $content);
        $this->assertStringNotContainsString('INV-2026-000001', $content);
        $own = $this->actingAs($fx['customer'])->get(route('portal.history.index'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('ORD-2026-000001', $own);

        // Employee B cannot open employee A's work history; admin can.
        $this->actingAs($fx['agent'])->get(route('admin.history.employee', $fx['engineer']))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.history.employee', $fx['engineer']))->assertStatus(200);

        // Customers cannot reach staff history or audit areas.
        $this->actingAs($fx['customer'])->get(route('admin.history.customer', $fx['customer']))->assertStatus(403);
        $this->actingAs($fx['customer'])->get(route('admin.history.audit'))->assertStatus(403);

        // Staff can open the customer 360 page.
        $this->actingAs($fx['agent'])->get(route('admin.history.customer', $fx['customer']))->assertStatus(200);
    }

    /** @test */
    public function global_search_finds_references_and_people()
    {
        $fx = $this->lifecycle();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $results = TraceabilityService::globalSearch('TK-2026-000001');
        $this->assertNotEmpty($results['references']);

        $results = TraceabilityService::globalSearch('NorthBridge');
        $this->assertTrue($results['customers']->contains('id', $fx['customer']->id));

        $this->actingAs($admin)->get(route('admin.search.index', ['q' => 'INV-2026-000001']))->assertStatus(200)->assertSee('INV-2026-000001', false);
    }

    /** @test */
    public function consistency_check_flags_real_problems()
    {
        $this->lifecycle();
        // Craft a genuine mismatch: paid + due != total.
        Invoice::where('invoice_number', 'INV-2026-000001')->first()->update(['amount_due' => 500]);

        $issues = TraceabilityService::consistencyCheck();
        $this->assertArrayHasKey('invoice_math', $issues);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.history.consistency'))->assertStatus(200)->assertSee('Invoices where paid + due', false);
    }

    /** @test */
    public function audit_dashboard_and_my_work_render()
    {
        $fx = $this->lifecycle();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.history.audit'))->assertStatus(200);
        $this->actingAs($fx['engineer'])->get(route('admin.history.my-work'))->assertStatus(200)->assertSee('M365 Configuration', false);
    }

    /** @test */
    public function ai_staff_context_contains_only_own_work()
    {
        $fx = $this->lifecycle();
        $service = app(\App\Services\Ai\AiKnowledgeService::class);
        $ctx = $service->getStaffContext($fx['engineer']);
        $this->assertEmpty($ctx['open_tasks'] ?? []);
        $this->assertSame(1, $ctx['month_summary']['tasks_completed_this_month']);

        $other = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $ctxOther = $service->getStaffContext($other);
        $this->assertSame(0, $ctxOther['month_summary']['tasks_completed_this_month']);
    }
}
