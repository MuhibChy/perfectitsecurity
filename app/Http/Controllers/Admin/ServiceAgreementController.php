<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceAgreement;
use App\Models\ServiceApproval;
use App\Models\SlaBreachLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ItsmService;
use Illuminate\Http\Request;

class ServiceAgreementController extends Controller
{
    public function index(Request $request)
    {
        $agreements = ServiceAgreement::with('customer')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('agreement_number', 'like', "%{$request->search}%")
                   ->orWhere('title', 'like', "%{$request->search}%");
            }))
            ->latest()->paginate(20);
        $customers = User::where('role', 'customer')->where('is_active', true)->limit(200)->get();

        return view('admin.agreements.index', compact('agreements', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'scope' => 'nullable|string',
            'coverage_hours' => 'nullable|string|max:255',
            'response_target_minutes' => 'nullable|integer|min:1',
            'resolution_target_minutes' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'required|in:draft,active,suspended,expired,terminated',
        ]);
        $agreement = ServiceAgreement::create($data);
        AuditService::log('agreement.create', 'itsm', $agreement, "Agreement {$agreement->agreement_number} created");

        return redirect()->route('admin.agreements.show', $agreement)->with('success', 'Service agreement recorded.');
    }

    public function show(ServiceAgreement $agreement)
    {
        $agreement->load('customer');

        return view('admin.agreements.show', compact('agreement'));
    }

    public function update(Request $request, ServiceAgreement $agreement)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'scope' => 'nullable|string',
            'coverage_hours' => 'nullable|string|max:255',
            'response_target_minutes' => 'nullable|integer|min:1',
            'resolution_target_minutes' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'sometimes|in:draft,active,suspended,expired,terminated',
            'renewal_reminder_at' => 'nullable|date',
        ]);
        $old = $agreement->only(array_keys($data));
        $agreement->update($data);
        AuditService::log('agreement.update', 'itsm', $agreement, "Agreement {$agreement->agreement_number} updated", $old, $data);

        return redirect()->back()->with('success', 'Service agreement updated.');
    }

    public function breaches(Request $request)
    {
        $breaches = SlaBreachLog::with('ticket', 'slaPolicy')
            ->when($request->breach_type, fn ($q) => $q->where('breach_type', $request->breach_type))
            ->latest('breached_at')->paginate(20);

        return view('admin.agreements.breaches', compact('breaches'));
    }

    public function acknowledgeBreach(Request $request, SlaBreachLog $breach)
    {
        $data = $request->validate(['notes' => 'nullable|string|max:2000']);
        $breach->update([
            'acknowledged_by' => $request->user()->id,
            'acknowledged_at' => now(),
            'notes' => $data['notes'] ?? $breach->notes,
        ]);
        AuditService::log('sla.acknowledge', 'itsm', $breach, "SLA breach #{$breach->id} acknowledged");

        return redirect()->back()->with('success', 'Breach acknowledged.');
    }

    public function approvals(Request $request)
    {
        $approvals = ServiceApproval::with('requester', 'approver')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest()->paginate(20);

        return view('admin.agreements.approvals', compact('approvals'));
    }

    public function decideApproval(Request $request, ServiceApproval $approval, ItsmService $itsm)
    {
        $data = $request->validate([
            'decision' => 'required|in:approved,rejected,cancelled',
            'comments' => 'nullable|string|max:2000',
        ]);
        $itsm->decideApproval($approval, $data['decision'], $request->user()->id, $data['comments'] ?? null);

        return redirect()->back()->with('success', "Approval {$data['decision']}.");
    }
}
