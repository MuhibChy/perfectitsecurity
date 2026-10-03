<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CallLog;
use App\Models\EmployeeAssignment;
use App\Models\EmployeeCompensation;
use App\Models\ProfileDetail;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\CallLogService;
use App\Services\DirectoryService;
use Illuminate\Http\Request;

/**
 * Admin profile/assignment/call management (§14). All routes live in the
 * staff boundary; every action re-checks its capability gate server-side:
 * - compensation → finance managers (§4, salary-grade privacy)
 * - assignments → admins / support / project managers (§15)
 * - calls → any staff for own-involved records; managers see all (§10)
 * - directory → staff; compensation labels stay finance-only (§11, §18)
 */
class AccountEcosystemController extends Controller
{
    // ── Compensation (§4, §16) ──────────────────────────────────
    public function editCompensation(User $user)
    {
        $me = auth()->user();
        abort_unless($me->isFinanceManager(), 403);
        abort_if($user->isCustomer(), 422, 'Customers do not hold compensation models.');
        $user->loadMissing('compensation');
        return view('admin.ecosystem.compensation', [
            'employee' => $user,
            'compensation' => $user->compensation,
            'rules' => \App\Models\CommissionRule::where('is_active', true)->orderBy('name')->get(),
            'methods' => ProfileDetail::CONTACT_METHODS,
        ]);
    }

    public function updateCompensation(Request $request, User $user)
    {
        $me = auth()->user();
        abort_unless($me->isFinanceManager(), 403);
        abort_if($user->isCustomer(), 422, 'Customers do not hold compensation models.');

        $data = $request->validate([
            'has_salary' => 'nullable|boolean',
            'salary_amount' => 'nullable|numeric|min:0|max:10000000',
            'salary_frequency' => 'nullable|in:weekly,biweekly,monthly,yearly',
            'salary_start_date' => 'nullable|date',
            'has_commission' => 'nullable|boolean',
            'commission_type' => 'nullable|in:percentage,fixed',
            'commission_value' => 'nullable|numeric|min:0',
            'commission_rule_id' => 'nullable|exists:commission_rules,id',
            'has_project_pay' => 'nullable|boolean',
            'project_terms' => 'nullable|string|max:2000',
            'status' => 'required|in:active,suspended',
        ]);
        foreach (['has_salary', 'has_commission', 'has_project_pay'] as $flag) {
            $data[$flag] = !empty($data[$flag]);
        }
        if (($data['commission_type'] ?? null) === 'percentage') {
            abort_if(($data['commission_value'] ?? 0) > 100, 422, 'Commission percentage cannot exceed 100.');
        }

        $comp = EmployeeCompensation::updateOrCreate(['user_id' => $user->id], $data + ['updated_by' => $me->id]);
        AuditLog::log('compensation.updated', 'employee_compensations', $comp, "Compensation model for {$user->name} set to {$comp->modelLabel()} by {$me->name}.");
        \App\Services\ServiceTrackingService::notify((int) $user->id, 'compensation_updated', 'Compensation updated', "Your compensation model is now: {$comp->modelLabel()}.");
        return back()->with('success', "Compensation model saved ({$comp->modelLabel()}).");
    }

    // ── Assignments (§15) ───────────────────────────────────────
    public function assignments(Request $request)
    {
        $this->authorizeAssignments(auth()->user());
        $q = EmployeeAssignment::with(['employee', 'assigner', 'assignable'])->latest();
        if ($request->filled('employee_id')) $q->where('employee_id', $request->input('employee_id'));
        if ($request->filled('status')) $q->where('status', $request->input('status'));
        return view('admin.ecosystem.assignments', [
            'assignments' => $q->paginate(20),
            'employees' => User::where('is_active', true)->whereNotIn('role', ['customer'])->orderBy('name')->limit(200)->get(),
            'types' => array_keys(AssignmentService::ASSIGNABLE),
        ]);
    }

