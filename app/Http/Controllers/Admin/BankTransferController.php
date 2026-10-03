<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransfer;
use App\Services\BankTransferService;
use Illuminate\Http\Request;

/** Bank-transfer oversight (finance-gated). Read + state transitions only. */
class BankTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = BankTransfer::with(['beneficiary', 'franchise'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('purpose')) $query->where('purpose', $request->purpose);
        $transfers = $query->paginate(20)->withQueryString();
        return view('admin.transfers.index', compact('transfers'));
    }

    public function show(BankTransfer $transfer)
    {
        $transfer->load(['beneficiary', 'franchise']);
        return view('admin.transfers.show', compact('transfer'));
    }

    public function approve(BankTransfer $transfer, BankTransferService $svc)
    {
        return back()->with('success', 'Transfer ' . $svc->approve($transfer, auth()->user())->reference . ' approved.');
    }

    public function process(BankTransfer $transfer, BankTransferService $svc)
    {
        return back()->with('success', 'Transfer ' . $svc->markProcessing($transfer, auth()->user())->reference . ' sent for provider processing.');
    }

    public function complete(Request $request, BankTransfer $transfer, BankTransferService $svc)
    {
        $data = $request->validate(['external_reference' => 'required|string|max:255']);
        return back()->with('success', 'Transfer ' . $svc->complete($transfer, auth()->user(), $data['external_reference'])->reference . ' completed against provider reference.');
    }

    public function fail(Request $request, BankTransfer $transfer, BankTransferService $svc)
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $svc->fail($transfer, auth()->user(), $data['reason']);
        return back()->with('success', 'Transfer marked failed (audited, no money moved).');
    }

    public function cancel(BankTransfer $transfer, BankTransferService $svc)
    {
        $svc->cancel($transfer, auth()->user());
        return back()->with('success', 'Transfer cancelled.');
    }
}
