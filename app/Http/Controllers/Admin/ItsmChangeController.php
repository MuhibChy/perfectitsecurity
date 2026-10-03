<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItsmChange;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ItsmService;
use Illuminate\Http\Request;

class ItsmChangeController extends Controller
{
    public function index(Request $request)
    {
        $query = ItsmChange::with('customer', 'assignee');
        if ($request->status) $query->where('status', $request->status);
        if ($request->risk) $query->where('risk', $request->risk);
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('change_number', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%");
            });
        }
        $changes = $query->latest()->paginate(20);
        return view('admin.changes.index', compact('changes'));
    }

    public function create()
    {
        $agents = User::whereIn('role', ['support_agent', 'support_manager', 'admin', 'super_admin'])
            ->where('is_active', true)->get();
        return view('admin.changes.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:standard,normal,emergency',
            'risk' => 'required|in:low,medium,high',
            'impact' => 'required|in:low,medium,high,critical',
            'customer_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'implementation_plan' => 'nullable|string',
            'rollback_plan' => 'nullable|string',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after_or_equal:scheduled_start',
        ]);
        $data['requested_by'] = $request->user()->id;

        $change = ItsmChange::create($data);
        AuditService::log('change.create', 'itsm', $change, "Change {$change->change_number} created");

        return redirect()->route('admin.changes.show', $change)->with('success', 'Change request recorded.');
    }

    public function show(ItsmChange $change)
    {
        $change->load('customer', 'requester', 'assignee', 'approvals.approver', 'workflowApprovals');
        $agents = User::whereIn('role', ['support_agent', 'support_manager', 'admin', 'super_admin'])
            ->where('is_active', true)->get();
        return view('admin.changes.show', compact('change', 'agents'));
    }

    public function update(Request $request, ItsmChange $change)
    {
        if (in_array($change->status, ['completed', 'cancelled'], true)) {
            return redirect()->back()->withErrors(['status' => 'Closed changes cannot be edited.']);
        }
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|in:standard,normal,emergency',
            'risk' => 'sometimes|in:low,medium,high',
            'impact' => 'sometimes|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'implementation_plan' => 'nullable|string',
            'rollback_plan' => 'nullable|string',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after_or_equal:scheduled_start',
            'implementation_result' => 'nullable|string',
            'failure_notes' => 'nullable|string',
        ]);
        $old = $change->only(array_keys($data));
        $change->update($data);
        AuditService::log('change.update', 'itsm', $change, "Change {$change->change_number} updated", $old, $data);

        return redirect()->back()->with('success', 'Change updated.');
    }

    public function transition(Request $request, ItsmChange $change, ItsmService $itsm)
    {
        $data = $request->validate([
            'to' => 'required|in:requested,assessed,approved,scheduled,implementing,completed,failed,cancelled',
            'implementation_result' => 'nullable|string',
            'failure_notes' => 'nullable|string',
        ]);
        $extra = array_filter($request->only(['implementation_result', 'failure_notes']), fn ($v) => $v !== null);
        $itsm->transitionChange($change, $data['to'], $request->user()->id, $extra);

        return redirect()->back()->with('success', "Change moved to {$data['to']}.");
    }

    public function approve(Request $request, ItsmChange $change)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'comments' => 'nullable|string|max:2000',
        ]);
        $change->approvals()->create([
            'approver_id' => $request->user()->id,
            'decision' => $data['decision'],
            'comments' => $data['comments'] ?? null,
            'decided_at' => now(),
        ]);
        AuditService::log('change.approval', 'itsm', $change, "Change {$change->change_number} {$data['decision']} by {$request->user()->name}");

        if ($data['decision'] === 'approved' && $change->status === 'assessed') {
            app(ItsmService::class)->transitionChange($change->fresh(), 'approved', $request->user()->id);
        }

        return redirect()->back()->with('success', "Change {$data['decision']}.");
    }
}
