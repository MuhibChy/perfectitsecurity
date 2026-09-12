<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Commission::with('worker', 'rule', 'task', 'customer');
        if ($request->status) $query->where('status', $request->status);
        if ($request->worker_id) $query->where('worker_id', $request->worker_id);
        $commissions = $query->latest()->paginate(20);

        $workers = User::whereIn('role', ['freelancer', 'commission_agent'])->get();
        return view('admin.commissions.index', compact('commissions', 'workers'));
    }

    public function show($id)
    {
        $commission = Commission::with('worker', 'rule', 'task', 'customer', 'approver')->findOrFail($id);
        return view('admin.commissions.show', compact('commission'));
    }

    public function approve($id)
    {
        $commission = Commission::findOrFail($id);
        app(CommissionService::class)->approveCommission($commission, auth()->id());
        return redirect()->back()->with('success', 'Commission approved!');
    }

    public function reject($id, Request $request)
    {
        $commission = Commission::findOrFail($id);
        app(CommissionService::class)->rejectCommission($commission, auth()->id(), $request->notes);
        return redirect()->back()->with('info', 'Commission rejected.');
    }

    public function payout(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|exists:users,id',
            'commission_ids' => 'required|array|min:1',
        ]);

        app(CommissionService::class)->processPayout(
            $request->worker_id,
            $request->commission_ids,
            $request->payment_method ?? 'bank_transfer'
        );

        return redirect()->route('admin.commissions.index')->with('success', 'Payout processed!');
    }
}
