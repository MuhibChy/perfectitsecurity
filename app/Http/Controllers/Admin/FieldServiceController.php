<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RemoteSession;
use App\Models\SiteVisit;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class FieldServiceController extends Controller
{
    // ---- Remote sessions ----

    public function remoteIndex(Request $request)
    {
        $sessions = RemoteSession::with('customer', 'technician', 'ticket')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('session_number', 'like', "%{$request->search}%"))
            ->latest()->paginate(20);
        $customers = User::where('role', 'customer')->where('is_active', true)->limit(200)->get();
        return view('admin.field.remote-index', compact('sessions', 'customers'));
    }

    public function remoteStore(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => 'nullable|exists:tickets,id',
            'customer_id' => 'required|exists:users,id',
            'technician_id' => 'nullable|exists:users,id',
            'provider' => 'required|in:support_link,teamviewer,anydesk,other',
            'session_url' => 'nullable|url|max:500',
            'scheduled_at' => 'nullable|date',
        ]);
        $session = RemoteSession::create($data);
        AuditService::log('remote.create', 'itsm', $session, "Remote session {$session->session_number} scheduled");

        return redirect()->route('admin.remote.show', $session)->with('success', 'Remote session recorded.');
    }

    public function remoteShow(RemoteSession $session)
    {
        $session->load('customer', 'technician', 'ticket', 'consenter');
        $technicians = User::whereIn('role', ['support_agent', 'support_manager', 'admin', 'super_admin', 'technician', 'employee'])
            ->where('is_active', true)->get();
        return view('admin.field.remote-show', compact('session', 'technicians'));
    }

    public function remoteUpdate(Request $request, RemoteSession $session)
    {
        $data = $request->validate([
            'technician_id' => 'nullable|exists:users,id',
            'provider' => 'sometimes|in:support_link,teamviewer,anydesk,other',
            'session_url' => 'nullable|url|max:500',
            'status' => 'sometimes|in:requested,scheduled,active,completed,expired,cancelled',
            'scheduled_at' => 'nullable|date',
            'outcome' => 'nullable|string',
        ]);
        // Authorisation gate: a session cannot go active without recorded customer consent.
        if (($data['status'] ?? null) === 'active' && !$session->consent_given) {
            return redirect()->back()->withErrors(['status' => 'Customer consent is required before starting a remote session.']);
        }
        if (($data['status'] ?? null) === 'active' && empty($data['technician_id'] ?? $session->technician_id)) {
            return redirect()->back()->withErrors(['technician_id' => 'An authorised technician must be assigned.']);
        }
        $old = $session->only(array_keys($data));
        $session->update($data);
        if (($data['status'] ?? null) === 'active' && !$session->started_at) {
            $session->update(['started_at' => now()]);
        }
        if (in_array($data['status'] ?? null, ['completed', 'expired', 'cancelled'], true) && !$session->ended_at) {
            $session->update(['ended_at' => now()]);
        }
        AuditService::log('remote.update', 'itsm', $session->fresh(), "Remote session {$session->session_number} updated", $old, $data);

        return redirect()->back()->with('success', 'Remote session updated.');
    }

    // ---- Site visits ----

    public function visitIndex(Request $request)
    {
        $visits = SiteVisit::with('customer', 'technician', 'ticket')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where('visit_number', 'like', "%{$request->search}%"))
            ->latest()->paginate(20);
        $customers = User::where('role', 'customer')->where('is_active', true)->limit(200)->get();
        return view('admin.field.visit-index', compact('visits', 'customers'));
    }

    public function visitStore(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => 'nullable|exists:tickets,id',
            'customer_id' => 'required|exists:users,id',
            'address' => 'required|string|max:500',
            'scheduled_at' => 'nullable|date',
            'technician_id' => 'nullable|exists:users,id',
        ]);
        if (!empty($data['ticket_id'])) {
            $ticket = Ticket::find($data['ticket_id']);
            if ($ticket && (int) $ticket->customer_id !== (int) $data['customer_id']) {
                return redirect()->back()->withErrors(['ticket_id' => 'Ticket does not belong to this customer.'])->withInput();
            }
        }
        $visit = SiteVisit::create($data);
        AuditService::log('visit.create', 'itsm', $visit, "Site visit {$visit->visit_number} scheduled");

        return redirect()->route('admin.visits.show', $visit)->with('success', 'Site visit scheduled.');
    }

    public function visitShow(SiteVisit $visit)
    {
        $visit->load('customer', 'technician', 'ticket');
        $technicians = User::whereIn('role', ['support_agent', 'support_manager', 'admin', 'super_admin', 'technician', 'employee'])
            ->where('is_active', true)->get();
        return view('admin.field.visit-show', compact('visit', 'technicians'));
    }

    public function visitUpdate(Request $request, SiteVisit $visit)
    {
        $data = $request->validate([
            'technician_id' => 'nullable|exists:users,id',
            'address' => 'sometimes|string|max:500',
            'scheduled_at' => 'nullable|date',
            'status' => 'sometimes|in:scheduled,en_route,on_site,completed,cancelled',
            'work_performed' => 'nullable|string',
            'parts_used' => 'nullable|string',
            'follow_up_notes' => 'nullable|string',
        ]);
        $old = $visit->only(array_keys($data));
        $visit->update($data);
        if (($data['status'] ?? null) === 'on_site' && !$visit->check_in_at) {
            $visit->update(['check_in_at' => now()]);
        }
        if (($data['status'] ?? null) === 'completed' && !$visit->check_out_at) {
            $visit->update(['check_out_at' => now()]);
        }
        AuditService::log('visit.update', 'itsm', $visit->fresh(), "Site visit {$visit->visit_number} updated", $old, $data);

        return redirect()->back()->with('success', 'Site visit updated.');
    }
}
