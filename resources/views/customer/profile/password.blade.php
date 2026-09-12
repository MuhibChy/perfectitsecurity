@extends('layouts.app')
@section('page-title', 'Change Password')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <div class="glass-card p-8">
        <h2 class="text-xl font-bold text-white mb-6">Change Password</h2>

        <form method="POST" action="{{ route('portal.profile.password.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="form-label">Current Password *</label>
                    <input type="password" name="current_password" class="form-input" required>
                    @error('current_password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">New Password *</label>
                    <input type="password" name="password" class="form-input" required>
                    @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="btn bg-brand-600 text-white hover:bg-brand-700 rounded-xl px-6 py-2.5 font-semibold">
                    Update Password
                </button>
                <a href="{{ route('portal.profile.edit') }}" class="btn btn-ghost rounded-xl px-6 py-2.5">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
