@extends('layouts.app')
@section('page-title', 'My Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="glass-card p-8">
        <h2 class="text-xl font-bold text-white mb-6">Profile Information</h2>

        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('portal.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                    @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="+1 (555) 000-0000">
                    @error('phone') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" class="form-input">
                    @error('company_name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">State / Province</label>
                    <input type="text" name="state" value="{{ old('state', $user->state) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">ZIP / Postal Code</label>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $user->zip_code) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Country</label>
                    <input type="text" name="country" value="{{ old('country', $user->country) }}" class="form-input">
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="btn bg-brand-600 text-white hover:bg-brand-700 rounded-xl px-6 py-2.5 font-semibold">
                    Save Changes
                </button>
                <a href="{{ route('portal.dashboard') }}" class="btn btn-ghost rounded-xl px-6 py-2.5">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Account Status --}}
    <div class="glass-card p-8">
        <h2 class="text-xl font-bold text-white mb-4">Account Status</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/5">
                <div class="w-3 h-3 rounded-full {{ $user->isEmailVerified() ? 'bg-emerald-400' : 'bg-amber-400' }}"></div>
                <div>
                    <div class="text-sm font-medium text-white">Email Verification</div>
                    <div class="text-xs text-slate-400">{{ $user->isEmailVerified() ? 'Verified' : 'Pending Verification' }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/5">
                <div class="w-3 h-3 rounded-full {{ $user->isPhoneVerified() ? 'bg-emerald-400' : 'bg-amber-400' }}"></div>
                <div>
                    <div class="text-sm font-medium text-white">Phone Verification</div>
                    <div class="text-xs text-slate-400">{{ $user->isPhoneVerified() ? 'Verified' : 'Pending Verification' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="glass-card p-8">
        <h2 class="text-xl font-bold text-white mb-4">Change Password</h2>
        <a href="{{ route('portal.profile.password') }}" class="btn btn-ghost rounded-xl px-6 py-2.5 border border-white/10 hover:border-cyan-400/40">
            Update Password
        </a>
    </div>
</div>
@endsection
