<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequest;
use App\Services\EmergencyService;
use Illuminate\Http\Request;

/** Emergency lane: any authenticated user may raise; only staff triage. */
class EmergencyController extends Controller
{
    public function __construct(private EmergencyService $service)
    {
    }

    public function index()
    {
        $me = auth()->user();
        $query = EmergencyRequest::with(['requester', 'assignee'])->latest();
        if (! $me->isStaff()) {
            $query->where('requester_id', $me->id);
        }
        $emergencies = $query->paginate(20);

        return view('emergency.index', compact('emergencies'));
    }

    public function create()
    {
        return view('emergency.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'severity' => 'required|in:CRITICAL,HIGH,NORMAL',
            'category' => 'nullable|string|max:100',
            'description' => 'required|string|min:10|max:2000',
        ]);
        $emergency = $this->service->raise(auth()->user(), $data);
        $route = auth()->user()->isCustomer() ? 'portal.emergency.show' : 'admin.emergency.show';

        return redirect()->route($route, $emergency->id)->with('success', "Emergency {$emergency->reference} logged and queued for triage.");
    }

    public function show($id)
    {
        $emergency = EmergencyRequest::with(['requester', 'assignee'])->findOrFail($id);
        $me = auth()->user();
        abort_unless($me->isStaff() || (int) $emergency->requester_id === (int) $me->id, 403);
        $staff = $me->isStaff() ? \App\Models\User::staff()->where('is_active', true)->orderBy('name')->limit(50)->get() : collect();

        return view('emergency.show', compact('emergency', 'staff'));
    }

    public function transition(Request $request, $id)
    {
        $data = $request->validate([
            'to' => 'required|in:acknowledged,assigned,in_progress,resolved,closed',
            'assignee_id' => 'nullable|exists:users,id',
        ]);
        $emergency = EmergencyRequest::findOrFail($id);
        $updated = $this->service->transition($emergency, $data['to'], auth()->user(), $data['assignee_id'] ?? null);

        return back()->with('success', "Emergency {$updated->reference} is now {$updated->status}.");
    }
}
