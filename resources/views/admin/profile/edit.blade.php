@extends('layouts.app')
@section('page-title', 'My profile')
@section('content')

    <x-page-header title="My profile" sys="ADMIN://PROFILE" />
<div class="term-panel p-6">
    <h2 class="text-xl font-bold mb-1">My profile</h2>
    <p class="text-xs text-gray-500 mb-2">{{ $user->roleDisplayName() }} · {{ $user->email }} · Role, salary and employment status are managed by administration.</p>
    <p class="text-xs text-gray-500 mb-4">Identity: <strong>{{ $user->isIdentityVerified() ? 'VERIFIED' : strtoupper(str_replace('_', ' ', $user->identity_status ?? 'not started')) }}</strong> · 2FA: <strong>{{ $user->hasMfaEnabled() ? 'ENABLED' : 'DISABLED' }}</strong> · <img src="{{ $user->avatar_url }}" alt="Current profile photo" class="inline h-8 w-8 rounded-full object-cover align-middle"></p>
    <form method="POST" action="{{ route('admin.my-profile.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
        <label class="term-field-label">Display name</label><input name="name" value="{{ old('name', $user->name) }}" required class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="255" />
        <x-phone-input :selected="old('country', $user->country_code ?? null)" label="Phone" hint="Current: {{ $user->phone ?: 'none' }}." />
        <label class="term-field-label">Timezone</label><input name="timezone" value="{{ old('timezone', $user->timezone) }}" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="60" />
        <label class="term-field-label">Language</label><input name="preferred_locale" value="{{ old('preferred_locale', $user->preferred_locale) }}" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="10" />
        <label class="term-field-label">Profile photo</label><input type="file" name="avatar" accept="image/*" class="block mb-2 text-sm" />
        <div class="grid sm:grid-cols-2 gap-x-4">
            <div><label class="term-field-label">Secondary phone</label><input name="secondary_phone" value="{{ old('secondary_phone', $user->profileDetail?->secondary_phone) }}" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="30" /></div>
            <div><label class="term-field-label">WhatsApp</label><input name="whatsapp_number" value="{{ old('whatsapp_number', $user->profileDetail?->whatsapp_number) }}" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="30" /></div>
            <div><label class="term-field-label">Preferred contact</label>
                <select name="preferred_contact_method" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800">
                    <option value="">—</option>
                    @foreach($contactMethods as $m)<option value="{{ $m }}" {{ old('preferred_contact_method', $user->profileDetail?->preferred_contact_method) === $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>@endforeach
                </select>
            </div>
            <div><label class="term-field-label">Available hours</label><input name="contact_hours" value="{{ old('contact_hours', $user->profileDetail?->contact_hours) }}" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800" maxlength="100" /></div>
            <div class="sm:col-span-2"><label class="term-field-label">Skills (one per line)</label><textarea name="skills_text" rows="2" class="block w-full rounded border px-2 py-1 mb-2 dark:bg-gray-800">{{ old('skills_text', implode("\n", (array) ($user->profileDetail?->skills ?? []))) }}</textarea></div>
        </div>
        <button class="term-btn term-btn-sm">Save profile</button>
    </form>
    <div class="mt-6 pt-5 border-t border-slate-200 dark:border-white/10">
        <div class="flex items-center justify-between gap-3 mb-2">
            <h3 class="font-bold text-sm font-mono tracking-[0.14em] uppercase">Profile completion</h3>
            <span class="font-mono text-sm font-bold text-emerald-700 dark:text-accent-soft">{{ $completion['percent'] }}%</span>
        </div>
        <div class="h-2 rounded bg-slate-200 dark:bg-white/10 overflow-hidden" role="progressbar" aria-valuenow="{{ $completion['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion">
            <div class="h-full bg-emerald-600 dark:bg-accent" style="width: {{ $completion['percent'] }}%"></div>
        </div>
        @if(!empty($completion['missing']))
        <p class="mt-2 text-xs text-slate-600 dark:text-term-800">Missing: {{ implode(' · ', $completion['missing']) }}</p>
        @endif
    </div>
    <form method="POST" action="{{ route('admin.my-profile.password') }}" class="mt-6">@csrf
        <h3 class="font-bold text-sm mb-2">Change password</h3>
        <input type="password" name="current_password" placeholder="Current password" required class="block rounded border px-2 py-1 mb-2 dark:bg-gray-800" />
        <input type="password" name="password" placeholder="New password (min 8)" required class="block rounded border px-2 py-1 mb-2 dark:bg-gray-800" />
        <input type="password" name="password_confirmation" placeholder="Confirm" required class="block rounded border px-2 py-1 mb-2 dark:bg-gray-800" />
        <button class="px-3 py-1 rounded bg-gray-700 text-white text-sm">Update password</button>
    </form>
</div>
@endsection
