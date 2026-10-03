@extends('layouts.app')
@section('page-title', 'My Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header title="Profile Settings" subtitle="Manage your contact details, address and preferences." sys="CLIENT://PROFILE" num="08" />

    {{-- Profile completion (§2) --}}
    <div class="term-panel p-6">
        <div class="flex items-center justify-between gap-3 mb-2">
            <h2 class="text-sm font-bold font-mono tracking-[0.14em] uppercase text-slate-900 dark:text-white">Profile completion</h2>
            <span class="font-mono text-sm font-bold text-emerald-700 dark:text-accent-soft">{{ $completion['percent'] }}%</span>
        </div>
        <div class="h-2 rounded bg-slate-200 dark:bg-white/10 overflow-hidden" role="progressbar" aria-valuenow="{{ $completion['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion">
            <div class="h-full bg-emerald-600 dark:bg-accent" style="width: {{ $completion['percent'] }}%"></div>
        </div>
        @if(!empty($completion['missing']))
        <p class="mt-2 text-xs text-slate-600 dark:text-term-800">Missing: {{ implode(' · ', $completion['missing']) }}</p>
        @endif
        <p class="mt-1 font-mono text-[10px] tracking-[0.14em] uppercase text-slate-500 dark:text-term-700">Customer ID: {{ $user->member_number ?? ('CUS-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT)) }} · Joined {{ optional($user->created_at)->format('Y-m-d') }}</p>
    </div>

    <div class="term-panel p-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Profile Information</h2>

        @if(session('success'))
        <div class="term-alert term-alert-ok mb-6">
            <span class="term-alert-tag">OK</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('portal.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-4 mb-6">
                <img src="{{ $user->avatar_url }}" alt="Profile photo" class="h-16 w-16 rounded-full object-cover border border-slate-300 dark:border-white/10">
                <div>
                    <label class="term-field-label">Profile Photo (JPG/PNG/WebP, max 2 MB)</label>
                    <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="term-input text-sm">
                    @error('avatar') <p class="term-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="term-field-label">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="term-input" required>
                    @error('name') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="term-input" required>
                    @error('email') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <x-phone-input :selected="old('country', $user->country_code ?? null)" :national="old('national_number', '')"
                        label="Phone Number" hint="Current: {{ $user->phone ?: 'none' }} — changing it resets phone verification until re-verified." />
                    @error('phone') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" class="term-input">
                    @error('company_name') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="term-field-label">Address</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="term-input">
                </div>

                <div>
                    <label class="term-field-label">City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" class="term-input">
                </div>

                <div>
                    <label class="term-field-label">State / Province</label>
                    <input type="text" name="state" value="{{ old('state', $user->state) }}" class="term-input">
                </div>

                <div>
                    <label class="term-field-label">ZIP / Postal Code</label>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $user->zip_code) }}" class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Country</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" class="term-input">
                </div>

                <div>
                    <label class="term-field-label">Preferred Currency</label>
                    <select name="preferred_currency" class="term-input">
                        <option value="">— Default —</option>
                        {{-- Allowed set only: supported local currency + USD (central service). --}}
                        @foreach(app(\App\Services\CustomerCurrencyService::class)->detailsFor($user) as $c)
                        <option value="{{ $c['currency_code'] }}" {{ old('preferred_currency', $user->preferred_currency) === $c['currency_code'] ? 'selected' : '' }}>{{ $c['currency_code'] }} — {{ $c['currency_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="term-field-label">Secondary Phone</label>
                    <input type="text" name="secondary_phone" value="{{ old('secondary_phone', $user->profileDetail?->secondary_phone) }}" class="term-input" maxlength="30">
                </div>

                <div>
                    <label class="term-field-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $user->profileDetail?->whatsapp_number) }}" class="term-input" maxlength="30" placeholder="+44…">
                </div>

                <div>
                    <label class="term-field-label">Preferred Contact Method</label>
                    <select name="preferred_contact_method" class="term-input">
                        <option value="">— Select —</option>
                        @foreach($contactMethods as $m)
                        <option value="{{ $m }}" {{ old('preferred_contact_method', $user->profileDetail?->preferred_contact_method) === $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="term-field-label">Available Hours</label>
                    <input type="text" name="contact_hours" value="{{ old('contact_hours', $user->profileDetail?->contact_hours) }}" class="term-input" maxlength="100" placeholder="e.g. Mon–Fri 09:00–17:00 UTC">
                </div>

                <div class="sm:col-span-2">
                    <label class="term-field-label">Availability Note</label>
                    <input type="text" name="availability_note" value="{{ old('availability_note', $user->profileDetail?->availability_note) }}" class="term-input" maxlength="255">
                </div>

                <div class="sm:col-span-2">
                    <label class="term-field-label">Business Information</label>
                    <textarea name="business_info" rows="3" class="term-input" maxlength="2000">{{ old('business_info', $user->profileDetail?->business_info) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="term-btn">
                    Save Changes
                </button>
                <a href="{{ route('portal.dashboard') }}" class="term-btn term-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Account Status --}}
    <div class="term-panel p-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Account Status</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="term-panel-2 p-3 flex items-center gap-3">
                <span class="term-status-dot {{ $user->isEmailVerified() ? '' : 'term-status-dot-amber' }}"></span>
                <div>
                    <div class="text-sm font-medium text-slate-900 dark:text-white">Email Verification</div>
                    <div class="term-hint">{{ $user->isEmailVerified() ? 'Verified' : 'Pending Verification' }}</div>
                </div>
            </div>
            <div class="term-panel-2 p-3 flex items-center gap-3">
                <span class="term-status-dot {{ $user->isPhoneVerified() ? '' : 'term-status-dot-amber' }}"></span>
                <div>
                    <div class="text-sm font-medium text-slate-900 dark:text-white">Phone Verification</div>
                    <div class="term-hint">{{ $user->isPhoneVerified() ? 'Verified' : 'Pending Verification' }}</div>
                </div>
            </div>
            <div class="term-panel-2 p-3 flex items-center gap-3">
                <span class="term-status-dot {{ $user->isIdentityVerified() ? '' : 'term-status-dot-amber' }}"></span>
                <div>
                    <div class="text-sm font-medium text-slate-900 dark:text-white">Government ID Verification</div>
                    <div class="term-hint">{{ $user->isIdentityVerified() ? 'Verified' : ucfirst(str_replace('_', ' ', $user->identity_status ?? 'not started')) }}</div>
                </div>
            </div>
            <div class="term-panel-2 p-3 flex items-center gap-3">
                <span class="term-status-dot {{ $user->hasMfaEnabled() ? '' : 'term-status-dot-amber' }}"></span>
                <div>
                    <div class="text-sm font-medium text-slate-900 dark:text-white">Two-Factor Authentication</div>
                    <div class="term-hint">{{ $user->hasMfaEnabled() ? 'Enabled' : 'Disabled' }}</div>
                </div>
            </div>
        </div>
        @if(!$user->isIdentityVerified())
        <a href="{{ route('identity.index') }}" class="term-btn term-btn-ghost mt-4">Verify My Identity →</a>
        @endif
    </div>

    {{-- Transaction History (chronological: date · type · description · status · reference) --}}
    <div class="term-panel p-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Transaction History</h2>
        <p class="term-hint mb-4">Complete chronological record of your account — read-only. Entries are never deleted.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left font-mono text-[11px] tracking-[0.14em] uppercase text-slate-500">
                        <th class="py-2 pr-3">Date</th>
                        <th class="py-2 pr-3">Type</th>
                        <th class="py-2 pr-3">Description</th>
                        <th class="py-2 pr-3">Status</th>
                        <th class="py-2">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $entry)
                    <tr class="border-t border-slate-200 dark:border-white/10">
                        <td class="py-2 pr-3 whitespace-nowrap font-mono text-xs">{{ optional($entry['at'])->format('Y-m-d H:i') }}</td>
                        <td class="py-2 pr-3"><x-status-badge :status="$entry['type']" :label="str_replace('_', ' ', $entry['type'])" /></td>
                        <td class="py-2 pr-3">
                            @if(!empty($entry['url']))
                            <a href="{{ $entry['url'] }}" class="term-link">{{ $entry['description'] }}</a>
                            @else
                            {{ $entry['description'] }}
                            @endif
                        </td>
                        <td class="py-2 pr-3">{{ $entry['status'] ? str_replace('_', ' ', $entry['status']) : '—' }}</td>
                        <td class="py-2 font-mono text-xs">{{ $entry['reference'] ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-4 term-hint">No transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Change Password (same page; current password re-verified server-side) --}}
    <div class="term-panel p-8">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Change Password</h2>
        <form method="POST" action="{{ route('portal.profile.password.update') }}" class="grid sm:grid-cols-2 gap-4 max-w-xl">
            @csrf
            @method('PUT')
            <div class="sm:col-span-2">
                <label class="term-field-label">Current Password *</label>
                <input type="password" name="current_password" required autocomplete="current-password" class="term-input">
                @error('current_password') <p class="term-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="term-field-label">New Password (min 8) *</label>
                <input type="password" name="password" required autocomplete="new-password" class="term-input">
                @error('password') <p class="term-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="term-field-label">Confirm New Password *</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password" class="term-input">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="term-btn term-btn-ghost">Change Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
