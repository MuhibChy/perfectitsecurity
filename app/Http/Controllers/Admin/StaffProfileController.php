<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\UserSetting;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

/**
 * Staff self-profile (Phase 3). Whitelisted personal fields only — role,
 * permissions, salary, employment status, employee number and audit history
 * are never writable here (server-side, not UI-only).
 */
class StaffProfileController extends Controller
{
    use \App\Http\Controllers\Concerns\ResolvesPhoneInput;
    public function edit()
    {
        $user = auth()->user();
        $user->loadMissing('profileDetail');
        return view('admin.profile.edit', [
            'user' => $user,
            'completion' => app(\App\Services\ProfileCompletionService::class)->for($user),
            'contactMethods' => \App\Models\ProfileDetail::CONTACT_METHODS,
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'timezone' => 'nullable|timezone:all',
            'preferred_locale' => 'nullable|string|max:10',
            'preferred_currency' => 'nullable|string|size:3',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // Self-editable professional fields only (§13). HR-owned data
            // (team, manager, dates, employment status, internal notes) is
            // never writable here — administration only.
            'secondary_phone' => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'preferred_contact_method' => 'nullable|in:website,email,phone,whatsapp,video,sms',
            'contact_hours' => 'nullable|string|max:100',
            'availability_note' => 'nullable|string|max:255',
            'skills' => 'nullable|array|max:30',
            'skills.*' => 'nullable|string|max:80',
            'skills_text' => 'nullable|string|max:2000',
            'certifications' => 'nullable|array|max:30',
            'certifications.*' => 'nullable|string|max:120',
            'expertise' => 'nullable|array|max:30',
            'expertise.*' => 'nullable|string|max:120',
        ]);
        $avatar = $request->file('avatar');
        unset($validated['avatar']);
        $resolvedPhone = $this->resolvePhoneInput($request);
        if ($resolvedPhone !== null) {
            $validated['phone'] = $resolvedPhone;
        }
        if (!empty($validated['preferred_currency'])) {
            $code = strtoupper($validated['preferred_currency']);
            abort_unless(\App\Services\Money::isActive($code), 422, 'Unsupported currency.');
            $validated['preferred_currency'] = $code;
        }
        $detailKeys = ['secondary_phone', 'whatsapp_number', 'preferred_contact_method', 'contact_hours', 'availability_note', 'skills', 'certifications', 'expertise'];
        $detailData = [];
        foreach ($detailKeys as $k) {
            if (array_key_exists($k, $validated)) $detailData[$k] = $validated[$k];
            unset($validated[$k]);
        }
        // Free-text skills box (one per line) maps onto the skills array.
        if (array_key_exists('skills_text', $validated)) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $validated['skills_text']);
            $detailData['skills'] = array_values(array_filter(array_map(fn ($s) => mb_substr(trim($s), 0, 80), $lines)));
            unset($validated['skills_text']);
        }
        $user->update($validated);
        if ($detailData !== []) {
            $user->profileDetail()->updateOrCreate(['user_id' => $user->id], $detailData);
        }
        if ($avatar) {
            if ($user->avatar) Storage::disk('private')->delete($user->avatar);
            $user->update(['avatar' => $avatar->store('avatars', 'private')]);
        }
        AuditLog::log('staff.profile_updated', 'users', $user, "Staff profile updated by {$user->name}.");
        return back()->with('success', 'Profile updated.');
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'theme' => 'nullable|in:dark,light,system',
            'locale' => 'nullable|string|max:10',
            'dashboard_layout' => 'nullable|string|max:50',
            'email_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',
        ]);
        foreach (['theme' => 'string', 'locale' => 'string', 'dashboard_layout' => 'string', 'email_notifications' => 'bool', 'sms_notifications' => 'bool'] as $k => $t) {
            if (array_key_exists($k, $data)) UserSetting::set($user, 'preferences', $k, $data[$k], $t);
        }
        AuditLog::log('staff.settings_updated', 'users', $user, "Staff settings updated by {$user->name}.");
        return back()->with('success', 'Settings saved.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate(['current_password' => 'required', 'password' => ['required', 'confirmed', Password::min(8)]]);
        $user = auth()->user();
        abort_unless(Hash::check($request->current_password, $user->password), 422, 'Current password is incorrect.');
        $user->update(['password' => Hash::make($request->password)]);
        AuditLog::log('staff.password_changed', 'users', $user, 'Staff password changed.');
        try {
            \Illuminate\Support\Facades\Auth::logoutOtherDevices($request->password);
        } catch (\Throwable $e) {
        }
        return back()->with('success', 'Password updated.');
    }

    /** My work: assigned tasks, contributions, timeline (own record only). */
    public function myWork()
    {
        $user = auth()->user();
        // Lifetime earnings from authoritative rows only (§6/§63): paid
        // salaries + commission lifecycle states. Customer payments and
        // company revenue are NEVER counted as employee income.
        $salaryPaid = (float) $user->salaries()->where('status', 'paid')->sum('net_salary');
        $salaryPending = (float) $user->salaries()->whereIn('status', ['pending', 'approved'])->sum('net_salary');
        $commissions = app(\App\Services\CommissionService::class)->getWorkerEarnings($user->id);
        return view('admin.profile.work', [
            'user' => $user,
            'overview' => TraceabilityService::employeeOverview($user),
            'timeline' => TraceabilityService::employeeTimeline($user, 100),
            'links' => TraceabilityService::employeeCustomerLinks($user),
            'contributions' => $user->contributedTasks()->with(['customer', 'serviceOrder'])->limit(50)->get(),
            'earnings' => [
                'salary_paid' => round($salaryPaid, 2),
                'salary_pending' => round($salaryPending, 2),
                'commission_paid' => round($commissions['paid'] ?? 0, 2),
                'commission_pending' => round(($commissions['pending'] ?? 0) + ($commissions['submitted'] ?? 0) + ($commissions['under_review'] ?? 0) + ($commissions['approved'] ?? 0) + ($commissions['payable'] ?? 0), 2),
                'lifetime_paid' => round($salaryPaid + (float) ($commissions['paid'] ?? 0), 2),
            ],
        ]);
    }
}
