<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('assignee', 'creator', 'project');
        if ($request->status) $query->where('status', $request->status);
        if ($request->priority) $query->where('priority', $request->priority);
        if ($request->assigned_to) $query->where('assigned_to', $request->assigned_to);
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('task_number', 'like', "%{$request->search}%");
            });
        }
        $tasks = $query->latest()->paginate(20);
        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->get();
        $projects = Project::where('status', '!=', 'completed')->get();
        return view('admin.tasks.create', compact('users', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'type' => 'required|in:open,assigned,application_required,first_come,team,recurring',
            'budget' => 'nullable|numeric|min:0',
            'reward_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'max_applicants' => 'nullable|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();
        $task = Task::create($validated);

        return redirect()->route('admin.tasks.index')->with('success', 'Task created!');
    }

    public function show($id)
    {
        $task = Task::with('assignee', 'creator', 'project', 'applications.user', 'comments.user', 'attachments')->findOrFail($id);
        return view('admin.tasks.show', compact('task'));
    }

    public function edit($id)
    {
        $task = Task::findOrFail($id);
        $users = User::where('is_active', true)->get();
        $projects = Project::where('status', '!=', 'completed')->get();
        return view('admin.tasks.edit', compact('task', 'users', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,submitted,under_review,approved,rejected,completed,cancelled',
            'deadline' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'technical_notes' => 'nullable|string',
            'actual_minutes' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Enforcement: Cannot start work if payment authorization is not satisfied on linked Service Order
        if (in_array($validated['status'], ['in_progress', 'started']) && $task->serviceOrder) {
            abort_unless(
                $task->serviceOrder->canStartWork(),
                422,
                "IT work cannot start: Payment authorization status is '{$task->serviceOrder->payment_authorization}'. A minimum deposit or manager override is required."
            );
        }

        if ($validated['status'] === 'completed') {
            app(\App\Services\ServiceOrderWorkflowService::class)->completeTechnicalTask(
                $task,
                auth()->user(),
                $request->input('technical_notes'),
                $request->input('actual_minutes') ? (int) $request->input('actual_minutes') : null
            );
            \App\Services\ServiceTrackingService::record([
                'entity_type' => \App\Models\Task::class, 'entity_id' => $task->id,
                'order_id' => $task->service_order_id, 'project_id' => $task->project_id,
                'customer_id' => $task->customer_id ?? $task->project?->customer_id,
                'action' => 'completed', 'old' => $task->status, 'new' => 'completed',
                'reason' => $request->input('reason'), 'visible' => true,
            ]);
            return redirect()->route('admin.tasks.index')->with('success', 'Task marked as completed! Order financial status updated.');
        }

        $oldStatus = $task->status;
        $oldDeadline = $task->deadline?->format('Y-m-d');
        $oldProgress = (int) ($task->progress ?? 0);
        $tracked = $validated;
        unset($tracked['reason']);
        if ($validated['status'] === 'in_progress' && !$task->start_date) {
            $tracked['start_date'] = now();
        }
        if ($validated['status'] === 'in_progress') {
            $tracked['paused_at'] = null;
        }
        $task->update($tracked);

        $tracker = \App\Services\ServiceTrackingService::class;
        $base = ['entity_type' => \App\Models\Task::class, 'entity_id' => $task->id,
            'order_id' => $task->service_order_id, 'project_id' => $task->project_id,
            'customer_id' => $task->customer_id ?? $task->project?->customer_id,
            'reason' => $request->input('reason')];
        if ($oldStatus !== $validated['status']) {
            $action = ($oldStatus === 'completed' && $validated['status'] === 'in_progress') ? 'reopened' : 'status_changed';
            if ($validated['status'] === 'in_progress' && $oldStatus === 'pending') $action = 'started';
            $tracker::record($base + ['action' => $action, 'old' => $oldStatus, 'new' => $validated['status'],
                'visible' => in_array($validated['status'], ['in_progress', 'completed'], true)]);
            if ($task->assigned_to) {
                $tracker::notify($task->assigned_to, 'task_status', 'Task status changed', "Task {$task->task_number} is now {$validated['status']}.");
            }
        }
        if (($validated['deadline'] ?? null) !== $oldDeadline) {
            $tracker::record($base + ['action' => 'eta_changed', 'old' => $oldDeadline, 'new' => $validated['deadline'] ?? null, 'visible' => true]);
        }
        if ((int) ($validated['progress'] ?? $oldProgress) !== $oldProgress) {
            $tracker::record($base + ['action' => 'progress_updated', 'old' => (string) $oldProgress, 'new' => (string) ($validated['progress'] ?? $oldProgress), 'visible' => true]);
        }
        return redirect()->route('admin.tasks.index')->with('success', 'Task updated!');
    }

    public function pause(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $data = $request->validate(['reason' => 'required|string|min:5|max:1000', 'waiting_for_customer' => 'nullable|boolean']);
        $task->update(['paused_at' => now()]);
        \App\Services\ServiceTrackingService::record([
            'entity_type' => \App\Models\Task::class, 'entity_id' => $task->id,
            'order_id' => $task->service_order_id, 'project_id' => $task->project_id,
            'customer_id' => $task->customer_id ?? $task->project?->customer_id,
            'action' => !empty($data['waiting_for_customer']) ? 'waiting_for_customer' : 'paused',
            'reason' => $data['reason'],
            'comment' => !empty($data['waiting_for_customer']) ? 'Waiting for customer information.' : null,
            'visible' => !empty($data['waiting_for_customer']),
        ]);
        return back()->with('success', 'Task marked as waiting. Running time is frozen and the customer sees the reason where appropriate.');
    }

    public function resume(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->update(['paused_at' => null]);
        \App\Services\ServiceTrackingService::record([
            'entity_type' => \App\Models\Task::class, 'entity_id' => $task->id,
            'order_id' => $task->service_order_id, 'project_id' => $task->project_id,
            'customer_id' => $task->customer_id ?? $task->project?->customer_id,
            'action' => 'resumed', 'reason' => $request->input('reason'), 'visible' => true,
        ]);
        return back()->with('success', 'Task resumed.');
    }

    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted.');
    }

    public function assign(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $task->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'assigned',
            'is_locked' => true,
            'locked_by' => $request->assigned_to,
            'locked_at' => now(),
        ]);

        \App\Services\ServiceTrackingService::record([
            'entity_type' => \App\Models\Task::class, 'entity_id' => $task->id,
            'order_id' => $task->service_order_id, 'project_id' => $task->project_id,
            'customer_id' => $task->customer_id ?? $task->project?->customer_id,
            'action' => 'assigned', 'new' => optional(\App\Models\User::find($request->assigned_to))->name,
        ]);
        \App\Services\ServiceTrackingService::notify($request->assigned_to, 'task_assigned', 'Task assigned', "You were assigned task {$task->task_number}: {$task->title}.");

        return redirect()->back()->with('success', 'Task assigned!');
    }

    public function approve(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->update(['status' => 'approved', 'progress' => 100]);

        if ($task->reward_amount > 0 && $task->assigned_to) {
            app(CommissionService::class)->calculateCommission(
                $task->assigned_to,
                $task->reward_amount,
                null,
                ['task_id' => $task->id, 'project_id' => $task->project_id]
            );
        }

        return redirect()->back()->with('success', 'Task approved!');
    }

    public function reject(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->update(['status' => 'rejected']);
        return redirect()->back()->with('info', 'Task rejected.');
    }
}
