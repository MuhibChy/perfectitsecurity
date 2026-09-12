@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name . ' — Admin Portal')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-page-header
        :title="'Edit User: ' . $user->name"
        subtitle="Modify role privileges, personal details, or account accessibility."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Users' => route('admin.users.index'), 'Edit' => null]"
    />

    <div class="glass-card p-8 lg:p-10 rounded-2xl shadow-xl border border-white/10">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Role Assignment</label>
                    <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        @foreach(['customer' => 'Customer (Client Portal)', 'support_agent' => 'Support Agent', 'project_manager' => 'Project Manager', 'finance_manager' => 'Finance Manager', 'employee' => 'Employee / Engineer', 'freelancer' => 'Freelancer / Contractor', 'admin' => 'System Administrator'] as $rKey => $rLabel)
                        <option value="{{ $rKey }}" {{ old('role', $user->role) === $rKey ? 'selected' : '' }}>{{ $rLabel }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-white/5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Reset Password (Leave blank to keep unchanged)</h4>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                        <input type="password" name="password" class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 rounded-xl border border-surface-300 dark:border-white/10 bg-white dark:bg-navy-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Account is Active</span>
                </label>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">Cancel</a>
                    <button type="submit" class="btn-primary btn-sm px-6">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
