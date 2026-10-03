@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name . ' — Admin Portal')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header sys="SYSTEM://USERS"
        :title="'Edit User: ' . $user->name"
        subtitle="Modify role privileges, personal details, or account accessibility."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Users' => route('admin.users.index'), 'Edit' => null]"
    />

    <div class="term-panel p-8 lg:p-10 shadow-xl border border-white/10">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="term-input">
                    @error('name') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="term-input">
                    @error('email') <p class="term-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Role Assignment</label>
                    <select name="role" required class="term-input">
                        @foreach(['customer' => 'Customer (Client Portal)', 'support_agent' => 'Support Agent', 'project_manager' => 'Project Manager', 'finance_manager' => 'Finance Manager', 'employee' => 'Employee / Engineer', 'freelancer' => 'Freelancer / Contractor', 'admin' => 'System Administrator'] as $rKey => $rLabel)
                        <option value="{{ $rKey }}" {{ old('role', $user->role) === $rKey ? 'selected' : '' }}>{{ $rLabel }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <x-phone-input :selected="old('country', $user->country_code ?? null)" label="Phone Number" hint="Current: {{ $user->phone ?: 'none' }}{{ $user->isPhoneVerified() ? ' (verified)' : ' (unverified)' }}." />
                    @error('phone') <p class="term-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-white/5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Reset Password (Leave blank to keep unchanged)</h4>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="term-field-label">New Password</label>
                        <input type="password" name="password" class="term-input">
                        @error('password') <p class="term-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="term-field-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="term-input">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Account is Active</span>
                </label>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.users.index') }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                    <button type="submit" class="term-btn term-btn-sm">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
