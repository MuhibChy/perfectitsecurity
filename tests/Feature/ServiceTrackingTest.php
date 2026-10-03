<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceEvent;
use App\Models\ServiceOrder;
use App\Models\Task;
use App\Models\User;
use App\Services\ServiceTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function fixture(): array
    {
        $cat = ServiceCategory::create(['name' => 'Audit Cat', 'slug' => 'audit-cat-st']);
        $service = Service::create(['category_id' => $cat->id, 'name' => 'Security Assessment', 'slug' => 'sec-assess']);
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $pm = User::factory()->create(['role' => 'project_manager', 'is_active' => true]);
        $eng = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $order = ServiceOrder::create(['order_number' => 'ORD-ST-1', 'customer_id' => $customer->id, 'service_id' => $service->id, 'requirements' => 'Assess our site', 'status' => 'confirmed', 'total' => 1500, 'amount_paid' => 1500, 'amount_due' => 0]);
        $project = Project::create(['project_number' => 'PRJ-ST-1', 'name' => 'Assessment', 'slug' => 'assess-1', 'customer_id' => $customer->id, 'project_manager_id' => $pm->id, 'service_id' => $service->id, 'status' => 'in_progress', 'deadline' => today()->addDays(10)->toDateString()]);

        return compact('service', 'customer', 'pm', 'eng', 'order', 'project');
    }

    /** @test */
    public function order_preserves_service_snapshot()
    {
        $fx = $this->fixture();
        $order = $fx['order']->fresh();
        $this->assertSame('Security Assessment', $order->service_snapshot['name']);
        // Later catalogue edits must not rewrite history.
        $fx['service']->update(['name' => 'Renamed Service']);
        $this->assertSame('Security Assessment', $order->fresh()->service_snapshot['name']);
    }

    /** @test */
    public function progress_is_calculated_from_real_work()
    {
        $fx = $this->fixture();
        ProjectMilestone::create(['project_id' => $fx['project']->id, 'name' => 'Scope', 'is_completed' => true]);
        ProjectMilestone::create(['project_id' => $fx['project']->id, 'name' => 'Testing', 'is_completed' => false]);
        Task::create(['task_number' => 'TSK-ST-A', 'project_id' => $fx['project']->id, 'title' => 'A', 'assigned_to' => $fx['eng']->id, 'created_by' => $fx['pm']->id, 'status' => 'completed']);
        Task::create(['task_number' => 'TSK-ST-B', 'project_id' => $fx['project']->id, 'title' => 'B', 'assigned_to' => $fx['eng']->id, 'created_by' => $fx['pm']->id, 'status' => 'completed']);
        Task::create(['task_number' => 'TSK-ST-C', 'project_id' => $fx['project']->id, 'title' => 'C', 'assigned_to' => $fx['eng']->id, 'created_by' => $fx['pm']->id, 'status' => 'in_progress', 'start_date' => now()->subDays(2)]);

        $progress = ServiceTrackingService::projectProgress($fx['project']->fresh());
        // Milestones 1/2 = 50%, tasks 2/3 ≈ 67% → average ≈ 58%.
        $this->assertEqualsWithDelta(58, $progress['percent'], 1);
        $this->assertStringContainsString('milestones', $progress['basis']);
    }

    /** @test */
    public function task_lifecycle_records_events_and_running_time()
    {
        $fx = $this->fixture();
        $task = Task::create(['task_number' => 'TSK-ST-D', 'project_id' => $fx['project']->id, 'title' => 'D', 'assigned_to' => $fx['eng']->id, 'created_by' => $fx['pm']->id, 'status' => 'pending']);

        // Start → start_date set + started event.
        $this->actingAs($fx['pm'])->put(route('admin.tasks.update', $task), [
            'title' => 'D', 'priority' => 'high', 'status' => 'in_progress', 'reason' => 'Kicking off.',
        ])->assertRedirect();
        $task->refresh();
        $this->assertNotNull($task->start_date);
        $this->assertDatabaseHas('service_events', ['entity_id' => $task->id, 'action' => 'started']);

        $run = ServiceTrackingService::taskRunning($task);
        $this->assertNotNull($run);
        $this->assertStringContainsString('min', $run['human']);

        // Pause freezes running time with reason.
        $this->actingAs($fx['pm'])->post(route('admin.tasks.pause', $task), ['reason' => 'Waiting on customer credentials.', 'waiting_for_customer' => 1])->assertSessionHas('success');
        $run = ServiceTrackingService::taskRunning($task->fresh());
        $this->assertTrue($run['frozen']);

        // Resume + ETA change recorded with history.
        $this->actingAs($fx['pm'])->post(route('admin.tasks.resume', $task), ['reason' => 'Credentials received.'])->assertSessionHas('success');
        $this->actingAs($fx['pm'])->put(route('admin.tasks.update', $task), [
            'title' => 'D', 'priority' => 'high', 'status' => 'in_progress',
            'deadline' => today()->addDays(5)->toDateString(), 'reason' => 'Scope grew.',
        ])->assertRedirect();
        $etaHistory = ServiceTrackingService::etaHistory(Task::class, $task->id);
        $this->assertCount(1, $etaHistory);
        $this->assertSame(today()->addDays(5)->toDateString(), $etaHistory->first()->new_value);

        // Reopen after completion preserves the completion event.
        $this->actingAs($fx['pm'])->put(route('admin.tasks.update', $task), [
            'title' => 'D', 'priority' => 'high', 'status' => 'completed', 'reason' => 'Work done.',
        ])->assertRedirect();
        $this->actingAs($fx['pm'])->put(route('admin.tasks.update', $task), [
            'title' => 'D', 'priority' => 'high', 'status' => 'in_progress', 'reason' => 'Customer found a gap.',
        ])->assertRedirect();
        $actions = ServiceEvent::where('entity_type', Task::class)->where('entity_id', $task->id)->pluck('action')->all();
        $this->assertContains('completed', $actions);
        $this->assertContains('reopened', $actions);
    }

    /** @test */
    public function customer_updates_are_visible_and_internal_notes_are_not()
    {
        $fx = $this->fixture();
        $this->actingAs($fx['pm'])->post(route('admin.projects.publish-update', $fx['project']), ['comment' => 'Testing is 75% complete, validation next.'])->assertSessionHas('success');
        $this->actingAs($fx['pm'])->post(route('admin.projects.internal-note', $fx['project']), ['comment' => 'Freelancer rate still unconfirmed.'])->assertSessionHas('success');

        $visible = ServiceEvent::where('project_id', $fx['project']->id)->where('customer_visible', true)->pluck('action')->all();
        $this->assertContains('update_published', $visible);

        $page = $this->actingAs($fx['customer'])->get(route('portal.tracking.show', $fx['project']))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Testing is 75% complete', $page);
        $this->assertStringNotContainsString('Freelancer rate', $page);
    }

    /** @test */
    public function customer_update_requests_notify_and_throttle()
    {
        $fx = $this->fixture();
        $this->actingAs($fx['customer'])->post(route('portal.tracking.request-update', $fx['project']))->assertSessionHas('success');
        $this->actingAs($fx['customer'])->post(route('portal.tracking.request-update', $fx['project']))->assertSessionHas('error');
        $this->assertSame(1, ServiceEvent::where('project_id', $fx['project']->id)->where('action', 'update_requested')->count());

        $event = ServiceEvent::where('action', 'update_requested')->first();
        $this->actingAs($fx['pm'])->post(route('admin.service-events.answer', $event), ['answer' => 'Report lands Friday.'])->assertSessionHas('success');
        $this->assertDatabaseHas('service_events', ['action' => 'query_answered']);
    }

    /** @test */
    public function change_requests_preserve_original_scope_until_decided()
    {
        $fx = $this->fixture();
        $this->actingAs($fx['customer'])->post(route('portal.orders.change-request', $fx['order']), [
            'title' => 'Add API testing', 'details' => 'Please also cover our public API endpoints thoroughly.',
        ])->assertSessionHas('success');

        $change = \App\Models\ServiceChangeRequest::firstOrFail();
        $this->assertSame('requested', $change->status);
        $this->assertSame('Assess our site', $fx['order']->fresh()->requirements);

        $this->actingAs($fx['pm'])->post(route('admin.change-requests.review', $change), [
            'status' => 'approved', 'decision_note' => 'Approved with +2 days and +£200.',
            'price_impact' => 200, 'time_impact_days' => 2,
        ])->assertSessionHas('success');
        $this->assertSame('approved', $change->fresh()->status);
        $this->assertSame('Assess our site', $fx['order']->fresh()->requirements);
    }

    /** @test */
    public function maintenance_lifecycle_and_visibility()
    {
        $fx = $this->fixture();
        $this->actingAs($fx['pm'])->post(route('admin.projects.maintenance.store', $fx['project']), [
            'title' => 'Quarterly security review', 'type' => 'security review',
            'frequency' => 'quarterly', 'next_due_at' => today()->addDays(10)->toDateString(),
        ])->assertSessionHas('success');

        $m = \App\Models\ServiceMaintenance::firstOrFail();
        $page = $this->actingAs($fx['customer'])->get(route('portal.tracking.index'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Quarterly security review', $page);

        $this->actingAs($fx['pm'])->post(route('admin.maintenance.complete', $m))->assertSessionHas('success');
        $this->assertSame('completed', $m->fresh()->status);
        $this->assertDatabaseHas('service_events', ['action' => 'maintenance_completed']);
    }

    /** @test */
    public function operations_and_tracking_pages_render_with_real_data()
    {
        $fx = $this->fixture();
        Task::create(['task_number' => 'TSK-ST-E', 'project_id' => $fx['project']->id, 'title' => 'Live work', 'assigned_to' => $fx['eng']->id, 'created_by' => $fx['pm']->id, 'status' => 'in_progress', 'start_date' => now()->subDays(3)->subHours(7)]);

        $ops = $this->actingAs($fx['pm'])->get(route('admin.operations.index'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Live work', $ops);
        $this->assertStringContainsString('3 days', $ops);

        $mine = $this->actingAs($fx['customer'])->get(route('portal.tracking.index'))->assertStatus(200)->getContent();
        $this->assertStringContainsString('ORD-ST-1', $mine);

        $detail = $this->actingAs($fx['pm'])->get(route('admin.service.show', $fx['project']))->assertStatus(200)->getContent();
        $this->assertStringContainsString('Service Timeline', $detail);
    }

    /** @test */
    public function tracking_idor_boundaries_hold()
    {
        $fx = $this->fixture();
        $other = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        // Oracle-free scoping (owner-scoped findOrFail in TrackingController
        // and CustomerOrderController): another customer's project/order
        // answers 404, never 403 — no existence oracle.
        $this->actingAs($other)->get(route('portal.tracking.show', $fx['project']))->assertStatus(404);
        $this->actingAs($other)->post(route('portal.tracking.request-update', $fx['project']))->assertStatus(404);
        $this->actingAs($other)->post(route('portal.orders.change-request', $fx['order']), ['title' => 'X', 'details' => 'Trying to hijack this order.'])->assertStatus(404);

        $support = User::factory()->create(['role' => 'support_agent', 'is_active' => true]);
        $this->actingAs($support)->get(route('admin.operations.index'))->assertStatus(403);
        $this->actingAs($support)->get(route('admin.service.show', $fx['project']))->assertStatus(200);
    }

    /** @test */
    public function ai_reports_recorded_status_without_invention()
    {
        $fx = $this->fixture();
        Task::create(['task_number' => 'TSK-ST-AI', 'project_id' => $fx['project']->id, 'title' => 'AI visible work', 'assigned_to' => $fx['eng']->id, 'created_by' => $fx['pm']->id, 'status' => 'in_progress', 'start_date' => now()->subDay()]);
        $service = app(\App\Services\Ai\AiKnowledgeService::class);
        $ctx = $service->getCustomerContext($fx['customer']);
        $this->assertNotEmpty($ctx['services']);
        $this->assertSame('ORD-ST-1', $ctx['services'][0]['order_number']);
        $this->assertSame('confirmed', $ctx['services'][0]['status']);

        $staffCtx = $service->getStaffContext($fx['eng']);
        $this->assertArrayHasKey('active_work', $staffCtx);
    }
}
