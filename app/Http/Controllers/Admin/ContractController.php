<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use App\Models\Proposal;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('customer')->latest()->paginate(20);
        return view('admin.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $customers = User::customers()->active()->orderBy('name')->get();
        $proposals = Proposal::whereIn('status', ['accepted', 'sent'])->latest()->limit(50)->get();
        return view('admin.contracts.create', compact('customers', 'proposals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'required|exists:users,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'body' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:draft,sent,active,expired,terminated',
        ]);

        $data['created_by'] = auth()->id();
        $contract = Contract::create($data);

        return redirect()->route('admin.contracts.show', $contract)->with('success', 'Contract created.');
    }

    public function show(Contract $contract)
    {
        $contract->load('customer', 'proposal');
        return view('admin.contracts.show', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'body' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,sent,active,expired,terminated',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'signed_by_name' => 'nullable|string|max:255',
        ]);

        if (($data['status'] ?? null) === 'active' && !$contract->signed_at) {
            $data['signed_at'] = now();
        }

        $contract->update($data);
        return back()->with('success', 'Contract updated.');
    }
}