    public function storeAssignment(Request $request, AssignmentService $assignments)
    {
        $this->authorizeAssignments(auth()->user());
        $data = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'assignable_type' => 'required|string',
            'assignable_id' => 'required|integer',
            'notes' => 'nullable|string|max:2000',
        ]);
        $assignment = $assignments->assign(auth()->user(), User::findOrFail($data['employee_id']), $data['assignable_type'], (int) $data['assignable_id'], $data['notes'] ?? null);
        return back()->with('success', "Assignment #{$assignment->id} created.");
    }

    public function transitionAssignment(Request $request, EmployeeAssignment $assignment, AssignmentService $assignments)
    {
        $this->authorizeAssignments(auth()->user());
        $data = $request->validate(['status' => 'required|in:completed,revoked']);
        $assignments->transition(auth()->user(), $assignment, $data['status']);
        return back()->with('success', "Assignment marked {$data['status']}.");
    }

    // ── Call logs (§10) ─────────────────────────────────────────
    public function calls(Request $request)
    {
        $me = auth()->user();
        $q = CallLog::with(['caller', 'recipient', 'related'])->latest();
        $canSeeAll = $me->isAdmin() || $me->isSupportManager() || $me->isProjectManager() || $me->isFinanceManager();
        if (!$canSeeAll) {
            $q->where(fn ($w) => $w->where('caller_id', $me->id)->orWhere('recipient_id', $me->id));
        }
        if ($request->filled('outcome')) $q->where('outcome', $request->input('outcome'));
        return view('admin.ecosystem.calls', ['calls' => $q->paginate(20), 'canSeeAll' => $canSeeAll]);
    }

    public function storeCall(Request $request, CallLogService $calls)
    {
        $me = auth()->user();
        $data = $request->validate([
            'caller_id' => 'nullable|exists:users,id',
            'recipient_id' => 'required|exists:users,id',
            'direction' => 'nullable|in:outbound,inbound',
            'started_at' => 'nullable|date',
            'duration_seconds' => 'nullable|integer|min:0|max:86400',
            'outcome' => 'required|in:' . implode(',', CallLog::OUTCOMES),
            'related_type' => 'nullable|string',
            'related_id' => 'nullable|integer',
            'subject' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_customer_visible' => 'nullable|boolean',
        ]);
        // Non-managers may only log calls they participate in.
        $caller = isset($data['caller_id']) ? User::findOrFail($data['caller_id']) : $me;
        if (!($me->isAdmin() || $me->isSupportManager() || $me->isProjectManager())) {
            abort_unless((int) $caller->id === (int) $me->id, 403, 'You may only log your own calls.');
        }
        $log = $calls->log($me, $caller, User::findOrFail($data['recipient_id']), $data);
        return back()->with('success', "Call logged ({$log->uuid}).");
    }

    // ── Staff directory (§11) ───────────────────────────────────
    public function directory(DirectoryService $directory)
    {
        $staff = $directory->staffCards(auth()->user());
        $customers = auth()->user()->isAdmin() ? $directory->customerCards(auth()->user()) : null;
        return view('admin.ecosystem.directory', compact('staff', 'customers'));
    }

    public function directoryShow(User $user, DirectoryService $directory)
    {
        $me = auth()->user();
        abort_if($user->isCustomer() && !$me->isStaff(), 403);
        $user->loadMissing(['profileDetail.manager', 'compensation']);
        $card = $directory->publicCard($user);
        $financeSeesPay = $me->isFinanceManager();
        return view('admin.ecosystem.directory-show', [
            'member' => $user, 'card' => $card,
            'contact' => $directory->directContact($user),
            'compensationLabel' => $financeSeesPay ? ($user->compensation?->modelLabel() ?? 'Not configured') : null,
            'assignments' => $user->activeAssignments()->with('assignable')->latest()->limit(10)->get(),
        ]);
    }

    protected function authorizeAssignments(User $user): void
    {
        abort_unless($user->isAdmin() || $user->isSupportManager() || $user->isProjectManager(), 403);
    }
}
