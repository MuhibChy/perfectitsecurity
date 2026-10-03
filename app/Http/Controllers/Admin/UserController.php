<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use \App\Http\Controllers\Concerns\ResolvesPhoneInput;

    public function index(Request $request)
    {
        $query = User::with('company');
        if ($request->role) {
            $query->where('role', $request->role);
        }
        if ($request->status === 'pending') {
            $query->where('is_active', false)->whereNull('approved_at');
        } elseif ($request->status === 'approved') {
            $query->where('is_active', true)->whereNotNull('approved_at');
        } elseif ($request->status === 'suspended') {
            $query->where('is_active', false)->whereNotNull('approved_at');
        }
        if ($request->search) {
            $search = addcslashes($request->search, '%_\\');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,finance_manager,support_manager,support_agent,project_manager,employee,freelancer,commission_agent,sales_agent,training_manager,customer',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $resolvedPhone = $this->resolvePhoneInput($request);
        if ($resolvedPhone !== null) {
            $validated['phone'] = $resolvedPhone;
        }

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created!');
    }

    public function show(User $user)
    {
        $user->load(['tickets', 'projects', 'commissions', 'invoices']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:super_admin,admin,finance_manager,support_manager,support_agent,project_manager,employee,freelancer,commission_agent,sales_agent,training_manager,customer',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $resolvedPhone = $this->resolvePhoneInput($request);
        if ($resolvedPhone !== null) {
            $validated['phone'] = $resolvedPhone;
        }
        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }
        // Financial history protection: refuse deletion while completed
        // wallet ledger rows exist; deactivate the account instead.
        $ledgerRows = \App\Models\WalletTransaction::whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))->where('status', 'completed')->count();
        if ($ledgerRows > 0) {
            return redirect()->back()->with('error', 'This user has wallet financial history and cannot be deleted. Deactivate the account instead — history is preserved.');
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    /** Approve a pending account: activates + records approver/timestamp. */
    public function approve(Request $request, User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot approve your own account.');
        $data = $request->validate(['approval_note' => 'nullable|string|max:1000']);
        $wasActive = $user->is_active;
        $user->update([
            'is_active' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $data['approval_note'] ?? null,
        ]);
        \App\Models\AuditLog::log('user.approved', 'users', $user, 'Account approved by '.auth()->user()->name.'.', ['is_active' => $wasActive], ['is_active' => true]);
        \App\Services\ServiceTrackingService::notify($user->id, 'account_approved', 'Account approved', 'Your PerfectITSecurity account has been approved. You can now use the platform.');

        return back()->with('success', "User {$user->name} approved.");
    }

    /** Reject a pending account: stays inactive with reason + audit. */
    public function reject(Request $request, User $user)
    {
        abort_if($user->id === auth()->id(), 403);
        $data = $request->validate(['approval_note' => 'nullable|string|max:1000']);
        $user->update([
            'is_active' => false,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $data['approval_note'] ?? 'Rejected by administrator.',
        ]);
        \App\Models\AuditLog::log('user.rejected', 'users', $user, 'Account rejected by '.auth()->user()->name.'.');

        return back()->with('success', "User {$user->name} rejected.");
    }

    /** Suspend an active account: deactivates, preserves all history. */
    public function suspend(Request $request, User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot suspend your own account.');
        $data = $request->validate(['approval_note' => 'nullable|string|max:1000']);
        $user->update([
            'is_active' => false,
            'approval_note' => $data['approval_note'] ?? 'Suspended by administrator.',
        ]);
        \App\Models\AuditLog::log('user.suspended', 'users', $user, 'Account suspended by '.auth()->user()->name.'.', ['is_active' => true], ['is_active' => false]);

        return back()->with('success', "User {$user->name} suspended. History preserved.");
    }
}
