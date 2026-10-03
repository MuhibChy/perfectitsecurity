<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;

/**
 * Administrative People / Accounts area (§83-84).
 * One authoritative identity per person; this controller only READS and
 * aggregates existing relations (no duplicate profiles). Every section is
 * gated: financial sections require finance role, employee detail is
 * own-record or admin-only (mirrors TraceabilityController).
 */
class PeopleController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('franchise')->latest();
        if ($request->filled('role')) $query->where('role', $request->role);
        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }
        if ($request->filled('country')) $query->where('country', $request->country);
        if ($request->filled('branch')) $query->where('branch', $request->branch);
        if ($request->filled('department')) $query->where('department', $request->department);
        if ($request->filled('franchise_id')) $query->where('franchise_id', $request->franchise_id);
        if ($request->filled('search')) {
            $s = addcslashes($request->search, '%_\\');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('employee_number', 'like', "%{$s}%"));
        }
        $people = $query->paginate(20)->withQueryString();
        $roles = collect(\App\Support\RoleRegistry::definitions())->mapWithKeys(fn ($d) => [$d['name'] => $d['display_name'] ?? $d['name']])->all();
        return view('admin.people.index', compact('people', 'roles'));
    }

    /** 360° profile: sections rendered per viewer authorization. */
    public function show(User $user)
    {
        $viewer = auth()->user();
        $this->authorizeView($viewer, $user);

        $data = ['user' => $user->load('franchise', 'company', 'settings')];
        if ($user->isCustomer()) {
            $data['overview'] = TraceabilityService::customerOverview($user);
            $data['timeline'] = TraceabilityService::customerTimeline($user, 'admin', 100);
            $data['kind'] = 'customer';
        } else {
            abort_unless($viewer->id === $user->id || $viewer->isAdmin() || $viewer->isProjectManager() || $viewer->isFinanceManager() || $viewer->isSupportManager(), 403);
            $data['overview'] = TraceabilityService::employeeOverview($user);
            $data['timeline'] = TraceabilityService::employeeTimeline($user, 100);
            $data['links'] = TraceabilityService::employeeCustomerLinks($user);
            $data['kind'] = 'employee';
        }
        // Finance sections: finance role or admin only (never peers).
        $data['showFinance'] = $viewer->isFinanceManager();
        if ($data['showFinance']) {
            $data['salaries'] = $user->salaries()->latest()->limit(20)->get();
            $data['transfers'] = $user->bankTransfers()->latest()->limit(20)->get();
            $data['commissions'] = $user->commissions()->latest()->limit(20)->get();
            $data['payouts'] = \App\Models\CommissionPayout::where('worker_id', $user->id)->latest()->limit(10)->get();
        }
        $data['contributions'] = $user->contributedTasks()->with(['customer', 'serviceOrder'])->limit(50)->get();
        $data['messages'] = \App\Models\DirectMessage::with(['sender', 'recipient'])->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))->latest()->limit(10)->get();
        return view('admin.people.show', $data);
    }

    protected function authorizeView(User $viewer, User $target): void
    {
        // Customers' data stays inside staff boundary (route already staff-gated).
        // Employee private data: own record, admin, or authorized manager hierarchy.
        if ($target->isCustomer()) return;
        if ($viewer->id === $target->id || $viewer->isAdmin()) return;
        abort_unless($viewer->isProjectManager() || $viewer->isFinanceManager() || $viewer->isSupportManager(), 403);
    }
}
