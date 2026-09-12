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
            'status' => 'required',
            'deadline' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'technical_notes' => 'nullable|string',
            'actual_minutes' => 'nullable|integer|min:0',
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
            return redirect()->route('admin.tasks.index')->with('success', 'Task marked as completed! Order financial status updated.');
        }

        $task->update($validated);
        return redirect()->route('admin.tasks.index')->with('success', 'Task updated!');
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
