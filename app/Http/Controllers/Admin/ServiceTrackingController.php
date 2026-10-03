<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ServiceChangeRequest;
use App\Models\ServiceEvent;
use App\Models\ServiceMaintenance;
use App\Services\ServiceTrackingService;
use Illuminate\Http\Request;

/**
 * Live service operations: active work, follow-ups, maintenance and
 * change-request review. All figures come from authoritative rows.
 */
class ServiceTrackingController extends Controller
{
    public function operations()
    {
        $stats = ServiceTrackingService::operationsStats();
        $active = ServiceTrackingService::activeWork();
        $followUps = ServiceEvent::with(['actor', 'customer', 'order', 'project'])
            ->whereIn('action', ['update_requested', 'query_asked'])
            ->latest()->take(20)->get();
        $answeredIds = ServiceEvent::whereIn('action', ['update_published', 'query_answered'])
            ->selectRaw('MAX(id) as id')->groupBy('entity_type', 'entity_id')->pluck('id');
        $answeredKeys = ServiceEvent::whereIn('id', $answeredIds)->get()
            ->map(fn ($e) => $e->entity_type . ':' . $e->entity_id)->all();
        $followUps = $followUps->reject(fn ($e) => in_array($e->entity_type . ':' . $e->entity_id, $answeredKeys)
            && ServiceEvent::where('entity_type', $e->entity_type)->where('entity_id', $e->entity_id)->whereIn('action', ['update_published', 'query_answered'])->where('created_at', '>', $e->created_at)->exists());
        $maintenanceDue = ServiceMaintenance::with(['customer', 'project', 'assignee'])
            ->whereIn('status', ['scheduled', 'active'])->whereDate('next_due_at', '<=', today()->addDays(14))
            ->orderBy('next_due_at')->take(20)->get();
        return view('admin.tracking.operations', compact('stats', 'active', 'followUps', 'maintenanceDue'));
    }

    public function service(Project $project)
    {
        $project->load(['customer', 'service', 'projectManager', 'milestones', 'tasks.assignee', 'comments.user', 'members']);
        $progress = ServiceTrackingService::projectProgress($project);
        $stage = ServiceTrackingService::currentStage($project);
        $timeline = ServiceTrackingService::serviceTimeline(null, $project->id);
        $etaHistory = ServiceTrackingService::etaHistory(Project::class, $project->id);
        $maintenances = ServiceMaintenance::where('project_id', $project->id)->latest()->get();
        $changes = ServiceChangeRequest::with('requester')->where('project_id', $project->id)->latest()->get();
        return view('admin.tracking.service', compact('project', 'progress', 'stage', 'timeline', 'etaHistory', 'maintenances', 'changes'));
    }

    public function publishUpdate(Request $request, Project $project)
    {
        $data = $request->validate(['comment' => 'required|string|min:5|max:5000', 'next_update' => 'nullable|date']);
        $comment = $project->comments()->create([
            'user_id' => auth()->id(), 'comment' => $data['comment'], 'is_customer_visible' => true,
        ]);
        ServiceTrackingService::record([
            'entity_type' => Project::class, 'entity_id' => $project->id,
            'project_id' => $project->id, 'customer_id' => $project->customer_id,
            'action' => 'update_published', 'comment' => mb_substr($data['comment'], 0, 200),
            'metadata' => ['next_update' => $data['next_update'] ?? null], 'visible' => true,
        ]);
        ServiceTrackingService::notify($project->customer_id, 'service_update', 'Service update published', "New update on '{$project->name}'.");
        return back()->with('success', 'Customer-visible update published and the customer notified.');
    }

