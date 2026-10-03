<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Support\RoleRegistry;
use Illuminate\Http\Request;

/**
 * RoleTestingController — admin-only test-user management, role-request
 * approval, and the documented testing matrix.
 *
 * No impersonation: testers log in with credentials issued through the
 * protected channel. No passwords are ever displayed here.
 */
class RoleTestingController extends Controller
{
    public function index()
    {
        $testUsers = User::where('is_demo', true)->orderBy('role')->orderBy('name')->get();
        $pending = User::where('role_approval_status', 'pending')->whereNotNull('requested_role')->with('approver')->latest()->get();
        $roles = Role::orderBy('sort_order')->get();
        $matrix = $this->permissionMatrix();

        return view('admin.role-testing.index', compact('testUsers', 'pending', 'roles', 'matrix'));
    }

    /** Documented matrix derived from registry capabilities (reality, not wishes). */
    protected function permissionMatrix(): array
    {
        $features = [
            'portal.own' => 'Client Portal', 'tickets.handle' => 'Ticket handling',
            'projects.manage' => 'Project management', 'tasks.work' => 'Assigned tasks',
            'finance.manage' => 'Finance admin', 'crm.manage' => 'CRM / leads',
            'users.manage' => 'User management', 'settings.manage' => 'System settings',
            'content.manage' => 'Content / KB publishing', 'reports.view' => 'Reports / audit',
            'training.manage' => 'Training management', 'ai.use' => 'AI assistant',
        ];
        $roles = ['customer', 'support_agent', 'project_manager', 'finance_manager', 'employee', 'freelancer', 'admin'];
        $rows = [];
        foreach ($features as $cap => $label) {
            $row = ['feature' => $label];
            foreach ($roles as $role) {
                $row[$role] = in_array($cap, RoleRegistry::capabilitiesOf($role), true);
            }
            $rows[] = $row;
        }

        return ['roles' => $roles, 'rows' => $rows];
    }

    public function approve(Request $request, User $user)
    {
        abort_unless($user->hasPendingRoleRequest(), 404);
        $old = $user->role;
        $user->update([
            'role' => $user->requested_role,
            'requested_role' => null,
            'role_approval_status' => 'approved',
            'role_approved_by' => auth()->id(),
            'role_approved_at' => now(),
        ]);
        AuditLog::log('role.approved', 'roles', $user, "Role changed {$old} → {$user->role} (approved by ".auth()->user()->name.').', ['role' => $old], ['role' => $user->role]);

        return back()->with('success', "Role approved: {$user->name} is now ".RoleRegistry::displayName($user->role).'.');
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($user->hasPendingRoleRequest(), 404);
        $data = $request->validate(['reason' => 'nullable|string|max:500']);
        $requested = $user->requested_role;
        $user->update(['requested_role' => null, 'role_approval_status' => 'rejected', 'role_approved_by' => auth()->id(), 'role_approved_at' => now()]);
        AuditLog::log('role.rejected', 'roles', $user, "Role request rejected: {$requested}. ".($data['reason'] ?? ''));

        return back()->with('success', 'Role request rejected.');
    }

    public function toggleActive(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate your own account.');
        $user->update(['is_active' => ! $user->is_active]);
        AuditLog::log($user->is_active ? 'user.reactivated' : 'user.deactivated', 'roles', $user, 'Test account status changed to '.($user->is_active ? 'active' : 'inactive').'.');

        return back()->with('success', 'Account status updated.');
    }

    public function retire(Role $role)
    {
        // Safe deprecation: blocks new registrations/grants, keeps every
        // historical user and their records intact.
        $data = request()->validate(['status' => 'required|in:active,inactive,deprecated,archived']);
        if ($role->users()->exists() && in_array($data['status'], ['deprecated', 'archived'], true)) {
            // Allowed: users keep working; only new assignments stop.
        }
        $role->update(['status' => $data['status']]);
        AuditLog::log('role.status', 'roles', null, "Role {$role->name} status → {$data['status']}.");

        return back()->with('success', "Role {$role->display_name} marked {$data['status']}. Existing users are unaffected.");
    }
}
