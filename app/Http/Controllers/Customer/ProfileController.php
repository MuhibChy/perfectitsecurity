<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    use \App\Http\Controllers\Concerns\ResolvesPhoneInput;

    public function edit()
    {
        $user = auth()->user();
        $user->loadMissing('profileDetail');

        return view('customer.profile.edit', [
            'user' => $user,
            'completion' => app(\App\Services\ProfileCompletionService::class)->for($user),
            'contactMethods' => \App\Models\ProfileDetail::CONTACT_METHODS,
            'ledger' => \App\Services\TraceabilityService::profileLedger($user, 'portal', 50),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|timezone:all',
            'preferred_currency' => 'nullable|string|size:3',
            'preferred_locale' => 'nullable|string|max:10',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // Extended contact & communication preferences (§1A, §3).
            'secondary_phone' => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'preferred_contact_method' => 'nullable|in:website,email,phone,whatsapp,video,sms',
            'contact_hours' => 'nullable|string|max:100',
            'availability_note' => 'nullable|string|max:255',
            'business_info' => 'nullable|string|max:2000',
        ]);

        // Shared phone input ([country + national number] preferred, full
        // international `phone` accepted) → canonical E.164 or null.
        $resolvedPhone = $this->resolvePhoneInput($request);
        if ($resolvedPhone !== null) {
            $validated['phone'] = $resolvedPhone;
        }

        // Preferred currency must be an active catalog code — stored as code.
        // Customers are further limited to their own allowed set
        // (supported local currency + USD) via CustomerCurrencyService.
        if (! empty($validated['preferred_currency'])) {
            $code = strtoupper($validated['preferred_currency']);
            abort_unless(\App\Services\Money::isActive($code), 422, 'Unsupported currency.');
            if ($user->isCustomer()
                && ! app(\App\Services\CustomerCurrencyService::class)->allows($user, $code)) {
                abort(422, 'This currency is not available for your account.');
            }
            $validated['preferred_currency'] = $code;
        }

        // Keep the authoritative ISO country code in sync when the account
        // country text resolves to the registry (never invent a code).
        if (array_key_exists('country', $validated) && $validated['country'] !== null) {
            $resolved = app(\App\Services\CustomerCurrencyService::class)->resolveCountryCode((string) $validated['country']);
            if ($resolved) {
                $validated['country_code'] = $resolved;
            }
        }

        $avatarFile = $request->file('avatar');
        unset($validated['avatar']);

        // Split extended fields into the one-row-per-user profile_details.
        $detailKeys = ['secondary_phone', 'whatsapp_number', 'preferred_contact_method', 'contact_hours', 'availability_note', 'business_info'];
        $detailData = [];
        foreach ($detailKeys as $k) {
            if (array_key_exists($k, $validated)) {
                $detailData[$k] = $validated[$k];
            }
            unset($validated[$k]);
        }

        // Changing contact identity resets verification — the new address/number
        // is unverified until the owner re-verifies (prevents account takeover
        // via profile edit).
        $emailChanged = isset($validated['email']) && $validated['email'] !== $user->email;
        $phoneChanged = array_key_exists('phone', $validated) && $validated['phone'] !== $user->phone;
        if ($emailChanged) {
            $validated['email_verified_at'] = null;
        }
        if ($phoneChanged) {
            $validated['phone_verified_at'] = null;
        }

        $user->update($validated);
        if ($emailChanged) {
            \App\Models\AuditLog::log('profile.email_changed', 'users', $user, 'Email changed — re-verification required.');
        }
        if ($phoneChanged) {
            \App\Models\AuditLog::log('profile.phone_changed', 'users', $user, 'Mobile number changed — re-verification required.');
        }
        if ($detailData !== []) {
            $user->profileDetail()->updateOrCreate(['user_id' => $user->id], $detailData);
            \App\Models\AuditLog::log('profile.contact_updated', 'users', $user, 'Contact & communication preferences updated.');
        }

        // Avatar upload (private disk, validated image only; old file removed).
        if ($avatarFile) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($user->avatar);
            }
            $path = $avatarFile->store('avatars', 'private');
            $user->update(['avatar' => $path]);
            \App\Models\AuditLog::log('profile.avatar_updated', 'users', $user, 'Profile photo updated.');
        }

        return redirect()->route('portal.profile.edit')
            ->with('success', 'Your profile has been updated successfully.');
    }

    /** Per-user settings (theme, locale, notifications, dashboard). */
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
            if (array_key_exists($k, $data)) {
                \App\Models\UserSetting::set($user, 'preferences', $k, $data[$k], $t);
            }
        }
        \App\Models\AuditLog::log('profile.settings_updated', 'users', $user, 'Account settings updated.');

        return back()->with('success', 'Settings saved.');
    }

    public function editPassword()
    {
        return view('customer.profile.password', [
            'user' => auth()->user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);
        \App\Models\AuditLog::log('profile.password_changed', 'users', $user, 'Account password changed by owner.');
        // Keep only the current session: changing a password must log out
        // every other device/session holding the old credential.
        try {
            \Illuminate\Support\Facades\Auth::logoutOtherDevices($request->password);
        } catch (\Throwable $e) {
            // Non-database drivers or unhashable states: current session stays.
        }

        return redirect()->route('portal.profile.edit')
            ->with('success', 'Your password has been updated successfully.');
    }
}
