<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('customer', 'projectManager');
        if ($request->status) $query->where('status', $request->status);
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('project_number', 'like', "%{$request->search}%");
            });
        }
        $projects = $query->latest()->paginate(20);
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $customers = User::customers()->get();
        $managers = User::where('role', 'project_manager')->get();
        $services = Service::where('is_active', true)->get();
        return view('admin.projects.create', compact('customers', 'managers', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'required|exists:users,id',
            'project_manager_id' => 'nullable|exists:users,id',
            'service_id' => 'nullable|exists:services,id',
            'description' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $validated['project_number'] = 'PRJ-' . strtoupper(Str::random(8));
        $validated['slug'] = Str::slug($validated['name']);

        $project = Project::create($validated);

        return redirect()->route('admin.projects.index')->with('success', 'Project created!');
    }

    public function show($id)
    {
        $project = Project::with('customer', 'projectManager', 'tasks', 'milestones', 'comments.user', 'files', 'invoices')->findOrFail($id);
        $tasks = Task::where('project_id', $id)->with('assignee')->get();
        return view('admin.projects.show', compact('project', 'tasks'));
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $customers = User::customers()->get();
        $managers = User::where('role', 'project_manager')->get();
        $services = Service::where('is_active', true)->get();
        return view('admin.projects.edit', compact('project', 'customers', 'managers', 'services'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'required|in:pending,planning,in_progress,on_hold,review,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $project->update($validated);
        return redirect()->route('admin.projects.index')->with('success', 'Project updated!');
    }

    public function destroy($id)
    {
        Project::findOrFail($id)->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }

    public function updateStatus(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $request->validate(['status' => 'required']);
        $project->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Project status updated!');
    }
}
