@extends('layouts.app')

@section('title', 'Register New User — Admin Portal')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header sys="SYSTEM://USERS"
        title="Create User Account"
        subtitle="Provision a new administrative, engineering, client, or contractor identity."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Users' => route('admin.users.index'), 'Create' => null]"
    />

    <div class="term-panel p-8 lg:p-10 shadow-xl border border-white/10">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="term-input">
                    @error('name') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="term-input">
                    @error('email') <p class="term-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Role Assignment</label>
                    <select name="role" required class="term-input">
                        <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>Customer (Client Portal)</option>
                        <option value="support_agent" {{ old('role') === 'support_agent' ? 'selected' : '' }}>Support Agent (Ticket Handling)</option>
                        <option value="project_manager" {{ old('role') === 'project_manager' ? 'selected' : '' }}>Project Manager</option>
                        <option value="finance_manager" {{ old('role') === 'finance_manager' ? 'selected' : '' }}>Finance Manager</option>
                        <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee / Engineer</option>
                        <option value="freelancer" {{ old('role') === 'freelancer' ? 'selected' : '' }}>Freelancer / Contractor</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>System Administrator</option>
                    </select>
                    @error('role') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <x-phone-input label="Phone Number" hint="Stored verified/unverified as entered; verification follows the standard OTP workflow." />
                    @error('phone') <p class="term-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="term-field-label">Initial Password</label>
                    <input type="password" name="password" required class="term-input">
                    @error('password') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="term-input">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Account Active immediately</span>
                </label>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.users.index') }}" class="term-btn term-btn-ghost term-btn-sm">Cancel</a>
                    <button type="submit" class="term-btn term-btn-sm">Create Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
