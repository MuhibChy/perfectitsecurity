<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\ConfigurationItem;
use App\Models\RemoteSession;
use App\Models\ServiceAgreement;
use App\Models\SiteVisit;
use App\Services\AuditService;
use Illuminate\Http\Request;

/**
 * Customer portal ITSM views. Every query is scoped to the authenticated
 * customer — customers can never see another customer's assets, CIs,
 * sessions, visits, or agreements.
 */
class ItsmPortalController extends Controller
{
    public function assets(Request $request)
    {
        $assets = Asset::forCustomer($request->user()->id)->with('configurationItems')->latest()->paginate(15);

        return view('customer.itsm.assets', compact('assets'));
    }

    public function cis(Request $request)
    {
        $cis = ConfigurationItem::forCustomer($request->user()->id)->with('asset')->latest()->paginate(15);

        return view('customer.itsm.cis', compact('cis'));
    }

    public function agreements(Request $request)
    {
        $agreements = ServiceAgreement::forCustomer($request->user()->id)->latest()->paginate(15);

        return view('customer.itsm.agreements', compact('agreements'));
    }

    public function remoteIndex(Request $request)
    {
        $sessions = RemoteSession::forCustomer($request->user()->id)->with('technician', 'ticket')->latest()->paginate(15);

        return view('customer.itsm.remote', compact('sessions'));
    }

    public function remoteStore(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => 'nullable|exists:tickets,id',
            'provider' => 'required|in:support_link,teamviewer,anydesk,other',
            'scheduled_at' => 'nullable|date|after:now',
        ]);
        // Ownership check: ticket must belong to the requesting customer.
        if (! empty($data['ticket_id'])) {
            $ticket = \App\Models\Ticket::find($data['ticket_id']);
            if (! $ticket || (int) $ticket->customer_id !== (int) $request->user()->id) {
                return redirect()->back()->withErrors(['ticket_id' => 'Ticket not found.'])->withInput();
            }
        }
        $session = RemoteSession::create($data + ['customer_id' => $request->user()->id]);
        AuditService::log('remote.request', 'itsm', $session, "Remote session {$session->session_number} requested by customer");

        return redirect()->route('portal.itsm.remote')->with('success', 'Remote assistance requested. A technician will confirm a time.');
    }

    public function remoteConsent(Request $request, RemoteSession $session)
    {
        abort_unless((int) $session->customer_id === (int) $request->user()->id, 404);
        if (! in_array($session->status, ['requested', 'scheduled'], true)) {
            return redirect()->back()->withErrors(['status' => 'This session can no longer accept consent.']);
        }
        $session->update([
            'consent_given' => true,
            'consent_at' => now(),
            'consent_by' => $request->user()->id,
        ]);
        AuditService::log('remote.consent', 'itsm', $session->fresh(), "Customer consent recorded for {$session->session_number}");

        return redirect()->back()->with('success', 'Consent recorded. The technician may now start the session.');
    }

    public function visits(Request $request)
    {
        $visits = SiteVisit::forCustomer($request->user()->id)->with('technician', 'ticket')->latest()->paginate(15);

        return view('customer.itsm.visits', compact('visits'));
    }

    public function confirmVisit(Request $request, SiteVisit $visit)
    {
        abort_unless((int) $visit->customer_id === (int) $request->user()->id, 404);
        if ($visit->status !== 'completed') {
            return redirect()->back()->withErrors(['status' => 'Only completed visits can be confirmed.']);
        }
        $data = $request->validate(['customer_signature_name' => 'required|string|max:255']);
        $visit->update([
            'customer_signature_name' => $data['customer_signature_name'],
            'customer_confirmed_at' => now(),
        ]);
        AuditService::log('visit.confirm', 'itsm', $visit->fresh(), "Visit {$visit->visit_number} confirmed by customer");

        return redirect()->back()->with('success', 'Visit confirmed. Thank you.');
    }
}
