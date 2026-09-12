<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('customer_id', auth()->id())->with('projectManager')->latest()->paginate(15);
        return view('customer.projects.index', compact('projects'));
    }

    public function show($id)
    {
        $project = Project::where('customer_id', auth()->id())->with('tasks', 'milestones', 'comments.user', 'projectManager')->findOrFail($id);
        return view('customer.projects.show', compact('project'));
    }
}
