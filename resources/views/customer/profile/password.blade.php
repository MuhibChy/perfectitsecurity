@extends('layouts.app')
@section('page-title', 'Change Password')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <x-page-header title="Change Password" subtitle="Rotate your account credential." sys="CLIENT://PROFILE" />
    <div class="term-panel p-8">
        <form method="POST" action="{{ route('portal.profile.password.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="term-field-label">Current Password *</label>
                    <input type="password" name="current_password" class="term-input" required>
                    @error('current_password') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">New Password *</label>
                    <input type="password" name="password" class="term-input" required>
                    @error('password') <p class="term-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="term-field-label">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" class="term-input" required>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="term-btn">
                    Update Password
                </button>
                <a href="{{ route('portal.profile.edit') }}" class="term-btn term-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