    public function internalNote(Request $request, Project $project)
    {
        $data = $request->validate(['comment' => 'required|string|min:3|max:5000']);
        $project->comments()->create([
            'user_id' => auth()->id(), 'comment' => $data['comment'], 'is_customer_visible' => false,
        ]);
        ServiceTrackingService::record([
            'entity_type' => Project::class, 'entity_id' => $project->id,
            'project_id' => $project->id, 'customer_id' => $project->customer_id,
            'action' => 'progress_updated', 'comment' => 'Internal note recorded.', 'visible' => false,
        ]);
        return back()->with('success', 'Internal note recorded (never visible to the customer).');
    }

    public function answerQuery(Request $request, ServiceEvent $event)
    {
        abort_unless(in_array($event->action, ['query_asked', 'update_requested'], true), 404);
        $data = $request->validate(['answer' => 'required|string|min:5|max:5000', 'publish' => 'nullable|boolean']);
        ServiceTrackingService::record([
            'entity_type' => $event->entity_type, 'entity_id' => $event->entity_id,
            'order_id' => $event->order_id, 'project_id' => $event->project_id,
            'customer_id' => $event->customer_id, 'action' => 'query_answered',
            'comment' => mb_substr($data['answer'], 0, 500), 'visible' => (bool) ($data['publish'] ?? true),
        ]);
        if ($event->customer_id) {
            ServiceTrackingService::notify($event->customer_id, 'service_update', 'Response to your query', mb_substr($data['answer'], 0, 200));
        }
        return back()->with('success', 'Response recorded and linked to the service.');
    }

    public function storeMaintenance(Request $request, Project $project)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255', 'type' => 'required|string|max:100',
            'starts_at' => 'nullable|date', 'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'frequency' => 'nullable|string|max:100', 'next_due_at' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id', 'notes' => 'nullable|string',
        ]);
        $maintenance = ServiceMaintenance::create($data + [
            'project_id' => $project->id, 'customer_id' => $project->customer_id, 'status' => 'scheduled',
        ]);
        ServiceTrackingService::record([
            'entity_type' => Project::class, 'entity_id' => $project->id,
            'project_id' => $project->id, 'customer_id' => $project->customer_id,
            'action' => 'maintenance_scheduled', 'comment' => $maintenance->title, 'visible' => true,
        ]);
        return back()->with('success', 'Maintenance scheduled with customer visibility.');
    }

    public function completeMaintenance(ServiceMaintenance $maintenance)
    {
        $maintenance->update(['status' => 'completed']);
        ServiceTrackingService::record([
            'entity_type' => Project::class, 'entity_id' => $maintenance->project_id,
            'project_id' => $maintenance->project_id, 'customer_id' => $maintenance->customer_id,
            'action' => 'maintenance_completed', 'comment' => $maintenance->title, 'visible' => true,
        ]);
        return back()->with('success', 'Maintenance marked completed.');
    }

    public function reviewChange(Request $request, ServiceChangeRequest $change)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected,implemented',
            'decision_note' => 'required|string|min:5|max:2000',
            'price_impact' => 'nullable|numeric|min:0',
            'time_impact_days' => 'nullable|integer|min:0|max:365',
        ]);
        $change->update([
            'status' => $data['status'], 'decision_note' => $data['decision_note'],
            'price_impact' => $data['price_impact'] ?? $change->price_impact,
            'time_impact_days' => $data['time_impact_days'] ?? $change->time_impact_days,
            'reviewed_by' => auth()->id(), 'decided_at' => now(),
        ]);
        ServiceTrackingService::record([
            'entity_type' => $change->project_id ? Project::class : \App\Models\ServiceOrder::class,
            'entity_id' => $change->project_id ?? $change->order_id,
            'order_id' => $change->order_id, 'project_id' => $change->project_id,
            'customer_id' => $change->requester->id,
            'action' => 'change_decided', 'old' => 'requested', 'new' => $data['status'],
            'reason' => $data['decision_note'], 'visible' => true,
        ]);
        ServiceTrackingService::notify($change->requested_by, 'change_decision', 'Change request ' . $data['status'], mb_substr($data['decision_note'], 0, 200));
        return back()->with('success', 'Change request decided and the customer notified.');
    }
}
