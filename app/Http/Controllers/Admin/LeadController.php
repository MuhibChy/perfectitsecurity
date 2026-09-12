<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\Country;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('assignee', 'country')->latest();
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('lead_number', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%");
            });
        }
        $leads = $query->paginate(20);
        return view('admin.leads.index', compact('leads'));
    }

    public function create()
    {
        $staff = User::staff()->active()->orderBy('name')->get();
        $countries = Country::active()->orderBy('sort_order')->get();
        return view('admin.leads.create', compact('staff', 'countries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
            'status' => 'required|in:new,contacted,qualified,proposal,won,lost',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'country_id' => 'nullable|exists:countries,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'next_follow_up_at' => 'nullable|date',
        ]);

        $lead = Lead::create($data);
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'subject' => 'Lead created',
            'body' => 'Lead created in CRM.',
        ]);

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead created.');
    }

    public function show(Lead $lead)
    {
        $lead->load('activities.user', 'assignee', 'country', 'proposals');
        $staff = User::staff()->active()->orderBy('name')->get();
        return view('admin.leads.show', compact('lead', 'staff'));
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => 'nullable|in:new,contacted,qualified,proposal,won,lost',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'next_follow_up_at' => 'nullable|date',
        ]);

        $oldStatus = $lead->status;
        $lead->update(array_filter($data, fn ($v) => !is_null($v)));

        if (!empty($data['status']) && $data['status'] !== $oldStatus) {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'subject' => 'Status changed',
                'body' => "{$oldStatus} → {$data['status']}",
            ]);
            if ($data['status'] === 'won') {
                $lead->update(['converted_at' => now()]);
            }
        }

        return back()->with('success', 'Lead updated.');
    }

    public function addActivity(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'type' => 'required|in:note,call,email,meeting',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Activity logged.');
    }
}
