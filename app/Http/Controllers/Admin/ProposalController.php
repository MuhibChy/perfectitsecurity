<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalSection;
use App\Models\ProposalVersion;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index()
    {
        $proposals = Proposal::with('customer', 'lead')->latest()->paginate(20);
        return view('admin.proposals.index', compact('proposals'));
    }

    public function create(Request $request)
    {
        $customers = User::customers()->active()->orderBy('name')->get();
        $leads = Lead::whereNotIn('status', ['won', 'lost'])->latest()->limit(100)->get();
        return view('admin.proposals.create', compact('customers', 'leads'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'scope_of_work' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'timeline' => 'nullable|string',
            'terms' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'valid_until' => 'nullable|date',
            'sections' => 'nullable|array',
            'sections.*.heading' => 'required_with:sections|string|max:255',
            'sections.*.body' => 'nullable|string',
        ]);

        $subtotal = (float) ($data['subtotal'] ?? 0);
        $taxRate = (float) ($data['tax_rate'] ?? 0);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);

        $proposal = Proposal::create([
            'title' => $data['title'],
            'customer_id' => $data['customer_id'] ?? null,
            'lead_id' => $data['lead_id'] ?? null,
            'created_by' => auth()->id(),
            'scope_of_work' => $data['scope_of_work'] ?? null,
            'deliverables' => $data['deliverables'] ?? null,
            'timeline' => $data['timeline'] ?? null,
            'terms' => $data['terms'] ?? null,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
            'currency' => $data['currency'] ?? 'USD',
            'valid_until' => $data['valid_until'] ?? now()->addDays(30),
            'status' => 'draft',
            'version' => 1,
        ]);

        foreach ($data['sections'] ?? [] as $i => $section) {
            ProposalSection::create([
                'proposal_id' => $proposal->id,
                'heading' => $section['heading'],
                'body' => $section['body'] ?? null,
                'sort_order' => $i,
            ]);
        }

        $this->snapshot($proposal);

        return redirect()->route('admin.proposals.show', $proposal)->with('success', 'Proposal created.');
    }

    public function show(Proposal $proposal)
    {
        $proposal->load('sections', 'versions', 'customer', 'lead');
        return view('admin.proposals.show', compact('proposal'));
    }

    public function update(Request $request, Proposal $proposal)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'scope_of_work' => 'nullable|string',
            'deliverables' => 'nullable|string',
            'timeline' => 'nullable|string',
            'terms' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,sent,viewed,accepted,rejected,revised',
        ]);

        if (isset($data['subtotal']) || isset($data['tax_rate'])) {
            $subtotal = (float) ($data['subtotal'] ?? $proposal->subtotal);
            $taxRate = (float) ($data['tax_rate'] ?? $proposal->tax_rate);
            $data['tax_amount'] = round($subtotal * ($taxRate / 100), 2);
            $data['total'] = $subtotal + $data['tax_amount'];
            $data['version'] = $proposal->version + 1;
            $data['status'] = $data['status'] ?? 'revised';
        }

        $proposal->update($data);
        $this->snapshot($proposal);

        return back()->with('success', 'Proposal updated.');
    }

    public function send(Proposal $proposal)
    {
        $proposal->update(['status' => 'sent', 'sent_at' => now()]);
        return back()->with('success', 'Proposal marked as sent.');
    }

    protected function snapshot(Proposal $proposal): void
    {
        ProposalVersion::create([
            'proposal_id' => $proposal->id,
            'version' => $proposal->version,
            'created_by' => auth()->id(),
            'snapshot' => $proposal->load('sections')->toArray(),
        ]);
    }
}
