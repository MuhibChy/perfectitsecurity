<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ServiceChangeRequest;
use App\Models\ServiceEvent;
use App\Models\ServiceMaintenance;
use App\Models\ServiceOrder;
use App\Services\ServiceTrackingService;
use Illuminate\Http\Request;

/**
 * Customer live tracking: own services only, every lookup scoped.
 */
class TrackingController extends Controller
{
    public function index()
    {
        $services = ServiceTrackingService::customerServices(auth()->user());
        $maintenance = ServiceMaintenance::where('customer_id', auth()->id())
            ->whereIn('status', ['scheduled', 'active'])->orderBy('next_due_at')->take(10)->get();
        return view('customer.tracking.index', compact('services', 'maintenance'));
    }

    public function show($id)
    {
        // Owner-scoped lookup (no implicit binding): another customer's
        // project answers 404, never 403 — no existence oracle.
        $project = Project::where('customer_id', auth()->id())->findOrFail($id);
        $project->load(['service', 'projectManager', 'milestones', 'tasks.assignee']);
        $progress = ServiceTrackingService::projectProgress($project);
        $stage = ServiceTrackingService::currentStage($project);
        $updates = ServiceEvent::where('project_id', $project->id)->where('customer_visible', true)->latest()->take(50)->get();
        $comments = $project->comments()->where('is_customer_visible', true)->with('user')->latest()->take(20)->get();
        $maintenances = ServiceMaintenance::where('project_id', $project->id)->latest()->take(50)->get();
        $changes = ServiceChangeRequest::where('project_id', $project->id)->where('requested_by', auth()->id())->latest()->take(50)->get();
        return view('customer.tracking.show', compact('project', 'progress', 'stage', 'updates', 'comments', 'maintenances', 'changes'));
    }

    public function requestUpdate(Request $request, $id)
    {
        $project = Project::where('customer_id', auth()->id())->findOrFail($id);
        $recent = ServiceEvent::where('project_id', $project->id)->where('action', 'update_requested')
            ->where('created_at', '>=', now()->subDay())->exists();
        if ($recent) {
            return back()->with('error', 'An update was already requested in the last 24 hours. The team has been notified.');
        }
        ServiceTrackingService::record([
            'entity_type' => Project::class, 'entity_id' => $project->id,
            'project_id' => $project->id, 'customer_id' => auth()->id(),
            'action' => 'update_requested', 'comment' => 'Customer requested a service update.',
            'visible' => false,
        ]);
        foreach (array_filter([$project->project_manager_id]) as $staffId) {
            ServiceTrackingService::notify($staffId, 'update_requested', 'Customer requested an update', "Update requested on '{$project->name}'.");
        }
        return back()->with('success', 'Update requested. The team has been notified and this request is part of your service history.');
    }

    public function askQuery(Request $request, $id)
    {
        $project = Project::where('customer_id', auth()->id())->findOrFail($id);
        $data = $request->validate(['question' => 'required|string|min:5|max:2000']);
        ServiceTrackingService::record([
            'entity_type' => Project::class, 'entity_id' => $project->id,
            'project_id' => $project->id, 'customer_id' => auth()->id(),
            'action' => 'query_asked', 'comment' => mb_substr($data['question'], 0, 500),
            'visible' => false,
        ]);
        if ($project->project_manager_id) {
            ServiceTrackingService::notify($project->project_manager_id, 'customer_query', 'Customer question', "On '{$project->name}': " . mb_substr($data['question'], 0, 150));
        }
        return back()->with('success', 'Question sent and linked to your service.');
    }

    public function storeChange(Request $request, $orderId)
    {
        $order = ServiceOrder::where('customer_id', auth()->id())->findOrFail($orderId);
        $data = $request->validate(['title' => 'required|string|max:255', 'details' => 'required|string|min:10|max:5000']);
        $projectId = $order->tasks()->whereNotNull('project_id')->first()?->project_id
            ?? ServiceEvent::where('order_id', $order->id)->whereNotNull('project_id')->latest()->first()?->project_id;
        $change = ServiceChangeRequest::create([
            'order_id' => $order->id, 'project_id' => $projectId,
            'requested_by' => auth()->id(), 'title' => $data['title'], 'details' => $data['details'],
            'status' => 'requested',
        ]);
        ServiceTrackingService::record([
            'entity_type' => ServiceOrder::class, 'entity_id' => $order->id,
            'order_id' => $order->id, 'project_id' => $projectId, 'customer_id' => auth()->id(),
            'action' => 'change_requested', 'comment' => $data['title'], 'visible' => false,
        ]);
        return back()->with('success', 'Change request submitted for review. The original scope is unchanged until a decision is recorded.');
    }
}
