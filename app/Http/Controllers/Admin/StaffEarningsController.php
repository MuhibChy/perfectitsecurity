<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionPayout;
use App\Services\AccountEarningsService;

/**
 * Employee earnings dashboard (§5): the authenticated staff member's OWN
 * figures only — salary / commission / project / bonus / deductions from
 * authoritative rows. Viewing another member's earnings is forbidden here
 * (managers use finance reports with their own gates).
 */
class StaffEarningsController extends Controller
{
    public function show(AccountEarningsService $earnings)
    {
        $user = auth()->user();
        abort_if($user->isCustomer(), 403);
        $user->loadMissing('compensation');
        return view('admin.earnings.show', [
            'user' => $user,
            'summary' => $earnings->forEmployee($user),
            'salaries' => $user->salaries()->latest()->paginate(10, ['*'], 'salaries_page'),
            'commissions' => $user->commissions()->with(['rule', 'project'])->latest()->paginate(10, ['*'], 'commissions_page'),
            'payouts' => CommissionPayout::where('worker_id', $user->id)->latest()->limit(10)->get(),
            'projects' => \App\Models\Project::where('project_manager_id', $user->id)
                ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id))
                ->with('customer')->latest()->limit(10)->get(),
        ]);
    }
}
